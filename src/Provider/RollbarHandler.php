<?php

namespace SymfonyRollbarBundle\Provider;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Rollbar\Rollbar as RollbarNotifier;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;
use Rollbar\Payload\Level;

class RollbarHandler extends AbstractProcessingHandler
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * Records whether any log records have been added since the last flush of the rollbar notifier
     * @var bool
     */
    private $hasRecords = false;

    /**
     * Monolog vs Rollbar
     * @var array
     */
    protected $levelMap = [
        Logger::DEBUG     => Level::DEBUG,
        Logger::INFO      => Level::INFO,
        Logger::NOTICE    => Level::NOTICE,
        Logger::WARNING   => Level::WARNING,
        Logger::ERROR     => Level::ERROR,
        Logger::CRITICAL  => Level::CRITICAL,
        Logger::ALERT     => Level::ALERT,
        Logger::EMERGENCY => Level::EMERGENCY,
    ];

    /**
     * RollbarHandler constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param bool|int                                                  $level
     * @param bool                                                      $bubble
     */
    public function __construct(ContainerInterface $container, $level = Logger::ERROR, $bubble = true)
    {
        $this->container = $container;
        parent::__construct($level, $bubble);

        // init notifier
        $config = $this->getContainer()->getParameter(SymfonyRollbarExtension::ALIAS . '.config');
        $kernel = $container->get('kernel');

        $rConfig  = $config['rollbar'];
        $override = [
            'root'      => $kernel->getRootDir(),
            'framework' => \Symfony\Component\HttpKernel\Kernel::VERSION,
        ];

        foreach ($override as $key => $value) {
            $rConfig[$key] = $value;
        }

        RollbarNotifier::init($rConfig, false, false, false);
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        if (!$this->initialized) {
            // __destructor() doesn't get called on Fatal errors
            register_shutdown_function([$this, 'close']);
            $this->initialized = true;
        }

        $context = $record['context'];
        $payload = [];
        if (isset($context['payload'])) {
            $payload = $context['payload'];
            unset($context['payload']);
        }

        $context = array_merge($context, $record['extra'], [
            'level'         => $this->levelMap[$record['level']],
            'monolog_level' => $record['level_name'],
            'channel'       => $record['channel'],
            'datetime'      => $record['datetime']->format('U'),
        ]);

        if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
            $payload['level'] = $context['level'];
            $exception        = $context['exception'];
            unset($context['exception']);

            RollbarNotifier::log(Level::ERROR, $exception, $payload);
        } else {
            RollbarNotifier::log($context['level'], $record['message'], $payload);
        }

        $this->hasRecords = true;
    }

    public function flush()
    {
        if ($this->hasRecords) {
            RollbarNotifier::flush();
            $this->hasRecords = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->flush();
    }
}

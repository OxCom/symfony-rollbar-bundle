<?php

namespace SymfonyRollbarBundle\Provider;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Rollbar\Rollbar as RollbarNotifier;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;
use Rollbar\Payload\Level;
use SymfonyRollbarBundle\Provider\Api\Filter;

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
    protected $hasRecords = false;

    /**
     * @var array
     */
    protected $exclude;

    /**
     * @var array
     */
    protected $rbConfig;

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

        $this->rbConfig = $this->initialize();

        if (!empty($this->rbConfig)) {
            RollbarNotifier::init($this->rbConfig, false, false, false);
        }
    }

    /**
     * @return array
     */
    protected function initialize()
    {
        try {
            $config = $this->getContainer()->getParameter(SymfonyRollbarExtension::ALIAS . '.config');
        } catch (\Exception $e) {
            return null;
        }

        $kernel   = $this->container->get('kernel');
        $rConfig  = $config['rollbar'];
        $override = [
            'root'      => $kernel->getRootDir(),
            'framework' => 'Symfony ' . \Symfony\Component\HttpKernel\Kernel::VERSION,
        ];

        foreach ($override as $key => $value) {
            $rConfig[$key] = $value;
        }

        // DI for 'person_fn'
        if (!empty($rConfig['person_fn'])) {
            if ($this->container->has($rConfig['person_fn'])) {
                $service = $this->container->get($rConfig['person_fn']);
            } elseif (class_exists($rConfig['person_fn'])) {
                $service = new $rConfig['person_fn']($this->container);
            }

            if (!empty($service) && $service instanceof AbstractPersonProvider) {
                $rConfig['person_fn'] = [$service, 'getPerson'];
            } else {
                $rConfig['person_fn'] = is_callable($rConfig['person_fn']) ? $rConfig['person_fn'] : null;
            }
        }

        $this->exclude = empty($config['exclude']) ? [] : $config['exclude'];

        return $rConfig;
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
        if (empty($this->rbConfig)) {
            return;
        }

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

        if (isset($context['exception'])
            && ($context['exception'] instanceof \Exception || $context['exception'] instanceof \Throwable)
        ) {
            $payload['level'] = $context['level'];
            $exception        = $context['exception'];
            unset($context['exception']);

            if ($this->shouldSkip($exception)) {
                return;
            }

            RollbarNotifier::log(Level::ERROR, $exception, $payload);
        } else {
            RollbarNotifier::log($context['level'], $record['message'], $payload);
        }

        $this->hasRecords = true;
    }

    /**
     * @param \Throwable $exception
     *
     * @return bool
     */
    public function shouldSkip($exception)
    {
        // check exception
        foreach ($this->exclude as $instance) {
            if ((class_exists($instance) || interface_exists($instance)) && $exception instanceof $instance) {
                return true;
            }
        }

        return false;
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

    /**
     * Track new build with Rollbar
     *
     * @param string $environment
     * @param string $revision
     * @param string $comment
     * @param string $rollbarUser
     * @param string $localUser
     *
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    public function trackBuild($environment, $revision, $comment = '', $rollbarUser = '', $localUser = '')
    {
        // There is no API in Rollbar SDK for tracking builds
        if (empty($this->rbConfig)) {
            return null;
        }

        /** @var \SymfonyRollbarBundle\Provider\ApiClient $client */
        $client = $this->getContainer()->get('symfony_rollbar.provider.api_client');

        // truncate payload according to limits
        $payload = [
            'access_token'     => $this->rbConfig['access_token'],
            'environment'      => Filter::process($environment, Filter\Length::class),
            'revision'         => Filter::process($revision, Filter\Length::class),
            // @link https://stackoverflow.com/questions/4420164/how-much-utf-8-text-fits-in-a-mysql-text-field
            'comment'          => Filter::process($comment, Filter\Length::class, ['max' => 21800]),
            'rollbar_username' => Filter::process($rollbarUser, Filter\Length::class),
            'local_username'   => Filter::process($localUser, Filter\Length::class),
        ];

        return $client->trackBuild($payload);
    }
}

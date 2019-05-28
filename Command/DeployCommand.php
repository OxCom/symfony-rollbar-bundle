<?php

namespace SymfonyRollbarBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DeployCommand
 *
 * @package SymfonyRollbarBundle\Command
 */
class DeployCommand extends Command
{
    protected static $defaultName = 'rollbar:deploy';

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $io;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * DeployCommand constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();

        $this->container = $container;
    }

    /**
     * Extend the initialize method to add additional parameters to the class.
     *
     * {@inheritdoc}
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $io       = new SymfonyStyle($input, $output);
        $this->io = $io;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        parent::configure();

        $help = implode("\n", [
            '<info>%command.name%</info> - Track new build with Rollbar after successful deployment.',
            'See: <info>https://rollbar.com/docs/deploys_other</info>',
        ]);

        $this
            ->setName(static::$defaultName)
            ->setDescription('Send notification about new build.')
            ->setHelp($help);

        $this->addArgument(
            'revision',
            InputArgument::REQUIRED,
            'String identifying the revision being deployed, such as a Git SHA.');

        $this->addOption(
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Additional text data to record with this deploy.'
        )->addOption(
            'rollbar_username',
            'ru',
            InputOption::VALUE_OPTIONAL,
            'Rollbar username of the user who deployed.'
        )->addOption(
            'local_username',
            'lu',
            InputOption::VALUE_OPTIONAL,
            'Username (on your system) who deployed.'
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \SymfonyRollbarBundle\Provider\RollbarHandler $rbHandler */
        $rbHandler = $this->container->get('symfony_rollbar.provider.rollbar_handler');

        $environment = $this->container->getParameter('kernel.environment');
        $revision    = $input->getArgument('revision');
        $comment     = $input->getOption('comment');
        $rUser       = $input->getOption('rollbar_username');
        $lUser       = $input->getOption('local_username');

        $lUser = empty($lUser) ? get_current_user() : $lUser;

        try {
            $rbHandler->trackBuild($environment, $revision, $comment, $rUser, $lUser);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->io->error("Build has been not tracked:\n" . $e->getMessage());
        }

        $this->io->success('Done.');
    }
}

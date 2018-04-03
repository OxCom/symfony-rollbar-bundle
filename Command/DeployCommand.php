<?php

namespace SymfonyRollbarBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeployCommand
 *
 * @package SymfonyRollbarBundle\Command
 */
class DeployCommand extends \SymfonyRollbarBundle\Command\AbstractCommand
{
    protected static $defaultName = 'rollbar:deploy';

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
        $rbHandler = $this->getContainer()->get('symfony_rollbar.provider.rollbar_handler');

        $environment = $this->getContainer()->getParameter('kernel.environment');
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

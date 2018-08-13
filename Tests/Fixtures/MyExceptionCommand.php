<?php

namespace SymfonyRollbarBundle\Tests\Fixtures;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MyExceptionCommand extends \SymfonyRollbarBundle\Command\AbstractCommand
{
    protected static $defaultName = 'rollbar:exception';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName(static::$defaultName)
            ->setDescription('Trigger exception.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('This is console exception');
    }
}

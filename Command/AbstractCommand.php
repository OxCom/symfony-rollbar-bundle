<?php

namespace SymfonyRollbarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AbstractCommand
 *
 * @package SymfonyRollbarBundle\Command
 */
abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $io;

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
}

<?php

namespace SymfonyRollbarBundle\Provider;

/**
 * Interface InterfaceLogger
 *
 * @package SymfonyRollbarBundle\Provider
 */
interface InterfaceLogger
{
    /**
     * @param mixed  $level
     * @param string $message
     *
     * @return void
     */
    public function log($level, $message);
}

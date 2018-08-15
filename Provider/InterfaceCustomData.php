<?php

namespace SymfonyRollbarBundle\Provider;

/**
 * Interface InterfaceCustomData
 *
 * @package SymfonyRollbarBundle\Provider
 */
interface InterfaceCustomData
{
    /**
     * @param \Exception|\Throwable|string $toLog
     * @param mixed $context
     *
     * @return array
     */
    public function __invoke($toLog, $context);
}

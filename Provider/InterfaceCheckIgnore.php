<?php

namespace SymfonyRollbarBundle\Provider;

/**
 * Interface InterfaceCheckIgnore
 *
 * @package SymfonyRollbarBundle\Provider
 */
interface InterfaceCheckIgnore
{
    /**
     * @param boolean                                 $isUncaught
     * @param \Rollbar\ErrorWrapper|\Exception|string $toLog
     * @param \Rollbar\Payload\Payload                $payload
     *
     * @return boolean
     */
    public function checkIgnore($isUncaught, $toLog, $payload);
}

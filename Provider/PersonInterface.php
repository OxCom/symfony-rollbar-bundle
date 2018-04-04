<?php

namespace SymfonyRollbarBundle\Provider;

/**
 * Interface PersonInterface
 *
 * @package SymfonyRollbarBundle\Provider
 * @link https://rollbar.com/docs/person-tracking/
 */
interface PersonInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getUsername();

    /**
     * @return string
     */
    public function getEmail();
}

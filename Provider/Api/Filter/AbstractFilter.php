<?php

namespace SymfonyRollbarBundle\Provider\Api\Filter;

/**
 * Class AbstractFilter
 *
 * @package SymfonyRollbarBundle\Provider\Api\Filter
 */
abstract class AbstractFilter
{
    /**
     * Do stuff with data
     *
     * @param $value
     *
     * @return mixed
     */
    abstract public function __invoke($value);
}

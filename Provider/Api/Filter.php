<?php

namespace SymfonyRollbarBundle\Provider\Api;

/**
 * Class Validator
 *
 * Before calls to API we have to check some restrictions on Rollbar side to avoid drops of request
 *
 * @package SymfonyRollbarBundle\Provider\Api
 */
class Filter
{
    /**
     * @param mixed  $value
     * @param string $filterName
     * @param array  $options
     *
     * @return mixed
     */
    public static function process($value, $filterName = '', $options = [])
    {
        $filter = new $filterName($options);

        return $filter($value);
    }
}

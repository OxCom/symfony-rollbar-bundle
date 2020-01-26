<?php

namespace SymfonyRollbarBundle\Tests\Fixtures;

class MyAwesomeSymfonyException extends \Symfony\Component\HttpKernel\Exception\HttpException
{
    /**
     * Returns the status code.
     *
     * @return int An HTTP response status code
     */
    public function getStatusCode()
    {
    }

    /**
     * Returns response headers.
     *
     * @return array Response headers
     */
    public function getHeaders()
    {
    }
}

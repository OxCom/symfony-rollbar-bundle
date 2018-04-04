<?php

namespace SymfonyRollbarBundle\Tests\Fixtures;

class MyAwesomeSymfonyException implements \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
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

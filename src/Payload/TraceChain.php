<?php

namespace SymfonyRollbarBundle\Payload;

class TraceChain
{
    /**
     * @param \Exception $exception
     *
     * @return array
     */
    public function __invoke(\Exception $exception)
    {
        $chain = [];
        $item  = new TraceItem();

        while (!empty($exception)) {
            $chain[] = $item($exception);
            $exception = $exception->getPrevious();
        }

        return $chain;
    }
}

<?php

namespace SymfonyRollbarBundle\Formatter;

class TraceChain
{
    /**
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

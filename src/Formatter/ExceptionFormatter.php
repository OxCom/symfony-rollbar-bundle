<?php

namespace SymfonyRollbarBundle\Formatter;

class ExceptionFormatter
{
    /**
     * Formats a log record.
     *
     * @param \Exception $exception
     *
     * @return mixed The formatted record
     */
    public static function format(\Exception $exception)
    {
        $record = [];

        if ($exception instanceof \Exception) {
            // handle exception
            $chain = new TraceChain();
            $item  = new TraceItem();

            $data = $item($exception);
            $record['message'] = $data['exception']['message'];
            $record['trace_chain'] = $chain($exception);
        }

        return $record;
    }
}

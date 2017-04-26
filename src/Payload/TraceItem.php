<?php

namespace SymfonyRollbarBundle\Payload;

class TraceItem
{
    /**
     * @param \Exception $exception
     *
     * @return array
     */
    public function __invoke(\Exception $exception)
    {
        $frames = [];

        foreach ($exception->getTrace() as $row) {
            // prepare initial frame
            $frame = [
                'filename'   => empty($row['file']) ? null : $row['file'],
                'lineno'     => empty($row['line']) ? null : $row['line'],
                'class_name' => empty($row['class']) ? null : $row['class'],
                'args'       => empty($row['args']) ? [] : $row['args'],
            ];

            // convert vars to types
            foreach ($frame['args'] as $key => $item) {
                $frame['args'][$key] = gettype($item);
            }

            // build method
            $method          = empty($row['function']) ? null : $row['function'];
            $call            = empty($row['type']) ? '::' : $row['type'];
            $frame['method'] = $frame['class_name'] . $call . $method . '()';

            $frames[] = $frame;
        }

        $record = [
            'exception' => [
                'class'   => get_class($exception),
                'message' => implode(' ', [
                    "'\\" . get_class($exception) . "'",
                    'with message',
                    "'" . $exception->getMessage() . "'",
                    'occurred in file',
                    "'" . $exception->getFile() . "'",
                    'line',
                    "'" . $exception->getLine() . "'",
                    'with code',
                    "'" . $exception->getCode() . "'",
                ]),
            ],
            'frames'    => $frames,
        ];

        return $record;
    }
}

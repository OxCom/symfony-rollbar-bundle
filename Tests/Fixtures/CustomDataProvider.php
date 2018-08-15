<?php
namespace SymfonyRollbarBundle\Tests\Fixtures;


use SymfonyRollbarBundle\Provider\InterfaceCustomData;

class CustomDataProvider implements InterfaceCustomData
{
    /**
     * @param \Exception|\Throwable|string $toLog
     * @param mixed                        $context
     *
     * @return array
     */
    public function __invoke($toLog, $context)
    {
        return [
            'random_number' => rand(1, 1000),
            'to_log'        => $toLog,
            'context'       => $context,
        ];
    }
}

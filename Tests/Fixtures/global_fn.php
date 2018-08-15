<?php

/**
 * @return array
 */
function get_awesome_person()
{
    return [
        'id'       => 'global_id',
        'username' => 'global_username',
        'email'    => 'global_email',
    ];
}

/**
 * @param boolean                                 $isUncaught
 * @param \Rollbar\ErrorWrapper|\Exception|string $toLog
 * @param \Rollbar\Payload\Payload                $payload
 *
 * @return boolean
 */
function should_ignore($isUncaught, $toLog, $payload)
{
    return false;
}

/**
 * @param \Exception|\Throwable|string $toLog
 * @param mixed $context
 *
 * @return array
 */
function custom_data_provider($toLog, $context)
{
    return [
        'random_number' => rand(1, 1000),
        'to_log'        => $toLog,
        'context'       => $context,
    ];
}

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

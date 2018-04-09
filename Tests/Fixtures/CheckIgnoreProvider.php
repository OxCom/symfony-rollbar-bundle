<?php
namespace SymfonyRollbarBundle\Tests\Fixtures;

use SymfonyRollbarBundle\Provider\InterfaceCheckIgnore;

class CheckIgnoreProvider implements InterfaceCheckIgnore
{
    /**
     * @var bool
     */
    protected $ignore = false;

    /**
     * @param boolean                                 $isUncaught
     * @param \Rollbar\ErrorWrapper|\Exception|string $toLog
     * @param \Rollbar\Payload\Payload                $payload
     *
     * @return boolean
     */
    public function checkIgnore($isUncaught, $toLog, $payload)
    {
        return $this->ignore;
    }

    /**
     * @param bool $ignore
     *
     * @return $this
     */
    public function setIgnore($ignore = false)
    {
        $this->ignore = $ignore;

        return $this;
    }
}

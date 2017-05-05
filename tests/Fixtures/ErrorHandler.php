<?php

namespace Tests\Fixtures;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class ErrorHandler extends AbstractProcessingHandler
{
    /**
     * @var \Tests\Fixtures\ErrorHandler
     */
    protected static $instance;

    /**
     * @var Callable
     */
    protected $assert;

    /**
     * @return \Tests\Fixtures\ErrorHandler
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new self(Logger::DEBUG);
        }

        return static::$instance;
    }

    /**
     * @param Callable $assert
     */
    public function setAssert($assert = null)
    {
        $this->assert = $assert;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     *
     * @return void
     */
    protected function write(array $record)
    {
        $closure = empty($this->assert) ? function() {} : $this->assert;
        call_user_func($closure, $record);
    }
}

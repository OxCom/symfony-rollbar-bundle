<?php

namespace SymfonyRollbarBundle\Tests\Fixtures;

use SymfonyRollbarBundle\Provider\PersonInterface;

/**
 * Class Person
 *
 * @package SymfonyRollbarBundle\Provider
 */
class AwesomePerson implements PersonInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $email;

    /**
     * Person constructor.
     *
     * @param string $id
     * @param string $username
     * @param string $email
     */
    public function __construct($id, $username = null, $email = null)
    {
        $this->id       = (string)$id;
        $this->username = (string)$username;
        $this->email    = (string)$email;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}

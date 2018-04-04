<?php

namespace SymfonyRollbarBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyRollbarBundle\Provider\PersonInterface;

abstract class AbstractPersonProvider
{
    /**
     * @var \SymfonyRollbarBundle\Provider\PersonInterface
     */
    protected $person;

    /**
     * Initialize current person that should be tracked with Rollbar
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    abstract public function __construct(ContainerInterface $container);

    /**
     * @return array|null
     */
    public function getPerson()
    {
        if (empty($this->person)) {
            return null;
        }

        return [
            'id'       => $this->person->getId(),
            'username' => $this->person->getUsername(),
            'email'    => $this->person->getEmail(),
        ];
    }

    /**
     * @param \SymfonyRollbarBundle\Provider\PersonInterface $person
     *
     * @return AbstractPersonProvider
     */
    public function setPerson(PersonInterface $person = null)
    {
        $this->person = $person;

        return $this;
    }
}

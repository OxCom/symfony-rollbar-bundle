<?php

namespace SymfonyRollbarBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyRollbarBundle\Provider\PersonInterface;

/**
 * Class AbstractPersonProvider
 *
 * @package SymfonyRollbarBundle\Provider
 */
abstract class AbstractPersonProvider
{
    /**
     * @var \SymfonyRollbarBundle\Provider\PersonInterface
     */
    protected $person;

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

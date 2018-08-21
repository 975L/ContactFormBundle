<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service\User;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use c975L\ContactFormBundle\Service\User\ContactFormUserInterface;

/**
 * Services related to ContactForm User
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormUser implements ContactFormUserInterface
{
    /**
     * Stores TokenStorage
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return (null !== $user && method_exists($user, 'getEmail')) ? $user->getEmail() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $name = null;

        if (null !== $user) {
            //Defines name from firstname
            if (method_exists($user, 'getFirstname')) {
                $name = $user->getFirstname();
                //Adds name from lastname
                if (method_exists($user, 'getLastname')) {
                    $name .= ' ' . $user->getLastname();
                }
            //Defines name from username
            } elseif (method_exists($user, 'getUsername')) {
                $name = $user->getUsername();
            }
        }

        return $name;
    }
}

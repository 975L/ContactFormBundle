<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service\User;

/**
 * Interface to be called for DI for ContactForm User related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface ContactFormUserInterface
{
    /**
     * Gets email if user has signed in
     * @return string|null
     */
    public function getEmail();

    /**
     * Gets name if user has signed in
     * @return string|null
     */
    public function getName();
}

<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service\User;

use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;

interface ContactFormUserInterface
{
    /**
     * Gets email if user has signed in
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Gets name if user has signed in
     *
     * @return string|null
     */
    public function getName();
}

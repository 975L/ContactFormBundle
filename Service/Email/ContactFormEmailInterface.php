<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service\Email;

use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;

/**
 * Interface to be called for DI for ContactForm Email related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface ContactFormEmailInterface
{
    /**
     * Defines data to use for email
     * @return array
     */
    public function defineData(ContactFormEvent $event, ContactForm $formData);

    /**
     * Sends email
     * @return bool
     */
    public function send(ContactFormEvent $event, ContactForm $formData);
}

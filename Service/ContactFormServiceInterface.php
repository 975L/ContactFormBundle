<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service;

use Symfony\Component\Form\Form;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;

/**
 * Interface to be called for DI for ContactFormServiceInterface related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface ContactFormServiceInterface
{
    /**
     * Creates the contactForm
     * @return ContactForm
     */
    public function create();

    /**
     * Creates the contactForm
     * @return Form
     */
    public function createForm(string $name, ContactForm $contactForm, ContactFormEvent $event);

    /**
     * Gets subject if provided by url parameter "s"
     * @return string|null
     */
    public function getSubject();

    /**
     * Gets referer defined in session
     * @return string|null
     */
    public function getReferer();

    /**
     * Tests if delay (defined in config) is not too short and if honeypot has not been filled, to avoid being used by bot
     * @return bool
     */
    public function isNotBot($username);

    /**
     * Defines the referer to redirect to after submission of form
     */
    public function setReferer();

    /**
     * Sends email resulting from submission of form
     * @return string|null
     */
    public function sendEmail(Form $form, ContactFormEvent $event);
}

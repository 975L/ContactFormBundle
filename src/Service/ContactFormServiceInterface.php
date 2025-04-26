<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service;

use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use Symfony\Component\Form\Form;

interface ContactFormServiceInterface
{
    public function create(): ContactForm;

    public function createForm(string $name, ContactForm $contactForm, ContactFormEvent $event): Form;

    public function getSubject(): ?string;

    public function getReferer(): ?string;

    public function isNotBot(string $username): bool;

    public function setReferer(): void;

    public function sendEmail(Form $form, ContactFormEvent $event): ?string;
}

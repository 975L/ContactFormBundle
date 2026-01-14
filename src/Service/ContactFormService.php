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
use Symfony\Component\HttpFoundation\Request;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\SiteBundle\Service\ServiceUserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\SiteBundle\Service\ServiceToolsInterface;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ContactFormBundle\Service\EmailServiceInterface;
use c975L\ContactFormBundle\Form\ContactFormFactoryInterface;

class ContactFormService implements ContactFormServiceInterface
{
    private readonly ?Request $request;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ConfigServiceInterface $configService,
        private readonly EmailServiceInterface $emailService,
        private readonly ContactFormFactoryInterface $contactFormFactory,
        private readonly ServiceToolsInterface $serviceTools,
        private readonly ServiceUserInterface $serviceUser
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    // Generates a random honeypot field name
    private function generateHoneypotFieldName(): string
    {
        $prefixes = ['user', 'account', 'client', 'contact', 'person', 'profile'];
        $suffixes = ['name', 'info', 'data', 'field', 'input', 'details'];

        return $prefixes[array_rand($prefixes)] . '_' . $suffixes[array_rand($suffixes)];
    }

    // Generates a random honeypot label
    private function generateHoneypotLabel(): string
    {
        $labels = [
            'Company website',
            'Your website',
            'Organization',
            'Department',
            'Job title',
            'Phone number',
            'Address',
            'City',
            'Postal code',
            'Country',
            'Fax number'
        ];

        return $labels[array_rand($labels)];
    }

    // Gets the honeypot field name from session
    public function getHoneypotFieldName(): string
    {
        return $this->request->getSession()->get('honeypotField', 'username');
    }

    // Gets the honeypot label from session
    public function getHoneypotLabel(): string
    {
        return $this->request->getSession()->get('honeypotLabel', 'Username');
    }

    // Creates the contactForm
    public function create(): ContactForm
    {
        //Adds time to session to check if a robot has filled the form
        if (null === $this->request->getSession()->get('time')) {
            $this->request->getSession()->set('time', time());
        }

        //Generates random honeypot field name for this session
        if (null === $this->request->getSession()->get('honeypotField')) {
            $this->request->getSession()->set('honeypotField', $this->generateHoneypotFieldName());
        }

        //Generates random honeypot label for this session
        if (null === $this->request->getSession()->get('honeypotLabel')) {
            $this->request->getSession()->set('honeypotLabel', $this->generateHoneypotLabel());
        }

        //Defines the referer
        $this->setReferer();

        //Defines the ContactForm
        $contactForm = new ContactForm();
        $contactForm->setName($this->serviceUser->getName());
        $contactForm->setEmail($this->serviceUser->getEmail());
        $contactForm->setSubject($this->getSubject());

        return $contactForm;
    }

    // Shortcut to call ContactFormFactory to create Form
    public function createForm(string $name, ContactForm $contactForm, ContactFormEvent $event): Form
    {
        return $this->contactFormFactory->create($name, $contactForm, $event);
    }

    // Gets subject if provided by url parameter "s"
    public function getSubject(): ?string
    {
        $subject = filter_var($this->request->query->get('s'), FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);

        return empty($subject) ? null : $subject;
    }

    // Gets referer defined in session
    public function getReferer(): ?string
    {
        //Redirects to url if defined in session
        $sessionRedirectUrl = $this->request->getSession()->get('redirectUrl');
        if (null !== $sessionRedirectUrl) {
            $this->request->getSession()->remove('redirectUrl');

            return $sessionRedirectUrl;
        }

        return null;
    }

    // Tests if delay (defined in config) is not too short and if honeypot has not been filled, to avoid being used by bot
    public function isNotBot($honeypotValue): bool
    {
        $bot = null === $this->request->getSession()->get('time');
        $bot = $bot ? true : $this->request->getSession()->get('time') + $this->configService->getParameter('c975LContactForm.delay') > time();
        $bot = $bot ? true : !empty($honeypotValue);

        return ! $bot;
    }

    // Defines the referer to redirect to after submission of form
    public function setReferer(): void
    {
        $this->request->getSession()->set('redirectUrl', $this->request->headers->get('referer'));
    }

    // Sends email resulting from submission of form if it's not a bot that has used the form
    public function sendEmail(Form $form, ContactFormEvent $event): ?string
    {
        $honeypotFieldName = $this->getHoneypotFieldName();
        $honeypotValue = $form->has($honeypotFieldName) ? $form->get($honeypotFieldName)->getData() : null;

        if ($this->isNotBot($honeypotValue)) {
            // Sends email and creates flash message
            if ($this->emailService->send($event, $form->getData())) {
                $this->serviceTools->createFlash('text.message_sent', 'contactForm');
            } else {
                $this->serviceTools->createFlash('text.message_not_sent', 'contactForm', 'danger', ['%error%' => $event->getError()]);
            }
        }

        // Clean honeypot field name from session after use
        $this->request->getSession()->remove('honeypotField');
        $this->request->getSession()->remove('honeypotLabel');

        return $this->getReferer();
    }
}

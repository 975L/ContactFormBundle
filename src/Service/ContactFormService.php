<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Form\ContactFormFactoryInterface;
use c975L\ContactFormBundle\Service\Email\ContactFormEmailInterface;
use c975L\ServicesBundle\Service\ServiceToolsInterface;
use c975L\ServicesBundle\Service\ServiceUserInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Main services related to ContactForm
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormService implements ContactFormServiceInterface
{
    /**
     * Stores current Request
     */
    private readonly ?Request $request;

    public function __construct(
        /**
         * Stores ConfigServiceInterface
         */
        private readonly ConfigServiceInterface $configService,
        /**
         * Stores ContactFormEmailInterface
         */
        private readonly ContactFormEmailInterface $contactFormEmail,
        /**
         * Stores ContactFormFactoryInterface
         */
        private readonly ContactFormFactoryInterface $contactFormFactory,
        RequestStack $requestStack,
        /**
         * Stores ServiceToolsInterface
         */
        private readonly ServiceToolsInterface $serviceTools,
        /**
         * Stores ServiceUserInterface
         */
        private readonly ServiceUserInterface $serviceUser
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function create(): ContactForm
    {
        //Adds time to session to check if a robot has filled the form
        if (null === $this->request->getSession()->get('time')) {
            $this->request->getSession()->set('time', time());
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

    /**
     * {@inheritdoc}
     */
    public function createForm(string $name, ContactForm $contactForm, ContactFormEvent $event): Form
    {
        return $this->contactFormFactory->create($name, $contactForm, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        $subject = filter_var($this->request->query->get('s'), FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);

        return empty($subject) ? null : $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferer()
    {
        //Redirects to url if defined in session
        $sessionRedirectUrl = $this->request->getSession()->get('redirectUrl');
        if (null !== $sessionRedirectUrl) {
            $this->request->getSession()->remove('redirectUrl');

            return $sessionRedirectUrl;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isNotBot($username)
    {
        $bot = null === $this->request->getSession()->get('time');
        $bot = $bot ? true : $this->request->getSession()->get('time') + $this->configService->getParameter('c975LContactForm.delay') > time();
        $bot = $bot ? true : null !== $username;

        return ! $bot;
    }

    /**
     * {@inheritdoc}
     */
    public function setReferer()
    {
        $this->request->getSession()->set('redirectUrl', $this->request->headers->get('referer'));
    }

    /**
     * {@inheritdoc}
     */
    public function sendEmail(Form $form, ContactFormEvent $event)
    {
        //Sends email if it's not a bot that has used the form
        if ($this->isNotBot($form->get('username')->getData())) {
            $emailSent = $this->contactFormEmail->send($event, $form->getData());

            //Creates flash message
            if ($emailSent) {
                $this->serviceTools->createFlash('text.message_sent', 'contactForm');
            } else {
                $this->serviceTools->createFlash('text.message_not_sent', 'contactForm', 'danger', ['%error%' => $event->getError()]);
            }
        }

        //Returns defined referer
        return $this->getReferer();
    }
}

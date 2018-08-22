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
use Symfony\Component\HttpFoundation\RequestStack;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Service\ContactFormServiceInterface;
use c975L\ContactFormBundle\Service\Email\ContactFormEmailInterface;
use c975L\ContactFormBundle\Service\Tools\ContactFormToolsInterface;
use c975L\ContactFormBundle\Service\User\ContactFormUserInterface;

/**
 * Main services related to ContactForm
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormService implements ContactFormServiceInterface
{
    /**
     * Stores current Request
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $request;

    /**
     * Stores ContactFormEmail Service
     * @var ContactFormEmailInterface
     */
    private $contactFormEmail;

    /**
     * Stores ContactFormTools Service
     * @var ContactFormToolsInterface
     */
    private $contactFormTools;

    /**
     * Stores ContactFormUser Service
     * @var ContactFormUserInterface
     */
    private $contactFormUser;

    public function __construct(
        RequestStack $requestStack,
        ContactFormEmailInterface $contactFormEmail,
        ContactFormToolsInterface $contactFormTools,
        ContactFormUserInterface $contactFormUser
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->contactFormEmail = $contactFormEmail;
        $this->contactFormTools = $contactFormTools;
        $this->contactFormUser = $contactFormUser;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        //Adds time to session to check if a robot has filled the form
        if (null === $this->request->getSession()->get('time')) {
            $this->request->getSession()->set('time', time());
        }

        //Defines the referer
        $this->setReferer();

        //Defines the ContactForm
        $contactForm = new ContactForm();
        $contactForm
            ->setName($this->contactFormUser->getName())
            ->setEmail($this->contactFormUser->getEmail())
            ->setSubject($this->getSubject())
            ->setIp($this->request->getClientIp())
        ;

        return $contactForm;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        $subject = filter_var($this->request->query->get('s'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

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
        if ($this->contactFormTools->isNotBot($form->get('username')->getData())) {
            $this->contactFormEmail->send($event, $form->getData());
        }

        //Returns defined referer
        return $this->getReferer();
    }
}

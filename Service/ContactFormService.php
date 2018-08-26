<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use c975L\ServicesBundle\Service\ServiceUserInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Service\ContactFormServiceInterface;
use c975L\ContactFormBundle\Service\Email\ContactFormEmailInterface;
use c975L\ContactFormBundle\Service\User\ContactFormUserInterface;

/**
 * Main services related to ContactForm
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormService implements ContactFormServiceInterface
{
    /**
     * Stores container
     * @var ContainerInterface
     */
    private $container;

    /**
     * Stores ContactFormEmailInterface
     * @var ContactFormEmailInterface
     */
    private $contactFormEmail;

    /**
     * Stores current Request
     * @var RequestStack
     */
    private $request;

    /**
     * Stores ServiceUserInterface
     * @var ServiceUserInterface
     */
    private $serviceUser;

    public function __construct(
        ContainerInterface $container,
        ContactFormEmailInterface $contactFormEmail,
        RequestStack $requestStack,
        ServiceUserInterface $serviceUser
    )
    {
        $this->container = $container;
        $this->contactFormEmail = $contactFormEmail;
        $this->request = $requestStack->getCurrentRequest();
        $this->serviceUser = $serviceUser;
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
            ->setName($this->serviceUser->getName())
            ->setEmail($this->serviceUser->getEmail())
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
    public function isNotBot($username)
    {
        $bot = null === $this->request->getSession()->get('time');
        $bot = $bot ? true : $this->request->getSession()->get('time') + $this->container->getParameter('c975_l_contact_form.delay') > time();
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
            $this->contactFormEmail->send($event, $form->getData());
        }

        //Returns defined referer
        return $this->getReferer();
    }
}

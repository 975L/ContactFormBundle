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
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ServicesBundle\Service\ServiceToolsInterface;
use c975L\ServicesBundle\Service\ServiceUserInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Form\ContactFormFactoryInterface;
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
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores ContactFormEmailInterface
     * @var ContactFormEmailInterface
     */
    private $contactFormEmail;

    /**
     * Stores ContactFormFactoryInterface
     * @var ContactFormFactoryInterface
     */
    private $contactFormFactory;

    /**
     * Stores current Request
     * @var RequestStack
     */
    private $request;

    /**
     * Stores ServiceToolsInterface
     * @var ServiceToolsInterface
     */
    private $serviceTools;

    /**
     * Stores ServiceUserInterface
     * @var ServiceUserInterface
     */
    private $serviceUser;

    public function __construct(
        ConfigServiceInterface $configService,
        ContactFormEmailInterface $contactFormEmail,
        ContactFormFactoryInterface $contactFormFactory,
        RequestStack $requestStack,
        ServiceToolsInterface $serviceTools,
        ServiceUserInterface $serviceUser
    )
    {
        $this->configService = $configService;
        $this->contactFormEmail = $contactFormEmail;
        $this->contactFormFactory = $contactFormFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->serviceTools = $serviceTools;
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
    public function createForm(string $name, ContactForm $contactForm, ContactFormEvent $event)
    {
        return $this->contactFormFactory->create($name, $contactForm, $event);
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
                $this->serviceTools->createFlash('contactForm', 'text.message_sent');
            } else {
                $this->serviceTools->createFlash('contactForm', 'text.message_not_sent', 'danger', array('%error%' => $event->getError()));
            }
        }

        //Returns defined referer
        return $this->getReferer();
    }
}

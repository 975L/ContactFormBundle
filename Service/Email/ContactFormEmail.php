<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service\Email;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Environment;
use c975L\EmailBundle\Service\EmailServiceInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Service\Email\ContactFormEmailInterface;

/**
 * Services related to ContactForm Email
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormEmail implements ContactFormEmailInterface
{
    /**
     * Stores container
     * @var ContainerInterface
     */
    private $container;

    /**
     * Stores current Request
     * @var RequestStack
     */
    private $request;

    /**
     * Stores Twig_Environment
     * @var Twig_Environment
     */
    private $templating;

    /**
     * Stores EmailService
     * @var EmailServiceInterface
     */
    private $emailService;

    public function __construct(
        ContainerInterface $container,
        EmailServiceInterface $emailService,
        RequestStack $requestStack,
        Twig_Environment $templating
    )
    {
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
        $this->emailService = $emailService;
    }

    /**
     * {@inheritdoc}
     */
    public function defineData(ContactFormEvent $event, ContactForm $formData)
    {
        $emailData = $event->getEmailData();

        //emailData has been updated after Event SEND_FORM dispatch
        if (is_array($emailData) &&
            array_key_exists('subject', $emailData) &&
            array_key_exists('bodyData', $emailData) &&
            array_key_exists('bodyEmail', $emailData)
        )
        {
            //Updates emailData
            if (!array_key_exists('sentFrom', $emailData)) {
                $emailData['sentFrom'] = $this->container->getParameter('c975_l_contact_form.sentTo');
            }
            if (!array_key_exists('sentTo', $emailData)) {
                $emailData['sentTo'] = $this->container->getParameter('c975_l_contact_form.sentTo');
            }
            if (!array_key_exists('sentCc', $emailData)) {
                $emailData['sentCc'] = $formData->getEmail();
            }
            if (!array_key_exists('replyTo', $emailData)) {
                $emailData['replyTo'] = $formData->getEmail();
            }
            if (!array_key_exists('ip', $emailData)) {
                $emailData['ip'] = $this->request->getClientIp();
            }
            if (!array_key_exists('form', $emailData['bodyData'])) {
                $emailData['bodyData']['form'] = $formData;
            }
            $emailData['body'] = $this->templating->render($emailData['bodyEmail'], $emailData['bodyData']);
            unset($emailData['bodyEmail']);
            unset($emailData['bodyData']);
        //Otherwise defines generic email
        } elseif (null === $event->getError()) {
            $bodyEmail = '@c975LContactForm/emails/contact.html.twig';
            $bodyData = array(
                '_locale' => $this->request->getLocale(),
                'form' => $formData,
                );
            $emailData = array(
                'subject' => $formData->getSubject(),
                'sentFrom' => $this->container->getParameter('c975_l_contact_form.sentTo'),
                'sentTo' => $this->container->getParameter('c975_l_contact_form.sentTo'),
                'sentCc' => $formData->getEmail(),
                'replyTo' => $formData->getEmail(),
                'body' => $this->templating->render($bodyEmail, $bodyData),
                'ip' => $this->request->getClientIp(),
                );
        }

        //Removes sentCC if checkbox to receive copy hasn't been checked
        if (true !== $formData->getReceiveCopy()) {
            unset($emailData['sentCc']);
        }

        return $emailData;
    }

    /**
     * {@inheritdoc}
     */
    public function send(ContactFormEvent $event, ContactForm $formData)
    {
        //Removes time from session
        $this->request->getSession()->remove('time');

        //Sends email
        $emailData = $this->defineData($event, $formData);
        if (is_array($emailData)) {
            return $this->emailService->send($emailData, $this->container->getParameter('c975_l_contact_form.database'));
        }

        return false;
    }
}

<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service\Email;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\EmailBundle\Service\EmailServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * Services related to ContactForm Email
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormEmail implements ContactFormEmailInterface
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores current Request
     * @var Request
     */
    private $request;

    /**
     * Stores Environment
     * @var Environment
     */
    private $environment;

    /**
     * Stores EmailServiceInterface
     * @var EmailServiceInterface
     */
    private $emailService;

    public function __construct(
        ConfigServiceInterface $configService,
        EmailServiceInterface $emailService,
        RequestStack $requestStack,
        Environment $environment
    )
    {
        $this->configService = $configService;
        $this->request = $requestStack->getCurrentRequest();
        $this->environment = $environment;
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
                $emailData['sentFrom'] = $this->configService->getParameter('c975LContactForm.sentTo');
            }
            if (!array_key_exists('sentTo', $emailData)) {
                $emailData['sentTo'] = $this->configService->getParameter('c975LContactForm.sentTo');
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
            $emailData['body'] = $this->environment->render($emailData['bodyEmail'], $emailData['bodyData']);
            unset($emailData['bodyEmail']);
            unset($emailData['bodyData']);
        //Otherwise defines generic email
        } elseif (null === $event->getError()) {
            $bodyEmail = '@c975LContactForm/emails/contact.html.twig';
            $bodyData = array(
                'locale' => $this->request->getLocale(),
                'form' => $formData,
                );
            $emailData = array(
                'subject' => $formData->getSubject(),
                'sentFrom' => $this->configService->getParameter('c975LContactForm.sentTo'),
                'sentTo' => $this->configService->getParameter('c975LContactForm.sentTo'),
                'sentCc' => $formData->getEmail(),
                'replyTo' => $formData->getEmail(),
                'body' => $this->environment->render($bodyEmail, $bodyData),
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
            return $this->emailService->send($emailData, $this->configService->getParameter('c975LContactForm.database'));
        }

        return false;
    }
}

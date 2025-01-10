<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service\Email;

use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use Symfony\Component\HttpFoundation\RequestStack;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ConfigBundle\Service\ConfigServiceInterface;

/**
 * Services related to ContactForm Email
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormEmail implements ContactFormEmailInterface
{
    /**
     * Stores current Request
     */
    private readonly ?Request $request;

    public function __construct(
        private readonly RequestStack $requestStack,
        /**
         * Stores ConfigServiceInterface
         */
        private readonly ConfigServiceInterface $configService,
        /**
         * Stores MailerInterface
         */
        private readonly MailerInterface $mailer
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function defineData(ContactFormEvent $event, ContactForm $formData)
    {
        $emailData = $event->getEmailData();

        // Defines adresses and names
        $from = is_array($emailData) && array_key_exists('sentFrom', $emailData) ? $emailData['sentFrom'] : $this->configService->getParameter('c975LContactForm.sentTo');
        $fromName = $this->configService->hasParameter('c975LContactForm.sentToName') ? $this->configService->getParameter('c975LContactForm.sentToName') : '';
        $to = is_array($emailData) && array_key_exists('sentTo', $emailData) ? $emailData['sentTo'] : $this->configService->getParameter('c975LContactForm.sentTo');
        $toName = $this->configService->hasParameter('c975LContactForm.sentToName') ? $this->configService->getParameter('c975LContactForm.sentToName') : '';
        $replyTo = is_array($emailData) && array_key_exists('replyTo', $emailData) ? $emailData['replyTo'] : $formData->getEmail();
        $cc = is_array($emailData) && array_key_exists('sentCc', $emailData) ? $emailData['sentCc'] : $formData->getEmail();

        // Creates email
        $email = new TemplatedEmail();
        $email->subject($formData->getSubject());
        $email->from(new Address($from, $fromName));
        $email->to(new Address($to, $toName));
        $email->replyTo(new Address($replyTo));
        $email->htmlTemplate('@c975LContactForm/emails/contact.html.twig');
        $email->context([
            'locale' => $this->request->getLocale(),
            'form' => $formData
        ]);

        // Adds cc if checkbox to receive copy has been checked
        if ($formData->getReceiveCopy()) {
            $email->cc(new Address($cc));
        }

        return $email;
    }

    /**
     * {@inheritdoc}
     */
    public function send(ContactFormEvent $event, ContactForm $formData)
    {
        // Removes time from session
        $this->request->getSession()->remove('time');

        // Defines data for email and sends it if TemplatedEmail
        $email = $this->defineData($event, $formData);
        if ($email instanceof TemplatedEmail) {
            $this->mailer->send($email);

            return true;
        }

        return false;
    }
}

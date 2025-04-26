<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service;

use Twig\Environment;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use Symfony\Component\HttpFoundation\RequestStack;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ConfigBundle\Service\ConfigServiceInterface;

class EmailService implements EmailServiceInterface
{
    private readonly ?Request $request;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ConfigServiceInterface $configService,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    // Defines data for email
    public function defineData(ContactFormEvent $event, ContactForm $formData): array
    {
        $emailData = $event->getEmailData();

        // Defines adresses and names
        $from = is_array($emailData) && array_key_exists('sentFrom', $emailData) ? $emailData['sentFrom'] : $this->configService->getParameter('c975LContactForm.from');
        $fromName = $this->configService->hasParameter('c975LContactForm.sentToName') ? $this->configService->getParameter('c975LContactForm.fromName') : '';

        $to = is_array($emailData) && array_key_exists('sentTo', $emailData) ? $emailData['sentTo'] : $this->configService->getParameter('c975LContactForm.sentTo');
        $toName = $this->configService->hasParameter('c975LContactForm.sentToName') ? $this->configService->getParameter('c975LContactForm.sentToName') : '';

        $replyTo = is_array($emailData) && array_key_exists('replyTo', $emailData) ? $emailData['replyTo'] : $formData->getEmail();
        $replyToName = is_array($emailData) && array_key_exists('replyToName', $emailData) ? $emailData['replyToName'] : $formData->getName();

        $emails = [];
        // Creates email for sending to the defined receiver
        $email = new TemplatedEmail();
        $email->subject($formData->getSubject());
        $email->from(new Address($from, $fromName));
        $email->to(new Address($to, $toName));
        $email->replyTo(new Address($replyTo, $replyToName));
        $email->htmlTemplate('@c975LContactForm/emails/contact.html.twig');
        $email->context([
            'locale' => $this->request->getLocale(),
            'form' => $formData
        ]);
        $emails[] = $email;

        // Creates email for sending to sender if checkbox to receive copy has been checked. Do so to avoid providing the email address of the receiver
        if ($formData->getReceiveCopy()) {
            $emailSender = clone $email;
            $emailSender->to(new Address($replyTo));
            $emailSender->getHeaders()->remove('Reply-To');

            $emails[] = $emailSender;
        }

        return $emails;
    }

    // Sends email
    public function send(ContactFormEvent $event, ContactForm $formData): bool
    {
        // Removes time from session
        $this->request->getSession()->remove('time');

        // Defines data for email and sends it if TemplatedEmail
        $emails = $this->defineData($event, $formData);
        try {
            foreach ($emails as $email) {
                if ($email instanceof TemplatedEmail) {
                    // echo $this->twig->render($email->getHtmlTemplate(), ['form' => $email->getContext()['form']]); dd(); // for debug
                    $this->mailer->send($email);
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

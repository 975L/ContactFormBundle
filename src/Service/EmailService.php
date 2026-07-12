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
        $from = $this->getDataParameter('email-from', $emailData);
        $fromName = $this->getDataParameter('email-from-name', $emailData);
        $fromName = (null === $fromName) ? $from : $fromName;

        $to = $this->getDataParameter('email-to', $emailData);
        $toName = $this->getDataParameter('email-to-name', $emailData);
        $toName = (null === $toName) ? $to : $toName;

        $replyTo = $this->getDataParameter('email-reply-to', $emailData);
        $replyToName = $this->getDataParameter('email-reply-to-name', $emailData);
        $replyToName = (null === $replyToName) ? $replyTo : $replyToName;

        // Lauches error if any of the parameters is missing
        if (null === $from || null === $to) {
            throw new \Exception('Missing email parameter(s)');
        }

        $emails = [];
        // Creates email for sending to the defined receiver
        $email = new TemplatedEmail();
        $email->subject($formData->getSubject());
        $email->from(new Address($from, $fromName));
        $email->to(new Address($to, $toName));
        if (null !== $replyTo) {
            $email->replyTo(new Address($replyTo, $replyToName));
        }
        $email->htmlTemplate('@c975LContactForm/emails/contact.html.twig');
        $email->context([
            'locale' => $this->request->getLocale(),
            'form' => $formData
        ]);
        $emails[] = $email;

        // Creates email for sending to sender if checkbox to receive copy has been checked. Do so to avoid providing the email address of the receiver
        if ($formData->getReceiveCopy()) {
            $emailSender = clone $email;
            $emailSender->to(new Address($formData->getEmail()));
            $emailSender->getHeaders()->remove('Reply-To');

            $emails[] = $emailSender;
        }

        return $emails;
    }

    // Gets data for parameter
    public function getDataParameter(string $parameter, array $emailData): ?string
    {
        if (isset($emailData[$parameter]) && '' !== $emailData[$parameter]) {
            return $emailData[$parameter];
        }

        return ($this->configService->hasParameter($parameter))
            ? ($this->configService->get($parameter) ?: null)
            : null;
    }

    // Sends email
    public function send(ContactFormEvent $event, ContactForm $formData): bool
    {
        // Removes time from session
        $this->request->getSession()->remove('time');

        // Defines data for email and sends it if TemplatedEmail
        try {
            $emails = $this->defineData($event, $formData);
            foreach ($emails as $email) {
                if ($email instanceof TemplatedEmail) {
                    // echo $this->twig->render($email->getHtmlTemplate(), ['form' => $email->getContext()['form']]); dd(); // For debug
                    $this->mailer->send($email);
                }
            }

            return true;
        } catch (\Exception $e) {
            $event->setError($e->getMessage());
            return false;
        }
    }
}

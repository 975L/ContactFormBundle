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
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use Symfony\Component\HttpFoundation\RequestStack;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ConfigBundle\Service\ConfigServiceInterface;

class EmailService implements EmailServiceInterface
{
    private readonly ?Request $request;
    /** @var string[] */
    private array $debugPreviews = [];

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ConfigServiceInterface $configService,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly Security $security,
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
                    if ($this->security->isGranted('ROLE_SUPER_ADMIN') && $this->configService->getBool($this->configService->get('email-debug'))) {
                        $renderedEmail = $this->twig->render($email->getHtmlTemplate(), ['form' => $email->getContext()['form']]);
                        $this->debugPreviews[] = $this->wrapDebugEmail($email, $renderedEmail);
                        continue;
                    }
                    $this->mailer->send($email);
                }
            }

            return true;
        } catch (\Exception $e) {
            $event->setError($e->getMessage());
            return false;
        }
    }

    // Returns and clears the stashed debug previews, one per email that was rendered instead of sent
    public function consumeDebugPreview(): ?string
    {
        if ([] === $this->debugPreviews) {
            return null;
        }

        $preview = implode('<hr style="margin:24px 0;border:none;border-top:2px dashed #999;">', $this->debugPreviews);
        $this->debugPreviews = [];

        return $preview;
    }

    // Inserts a debug banner with the subject and addresses right after <body>, keeping a single valid HTML document
    private function wrapDebugEmail(TemplatedEmail $email, string $renderedEmail): string
    {
        $banner = sprintf(
            '<div style="margin:0;padding:8px 16px;background:#e53e3e;color:#fff;font-family:sans-serif;font-weight:bold;">EMAIL DEBUG (not sent) — %s<br>%s</div>',
            htmlspecialchars($email->getSubject() ?? ''),
            $this->formatDebugAddresses($email)
        );

        if (1 === preg_match('/<body[^>]*>/i', $renderedEmail)) {
            return preg_replace('/(<body[^>]*>)/i', '$1' . $banner, $renderedEmail, 1);
        }

        return $banner . $renderedEmail;
    }

    // Formats From/To/Cc/Bcc addresses for the debug banner
    private function formatDebugAddresses(TemplatedEmail $email): string
    {
        $lines = [];
        foreach (['From' => $email->getFrom(), 'To' => $email->getTo(), 'Cc' => $email->getCc(), 'Bcc' => $email->getBcc()] as $label => $addresses) {
            if ([] === $addresses) {
                continue;
            }

            $lines[] = htmlspecialchars(sprintf('%s: %s', $label, implode(', ', array_map(
                static fn (Address $address) => '' !== $address->getName()
                    ? sprintf('%s <%s>', $address->getName(), $address->getAddress())
                    : $address->getAddress(),
                $addresses
            ))));
        }

        return implode('<br>', $lines);
    }
}

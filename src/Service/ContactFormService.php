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
use c975L\ContactFormBundle\Service\EmailServiceInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactFormService implements ContactFormServiceInterface
{
    private readonly ?Request $request;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ConfigServiceInterface $configService,
        private readonly EmailServiceInterface $emailService,
        private readonly ContactFormFactoryInterface $contactFormFactory,
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
        private readonly ?object $contactFormByIpLimiterFactory = null,
        private readonly ?object $contactFormByEmailLimiterFactory = null,
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    private function consumeRateLimiter(?object $limiterFactory, string $key): bool
    {
        if (null === $limiterFactory || !method_exists($limiterFactory, 'create')) {
            return true;
        }

        $limiter = $limiterFactory->create($key);
        if (!is_object($limiter) || !method_exists($limiter, 'consume')) {
            return true;
        }

        $limit = $limiter->consume(1);
        if (!is_object($limit) || !method_exists($limit, 'isAccepted')) {
            return true;
        }

        return $limit->isAccepted();
    }

    private function isRateLimitAccepted(ContactForm $contactForm): bool
    {
        if (null === $this->contactFormByIpLimiterFactory && null === $this->contactFormByEmailLimiterFactory) {
            return true;
        }

        $ipKey = $this->request?->getClientIp() ?? 'unknown';
        $emailKey = strtolower(trim((string) $contactForm->getEmail()));

        if (!$this->consumeRateLimiter($this->contactFormByIpLimiterFactory, $ipKey)) {
            return false;
        }

        if ('' !== $emailKey && !$this->consumeRateLimiter($this->contactFormByEmailLimiterFactory, $emailKey)) {
            return false;
        }

        return true;
    }

    // Generates a random honeypot field name
    private function generateHoneypotFieldName(): string
    {
        $prefixes = ['user', 'account', 'client', 'contact', 'person', 'profile'];
        $suffixes = ['name', 'info', 'data', 'field', 'input', 'details'];

        return $prefixes[array_rand($prefixes)] . '_' . $suffixes[array_rand($suffixes)];
    }

    // Generates a random honeypot label
    private function generateHoneypotLabel(): string
    {
        $labels = [
            'Company website',
            'Your website',
            'Organization',
            'Department',
            'Job title',
            'Phone number',
            'Address',
            'City',
            'Postal code',
            'Country',
            'Fax number'
        ];

        return $labels[array_rand($labels)];
    }

    // Gets the honeypot field name from session
    public function getHoneypotFieldName(): string
    {
        return $this->request->getSession()->get('honeypotField', 'username');
    }

    // Gets the honeypot label from session
    public function getHoneypotLabel(): string
    {
        return $this->request->getSession()->get('honeypotLabel', 'Username');
    }

    // Creates the contactForm
    public function create(): ContactForm
    {
        //Adds time to session to check if a robot has filled the form
        if (null === $this->request->getSession()->get('time')) {
            $this->request->getSession()->set('time', time());
        }

        //Generates random honeypot field name for this session
        if (null === $this->request->getSession()->get('honeypotField')) {
            $this->request->getSession()->set('honeypotField', $this->generateHoneypotFieldName());
        }

        //Generates random honeypot label for this session
        if (null === $this->request->getSession()->get('honeypotLabel')) {
            $this->request->getSession()->set('honeypotLabel', $this->generateHoneypotLabel());
        }

        //Defines the referer
        $this->setReferer();

        //Defines the ContactForm
        $contactForm = new ContactForm();
        $user = $this->security->getUser();
        $contactForm->setName($user !== null && method_exists($user, 'getName') ? $user->getName() : null);
        $contactForm->setEmail($user !== null ? (method_exists($user, 'getEmail') ? $user->getEmail() : $user->getUserIdentifier()) : null);
        $contactForm->setSubject($this->getSubject());

        return $contactForm;
    }

    // Shortcut to call ContactFormFactory to create Form
    public function createForm(string $name, ContactForm $contactForm, ContactFormEvent $event): Form
    {
        return $this->contactFormFactory->create($name, $contactForm, $event);
    }

    // Gets subject if provided by url parameter "s"
    public function getSubject(): ?string
    {
        $subject = filter_var($this->request->query->get('s'), FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);

        return empty($subject) ? null : $subject;
    }

    // Gets referer defined in session
    public function getReferer(): ?string
    {
        //Redirects to url if defined in session
        $sessionRedirectUrl = $this->request->getSession()->get('redirectUrl');
        if (null !== $sessionRedirectUrl) {
            $this->request->getSession()->remove('redirectUrl');

            return $sessionRedirectUrl;
        }

        return null;
    }

    // Tests if delay (defined in config) is not too short and if honeypot has not been filled, to avoid being used by bot
    public function isNotBot($honeypotValue): bool
    {
        $bot = null === $this->request->getSession()->get('time');
        $bot = $bot ? true : $this->request->getSession()->get('time') + $this->configService->get('contact-form-delay') > time();
        $bot = $bot ? true : !empty($honeypotValue);

        return ! $bot;
    }

    // Defines the referer to redirect to after submission of form
    public function setReferer(): void
    {
        $this->request->getSession()->set('redirectUrl', $this->request->headers->get('referer'));
    }

    // Collects submitted custom fields values, keyed by their label, from the unmapped "custom" subform
    private function buildExtraFields(Form $form): array
    {
        if (!$form->has('custom')) {
            return [];
        }

        $extraFields = [];
        foreach ($form->get('custom')->all() as $name => $child) {
            $extraFields[$child->getConfig()->getOption('label') ?: $name] = $child->getData();
        }

        return $extraFields;
    }

    // Sends email resulting from submission of form if it's not a bot that has used the form
    public function sendEmail(Form $form, ContactFormEvent $event): ?string
    {
        $honeypotFieldName = $this->getHoneypotFieldName();
        $honeypotValue = $form->has($honeypotFieldName) ? $form->get($honeypotFieldName)->getData() : null;
        $formData = $form->getData();

        if ($this->isNotBot($honeypotValue)) {
            if ($formData instanceof ContactForm) {
                $formData->setExtraFields($this->buildExtraFields($form));

                if (!$this->isRateLimitAccepted($formData)) {
                    $this->request->getSession()->getFlashBag()->add('warning', $this->translator->trans('text.too_many_attempts', [], 'contactForm'));

                    return $this->getReferer();
                }
            }

            // Sends email and creates flash message
            if ($this->emailService->send($event, $formData)) {
                $this->request->getSession()->getFlashBag()->add('success', $this->translator->trans('text.message_sent', [], 'contactForm'));
            } else {
                $this->request->getSession()->getFlashBag()->add('danger', $this->translator->trans('text.message_not_sent', ['%error%' => $event->getError()], 'contactForm'));
            }
        }

        // Clean honeypot field name from session after use
        $this->request->getSession()->remove('honeypotField');
        $this->request->getSession()->remove('honeypotLabel');

        return $this->getReferer();
    }
}

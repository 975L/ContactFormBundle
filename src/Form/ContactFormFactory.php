<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Form;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactFormFactory implements ContactFormFactoryInterface
{
    public function __construct(
        private readonly ConfigServiceInterface $configService,
        private readonly FormFactoryInterface $formFactory,
        private readonly RequestStack $requestStack,
    )
    {
    }

    public function create(string $name, ContactForm $contactForm, ContactFormEvent $event): Form
    {
        switch ($name) {
            case 'display':
                $config = [
                    'receiveCopy' => $event->getReceiveCopy(),
                    'gdpr' => $this->configService->getParameter('c975LContactForm.gdpr'),
                    'recaptcha3SiteKey' => $this->configService->getContainerParameter('karser_recaptcha3.site_key') !== 'my_site_key' ? $this->configService->getContainerParameter('karser_recaptcha3.site_key') : null,
                    'recaptcha3SecretKey' => $this->configService->getContainerParameter('karser_recaptcha3.secret_key') !== 'my_secret' ? $this->configService->getContainerParameter('karser_recaptcha3.secret_key') : null,
                ];
                break;
            default:
                $config = [];
                break;
        }

        // Get honeypot field name from session
        $request = $this->requestStack->getCurrentRequest();
        $honeypotFieldName = $request?->getSession()->get('honeypotField', 'username') ?? 'username';
        $honeypotLabel = $request?->getSession()->get('honeypotLabel', 'Username') ?? 'Username';

        return $this->formFactory->create(ContactFormType::class, $contactForm, [
            'config' => $config,
            'honeypot_field_name' => $honeypotFieldName,
            'honeypot_label' => $honeypotLabel
        ]);
    }
}

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
use c975L\ContactFormBundle\Entity\ContactFormField;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Repository\ContactFormFieldRepository;
use Nelmio\SecurityBundle\EventListener\ContentSecurityPolicyListener;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactFormFactory implements ContactFormFactoryInterface
{
    private readonly ?Request $request;

    public function __construct(
        private readonly ConfigServiceInterface $configService,
        private readonly FormFactoryInterface $formFactory,
        private readonly RequestStack $requestStack,
        private readonly ContactFormFieldRepository $contactFormFieldRepository,
        private readonly ?ContentSecurityPolicyListener $cspListener = null,
    ) {
        $this->request = $this->requestStack->getCurrentRequest();
    }

    // Builds the "customFields" config array (name/label/type/required) from the admin-managed ContactFormField entities
    private function getCustomFields(): array
    {
        return array_map(
            static fn (ContactFormField $field): array => [
                'name' => $field->getName(),
                'label' => $field->getLabel(),
                'type' => $field->getType(),
                'placeholder' => $field->getPlaceholder(),
                'required' => $field->isRequired(),
            ],
            $this->contactFormFieldRepository->findAllOrdered()
        );
    }

    public function create(string $name, ContactForm $contactForm, ContactFormEvent $event): Form
    {
        switch ($name) {
            case 'display':
                $config = [
                    'receiveCopy' => $event->getReceiveCopy(),
                    // Falls back to true if "site-form-gdpr" isn't seeded, e.g. SiteBundle isn't installed
                    'gdpr' => $this->configService->get('site-form-gdpr') ?? true,
                    'recaptcha3SiteKey' => $this->configService->hasParameter('recaptcha3-site-key') ? $this->configService->get('recaptcha3-site-key') : $this->configService->getContainerParameter('karser_recaptcha3.site_key'),
                    'recaptcha3SecretKey' => $this->configService->hasParameter('recaptcha3-secret-key') ? $this->configService->get('recaptcha3-secret-key') : $this->configService->getContainerParameter('karser_recaptcha3.secret_key'),
                    'customFields' => $this->getCustomFields(),
                ];
                break;
            default:
                $config = [];
                break;
        }

        // Get honeypot field name from session
        $honeypotFieldName = $this->request?->getSession()->get('honeypotField', 'username') ?? 'username';
        $honeypotLabel = $this->request?->getSession()->get('honeypotLabel', 'Username') ?? 'Username';

        return $this->formFactory->create(ContactFormType::class, $contactForm, [
            'config' => $config,
            'honeypot_field_name' => $honeypotFieldName,
            'honeypot_label' => $honeypotLabel,
            'csp_nonce' => $this->cspListener?->getNonce('script'),
        ]);
    }
}

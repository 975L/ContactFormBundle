<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Form\ContactFormType;
use c975L\ContactFormBundle\Form\ContactFormFactoryInterface;

/**
 * ContactFormFactory class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormFactory implements ContactFormFactoryInterface
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores FormFactoryInterface
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(
        ConfigServiceInterface $configService,
        FormFactoryInterface $formFactory
    )
    {
        $this->configService = $configService;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $name, ContactForm $contactForm, ContactFormEvent $event)
    {
        switch ($name) {
            case 'display':
                $config = array(
                    'receiveCopy' => $event->getReceiveCopy(),
                    'gdpr' => $this->configService->getParameter('c975LContactForm.gdpr'),
                );
                break;
            default:
                $config = array();
                break;
        }

        return $this->formFactory->create(ContactFormType::class, $contactForm, array('config' => $config));
    }
}

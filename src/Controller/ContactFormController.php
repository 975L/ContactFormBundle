<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use c975L\ContactFormBundle\Service\ContactFormServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactFormController extends AbstractController
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ContactFormServiceInterface $contactFormService,
    ) {
    }

    //DISPLAY
    #[Route(
        '/contact',
        name: 'contactform_display',
        methods: ['GET', 'POST']
    )]
    public function display(Request $request, ConfigServiceInterface $configService): Response
    {
        //Creates ContactForm
        $contactForm = $this->contactFormService->create();

        //Dispatch Event CREATE_FORM
        $event = new ContactFormEvent($request, $contactForm);
        $this->dispatcher->dispatch($event, ContactFormEvent::CREATE_FORM);

        //Defines form
        $form = $this->contactFormService->createForm('display', $contactForm, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Dispatch Event SEND_FORM
            $event = new ContactFormEvent($request, $form->getData());
            $this->dispatcher->dispatch($event, ContactFormEvent::SEND_FORM);

            //Sends email and redirects to defined referer
            $redirectUrl = $this->contactFormService->sendEmail($form, $event);
            if (null !== $redirectUrl) {
                return $this->redirect($redirectUrl);
            }

        }

        //Renders the form
        return $this->render('@c975LContactForm/forms/contact.html.twig', [
            'form' => $form->createView(),
            'site' => $configService->getParameter('c975LCommon.site'),
            'subject' => $contactForm->getSubject()
        ])->setMaxAge(3600);
    }
}

<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Form\ContactFormType;
use c975L\ContactFormBundle\Service\ContactFormServiceInterface;

/**
 * Main Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class ContactFormController extends Controller
{
    /**
     * Stores EventDispatcher
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Stores ContactFormService
     * @var ContactFormServiceInterface
     */
    private $contactFormService;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ContactFormServiceInterface $contactFormService
    )
    {
        $this->dispatcher = $dispatcher;
        $this->contactFormService = $contactFormService;
    }

    /**
     * Displays ContactForm and handles its submission
     * @return Response
     *
     * @Route("/contact",
     *      name="contactform_display")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function display(Request $request)
    {
        //Creates ContactForm
        $contactForm = $this->contactFormService->create();

        //Dispatch Event CREATE_FORM
        $event = new ContactFormEvent($request, $contactForm);
        $this->dispatcher->dispatch(ContactFormEvent::CREATE_FORM, $event);

        //Defines form
        $contactFormConfig = array(
            'receiveCopy' => $event->getReceiveCopy(),
            'gdpr' => $this->getParameter('c975_l_contact_form.gdpr'),
        );
        $form = $this->createForm(ContactFormType::class, $contactForm, array('contactFormConfig' => $contactFormConfig));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Dispatch Event SEND_FORM
            $event = new ContactFormEvent($request, $form->getData());
            $this->dispatcher->dispatch(ContactFormEvent::SEND_FORM, $event);

            //Sends email and redirects to defined referer
            $redirectUrl = $this->contactFormService->sendEmail($form, $event);
            if (null !== $redirectUrl) {
                return $this->redirect($redirectUrl);
            }
        }

        //Renders the form
        return $this->render('@c975LContactForm/forms/contact.html.twig', array(
            'form' => $form->createView(),
            'site' => $this->getParameter('c975_l_contact_form.site'),
            'subject' => $contactForm->getSubject(),
            ));
    }
}

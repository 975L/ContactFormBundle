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
use c975L\ContactFormBundle\Service\Email\ContactFormEmailInterface;
use c975L\ContactFormBundle\Service\Tools\ContactFormToolsInterface;

class ContactFormController extends Controller
{
    /**
    * @var EventDispatcherInterface
    */
    private $dispatcher;

    /**
     * @var ContactFormServiceInterface
    */
    private $contactFormService;

    /**
    * @var ContactFormEmailInterface
    */
    private $contactFormEmail;

    /**
    * @var ContactFormToolsInterface
     */
    private $contactFormTools;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ContactFormServiceInterface $contactFormService,
        ContactFormEmailInterface $contactFormEmail,
        ContactFormToolsInterface $contactFormTools
    )
    {
        $this->dispatcher = $dispatcher;
        $this->contactFormService = $contactFormService;
        $this->contactFormEmail = $contactFormEmail;
        $this->contactFormTools = $contactFormTools;
    }

    /**
     * Displays ContactForm and treats its submission
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
        $form = $this->createForm(ContactFormType::class, $contactForm, array(
            'receiveCopy' => $event->getReceiveCopy(),
            'gdpr' => $this->getParameter('c975_l_contact_form.gdpr'),
            ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Tests if it's not a bot that has used the form
            if ($this->contactFormTools->isNotBot($form->get('username')->getData())) {
                //Dispatch Event SEND_FORM
                $event = new ContactFormEvent($request, $form->getData());
                $this->dispatcher->dispatch(ContactFormEvent::SEND_FORM, $event);

                //Sends email
                $this->contactFormEmail->send($event, $form->getData());
            }

            //Redirects to defined referer
            $redirectUrl = $this->contactFormService->getReferer();
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

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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Form\ContactFormType;
use c975L\ContactFormBundle\Service\ContactFormService;

class ContactFormController extends Controller
{
    /**
     * @Route("/contact",
     *      name="contactform_display")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function display(Request $request, ContactFormService $contactFormService)
    {
        //Defines contactForm
        $subject = $contactFormService->getSubject();
        $contactForm = new ContactForm();
        $contactForm
            ->setName($contactFormService->getUserName())
            ->setEmail($contactFormService->getUserEmail())
            ->setSubject($subject)
            ;

        //Dispatch Event CREATE_FORM
        $dispatcher = new EventDispatcher();
        $event = new ContactFormEvent($contactForm);
        $dispatcher->dispatch(ContactFormEvent::CREATE_FORM, $event);

        //Defines form
        $contactFormService->defineReferer();
        $form = $this->createForm(ContactFormType::class, $contactForm, array(
            'receiveCopy' => $event->getReceiveCopy(),
            'gdpr' => $this->getParameter('c975_l_contact_form.gdpr'),
            ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Dispatch Event SEND_FORM
            $event = new ContactFormEvent($form->getData());
            $dispatcher->dispatch(ContactFormEvent::SEND_FORM, $event);

            //Sends email
            $contactFormService->sendEmail($event, $form->getData());

            //Redirects to url if defined in session
            $session = $request->getSession();
            $sessionRedirectUrl = $session->get('redirectUrl');
            if ($sessionRedirectUrl !== null) {
                $session->remove('redirectUrl');

                return $this->redirect($sessionRedirectUrl);
            }
        }

        //Renders the form
        return $this->render('@c975LContactForm/forms/contact.html.twig', array(
            'form' => $form->createView(),
            'site' => $this->getParameter('c975_l_contact_form.site'),
            'subject' => $subject,
            ));
    }
}
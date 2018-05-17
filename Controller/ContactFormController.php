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
    public function displayAction(Request $request)
    {
        //Gets subject if passed by url parameter "s"
        $subject = filter_var($request->query->get('s'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        //Defines the referer to redirect to after submission
        $session = $request->getSession();
        $session->set('redirectUrl', $request->headers->get('referer'));

        //Gets email and name if user is logged
        $user = $this->getUser();
        if ($user !== null) {
            $userEmail = $user->getEmail();
            $name = $user->getFirstname();
            $name .= $name != '' ? ' ' : '';
            $name .= $user->getLastname();
        } else {
            $userEmail = null;
            $name = null;
        }

        //Defines contact
        $contact = new ContactForm();
        $contact
            ->setName($name)
            ->setEmail($userEmail)
            ->setSubject($subject)
            ;

        //Dispatch event
        $dispatcher = $this->get('event_dispatcher');
        $event = new ContactFormEvent($contact);
        $dispatcher->dispatch(ContactFormEvent::CREATE_FORM, $event);

        //Defines form
        $form = $this->createForm(ContactFormType::class, $contact, array('receiveCopy' => $event->getReceiveCopy()));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Gets the translator
            $translator = $this->get('translator');

            //Gets the data
            $formData = $form->getData();

            //Dispatch event
            $dispatcher = $this->get('event_dispatcher');
            $emailData = false;
            $event = new ContactFormEvent($formData, $emailData);
            $dispatcher->dispatch(ContactFormEvent::SEND_FORM, $event);

            //EmailData has been filled after Event dispatch
            $emailData = $event->getEmailData();
            if (is_array($emailData) &&
                array_key_exists('subject', $emailData) &&
                array_key_exists('bodyData', $emailData) &&
                array_key_exists('bodyEmail', $emailData)
            ) {
                //Updates emailData
                if (!array_key_exists('sentFrom', $emailData)) {
                    $emailData['sentFrom'] = $this->getParameter('c975_l_contact_form.sentTo');
                }
                if (!array_key_exists('sentTo', $emailData)) {
                    $emailData['sentTo'] = $this->getParameter('c975_l_contact_form.sentTo');
                }
                if (!array_key_exists('sentCc', $emailData)) {
                    $emailData['sentCc'] = $formData->getEmail();
                }
                if (!array_key_exists('replyTo', $emailData)) {
                    $emailData['replyTo'] = $formData->getEmail();
                }
                if (!array_key_exists('ip', $emailData)) {
                    $emailData['ip'] = $request->getClientIp();
                }
                if (!array_key_exists('form', $emailData['bodyData'])) {
                    $emailData['bodyData']['form'] = $formData;
                }
                $emailData['body'] = $this->renderView($emailData['bodyEmail'], $emailData['bodyData']);
                unset($emailData['bodyEmail']);
                unset($emailData['bodyData']);
            //Otherwise defines generic email
            } elseif ($event->getError() === null) {
                $bodyEmail = '@c975LContactForm/emails/contact.html.twig';
                $bodyData = array(
                    '_locale' => $request->getLocale(),
                    'form' => $formData,
                    );
                $emailData = array(
                    'subject' => $formData->getSubject(),
                    'sentFrom' => $this->getParameter('c975_l_contact_form.sentTo'),
                    'sentTo' => $this->getParameter('c975_l_contact_form.sentTo'),
                    'sentCc' => $formData->getEmail(),
                    'replyTo' => $formData->getEmail(),
                    'body' => $this->renderView($bodyEmail, $bodyData),
                    'ip' => $request->getClientIp(),
                    );
            }

            //Removes sentCC if checkbox to receive copy hasn't been checked
            if ($formData->getReceiveCopy() !== true) {
                unset($emailData['sentCc']);
            }

            //Sends email
            if (is_array($emailData)) {
                $emailService = $this->get(\c975L\EmailBundle\Service\EmailService::class);
                $emailSent = $emailService->send($emailData, $this->getParameter('c975_l_contact_form.database'));

                //Message sent
                if ($emailSent === true) {
                    $flash = $translator->trans('text.message_sent', array(), 'contactForm');
                    $session->getFlashBag()->add('success', $flash);
                //Message not sent
                } else {
                    $flash = $translator->trans('text.message_not_sent', array('%error%' => ''), 'contactForm');
                    $session->getFlashBag()->add('danger', $flash);
                }
            //Displays error message provided in event
            } else {
                //Creates flash
                $flash = $translator->trans('text.message_not_sent', array('%error%' => $event->getError()), 'contactForm');
                $session->getFlashBag()->add('danger', $flash);
            }

            //Redirects to url if defined
            $sessionRedirectUrl = $session->get('redirectUrl');
            if ($sessionRedirectUrl !== null) {
                $session->remove('redirectUrl');
                return $this->redirect($sessionRedirectUrl);
            }
        }

        return $this->render('@c975LContactForm/forms/contact.html.twig', array(
            'form' => $form->createView(),
            'site' => $this->getParameter('c975_l_contact_form.site'),
            'subject' => $subject,
            ));
    }
}
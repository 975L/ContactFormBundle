<?php
/*
 * (c) 2017: 975l <contact@975l.com>
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
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Form\ContactFormType;

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
            -setSubject($subject)
            )
            ;

        //Defines form
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Gets the translator
            $translator = $this->get('translator');

            //Gets the data
            $formData = $form->getData();

            //The function testSubject() has to be overriden, in your own Controller, if needed, to return specific email content, see function below
            $emailData = $this->testSubject($request, $subject, $formData);

            //Defines data for generic email if testSubject didn't returned correct array
            if (!is_array($emailData) ||
                !array_key_exists('subject', $emailData) ||
                !array_key_exists('sentTo', $emailData) ||
                !array_key_exists('body', $emailData)) {
                $bodyEmail = '@c975LContactForm/emails/contact.html.twig';
                $bodyData = array(
                    'locale' => $request->getLocale(),
                    'form' => $formData,
                    'site' => $this->getParameter('c975_l_contact_form.site'),
                    'email' => $this->getParameter('c975_l_contact_form.sentTo'),
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
            //Sends email
            $emailService = $this->get(\c975L\EmailBundle\Service\EmailService::class);
            $emailService->send($emailData, $this->getParameter('c975_l_contact_form.database'));

            //Creates flash
            $flash = $translator->trans('text.message_sent', array(), 'contactForm');
            $session->getFlashBag()->add('success', $flash);

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


    //Test the value of $subject in order to return specific email data related to it
    //This function has to be overriden, in your own Controller, if needed, see README.md
    //$formData can be used if needed
    public function testSubject(Request $request, $subject, $formData)
    {
        //Any condition to fulfill
        if (1 == 2) {
            //Defines data for email
            $bodyEmail = 'AnyTemplate.html.twig';
            $bodyData = array(
                'AnyDataNeededByTemplate Or empty array',
                );
            //The following array, with keys, MUST be returend by the function to hydrate email
            $emailData = array(
                'subject' => 'subjectEmail',
                'sentTo' => 'sentToEmail',
                'sentCc' => 'sentCcEmail',
                'replyTo' => 'replyToEmail',
                'body' => $this->renderView($bodyEmail, $bodyData),
                );

            return $emailData;
        }

        //No subject found
        return false;
    }
}

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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Form\ContactFormType;
use c975L\EmailBundle\Entity\Email;

class ContactFormController extends Controller
{
    /**
     * @Route("/contact",
     *      name="contact")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function contactAction(Request $request)
    {
        //Gets subject if passed by url parameter "s"
        $subject = $request->query->get('s') !== null ? $request->query->get('s') : null;

        //Defines the referer to redirect to after submission
        $session = $request->getSession();
        $session->set('redirectUrl', $request->headers->get('referer'));

        //Gets email and name if user is logged
        $user = $this->getUser();
        if ($user !== null) {
            $email = $user->getEmail();
            $name = $user->getFirstname();
            $name .= $name != '' ? ' ' : '';
            $name .= $user->getLastname();
        }
        else {
            $email = null;
            $name = null;
        }

        //Defines contact
        $contactData = array(
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            );
        $contact = new ContactForm();
        $contact->setDataFromArray($contactData);

        //Defines form
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Gets the translator
            $translator = $this->get('translator');

            //Defines data for email
            $formData = $form->getData();
            $bodyEmail = '@c975LContactForm/emails/contact.html.twig';
            $bodyData = array(
                'locale' => $request->getLocale(),
                'form' => $formData,
                'site' => $this->getParameter('c975_l_contact_form.site'),
                'email' => $this->getParameter('c975_l_contact_form.sentTo'),
                );

            //Creates the email
            $body = $this->renderView($bodyEmail, $bodyData);
            $emailData = array(
                'mailer' => $this->get('mailer'),
                'subject' => $formData->getSubject(),
                'sentFrom' => $this->getParameter('c975_l_contact_form.sentTo'),
                'sentTo' => $this->getParameter('c975_l_contact_form.sentTo'),
                'sentCc' => $formData->getEmail(),
                'replyTo' => $formData->getEmail(),
                'body' => $body,
                'ip' => $request->getClientIp(),
                );
            $email = new Email();
            $email->setDataFromArray($emailData);

            //Persists Email in DB
            if ($this->getParameter('c975_l_contact_form.database') === true) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($email);
                $em->flush();
            }

            //Sends email
            $email->send();

            //Creates flash
            $flash = $translator->trans('text.message_sent');
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
            ));
    }
}

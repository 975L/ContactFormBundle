<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service;

class ContactFormService
{
    private $container;
    private $emailService;
    private $request;
    private $templating;
    private $tokenStorage;

    public function __construct(
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        \c975L\EmailBundle\Service\EmailService $emailService,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Twig_Environment $templating,
        \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
        ) {
        $this->container = $container;
        $this->emailService = $emailService;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
    }

    //Creates flash message
    public function createFlash($emailSent)
    {
        //Gets the translator
        $translator = $this->container->get('translator');

        //Gets the session
        $session = $this->request->getSession();

        //Message sent
        if (true === $emailSent) {
            $flash = $translator->trans('text.message_sent', array(), 'contactForm');
            $session->getFlashBag()->add('success', $flash);
        //Message not sent
        } else {
            $flash = $translator->trans('text.message_not_sent', array('%error%' => ''), 'contactForm');
            $session->getFlashBag()->add('danger', $flash);
        }
    }

    //Defines data to use for email
    public function defineEmailData($event, $formData)
    {
        $emailData = $event->getEmailData();

        //emailData has been updated after Event SEND_FORM dispatch
        if (is_array($emailData) &&
            array_key_exists('subject', $emailData) &&
            array_key_exists('bodyData', $emailData) &&
            array_key_exists('bodyEmail', $emailData)
        ) {
            //Updates emailData
            if (!array_key_exists('sentFrom', $emailData)) {
                $emailData['sentFrom'] = $this->container->getParameter('c975_l_contact_form.sentTo');
            }
            if (!array_key_exists('sentTo', $emailData)) {
                $emailData['sentTo'] = $this->container->getParameter('c975_l_contact_form.sentTo');
            }
            if (!array_key_exists('sentCc', $emailData)) {
                $emailData['sentCc'] = $formData->getEmail();
            }
            if (!array_key_exists('replyTo', $emailData)) {
                $emailData['replyTo'] = $formData->getEmail();
            }
            if (!array_key_exists('ip', $emailData)) {
                $emailData['ip'] = $this->request->getClientIp();
            }
            if (!array_key_exists('form', $emailData['bodyData'])) {
                $emailData['bodyData']['form'] = $formData;
            }
            $emailData['body'] = $this->templating->render($emailData['bodyEmail'], $emailData['bodyData']);
            unset($emailData['bodyEmail']);
            unset($emailData['bodyData']);
        //Otherwise defines generic email
        } elseif ($event->getError() === null) {
            $bodyEmail = '@c975LContactForm/emails/contact.html.twig';
            $bodyData = array(
                '_locale' => $this->request->getLocale(),
                'form' => $formData,
                );
            $emailData = array(
                'subject' => $formData->getSubject(),
                'sentFrom' => $this->container->getParameter('c975_l_contact_form.sentTo'),
                'sentTo' => $this->container->getParameter('c975_l_contact_form.sentTo'),
                'sentCc' => $formData->getEmail(),
                'replyTo' => $formData->getEmail(),
                'body' => $this->templating->render($bodyEmail, $bodyData),
                'ip' => $this->request->getClientIp(),
                );
        }

        //Removes sentCC if checkbox to receive copy hasn't been checked
        if ($formData->getReceiveCopy() !== true) {
            unset($emailData['sentCc']);
        }

        return $emailData;
    }

    //Defines the referer to redirect to after submission of form
    public function defineReferer()
    {
        $this->request->getSession()->set('redirectUrl', $this->request->headers->get('referer'));
    }

    //Gets email if user has signed in
    public function getUserEmail()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return (null !== $user && method_exists($user, 'getEmail')) ? $user->getEmail() : null;
    }

    //Gets name if user has signed in
    public function getUserName()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $name = null;

        if ($user !== null) {
            //Defines name from firstname
            if (method_exists($user, 'getFirstname')) {
                $name = $user->getFirstname();
                //Adds name from lastname
                if (method_exists($user, 'getLastname')) {
                    $name .=  ' ' . $user->getLastname();
                }
            //Defines name from username
            } elseif (method_exists($user, 'getUsername')) {
                $name = $user->getUsername();
            }
        }

        return $name;
    }

    //Gets subject if passed by url parameter "s"
    public function getSubject()
    {
        return filter_var($this->request->query->get('s'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    }

    //Sends email
    public function sendEmail($event, $formData)
    {
        //Defines data to use
        $emailData = $this->defineEmailData($event, $formData);

        //Sends email
        if (is_array($emailData)) {
            return $this->emailService->send($emailData, $this->container->getParameter('c975_l_contact_form.database'));
        }

        //Displays error message provided in event
        $flash = $this->container->get('translator')->trans('text.message_not_sent', array('%error%' => $event->getError()), 'contactForm');
        $this->request->getSession()->getFlashBag()->add('danger', $flash);

        return false;
    }
}

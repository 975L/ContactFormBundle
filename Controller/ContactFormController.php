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
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Service\ContactFormServiceInterface;

/**
 * Main Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class ContactFormController extends Controller
{
    /**
     * Stores EventDispatcherInterface
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Stores ContactFormServiceInterface
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

//DASHBOARD
    /**
     * Displays the dashboard
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/contact/dashboard",
     *      name="contactform_dashboard")
     * @Method({"GET", "HEAD", "POST"})
     */
    //
    public function dashboard()
    {
        $this->denyAccessUnlessGranted('c975lContactForm-dashboard');

        //Renders the dashboard
        return $this->render('@c975LContactForm/pages/dashboard.html.twig');
    }

//DISPLAY
    /**
     * Displays ContactForm and handles its submission
     * @return Response
     *
     * @Route("/contact",
     *      name="contactform_display")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function display(Request $request, ConfigServiceInterface $configService)
    {
        //Creates ContactForm
        $contactForm = $this->contactFormService->create();

        //Dispatch Event CREATE_FORM
        $event = new ContactFormEvent($request, $contactForm);
        $this->dispatcher->dispatch(ContactFormEvent::CREATE_FORM, $event);

        //Defines form
        $form = $this->contactFormService->createForm('display', $contactForm, $event);
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
            'site' => $configService->getParameter('c975LCommon.site'),
            'subject' => $contactForm->getSubject(),
        ));
    }

//CONFIG
    /**
     * Displays the configuration
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/contact/config",
     *      name="contactform_config")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function config(Request $request, ConfigServiceInterface $configService)
    {
        $this->denyAccessUnlessGranted('c975lContactForm-config', null);

        //Defines form
        $form = $configService->createForm('c975l/contactform-bundle');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Validates config
            $configService->setConfig($form);

            //Redirects
            return $this->redirectToRoute('contactform_dashboard');
        }

        //Renders the config form
        return $this->render('@c975LConfig/forms/config.html.twig', array(
            'form' => $form->createView(),
            'toolbar' => '@c975LContactForm',
        ));
    }
}

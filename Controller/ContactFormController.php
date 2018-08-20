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
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Form\ContactFormType;

class ContactFormController extends Controller
{
    /**
    * @var \Symfony\Component\HttpFoundation\Request
    */
    private $request;

    /**
     * @var \c975L\ContactFormBundle\Service\ContactFormServiceInterface
    */
    private $contactFormService;

    /**
    * @var \c975L\ContactFormBundle\Service\Dispatcher\ContactFormDispatcherInterface
    */
    private $contactFormDispatcher;

    /**
    * @var \c975L\ContactFormBundle\Service\Email\ContactFormEmailInterface
    */
    private $contactFormEmail;

    /**
    * @var \c975L\ContactFormBundle\Service\Tools\ContactFormToolsInterface
     */
    private $contactFormTools;

    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \c975L\ContactFormBundle\Service\ContactFormServiceInterface $contactFormService,
        \c975L\ContactFormBundle\Service\Dispatcher\ContactFormDispatcherInterface $contactFormDispatcher,
        \c975L\ContactFormBundle\Service\Email\ContactFormEmailInterface $contactFormEmail,
        \c975L\ContactFormBundle\Service\Tools\ContactFormToolsInterface $contactFormTools
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->contactFormService = $contactFormService;
        $this->contactFormDispatcher = $contactFormDispatcher;
        $this->contactFormEmail = $contactFormEmail;
        $this->contactFormTools = $contactFormTools;
    }

    /**
     * Displays ContactForm and treats its submission
     *
     * @return Response
     *
     * @Route("/contact",
     *      name="contactform_display")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function display()
    {
        //Creates ContactForm
        $contactForm = $this->contactFormService->create();

        //Dispatch Event CREATE_FORM
        $event = $this->contactFormDispatcher->dispatch(ContactFormEvent::CREATE_FORM, $contactForm);

        //Defines form
        $form = $this->createForm(ContactFormType::class, $contactForm, array(
            'receiveCopy' => $event->getReceiveCopy(),
            'gdpr' => $this->getParameter('c975_l_contact_form.gdpr'),
            ));
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Tests if it's not a bot that has used the form
            if ($this->contactFormTools->isNotBot($form->get('username')->getData())) {
                //Dispatch Event SEND_FORM
                $event = $this->contactFormDispatcher->dispatch(ContactFormEvent::SEND_FORM, $form->getData());

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

<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Service\ContactFormServiceInterface;
use c975L\ContactFormBundle\Service\User\ContactFormUserInterface;

class ContactFormService implements ContactFormServiceInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $request;

    /**
    * @var \c975L\ContactFormBundle\Service\User\ContactFormUserInterface
    */
    private $contactFormUser;

    public function __construct(
        RequestStack $requestStack,
        ContactFormUserInterface $contactFormUser
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->contactFormUser = $contactFormUser;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        //Adds time to session to check if a robot has filled the form
        if (null === $this->request->getSession()->get('time')) {
            $this->request->getSession()->set('time', time());
        }

        //Defines the referer
        $this->setReferer();

        //Defines the ContactForm
        $contactForm = new ContactForm();
        $contactForm
            ->setName($this->contactFormUser->getName())
            ->setEmail($this->contactFormUser->getEmail())
            ->setSubject($this->getSubject())
            ->setIp($this->request->getClientIp())
        ;

        return $contactForm;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        $subject = filter_var($this->request->query->get('s'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        return empty($subject) ? null : $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferer()
    {
        //Redirects to url if defined in session
        $sessionRedirectUrl = $this->request->getSession()->get('redirectUrl');
        if (null !== $sessionRedirectUrl) {
            $this->request->getSession()->remove('redirectUrl');

            return $sessionRedirectUrl;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setReferer()
    {
        $this->request->getSession()->set('redirectUrl', $this->request->headers->get('referer'));
    }
}

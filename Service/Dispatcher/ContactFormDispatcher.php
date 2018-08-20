<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service\Dispatcher;

use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Service\Dispatcher\ContactFormDispatcherInterface;

class ContactFormDispatcher implements ContactFormDispatcherInterface
{
    /**
    * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
    */
    private $dispatcher;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $request;

    public function __construct(
        \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack
        ) {
        $this->dispatcher = $dispatcher;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(string $eventName, ContactForm $contactForm)
    {
        $event = new ContactFormEvent($this->request, $contactForm);
        $this->dispatcher->dispatch($eventName, $event);

        return $event;
    }
}
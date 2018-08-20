<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service\Tools;

use c975L\ContactFormBundle\Service\Tools\ContactFormToolsInterface;

class ContactFormTools implements ContactFormToolsInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $request;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Symfony\Component\Translation\TranslatorInterface $translator
        ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function createFlash($object, $error = null)
    {
        $style = 'success';
        $options = array();

        switch ($object) {
            //Message sent
            case true:
                $flash = 'text.message_sent';
                break;
            //Message not sent
            case false:
                $flash = 'text.message_not_sent';
                $options = array('%error%' => $error);
                $style = 'danger';
                break;
        }

        if(isset($flash)) {
            $this->request->getSession()
                ->getFlashBag()
                ->add($style, $this->translator->trans($flash, $options, 'contactForm'))
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isNotBot($username)
    {
        $delay = 7;

        $bot = null === $this->request->getSession()->get('time');
        $bot = true === $bot ? true : $this->request->getSession()->get('time') + $delay > time();
        $bot = true === $bot ? true : null !== $username;

        return ! $bot;
    }
}
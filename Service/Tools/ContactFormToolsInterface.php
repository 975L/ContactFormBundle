<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service\Tools;

use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;

interface ContactFormToolsInterface
{
    /**
     * Creates flash message
     */
    public function createFlash($object);

    /**
     * Tests if delay is not too short and if honeypot has been filled, to avoid being used by bot
     * @return bool
     */
    public function isNotBot($username);
}

<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service;

use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;

interface ContactFormServiceInterface
{
    /**
     * Creates the contactForm
     *
     * @return ContactForm
     */
    public function create();

    /**
     * Gets subject if passed by url parameter "s"
     *
     * @return string|null
     */
    public function getSubject();

    /**
     * Returns referer defined in session
     *
     * @return string|null
     */
    public function getReferer();

    /**
     * Defines the referer to redirect to after submission of form
     */
    public function setReferer();
}
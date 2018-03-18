<?php
/*
 * (c) 2018: 975l <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ContactFormEvent extends Event
{
    const SEND_FORM = 'c975l_contactform.send.form';

    protected $formData;
    protected $emailData;

    public function __construct($formData, $emailData)
    {
        $this->formData = $formData;
        $this->emailData = $emailData;
    }

    public function getFormData()
    {
        return $this->formData;
    }

    public function getEmailData()
    {
        return $this->emailData;
    }

    public function setEmailData($emailData)
    {
        $this->emailData = $emailData;
    }
}
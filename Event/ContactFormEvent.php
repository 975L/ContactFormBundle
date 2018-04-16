<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ContactFormEvent extends Event
{
    const CREATE_FORM = 'c975l_contactform.create.form';
    const SEND_FORM = 'c975l_contactform.send.form';

    protected $formData;
    protected $emailData;
    protected $error;
    protected $receiveCopy = true;

    public function __construct($formData = null, $emailData = null)
    {
        $this->formData = $formData;
        $this->emailData = $emailData;
    }

    //FormData
    public function getFormData()
    {
        return $this->formData;
    }

    //emailData
    public function setEmailData($emailData)
    {
        $this->emailData = $emailData;
    }
    public function getEmailData()
    {
        return $this->emailData;
    }

    //receiveCopy
    public function setReceiveCopy($receiveCopy)
    {
        $this->receiveCopy = $receiveCopy;
    }
    public function getReceiveCopy()
    {
        return $this->receiveCopy;
    }

    //error
    public function setError($error)
    {
        $this->error = $error;
    }
    public function getError()
    {
        return $this->error;
    }
}
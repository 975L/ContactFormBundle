<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Event;

use c975L\ContactFormBundle\Entity\ContactForm;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Events to be dispatched throughout the lifecycle of ContactForm
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormEvent extends Event
{
    /**
     * Used to dispatch event "create.form"
     */
    const CREATE_FORM = 'c975l_contactform.create.form';

    /**
     * Used to dispatch event "send.form"
     */
    const SEND_FORM = 'c975l_contactform.send.form';

    /**
     * Stores data issued fy form
     * @var ContactForm
     */
    protected $formData;

    /**
     * Stores data used to create email
     * @var array
     */
    protected $emailData;

    /**
     * Stores error
     * @var string
     */
    protected $error;

    /**
     * If user wants to receive a copy of the email sent by ContactForm
     * @var bool
     */
    protected $receiveCopy = true;

    /**
     * Stores Request
     * @var Request
     */
    protected $request;

    public function __construct(Request $request, ContactForm $formData = null, array $emailData = null)
    {
        $this->request = $request;
        $this->formData = $formData;
        $this->emailData = $emailData;
    }

    /**
     * Get formData
     * @return ContactForm
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * Set emailData
     * @param array
     */
    public function setEmailData(array $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get emailData
     * @return array
     */
    public function getEmailData()
    {
        return $this->emailData;
    }

    /**
     * Set receiveCopy
     * @param bool
     */
    public function setReceiveCopy($receiveCopy)
    {
        $this->receiveCopy = $receiveCopy;
    }

    /**
     * Get receiveCopy
     * @return bool
     */
    public function getReceiveCopy()
    {
        return $this->receiveCopy;
    }

    /**
     * Get request
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set error
     * @param string|null
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * Get error
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }
}

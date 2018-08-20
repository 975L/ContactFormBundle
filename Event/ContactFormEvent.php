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
use Symfony\Component\HttpFoundation\Request;
use c975L\ContactFormBundle\Entity\ContactForm;

class ContactFormEvent extends Event
{
    /**
     * Used to dispatch "create.form"
     */
    const CREATE_FORM = 'c975l_contactform.create.form';

    /**
     * Used to dispatch "send.form"
     */
    const SEND_FORM = 'c975l_contactform.send.form';

    /**
     * Stores formData
     */
    protected $formData;

    /**
     * Stores emailData
     */
    protected $emailData;

    /**
     * Stores error
     */
    protected $error;

    /**
     * Stores receiveCopy
     */
    protected $receiveCopy = true;

    /**
     * Stores Request
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
     *
     * @return ContactForm
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * Set emailData
     */
    public function setEmailData(array $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get emailData
     *
     * @return array
     */
    public function getEmailData()
    {
        return $this->emailData;
    }

    /**
     * Set receiveCopy
     */
    public function setReceiveCopy($receiveCopy)
    {
        $this->receiveCopy = $receiveCopy;
    }

    /**
     * Get receiveCopy
     *
     * @return boolean
     */
    public function getReceiveCopy()
    {
        return $this->receiveCopy;
    }

    /**
     * Get request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }
}

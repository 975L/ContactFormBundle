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
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class ContactFormEvent extends Event
{
    final public const CREATE_FORM = 'c975l_contactform.create.form';
    final public const SEND_FORM = 'c975l_contactform.send.form';

    protected $error;

    protected $receiveCopy = true;

    public function __construct(
        protected Request $request,
        protected ContactForm $formData,
        protected array $emailData = [],
    )
    {
    }

    public function getFormData(): ContactForm
    {
        return $this->formData;
    }

    public function setEmailData(array $emailData): void
    {
        $this->emailData = $emailData;
    }

    public function getEmailData(): array
    {
        return $this->emailData;
    }

    public function setReceiveCopy($receiveCopy): void
    {
        $this->receiveCopy = $receiveCopy;
    }

    public function getReceiveCopy(): bool
    {
        return $this->receiveCopy;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setError($error): void
    {
        $this->error = $error;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}

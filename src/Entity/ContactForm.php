<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class ContactForm
{
    #[Assert\NotBlank]
    #[Assert\Email(message: "email.not_valid")]
    protected $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 128)]
    protected $name;

    #[Assert\NotBlank]
    #[Assert\Length(min: 20, max: 2000)]
    protected $message;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 256)]
    protected $subject;

    protected $receiveCopy;

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setReceiveCopy(?bool $receiveCopy): self
    {
        $this->receiveCopy = $receiveCopy;

        return $this;
    }

    public function getReceiveCopy(): ?bool
    {
        return $this->receiveCopy;
    }
}

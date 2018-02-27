<?php
/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class ContactForm
{

    /**
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "email.not_valid",
     *     checkMX = true
     * )
     */
    protected $email;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 2,
     *      max = 128,
     *      minMessage = "name.min_length",
     *      maxMessage = "name.max_length"
     * )
     */
    protected $name;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 20,
     *      max = 2000,
     *      minMessage = "message.min_length",
     *      maxMessage = "message.max_length"
     * )
     */
    protected $message;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 2,
     *      max = 256,
     *      minMessage = "subject.min_length",
     *      maxMessage = "subject.max_length"
     * )
     */
    protected $subject;



    /**
     * Set name
     *
     * @return ContactForm
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @return ContactForm
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set subject
     *
     * @return ContactForm
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set message
     *
     * @return ContactForm
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}

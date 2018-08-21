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
    /**
     * Stores the email address provided in ContactForm
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "email.not_valid",
     *     checkMX = true
     * )
     */
    protected $email;

    /**
     * Stores the name provided in ContactForm
     * @var string
     *
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
     * Stores the message provided in ContactForm
     * @var string
     *
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
     * Stores the subject provided in ContactForm
     * @var string
     *
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
     * Stores the ip address used to submit ContactForm
     * @var string
     */
    protected $ip;

    /**
     * If user wants to receive a copy of the email sent by ContactForm
     * @var bool
     */
    protected $receiveCopy;


    /**
     * Set email
     * @param string|null
     * @return ContactForm
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set name
     * @param string|null
     * @return ContactForm
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set message
     * @param string|null
     * @return ContactForm
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set subject
     * @param string|null
     * @return ContactForm
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set ip
     * @param string|null
     * @return ContactForm
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     * @return string|null
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set receiveCopy
     * @param bool
     * @return ContactForm
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
}

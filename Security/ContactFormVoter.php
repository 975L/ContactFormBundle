<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Security;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for ContactForm access
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormVoter extends Voter
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores AccessDecisionManagerInterface
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * Used for access to config
     * @var string
     */
    public const CONFIG = 'c975lContactForm-config';

    /**
     * Used for access to dashboard
     * @var string
     */
    public const DASHBOARD = 'c975lContactForm-dashboard';

    /**
     * Contains all the available attributes to check with in supports()
     * @var array
     */
    private const ATTRIBUTES = array(
        self::CONFIG,
        self::DASHBOARD,
    );

    public function __construct(
        ConfigServiceInterface $configService,
        AccessDecisionManagerInterface $decisionManager
    )
    {
        $this->configService = $configService;
        $this->decisionManager = $decisionManager;
    }

    /**
     * Checks if attribute and subject are supported
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * Votes if access is granted
     * @return bool
     * @throws LogicException
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //Defines access rights
        switch ($attribute) {
            case self::CONFIG:
            case self::DASHBOARD:
                return $this->decisionManager->decide($token, array($this->configService->getParameter('c975LContactForm.roleNeeded', 'c975l/contactform-bundle')));
                break;
        }

        throw new LogicException('Invalid attribute: ' . $attribute);
    }
}
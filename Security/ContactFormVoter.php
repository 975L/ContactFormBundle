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
     * Used for access to config
     * @var string
     */
    final public const CONFIG = 'c975lContactForm-config';

    /**
     * Used for access to dashboard
     * @var string
     */
    final public const DASHBOARD = 'c975lContactForm-dashboard';

    /**
     * Contains all the available attributes to check with in supports()
     * @var array
     */
    private const ATTRIBUTES = [self::CONFIG, self::DASHBOARD];

    public function __construct(
        /**
         * Stores ConfigServiceInterface
         */
        private readonly ConfigServiceInterface $configService,
        /**
         * Stores AccessDecisionManagerInterface
         */
        private readonly AccessDecisionManagerInterface $decisionManager
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, self::ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            self::CONFIG, self::DASHBOARD => $this->decisionManager->decide($token, [$this->configService->getParameter('c975LContactForm.roleNeeded', 'c975l/contactform-bundle')]),
            default => throw new LogicException('Invalid attribute: ' . $attribute),
        };
    }
}
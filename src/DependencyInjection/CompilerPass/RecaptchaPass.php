<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\DependencyInjection\CompilerPass;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ContactFormBundle\Service\ReCaptchaFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RecaptchaPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('karser_recaptcha3.google.recaptcha')) {
            return;
        }

        $fallbackSecret = $container->getParameter('karser_recaptcha3.secret_key');
        $fallbackScoreThreshold = $container->getParameter('karser_recaptcha3.score_threshold');

        $container->register(ReCaptchaFactory::class, ReCaptchaFactory::class)
            ->setArguments([new Reference(ConfigServiceInterface::class)])
            ->setPublic(false);

        $definition = $container->getDefinition('karser_recaptcha3.google.recaptcha');
        $definition->setFactory([new Reference(ReCaptchaFactory::class), 'create']);
        $definition->setArguments([
            $fallbackSecret,
            new Reference('karser_recaptcha3.google.request_method'),
            $fallbackScoreThreshold,
        ]);
        // Removed, as the threshold is now set by ReCaptchaFactory, allowing it to be overridden via ConfigService
        $definition->removeMethodCall('setScoreThreshold');
    }
}

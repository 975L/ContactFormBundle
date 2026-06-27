<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Form\Extension;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class Recaptcha3TypeExtension extends AbstractTypeExtension
{
    public function __construct(private readonly ConfigServiceInterface $configService) {}

    public static function getExtendedTypes(): iterable
    {
        return [Recaptcha3Type::class];
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($this->configService->hasParameter('recaptcha3-site-key')) {
            $siteKey = $this->configService->get('recaptcha3-site-key');
            if ($siteKey) {
                $view->vars['site_key'] = $siteKey;
            }
        }
    }
}

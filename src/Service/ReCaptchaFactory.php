<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Service;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use Karser\Recaptcha3Bundle\ReCaptcha\ReCaptcha;
use Karser\Recaptcha3Bundle\ReCaptcha\RequestMethod;

class ReCaptchaFactory
{
    public function __construct(private readonly ConfigServiceInterface $configService) {}

    public function create(string $fallbackSecret, RequestMethod $requestMethod): ReCaptcha
    {
        $secret = $fallbackSecret;
        if ($this->configService->hasParameter('recaptcha3-secret-key')) {
            $configSecret = $this->configService->get('recaptcha3-secret-key');
            if ($configSecret) {
                $secret = $configSecret;
            }
        }

        return new ReCaptcha($secret, $requestMethod);
    }
}

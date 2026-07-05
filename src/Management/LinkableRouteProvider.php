<?php
/*
 * (c) 2026: 975L <contact@975l.com>
 * (c) 2026: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace c975L\ContactFormBundle\Management;

use c975L\ConfigBundle\Management\LinkableRouteProviderInterface;

// Exposes the contact page's route, so it can be picked as a SiteBundle Menu item (navbar/footer)
// without SiteBundle being aware of ContactFormBundle
class LinkableRouteProvider implements LinkableRouteProviderInterface
{
    public function getLinkableRoutes(): array
    {
        return [
            'contactform_display' => [
                'label' => 'label.contact',
                'translation_domain' => 'contactForm',
            ],
        ];
    }
}

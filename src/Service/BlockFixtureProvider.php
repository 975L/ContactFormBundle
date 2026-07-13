<?php
/*
 * (c) 2026: 975L <contact@975l.com>
 * (c) 2026: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace c975L\ContactFormBundle\Service;

use c975L\UiBundle\Contract\BlockFixtureProviderInterface;

// Sample data for the "contact_form" block kind, shown in UiBundle's block gallery
// (c975L\UiBundle\Controller\Management\BlockGalleryController)
class BlockFixtureProvider implements BlockFixtureProviderInterface
{
    public function getFixtures(): array
    {
        return [
            // ContactFormType has no fields of its own - the block just renders the real contact
            // form via a sub-request (see templates/components/ContactForm/ContactFormBlock.html.twig)
            'contact_form' => [
                '' => [],
            ],
        ];
    }
}

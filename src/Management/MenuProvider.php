<?php
/*
 * (c) 2026: 975L <contact@975l.com>
 * (c) 2026: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace c975L\ContactFormBundle\Management;

use c975L\ConfigBundle\Management\MenuProviderInterface;
use c975L\ContactFormBundle\Controller\Management\ContactFormFieldCrudController;

class MenuProvider implements MenuProviderInterface
{
    public function getMenuSection(): array
    {
        return [
            'label' => 'label.contact',
            'translation_domain' => 'contactForm',
        ];
    }

    public function getMenus(): array
    {
        return [
            'contact_form_field' => [
                'controller' => ContactFormFieldCrudController::class,
                'label' => 'label.custom_fields',
                'translation_domain' => 'contactForm',
                'icon' => 'fas fa-list',
            ],
        ];
    }

    public function getLinks(): array
    {
        return [];
    }
}

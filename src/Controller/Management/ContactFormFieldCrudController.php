<?php
/*
 * (c) 2026: 975L <contact@975l.com>
 * (c) 2026: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Controller\Management;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ContactFormBundle\Entity\ContactFormField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use function Symfony\Component\Translation\t;

class ContactFormFieldCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ConfigServiceInterface $configService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ContactFormField::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('label')
                ->setLabel(t('label.field_label', [], 'contactForm')),
            SlugField::new('name')
                ->setTargetFieldName('label')
                ->setLabel(t('label.field_name', [], 'contactForm'))
                ->hideOnIndex(),
            ChoiceField::new('type')
                ->setLabel(t('label.field_type', [], 'contactForm'))
                ->setTranslatableChoices([
                    ContactFormField::TYPE_TEXT => t('label.field_type_text', [], 'contactForm'),
                    ContactFormField::TYPE_TEXTAREA => t('label.field_type_textarea', [], 'contactForm'),
                    ContactFormField::TYPE_EMAIL => t('label.field_type_email', [], 'contactForm'),
                    ContactFormField::TYPE_CHECKBOX => t('label.field_type_checkbox', [], 'contactForm'),
                ]),
            TextField::new('placeholder')
                ->setLabel(t('label.field_placeholder', [], 'contactForm'))
                ->setRequired(false)
                ->hideOnIndex(),
            BooleanField::new('required')
                ->setLabel(t('label.field_required', [], 'contactForm')),
            IntegerField::new('position')
                ->setLabel(t('label.position', [], 'contactForm'))
                ->setRequired(false),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $role = $this->configService->get('site-role-admin');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::INDEX, $role)
            ->setPermission(Action::NEW, $role)
            ->setPermission(Action::EDIT, $role)
            ->setPermission(Action::DELETE, $role)
            ->setPermission(Action::DETAIL, $role)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityPermission($this->configService->get('site-role-admin'))
            ->setDefaultSort(['position' => 'ASC'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('type')
        ;
    }
}

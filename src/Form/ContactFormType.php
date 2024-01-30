<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ContactForm FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ContactFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $subjectReadonly = null !== $options['data']->getSubject() ? true : false;

        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'label.username',
                    'required' => false,
                    'mapped' => false,
                    'data' => null,
                    'attr' => [
                        'placeholder' => 'label.username',
                        'autocomplete' => 'off'
                    ]
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'label.name',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'placeholder.name',
                        'autocomplete' => 'off'
                    ]
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'label.email',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'placeholder.email',
                        'autocomplete' => 'off'
                    ]
                ]
            )
            ->add(
                'subject',
                TextType::class,
                [
                    'label' => 'label.subject',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'placeholder.subject',
                        'readonly' => $subjectReadonly,
                        'autocomplete' => 'off'
                    ]
                ]
            )
            ->add(
                'message',
                TextareaType::class,
                [
                    'label' => 'label.message',
                    'required' => true,
                    'attr' => [
                        'rows' => 10,
                        'placeholder' => 'placeholder.message'
                    ]
                ]
            );
        //Receive copy
        if ($options['config']['receiveCopy']) {
            $builder
                ->add(
                    'receiveCopy',
                    CheckboxType::class,
                    [
                        'label' => 'label.receive_copy',
                        'required' => false,
                        'data' => false
                    ]
                );
        }
        //GDPR
        if ($options['config']['gdpr']) {
            $builder
                ->add(
                    'gdpr',
                    CheckboxType::class,
                    [
                        'label' => 'text.gdpr',
                        'translation_domain' => 'services',
                        'required' => true,
                        'mapped' => false
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \c975L\ContactFormBundle\Entity\ContactForm::class,
            'intention' => 'contactForm',
            'translation_domain' => 'contactForm'
        ]);

        $resolver->setRequired('config');
    }
}

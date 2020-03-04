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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subjectReadonly = null !== $options['data']->getSubject() ? true : false;

        $builder
            ->add('username', TextType::class, array(
                'label' => 'label.username',
                'required' => false,
                'mapped' => false,
                'data' => null,
                'attr' => array(
                    'placeholder' => 'label.username',
                    'autocomplete' => 'off',
                )))
            ->add('name', TextType::class, array(
                'label' => 'label.name',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'placeholder.name',
                    'autocomplete' => 'off',
                )))
            ->add('email', EmailType::class, array(
                'label' => 'label.email',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'placeholder.email',
                    'autocomplete' => 'off',
                )))
            ->add('subject', TextType::class, array(
                'label' => 'label.subject',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'placeholder.subject',
                    'readonly' => $subjectReadonly,
                    'autocomplete' => 'off',
                )))
            ->add('message', TextareaType::class, array(
                'label' => 'label.message',
                'required' => true,
                'attr' => array(
                    'rows' => 10,
                    'placeholder' => 'placeholder.message',
                )))
            ->add('ip', TextType::class, array(
                'label' => 'label.ip',
                'translation_domain' => 'services',
                'required' => true,
                'attr' => array(
                    'readonly' => true,
                )))
        ;
        //Receive copy
        if ($options['config']['receiveCopy']) {
            $builder
                ->add('receiveCopy', CheckboxType::class, array(
                    'label' => 'label.receive_copy',
                    'required' => false,
                    'data' => false,
                    ))
            ;
        }
        //GDPR
        if ($options['config']['gdpr']) {
            $builder
                ->add('gdpr', CheckboxType::class, array(
                    'label' => 'text.gdpr',
                    'translation_domain' => 'services',
                    'required' => true,
                    'mapped' => false,
                    ))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'c975L\ContactFormBundle\Entity\ContactForm',
            'intention'  => 'contactForm',
            'translation_domain' => 'contactForm',
        ));

        $resolver->setRequired('config');
    }
}

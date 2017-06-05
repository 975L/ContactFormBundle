<?php
/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{

    //Builds the form
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subjectReadonly = $options['data']->getSubject() !== null ? true : false;

        $builder
            ->add('name', TextType::class, array(
                'label' => 'label.name',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'placeholder.name',
                )))
            ->add('email', EmailType::class, array(
                'label' => 'label.email',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'placeholder.email',
                )))
            ->add('subject', TextType::class, array(
                'label' => 'label.subject',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'placeholder.subject',
                    'readonly' => $subjectReadonly,
                )))
            ->add('message', TextareaType::class, array(
                'label' => 'label.message',
                'required' => true,
                'attr' => array(
                    'rows' => 10,
                    'placeholder' => 'placeholder.message',
                )))
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'c975L\ContactFormBundle\Entity\ContactForm',
            'intention'  => 'contactForm',
        ));
    }


    public function getName()
    {
        return 'contactForm';
    }

}

<?php

namespace App\Form;

use App\Entity\AdminResponseContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResponseContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('subject', TextType::class, [
            'label' => 'Sujet',
            'required' => true,
            'sanitize_html' => true,
        ])
        ->add('message', TextareaType::class, [
            'label' => 'Message',
            'required' => true,
            'sanitize_html' => true,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdminResponseContact::class,
            'sanitize_html' => true,
        ]);
    }
}

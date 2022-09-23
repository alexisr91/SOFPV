<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
               'label' => 'Email',
               'attr'=>[
                'placeholder'=>'Votre email'
               ]
            ])
            ->add('nickname', TextType::class, [
                'label' => 'Pseudo',
                'attr'=>[
                    'placeholder'=>'Votre pseudo'
                   ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr'=>[
                    'placeholder'=>'Votre mot de passe'
                   ]
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirmez votre mot de passe',
                'mapped'=>false,
                'attr'=>[
                    'placeholder'=>'Confirmez votre mot de passe'
                   ]
                
                
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

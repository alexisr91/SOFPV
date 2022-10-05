<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

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
          
            ->add('password', RepeatedType::class, [
                'type'=> PasswordType::class,
                'invalid_message'=>'Les mots de passe ne correspondent pas.',
                'required'=>'true',
                'first_options'=> ['attr'=>['placeholder'=>'Votre mot de passe', 'minlength'=>8], 'label'=>false],
                'second_options'=>['attr'=>['placeholder'=>'Confirmez votre mot de passe'], 'label'=>false]     
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

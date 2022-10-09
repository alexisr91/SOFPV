<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'disabled'=>true
            ])
            ->add('nickname', TextType::class, [
                'label'=>'Votre pseudo'
            ]) 
            ->add('firstname', TextType::class, [
                'label'=>'Votre prénom'
            ]) 
            ->add('lastname', TextType::class, [
                'label'=>'Votre nom'
            ]) 
            ->add('avatar', FileType::class, [
                'label'=>'Ajoutez un avatar',
                'required'=>false
            ]) 
            ->add('banner', FileType::class, [
                'label'=>'Ajoutez une bannière',
                'required'=>false
            ]) 
            ->add('address', TextType::class, [
                'label'=>'Votre adresse postale'
            ]) 
            ->add('address_complement', TextType::class, [
                'label'=>'Complément d\'adresse'
            ])
            ->add('zip', TextType::class, [
                'label'=>'Votre code postal'
            ]) 
            ->add('city', TextType::class, [
                'label'=>'Votre ville'
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

<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Sequentially;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'disabled'=>true,
                'label'=>'Votre Email'
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
            ->add('address', TextType::class, [
                'label'=>'Votre adresse postale'
            ]) 
            ->add('address_complement', TextType::class, [
                'label'=>'Complément d\'adresse',
                'required'=>false
            ])
            ->add('zip', TextType::class, [
                'label'=>'Votre code postal'
            ]) 
            ->add('city', TextType::class, [
                'label'=>'Votre ville'
            ])  
            ->add('avatar', FileType::class, [
                'label'=>'Modifiez votre avatar',
                'required'=>false,
                'mapped'=> false,
                'constraints'=> [
                    new Sequentially([
                        new File([                               
                            'mimeTypes' => 'image/*',
                            'mimeTypesMessage' => 'Format invalide: Veuillez sélectionner un fichier image.',
                            'maxSize' => '5000k',
                            'maxSizeMessage'=> 'Fichier trop volumineux : le maximum autorisé est {{ limit }}k.',
                        ]),
                        new Image([
                            'allowSquare'=>true,
                            'minHeight'=>120,
                            'minWidth'=>120,
                            'maxHeight'=>1080,
                            'maxWidth'=>1920
                            
                        ])
                    ])
                ]
            ]) 
            ->add('banner', FileType::class, [
                'label'=>'Modifiez votre bannière',
                'required'=>false,
                'mapped'=> false,
                'constraints'=> [
                    new Sequentially([
                        new File([                               
                            'mimeTypes' => 'image/*',
                            'mimeTypesMessage' => 'Format invalide: Veuillez sélectionner un fichier image.',
                            'maxSize' => '5000k',
                            'maxSizeMessage'=> 'Fichier trop volumineux : le maximum autorisé est {{ limit }}k.',
                        ]),
                        new Image([
                            'allowSquare'=>false,
                            'allowPortrait'=>false,
                            'minHeight'=>300,
                            'minWidth'=>720,
                            'maxHeight'=>500,
                            'maxWidth'=>1920
                        ])
                    ])
                ]
            ]) 
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}

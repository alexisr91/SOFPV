<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Length;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'disabled' => true,
                'label' => 'Votre Email',
            ])
            ->add('nickname', TextType::class, [
                'disabled' => true,
                'label' => 'Votre pseudo',
                'sanitize_html' => true,
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Votre prénom',
                'sanitize_html' => true,
                'required'=>false
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Votre nom',
                'sanitize_html' => true,
                'required'=>false
            ])
            ->add('address', TextType::class, [
                'label' => 'Votre adresse postale',
                'sanitize_html' => true,
                'required'=>false
            ])
            ->add('address_complement', TextType::class, [
                'label' => 'Complément d\'adresse',
                'required' => false,
                'sanitize_html' => true,
            ])
            ->add('zip', TextType::class, [
                'label' => 'Votre code postal',
                'sanitize_html' => true,
                'required'=>false,
                'constraints' => [
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Veuillez renseigner un code postal valide (5 chiffres).',
                        'max' => 5,
                        'maxMessage' => 'Veuillez renseigner un code postal valide (5 chiffres).',
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Votre ville',
                'sanitize_html' => true,
                'required'=>false
            ])
            ->add('instagram', TextType::class, [
                'label' => false,
                'required' => false,
                'sanitize_html' => true,
                'attr' => ['placeholder' => 'Votre tag sur Instagram'],
            ])
            ->add('tiktok', TextType::class, [
                'label' => false,
                'required' => false,
                'sanitize_html' => true,
                'attr' => ['placeholder' => 'Votre tag sur TikTok'],
            ])
            ->add('facebook', TextType::class, [
                'label' => false,
                'required' => false,
                'sanitize_html' => true,
                'attr' => ['placeholder' => 'Votre nom sur Facebook'],
            ])
            ->add('avatar', FileType::class, [
                'label' => 'Modifiez votre avatar',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Sequentially([
                        new File([
                            'mimeTypes' => 'image/*',
                            'mimeTypesMessage' => 'Format invalide: Veuillez sélectionner un fichier image.',
                            'maxSize' => '5000k',
                            'maxSizeMessage' => 'Fichier trop volumineux : le maximum autorisé est {{ limit }}k.',
                        ]),
                        new Image([
                            'allowSquare' => true,
                            'minHeight' => 120,
                            'minWidth' => 120,
                            'maxHeight' => 1080,
                            'maxWidth' => 1920,
                        ]),
                    ]),
                ],
            ])
            ->add('banner', FileType::class, [
                'label' => 'Modifiez votre bannière',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Sequentially([
                        new File([
                            'mimeTypes' => 'image/*',
                            'mimeTypesMessage' => 'Format invalide: Veuillez sélectionner un fichier image.',
                            'maxSize' => '5000k',
                            'maxSizeMessage' => 'Fichier trop volumineux : le maximum autorisé est {{ limit }}k.',
                        ]),
                        new Image([
                            'allowSquare' => false,
                            'allowPortrait' => false,
                            'minHeight' => 300,
                            'minWidth' => 720,
                            'maxHeight' => 500,
                            'maxWidth' => 1920,
                        ]),
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'sanitize_html' => true,
        ]);
    }
}

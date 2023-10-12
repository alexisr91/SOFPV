<?php

namespace App\Form;

use App\Entity\Drone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Sequentially;

class DroneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('frame', TextType::class, [
                'label' => 'Frame',
                'sanitize_html' => true,
            ])
            ->add('motors', TextType::class, [
                'label' => 'Moteurs',
                'sanitize_html' => true,
            ])
            ->add('fc', TextType::class, [
                'label' => 'FC',
                'sanitize_html' => true,
            ])
            ->add('esc', TextType::class, [
                'label' => 'ESC',
                'sanitize_html' => true,
            ])
            ->add('cam', TextType::class, [
                'label' => 'Caméra',
                'sanitize_html' => true,
            ])
            ->add('reception', ChoiceType::class, [
                'choices' => [
                    'Crossfire' => 'Crossfire',
                    'FrSky' => 'FrSky',
                    'ExpressLRS' => 'ExpressLRS',
                    'Autre' => 'Autre',
                ],
                'label' => 'Protocole de réception',
            ])
            ->add('lipoCells', ChoiceType::class, [
                'choices' => [
                    '1S' => 1,
                    '2S' => 2,
                    '3S' => 3,
                    '4S' => 4,
                    '5S' => 5,
                    '6S' => 6,
                ],
                'label' => 'Voltage',
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                // vérification séquentielle pour valider les différentes contraintes liées au fichier ET à l'image
                    new Sequentially([
                        new File([
                            'mimeTypes' => 'image/*',
                            'mimeTypesMessage' => 'Format invalide: Veuillez sélectionner un fichier image.',
                            'maxSize' => '5000k',
                            'maxSizeMessage' => 'Fichier trop volumineux : le maximum autorisé est {{ limit }}k.',
                        ]),
                        new Image([
                            'allowSquare' => true,
                            'minHeight' => 50,
                            'minWidth' => 50,
                            'maxHeight' => 1080,
                            'maxWidth' => 1920,
                        ]),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Drone::class,
            'sanitize_html' => true,
        ]);
    }
}

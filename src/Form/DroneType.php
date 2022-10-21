<?php

namespace App\Form;

use App\Entity\Drone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DroneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('frame', TextType::class, [
                'label' => 'Frame'
            ])
            ->add('motors', TextType::class, [
                'label'=> 'Moteurs'
            ])
            ->add('fc', TextType::class, [
                'label' => 'FC'
            ])
            ->add('esc', TextType::class, [
                'label'=> 'ESC'
            ])
            ->add('cam', TextType::class, [
                'label'=> 'Caméra'
        
            ])
            ->add('reception', ChoiceType::class, [
                'choices'=> [
                    'Crossfire' => 'Crossfire',
                    'FrSky'=>'FrSky',
                    'ExpressLRS'=> 'ExpressLRS',
                    'Autre'=>'Autre'                   
                ], 
                'label'=>'Protocole de réception'
            ])
            ->add('lipoCells', ChoiceType::class, [
                'choices'=> [
                    '1S'=> 1,
                    '2S'=> 2,
                    '3S'=> 3,
                    '4S'=> 4,
                    '5S'=> 5,
                    '6S'=> 6
                ],
                'label'=>'Voltage'
            ])
            ->add('image', FileType::class, [
                'required'=>false,
                'mapped'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Drone::class
        ]);
    }
}

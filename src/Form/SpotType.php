<?php

namespace App\Form;

use App\Entity\MapSpot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SpotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required'=>true,
                'label'=>'Nom du spot',
                'attr'=>['placeholder'=>'Ex: La Sabla']
                ])
            ->add('authorization',ChoiceType::class, [
                'choices'=>[
                    'Public' => 'Public',
                    'Télépilotes Pro' => 'Télépilotes Pro'
                ],
                'label' => 'Type d\'autorisation'
            ])
            ->add('address', TextType::class , [
               'label'=>'Adresse la plus proche',
               'required'=>true,
               'attr'=> ['placeholder'=>'Ex: 5 rue Gilbert Affre, 31830 Plaisance-du-Touch'],            
            ])
            ->add('longitude', NumberType::class, [
                'required'=>true,
                'label'=>'Longitude'
            ])
            ->add('latitude', NumberType::class, [
                'required'=>true,
                'label'=>'Latitude'
            ])
            ->add('adminMapSpot', CheckboxType::class, [
                'label'=>'Cochez si vous souhaitez que les sessions soient uniquement organisées par vous.',
                'required'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MapSpot::class,
        ]);
    }
}

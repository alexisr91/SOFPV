<?php

namespace App\Form;

use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label'=>'Date de la session',
                'widget'=>'choice',
                'attr' => ['id' => 'datepicker'],
                'format' => 'ddMMyyyy',
                'years'=> range(date('Y'), date('Y')+1 )
                
            ] )
            ->add('timesheet', ChoiceType::class, [
                'choices'=> [
                    'Matin'=>'matin',
                    'Après-midi'=>'après-midi'
                ],
                'label'=>'Tranche horaire'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}

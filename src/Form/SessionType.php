<?php

namespace App\Form;

use App\Entity\Session;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label' => 'Date de la session',
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker'],
                'label' => false,
                'format' => 'dd-MM-yyyy',
                'constraints' => [
                    new GreaterThanOrEqual((new \DateTime('now'))->format('d-M-y'), message: "Vous ne pouvez pas créer de sessions à une date antérieure à aujourd'hui."),
                ],
            ])
            ->add('timesheet', ChoiceType::class, [
                'choices' => [
                    'Matin' => 'matin',
                    'Après-midi' => 'après-midi',
                ],
                'label' => 'Tranche horaire',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'sanitize_html' => true,
        ]);
    }
}

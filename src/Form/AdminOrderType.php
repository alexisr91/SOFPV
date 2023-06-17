<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\OrderStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class AdminOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('address', TextType::class, [
            'attr' => [
                'placeholder' => 'Adresse',
            ],
            'mapped' => false,
            'label' => false,
            'required' => false,
            'sanitize_html' => true,
        ])
        ->add('addressComplement', TextType::class, [
            'attr' => [
                'placeholder' => "Complément d'adresse : N° d'appartement, résidence, etc.",
            ],
            'mapped' => false,
            'label' => false,
            'required' => false,
            'sanitize_html' => true,
        ])
        ->add('zip', TextType::class, [
            'attr' => [
                'placeholder' => 'Code Postal',
            ],
            'mapped' => false,
            'label' => false,
            'required' => false,
            'sanitize_html' => true,
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
            'attr' => [
                'placeholder' => 'Ville',
            ],
            'mapped' => false,
            'label' => false,
            'required' => false,
            'sanitize_html' => true,
        ])
            ->add('trackerID', TextType::class, [
                'label' => false,
                'required' => false,
                'sanitize_html' => true,
                'attr' => ['placeholder' => 'Numéro de suivi du colis']])
            ->add('delivery_status', EntityType::class, [
                'class' => OrderStatus::class,
                'label' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'sanitize_html' => true,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Transporter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'sanitize_html' => true,
                'required'=>true,
                'mapped'=>false,
                'label'=>false,
                'attr'=> [
                    'placeholder'=>'Votre prénom'
                ]
            ])
            ->add('lastname', TextType::class, [
                'sanitize_html' => true,
                'required'=>true,
                'mapped'=>false,
                'label'=>false,
                'attr'=> [
                    'placeholder'=>'Votre nom'
                ]
            ])
            ->add('deliveryAddress', ChoiceType::class, [
                'choices' => [
                    'Choisir cette adresse' => 'user_address',
                    'Livrer à une autre adresse' => 'other_address',
                ],
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label' => false,
                'empty_data' => 'other_address',
            ])
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
            ->add('transporter', EntityType::class, [
                  'class' => Transporter::class,
                  'multiple' => false,
                  'expanded' => true,
                  'label' => false,
            ])
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

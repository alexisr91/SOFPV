<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Test\FormInterface;

class ProductQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
        ->add('quantity', IntegerType::class, [
            'label'=> false,
            'data'=>1,
            'constraints'=> [
                new Positive()
            ]
           
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

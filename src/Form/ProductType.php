<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Sequentially;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'sanitize_html' => true,
            ]
            )->add('price_HT', NumberType::class, [
                'label' => 'Prix HT',
            ])
            ->add('price_TTC', NumberType::class, [
                'label' => 'Prix TTC',
                'disabled' => true,
                'help' => 'Le prix TTC sera automatiquement calculé avec la TVA française.',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du produit',
                'sanitize_html' => true,
            ])
            ->add('image', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'help' => 'Format maximum: 2000x2000px',
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
                            'minHeight' => 400,
                            'minWidth' => 400,
                            'maxHeight' => 2000,
                            'maxWidth' => 2000,
                        ]),
                    ]),
                ],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'allow_file_upload' => true,
            'sanitize_html' => true,
        ]);
    }
}

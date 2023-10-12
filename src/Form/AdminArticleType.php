<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image as ConstraintsImage;
use Symfony\Component\Validator\Constraints\Sequentially;

class AdminArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, [
            'label' => 'Titre de l\'article',
            'required' => true,
             'sanitize_html' => true,
        ])
        ->add('category', EntityType::class, [
            'class' => Category::class,
            'label' => 'Catégorie',
            'required' => true,
        ])
        ->add('content', TextareaType::class, [
            'label' => 'Contenu de l\'article',
            'attr' => ['placeholder' => 'Ajoutez un texte à votre article', 'rows' => 7, 'cols' => 7],
            'required' => true,
            'sanitize_html' => true,
        ])
        ->add('video', VideoType::class, ['required' => false])
        ->add('images', FileType::class, [
            'label' => false,
            'multiple' => true,
            'mapped' => false,
            'required' => false,
            'help' => 'Vous pouvez transférer un maximum de 8 images par article. Format maximum: 2000x2000px.',
            // contraintes pour les images
            'constraints' => [
                new All([
                    'constraints' => [
                        // vérification séquentielle pour valider les différentes contraintes liées au fichier ET à l'image
                        // sequential check for validating all constraints associated to the file and the image
                        new Sequentially([
                            new File([
                                'mimeTypes' => 'image/*',
                                'mimeTypesMessage' => 'Format invalide: Veuillez sélectionner un fichier image.',
                                'maxSize' => '5000k',
                                'maxSizeMessage' => 'Fichier trop volumineux : le maximum autorisé est {{ limit }}k.',
                            ]),
                            new ConstraintsImage([
                                'allowSquare' => true,
                                'minHeight' => 400,
                                'minWidth' => 600,
                                'maxHeight' => 2000,
                                'maxWidth' => 2000,
                            ]),
                        ]),
                    ],
                ]),
            ],
        ])
        ->add('adminNews', CheckboxType::class, [
            'label' => 'Souhaitez-vous que cet article soit l\'actualité "à la une" de la page d\'accueil ?',
            'required' => false,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'sanitize_html' => true,
        ]);
    }
}

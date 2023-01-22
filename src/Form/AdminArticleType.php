<?php

namespace App\Form;

use App\Entity\Article;
use App\Form\ImageType;
use App\Form\VideoType;
use App\Form\CategoryType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdminArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class , [
            'label'=>'Titre de l\'article',
            'required'=>true
        ])
        ->add('category', CategoryType::class, [
            'label'=>'Catégorie',
            'required'=>true
        ])
        ->add('content', TextareaType::class, [
            'label'=> 'Contenu de l\'article',
            'attr'=> ['placeholder'=>'Ajoutez un texte à votre article', 'rows'=>7, 'cols'=>7 ]
        ])
        ->add('video', VideoType::class, ['required'=>false])
        ->add('images', CollectionType::class, [
            'entry_type'=>ImageType::class,
            'allow_add'=>true,
            'allow_delete'=>true,
            'label'=>false,
            'prototype'=>true,
            'by_reference'=>false,
            'required'=>false,
            'mapped'=>false
        ])
        ->add('adminNews', CheckboxType::class, [
            'label'=>'Souhaitez-vous que cet article soit l\'actualité "à la une" de la page d\'accueil ?'
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}

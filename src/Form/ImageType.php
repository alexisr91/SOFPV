<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('source',FileType::class, [
                'label'=>false
            ])
            ->add('caption', TextType::class, [
                'attr'=>[
                     'placeholder'=> 'Titre de l\'image',   
                ],
                'required'=>false,
                'label'=>false
            
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'allow_file_upload' => true
        ]);
    }
}

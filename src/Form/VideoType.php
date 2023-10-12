<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la vidéo',
                'required' => false,
                'sanitize_html' => true,
                'attr' => ['placeholder' => 'Optionnel'],
            ])
            ->add('source', FileType::class, [
                'label' => 'Téléversez une vidéo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => 'video/*',
                        'mimeTypesMessage' => 'Format invalide: Veuillez sélectionner un fichier vidéo.',
                        'maxSize' => '30M',
                    ]),
                ],
            ])
            ->add('link', UrlType::class, [
                'label' => 'Insérez une URL',
                'mapped' => false,
                'trim' => true,
                'required' => false,
                'attr' => ['placeholder' => 'Ex: https://www.youtube.com/watch?v=xxxxxxxxxxxx ...'],
            ])
            ->add('link', UrlType::class, [
                'label' => 'Insérez une URL',
                'mapped' => false,
                'trim' => true,
                'required' => false,
                'attr' => ['placeholder' => 'Ex: https://www.youtube.com/watch?v=xxxxxxxxxxxx ...'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
            'allow_extra_fields' => true,
            'sanitize_html' => true,
        ]);
    }
}

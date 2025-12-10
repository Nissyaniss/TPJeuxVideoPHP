<?php

namespace App\Form;

use App\Entity\Editeur;
use App\Entity\Genre;
use App\Entity\JeuVideo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class JeuVideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('developpeur')
            ->add('dateSortie')
            ->add('prix', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Prix',
            ])
            ->add('description')
            // ->add('imageUrl') // Replaced by file upload
            ->add('imageFile', FileType::class, [
                'label' => 'Image du jeu',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WEBP)',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/jpeg, image/png, image/webp',
                ],
            ])
            ->add('editeur', EntityType::class, [
                'class' => Editeur::class,
                'choice_label' => 'nom',
            ])
            ->add('genre', EntityType::class, [
                'class' => Genre::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JeuVideo::class,
        ]);
    }
}

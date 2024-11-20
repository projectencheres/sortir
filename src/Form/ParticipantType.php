<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\DBAL\Types\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Image;


class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, ['label' => 'Pseudo'])
            ->add('prenom', TextType::class, ['label' => 'Prenom'])
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('telephone', IntegerType::class, ['label' => 'Telephone'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add(
                'password', PasswordType::class, [
                    'label' => 'Mot de passe',
                    'required' => false,
                ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirmation mot de passe',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new EqualTo(propertyPath: 'password', message: 'Les mots de passe ne correspondent pas')
                ],
            ])

            ->add('site', EntityType::class, [
                'label' => 'Ville de rattachement',
                'class' => Site::class,
                'placeholder' => 'Choisissez un site',
            ])
            ->add('photo', FileType::class, [
                'label' => 'Upload Photo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}

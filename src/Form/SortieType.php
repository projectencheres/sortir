<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Ville;
use App\Entity\Sortie;
use DateTimeImmutable;
use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType; 
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                // 'format' => 'yyyy-MM-dd HH:mm',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée(en minutes)',
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
                // 'format' => 'yyyy-MM-dd',
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
            ])

            ->add('site', TextType::class, [
                'label' => 'Ville organisatrice :',
                'disabled' => true, // ou prérempli si nécessaire
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
                'label' => 'Ville :',
                'placeholder' => '-- Sélectionnez une ville --',
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'label' => 'Lieu :',
                'placeholder' => '-- Sélectionnez un lieu --',
                'attr' => ['id' => 'lieu-selector'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}

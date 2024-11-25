<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SortieCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (minutes)',
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre maximum de participants',
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Informations supplémentaires',
                'required' => false,
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'label' => 'Site',
                'data' => $options['site'],
                'disabled' => true,
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'label' => 'Lieu existant',
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un lieu',
                'required' => false,
                'attr' => [
                            'data-nom' => 'nom',
                            'data-rue' => 'rue',
                            'data-latitude' => 'latitude',
                            'data-longitude' => 'longitude',
                            'data-ville' => 'ville',
                        ],
                // 'choice_attr' => function(Lieu $lieu){
                //     return[
                //         'data-lieu-nom' => $lieu->getNom(),
                //         'data-lieu-rue' => $lieu->getRue(),
                //         'data-lieu-ville' => $lieu->getVille(),
                //         'data-lieu-code-postal' => $lieu->getCodePostal(),
                //         'data-lieu-latitude' => $lieu->getLatitude(),
                //         'data-lieu-longitude' => $lieu->getLongitude(),
                //     ];
                // },
            ])
            ->add('lieuCreation', LieuType::class, [
                'label' => 'Créer un nouveau lieu',
                'required' => false, 
                'mapped' => false, 
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'site' => null,
        ]);
    }
}

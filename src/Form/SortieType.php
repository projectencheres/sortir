<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\LieuType;
use DateTimeImmutable;
use App\Entity\Participant;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use PhpOffice\PhpWord\Element\CheckBox;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
                    // 'widget' => 'choice',
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
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'label' => 'Site',
                'placeholder' => 'Choisissez un site',
            ])
            ->add('utiliserLieuExistant', CheckboxType::class, [
                'label' => 'Choisir un lieu existant',
                'mapped' => false,
                'required' => false,
            ])
    
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'label' => 'Lieu',
                'placeholder' => 'Choisissez un lieu',
                // 'choices' => $options['lieux'],

            ])
            ->add('nouveauLieu', LieuType::class, [
                'label' => 'Nouveau lieu',
                'required' => false,
                'mapped' => false,
            ])
        ;
        // Ajout d'un ecouteur  pour desactiver le champ lieu si le champ nouveauLieu est rempli
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event){
            $data = $event->getData();
            $form = $event->getForm();
            if (isset($data['utiliserLieuExistant']) && $data['utiliserLieuExistant'] ) {
                $form->remove('nouveauLieu');
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'lieux' => null,
        ]);
    }
}

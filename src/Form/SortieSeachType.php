<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;

use App\Entity\Participant;
use PhpOffice\PhpWord\Element\CheckBox;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SortieSeachType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', SearchType::class, [
                'label' => 'Le nom de la sortie contient :',
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Entre',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'et',
                'required' => false,
            ])
            ->add('organisateur', CheckboxType::class, [
                // 'class' => Participant::class,
                // 'choice_label' => 'pseudo',
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false,
            ])
            ->add('inscrit', CheckboxType::class, [
                // 'class' => Participant::class,
                // 'choice_label' => 'pseudo',
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false,
            ])
            ->add('nonInscrit', CheckboxType::class, [
                // 'class' => Participant::class,
                // 'choice_label' => 'pseudo',
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false,
            ])
            ->add('passees', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false,
            ])   
            ->add('site', EntityType::class, [
                'class' => Site::class,
                // 'choice' => 'nom',
                'label' => 'Site',
                'required' => false,
            ])
            // ->add('search', SubmitType::class, [
            //     'label' => 'Rechercher',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            
        ]);
    }
}

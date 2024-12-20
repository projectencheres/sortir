<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Faker\Core\Number;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class, [
                'label' => 'Nom du lieu',
                'required' => true,
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'required' => true,
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'required' => true,
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'required' => false,
            ])
            ->add('ville', EntityType::class, [
                'label' => 'Ville',
                'required' => true,
                'class' => Ville::class,
            ])
            ->add('codePostal', IntegerType::class, [
                'label' => 'Code Postal',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}

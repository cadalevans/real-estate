<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street')
            //->add('city')
            ->add('city', ChoiceType::class, [
                'choices' => [
                    'New York, CA' => 'New York, CA',
                    'Paris' => 'Paris',
                    'Casablanca' => 'Casablanca',
                    'Tokyo' => 'Tokyo',
                    'Marraekch' => 'Marraekch',
                    'Kyoto, Shibua' => 'Kyoto, Shibua'
                ],
                'label' => 'Property City',
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true',
                    'data-live-search-style' => 'begins',
                    'title' => 'Select your city'
                ]
            ])
            ->add('state')
            ->add('postalCode')
            ->add('country')
            //->add('property')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}

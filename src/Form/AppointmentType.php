<?php

namespace App\Form;

use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
         /*   ->add('dateTime' ,DateTimeType::class, [
        'widget' => 'single_text',
        'html5' => false,
        'attr' => ['class' => 'js-datepicker'],

    ])*/
           // ->add('status')
            //->add('price')
            //->add('user')
            //->add('property')
            //->add('visit')
         /*   ->add('appdate',DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker'],
            ])*/
            ->add('appdate', DateTimeType::class, [
                'label' => 'Appointment Date',
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new GreaterThan([
                        'value' => 'today',
                        'message' => 'The appointment date must be greater than today',
                    ]),
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}

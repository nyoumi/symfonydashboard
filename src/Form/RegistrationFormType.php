<?php

namespace App\Form;

use App\Security\User\WebserviceUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country_code', TextType::class,[
                'required' => true,
            ])
            ->add('phone_number', TextType::class,[
                'required' => true,
            ])
            ->add('email', EmailType::class,[
                'required'=>true
                ])
            ->add('firstname', TextType::class,[
                'required'=>true
            ])
            ->add('lastname', TextType::class,[
                'required'=>true
            ])

            /*           ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])*/
            ->add('plain_password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'required' => true,

            ])
            ->add('save', SubmitType::class, ['label' => 'S\'enregistrer'])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WebserviceUser::class,
        ]);
    }
}

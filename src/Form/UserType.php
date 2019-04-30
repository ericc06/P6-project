<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'username'
            ])
            ->add(
                'firstName',
                TextType::class,
                [
                    'required' => false,
                    'empty_data' => "",
                    'label' => 'firstname'
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'required' => false,
                    'empty_data' => "",
                    'label' => 'lastname'
                ]
            )
            ->add('email', EmailType::class, [
                'label' => 'email_address'
            ])
            ->add('password', PasswordType::class, [
                'label' => 'password'
            ])
            ->add('avatar', FileType::class, [
                'label' => 'profile_avatar',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'gui'
        ]);
    }
}

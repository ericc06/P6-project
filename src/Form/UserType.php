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
                'label' => 'username',
                'translation_domain' => 'gui',
            ])
            ->add(
                'firstName',
                TextType::class,
                array(
                    'required' => false,
                    'empty_data' => "",
                )
            )
            ->add(
                'lastName',
                TextType::class,
                array(
                    'required' => false,
                    'empty_data' => "",
                )
            )
            ->add('email', EmailType::class, [
                'label' => 'email_address',
                'translation_domain' => 'gui',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'password',
                'translation_domain' => 'gui',
            ])
            ->add('avatar', FileType::class, [
                'label' => 'profile_avatar',
                'translation_domain' => 'gui',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

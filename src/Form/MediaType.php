<?php

namespace App\Form;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileUrl', TextType::class, array('required'=>false))
            ->add('file', FileType::class, array('required'=>false))
            ->add('title', TextType::class)
            ->add('alt', TextType::class)
            /*->add('defaultCover', CheckboxType::class, array(
                'label' => 'Default cover?',
                'required' => false,
            ))
            */
            ->add('defaultCover', ChoiceType::class, array(
                'expanded' => true,
                'multiple' => false,
                //'choice_name' => 'default_image',
                'label' => 'Default cover?',
                'placeholder' => 'Use it as the default cover image',
                'required' => false,
                'data'   => false
            ))
            ->add('fileType', HiddenType::class, array(
                'data' => 'value_to_be_replaced',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}

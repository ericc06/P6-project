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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class MediaType extends AbstractType
{
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->i18n = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            

        $builder
            ->add('fileUrl', TextType::class, array(
                'required'=>false,
                'label' => $this->i18n->trans('video_url'),
            ))
            ->add('file', FileType::class, array(
                'required'=>false,
                'label' => $this->i18n->trans('image_file'),
            ))
            ->add('title', TextType::class, array(
                'required'=>false,
                'label' => $this->i18n->trans('title'),
            ))
            ->add('alt', TextType::class, array(
                'required'=>false,
                'label' => $this->i18n->trans('alt'),
            ))
            /*->add('defaultCover', CheckboxType::class, array(
                'label' => 'Default cover?',
                'required' => false,
            ))
            */
            ->add('defaultCover', ChoiceType::class, array(
                'expanded' => true,
                'multiple' => false,
                //'choice_name' => null, //'default_image',
                'label' => $this->i18n->trans('default_cover_question'),
                'placeholder' => false,
                'choices' => array(
                    $this->i18n->trans('Use_as_default_cover_image') => true
                ),
                /*'choice_attr' => function ($choiceValue, $key, $value) {
                    // adds a class like attending_yes, attending_no, etc
                    return ['value' => true];
                },*/
                'required' => false,
                'data'   => false
            ))
            ->add('fileType', HiddenType::class)
            ->add('id', HiddenType::class, array(
                'disabled' => true,
            ))
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'id' => null,
            'translation_domain' => 'gui'
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
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
            ->add('fileUrl', TextType::class, [
                'required'=>false,
                'label' => $this->i18n->trans('video_url'),
            ])
            ->add('file', FileType::class, [
                'required'=>false,
                'label' => $this->i18n->trans('image_file'),
            ])
            ->add('title', TextType::class, [
                'required'=>false,
                'label' => $this->i18n->trans('title'),
            ])
            ->add('alt', TextType::class, [
                'required'=>false,
                'label' => $this->i18n->trans('alt'),
            ])
            ->add('defaultCover', RadioType::class, [
                'label' => $this->i18n->trans('Use_as_default_cover_image'),
                'required' => false,
            ])
            ->add('fileType', HiddenType::class)
            ->add('id', HiddenType::class, [
                'disabled' => true,
            ]);
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

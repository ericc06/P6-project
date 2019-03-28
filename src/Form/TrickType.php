<?php

namespace App\Form;

use App\Entity\Trick;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Translation\TranslatorInterface;

class TrickType extends AbstractType
{
    private $i18n;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->i18n = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->i18n->trans('trick_name')
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->i18n->trans('description'),
            ])
            ->add('trickGroup', EntityType::class, [
                'class'        => 'App\Entity\TrickGroup',
                'choice_label' => 'name',
                'placeholder'  => $this->i18n->trans('choose_a_group'),
                'multiple'     => false,
                'label' => $this->i18n->trans('group')
            ])
            ->add('medias', CollectionType::class, [
                'entry_type' => MediaType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => $this->i18n->trans('medias'),
                'validation_groups' => $options['validation_groups']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
            'translation_domain' => 'gui'
        ]);
    }
}

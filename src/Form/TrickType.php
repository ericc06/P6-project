<?php

namespace App\Form;

use App\Entity\Trick;
use App\Form\TrickGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Translation\TranslatorInterface;

class TrickType extends AbstractType
{
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->i18n = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', TextType::class, array(
            'label' => $this->i18n->trans('name')
        ))
        
        ->add('description', TextareaType::class, array(
            'label' => $this->i18n->trans('description'),
        ))
            ->add('trickGroup', EntityType::class, array(
                'class'        => 'App\Entity\TrickGroup',
                'choice_label' => 'name',
                'placeholder'  => $this->i18n->trans('choose_a_group'),
                'multiple'     => false,
                'label' => $this->i18n->trans('group')
            ))
            /*->add('trickGroup', TrickGroupType::class)*/
            ->add('medias', CollectionType::class, array(
                'entry_type' => MediaType::class,
                'allow_add' => true,
                'allow_delete' => true,
                //'prototype' => true,
                'by_reference' => false,
                'label' => $this->i18n->trans('medias')
            ));
        ;
    }

            /*
            ->add('trickGroup', EntityType::class, array(
                'class'        => 'App\Entity\TrickGroup',
                'choice_label' => 'name',
                'placeholder'  => ' >> Choose a group <<',
                'multiple'     => false,
            ))
            */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
            'translation_domain' => 'gui'
        ]);
    }
}

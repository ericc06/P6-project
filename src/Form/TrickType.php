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

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('trickGroup', EntityType::class, array(
                'class'        => 'App\Entity\TrickGroup',
                'choice_label' => 'name',
                'placeholder'  => ' >> Choose a group <<',
                'multiple'     => false,
            ))
            /*->add('trickGroup', TrickGroupType::class)*/
            ->add('medias', CollectionType::class, array(
                'entry_type' => MediaType::class,
                'allow_add' => true,
                'allow_delete' => true,
                //'prototype' => true,
                'by_reference' => false
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
        ]);
    }
}

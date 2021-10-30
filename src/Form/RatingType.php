<?php

namespace App\Form;

use App\Entity\Series;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RatingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('note', ChoiceType::class, [
                'choices' =>
                    ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10'],
                    'data' => $options['note'],
                    'expanded' => true,
                    'label' => 'Note',
                    'attr' => ['class' => ''],
                    'row_attr' => ['class' => 'form-note-flex'],     
            ])
            ->add('commentaire', null, [
                'data' => $options['commentaire'],
                'required' => false,
                'label' => 'Commentaire (optionnel)'
            ])
            ->add('ajouter', SubmitType::class, [
                'label' => $options['bouton']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'note' => '5',
            'commentaire' => '',
            'bouton' => 'Ajouter'
        ]);
    }
}

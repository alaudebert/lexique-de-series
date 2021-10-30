<?php

namespace App\Form;

use App\Entity\Series;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SeriesSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'data' => $options['initiales'], 
                'label' => 'Initiales'
            ])
            ->add('choixRecherche', ChoiceType::class, [
                'choices' => ['contient' => 'contient', 'commence par' => 'commence', 'fini par' => 'fini'],
                'data' => $options['choix_recherche'],
                'expanded' => true,
                'label' => 'La série:'        
            ])
            ->add('rechercher', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-warning'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'initiales' => '',
            'choix_recherche' => 'contient'
        ]);
    }
}

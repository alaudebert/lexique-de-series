<?php

namespace App\Form;

use App\Entity\Series;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SeriesSortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choixTri = array('titre' => 'titre', 'date de sortie' => 'date-de-sortie');

        if($options['user_sort']) {
            $choixTri['note'] = 'note';
            $choixTri['note imdb'] = 'note-imdb';
            $choixTri['note moyenne generale'] = 'note-moyenne-generale';
        }        

        $builder
            ->add('choix', ChoiceType::class, [
                'choices' => $choixTri,
                'data' => $options['choix'],
                'label' => 'Trier par'                    
            ])
            ->add('ordre', ChoiceType::class, [
                'choices' =>
                    ['croissant' => 'croissant', 'décroissant' => 'decroissant'],
                    'data' => $options['ordre'],
                    'label' => 'Par ordre'
            ])
            ->add('trier', SubmitType::class, [
                'attr' => ['class' => 'btn btn-warning']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choix' => 'titre',
            'ordre' => 'croissant',
            'user_sort' => false
        ]);
    }
}

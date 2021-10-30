<?php

namespace App\Form;

use App\Entity\Series;
use App\Repository\SerieRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SeriesChooseGenreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $choixGenre = ['tous les genres' => 'tous-les-genres', 'Action' => 'Action'];

        foreach($options['genresAll'] as $genreAdd) {
            $genreName = $genreAdd->getName();
            $choixGenre += [$genreName => $genreName];
        }
        
        $builder
            ->add('genre', ChoiceType::class, [
                'choices' =>
                    $choixGenre,
                    'data' => $options['genre'],
                    'label' => 'Genre'
            ])
            ->add('voir', SubmitType::class, [
                'attr' => ['class' => 'btn btn-warning']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'genre' => 'tous-les-genres',
            'genresAll' => ''
        ]);
    }
}

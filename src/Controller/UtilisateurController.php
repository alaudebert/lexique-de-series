<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    /**
     * @Route("/mon-compte/videotheque/mes-series", name="utilisateur-mes-series")
     */
    public function mesSeries(): Response
    {       
        return $this->render('utilisateur/mesSeries.html.twig');
    }

    /**
     * @Route("/mon-compte/mes-commentaires", name="utilisateur-mes-notations")
     */
    public function mesNotations(): Response
    {       
        return $this->render('utilisateur/mesNotations.html.twig');
    }

    /**
     * @Route("/mon-compte/mes-episodes-vu", name="utilisateur-mes-episodes-vu")
     */
    public function mesEpisodesVu(): Response
    {       
        $user = $this->getUser();

        $lesEpisodes = $user->getEpisode();
        $lesSeries = array() ;

        foreach($lesEpisodes as $e) {
            $titreSerie = $e->getSeason()->getSeries()->getTitle();

            $laSaison = $e->getSeason();
            $laSerie = $laSaison->getSeries();

            $titreSerie = $laSerie->getTitle();
            $numeroSaison = $laSaison->getNumber();

            if(array_key_exists($titreSerie, $lesSeries)) {
                if(array_key_exists($numeroSaison, $lesSeries[$titreSerie]['saisons'])) {
                    $lesSeries[$titreSerie]['saisons'][$numeroSaison]['episodes'] += [$e->getNumber() => ['num' => $e->getNumber(), 'titre' => $e->getTitle(), 'episode' => $e]];
                } else {
                    $lesSeries[$titreSerie]['saisons'] += [$numeroSaison => ['saison' => $laSaison, 'episodes' => [$e->getNumber() => ['num' => $e->getNumber(), 'titre' => $e->getTitle(), 'episode' => $e]]]];
                }
            } else {                
                $lesSeries +=[$titreSerie =>['serie' => $laSerie,'saisons' => [$numeroSaison => ['saison' => $laSaison,'episodes' => [$e->getNumber() => ['num' => $e->getNumber(), 'titre' => $e->getTitle(), 'episode' => $e]]]]]];
            }
        }

        // FAIRE UN TRI DANS L'ORDRE DU TABLEAU $lesSeries

        return $this->render('utilisateur/mesEpisodesVu.html.twig', [
            'episodesVu' => $lesSeries
        ]);
    }
}

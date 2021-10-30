<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Country;
use App\Entity\Season;
use App\Entity\Series;
use App\Entity\Episode;
use App\Form\AddSeriesType;
use App\Repository\GenreRepository;
use App\Repository\SerieRepository;
use App\Repository\RatingRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/ajouter/serie", name="admin_ajouter_serie")
     */
    public function ajouterSerie(Request $request, SerieRepository $repository, GenreRepository $repositoryGenre): Response
    {
        // Formulaire de récupération ID IMDB série
        $imdbForm = $this->createForm(AddSeriesType::class, null, []);
        $formStatus = null;
        $API_KEY_YouTube = 'AIzaSyAY5hOI8JXiAX7XrGYzmiHSNgndT545cHQ';
        $API_KEY_OMDB = 'c4e2bdc2';
        $laSerie = null;

        // Gestion reception formulaire de tri des séries
        if($imdbForm->handleRequest($request)->isSubmitted() && $imdbForm->isValid()) {
            $parametres = $imdbForm->getData();

            $imdb_headers = @get_headers('https://www.imdb.com/title/'. $parametres['imdb'] .'/');
            if($imdb_headers[0] == 'HTTP/1.1 404 Not Found') {
                $formStatus = "ID IMDB incorrecte !";
            } else {
                if($repository->isIMDBalreadyIN($parametres['imdb'])) {
                    $formStatus = "ID IMDB Valide + La série est déjà dans la base de données"; 
                } else {
                    $newSeries = json_decode(file_get_contents('http://www.omdbapi.com/?apikey='. $API_KEY_OMDB .'&i='. $parametres['imdb'] .'&plot=full'));
                    if(!strcmp($newSeries->Type, "series")) {
                        $formStatus = "ID IMDB Valide + Il s'agit bien d'une série a insérer"; //temp

                        $entityManager = $this->getDoctrine()->getManager();

                        $laSerie = new Series();
                        $laSerie->setTitle($newSeries->Title);
                        $laSerie->setImdb($newSeries->imdbID);
                        if($newSeries->Plot != null && strcmp($newSeries->Plot, "N/A")) { $laSerie->setPlot($newSeries->Plot); }
                        if($newSeries->Director != null && strcmp($newSeries->Director, "N/A")) { $laSerie->setDirector($newSeries->Director); }
                        if($newSeries->Awards != null  && strcmp($newSeries->Awards, "N/A")) { $laSerie->setAwards($newSeries->Awards); }

                        $years = null;
                        if($newSeries->Year != null  && strcmp($newSeries->Year, "N/A") && str_contains($newSeries->Year, '–')) {
                            $allYears = explode("–", $newSeries->Year);
                            $years = array("start" => $allYears[0], "end" => $allYears[1]);
                        } else {
                            $years = array("start" => $newSeries->Year);
                        }
                        if($years['start'] != null) { $laSerie->setYearStart($years['start']); }
                        if(isset($years['end']) && $years['end'] != null) { $laSerie->setYearEnd($years['end']); }

                        // Languages
                        $countries = explode(", ", $newSeries->Country);
                        foreach($countries as $cntry) {
                            $pays = new Country();
                            $pays->setName($cntry);
                            $laSerie->addCountry($pays);
                            $pays->addSeries($laSerie);

                            $entityManager->persist($pays); // BDD add Pays
                        }

                        // Acteurs
                        $actors = explode(", ", $newSeries->Actors);
                        foreach($actors as $actor) {
                            $acteur = new Actor();
                            $acteur->setName($actor);
                            $laSerie->addActor($acteur);
                            $acteur->addSeries($laSerie);

                            $entityManager->persist($acteur); // BDD add Actors
                        }

                        // Trailer avec YT API
                        $textToSearch = str_replace(" ", "%20", $newSeries->Title);
                        $YT_Result = json_decode(file_get_contents('https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults=20&q='. $textToSearch .'%20Trailer&type=video&key=' . $API_KEY_YouTube));
                        $YT_Trailer_link = $YT_Result->items[0]->id->videoId;
                        $laSerie->setYoutubeTrailer('https://www.youtube.com/watch?v=' . $YT_Trailer_link);
                        
                        if($newSeries->Poster != null && strcmp($newSeries->Poster, "N/A")) {                             
                            $poster_data = fopen('data://text/plain;base64,' . base64_encode(file_get_contents($newSeries->Poster)),'r');
                            $laSerie->setPoster($poster_data); // convert jpg to blob stream
                        } 

                        // Gestion des saisons
                        $nbSaisons = $newSeries->totalSeasons;

                        if($nbSaisons != null  && $nbSaisons > 1) {
                            for($i = 1; $i <= $nbSaisons; $i++) {
                                $saison = new Season();
                                $infosSaison = json_decode(file_get_contents('http://www.omdbapi.com/?apikey='. $API_KEY_OMDB .'&i='. $parametres['imdb'] .'&plot=full&Season='. $i));

                                if($infosSaison->Response === "True") {                                

                                    $numSaison = $infosSaison->Season;
                                    $saison->setNumber($numSaison);
                                    $laSerie->addSeason($saison);
                                    $saison->setSeries($laSerie);
                                    $entityManager->persist($saison); // BDD add Season

                                    foreach($infosSaison->Episodes as $epi) {
                                        $episode = new Episode();

                                        $episode->setTitle($epi->Title);
                                        $episode->setNumber($epi->Episode);
                                        //$episode->setDate($epi->Released);
                                        $episode->setImdbrating(floatval($epi->imdbRating));
                                        $episode->setImdb($epi->imdbID);
                                        $episode->setSeason($saison);

                                        $entityManager->persist($episode); // BDD add Episode
                                    }

                                }
                            }
                        }    

                        
                        //Gestion des genres
                        $genresExixtants = $repositoryGenre->findAll();
                        if($newSeries->Genre != null && $newSeries->Genre !== "N/A") {
                            $lGenres = explode(", ", $newSeries->Genre);

                            foreach($lGenres as $g) {
                                foreach($genresExixtants as $gList) {
                                    if($g === $gList->getName()) {                                        
                                        $laSerie->addGenre($gList);
                                    }
                                }
                            }
                        }
                        
                        
                        $entityManager->persist($laSerie); // BDD add Series
                        $entityManager->flush();

                        $formStatus = "ID IMDB Valide + Serie ". $newSeries->Title ." ajoutée";
                    } else {
                        $formStatus = "ID IMDB Valide + Ce n'est pas une série c'est un (". $newSeries->Type .")"; 
                    }
                }
            }
        }

        $lesSeries = $repository->getSeriesPagination(1, 1, "date-de-sortie", "croissant");

        return $this->render('admin/ajoutSerie.html.twig', [
            'imdbForm' => $imdbForm->createView(),
            'formStatus' => $formStatus,
            'series' => $laSerie
        ]);
    }

    /**
     * @Route("/admin/moderer/commentaires", name="admin_voir_coms")
     */
    public function voirTousLesComs(RatingRepository $repository): Response
    {
        $lesNotations = $repository->findAll();

        return $this->render('admin/modererCommentaires.html.twig', [
            'notations' =>$lesNotations
        ]);
    }
}

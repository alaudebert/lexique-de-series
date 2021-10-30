<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Rating;
use App\Entity\Series;
use App\Entity\Episode;
use App\Form\RatingType;
use App\Form\SeriesSortType;
use App\Form\SeriesSearchType;
use App\Form\SeriesChooseGenreType;
use App\Repository\GenreRepository;
use App\Repository\SerieRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/series")
 */
class SeriesController extends AbstractController
{

    /**
     * @Route("/", name="series_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->redirectToRoute("series_index_page", ["page" => 1, "triPar" => 'titre', "leGenre" => 'tous-les-genres', "triOrdre" => 'croissant']);
    }

    /**
     * @Route("/{triPar}/{triOrdre}/{leGenre}/page/{page}", name="series_index_page", methods={"GET", "POST"})
     */
    public function indexPages(Request $request, UserInterface $user = null, SerieRepository $repository, GenreRepository $repositoryGenre, $triPar, $triOrdre, $leGenre, $page): Response
    {
        // Pagination + gestion pages précédentes
        $nbElementsParPage = 10;
        if($page<1) { return $this->redirectToRoute("series_index_page", ["page" => 1, "triPar" => $triPar, "triOrdre" => $triOrdre]); }        

        // Initialisation variables
        $lesSeries = null;        
        $nbSeries = null;
        $userSort = ($user == null) ? false : true;

        // Formulaire de recherche d'une série
        $searchForm = $this->createForm(SeriesSearchType::class);

        // Formulaire de recherche de genre
        $lesGenres = $repositoryGenre->findAll();
        $genreForm = $this->createForm(SeriesChooseGenreType::class, null, ["genre" => ucfirst($leGenre), "genresAll" => $lesGenres]);

        // Formulaire de tri des séries
        $sortForm = $this->createForm(SeriesSortType::class, null, ["choix" => $triPar, "ordre" => $triOrdre, "user_sort" => $userSort]);

        // Gestion reception formulaire de tri des séries
        if($sortForm->handleRequest($request)->isSubmitted() && $sortForm->isValid()) {
            $parametres = $sortForm->getData();
            $triPar = $parametres['choix'];
            $triOrdre = $parametres['ordre'];
            return $this->redirectToRoute("series_index_page", ["page" => 1, "triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre]);
        }

        // Gestion reception formulaire de recherche par genre
        if($genreForm->handleRequest($request)->isSubmitted() && $genreForm->isValid()) {
            $parametres = $genreForm->getData();
            $leGenre = strtolower($parametres['genre']);
            return $this->redirectToRoute("series_index_page", ["page" => 1, "triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre]);
        }

        // Gestion reception formulaire de recherche d'une série
        if($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()) {
            $parametres = $searchForm->getData();
            return $this->redirectToRoute("series_index_page_recherche", ["page" => 1, "triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre, "initiales" => $parametres['title'], "choixRecherche" => $parametres['choixRecherche']]);
        } else {
            // Récupération de toutes les séries
            $lesSeries = $repository->getSeriesPagination($page, $nbElementsParPage, $triPar, $triOrdre, $leGenre);        
            $nbSeries = $repository->getNbSeries($triPar === 'note', $triPar === 'note-imdb', $leGenre); 
        }

        // Gestion pages suivantes
        $nbPages = (($nbSeries%$nbElementsParPage) == 0) ? $nbSeries/$nbElementsParPage : ceil($nbSeries/$nbElementsParPage);
        if($nbSeries <= 0) { $nbPages = 1; }
        if($page>$nbPages) { return $this->redirectToRoute("series_index_page", ["page" => ($nbPages), "triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre]); }

        return $this->render('series/index.html.twig', [
            'series' => $lesSeries,
            'nbSeries' => $nbSeries,
            'nbPages' => $nbPages,
            'pageActuelle' => $page,
            'searchForm' => $searchForm->createView(),
            'initiales' => "",
            'triPar' => $triPar,
            'triOrdre' => $triOrdre,
            'sortForm' => $sortForm->createView(),
            'choixRecherche' => "",
            'leGenre' => $leGenre,
            'genreForm' => $genreForm->createView()
        ]);
    }

    /**
     * @Route("/{triPar}/{triOrdre}/{leGenre}/page/{page}/{choixRecherche}/{initiales}", name="series_index_page_recherche", methods={"GET", "POST"})
     */
    public function indexPagesRecherche(Request $request, UserInterface $user = null, SerieRepository $repository, GenreRepository $repositoryGenre, $page, $triPar, $triOrdre, $leGenre, $choixRecherche, $initiales): Response
    {
        if($page<1) { return $this->redirectToRoute("series_index_page_recherche", ["page" => 1, "triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre, "initiales" => $initiales, "choixRecherche" => $choixRecherche]); }

        // Initialisation variables
        $lesSeries = null; 
        $nbSeries = null; 
        $nbElementsParPage = 10;
        $userSort = ($user == null) ? false : true;

        // Formulaire de recherche d'une série
        $searchForm = $this->createForm(SeriesSearchType::class, null, ["initiales" => $initiales, "choix_recherche" => $choixRecherche]);

        // Formulaire de recherche de genre
        $lesGenres = $repositoryGenre->findAll();
        $genreForm = $this->createForm(SeriesChooseGenreType::class, null, ["genre" => ucfirst($leGenre), "genresAll" => $lesGenres]);

        // Formulaire de tri des séries
        $sortForm = $this->createForm(SeriesSortType::class, null, ["choix" => $triPar, "ordre" => $triOrdre, "user_sort" => $userSort]);

        // Gestion reception formulaire de tri des séries
        if($sortForm->handleRequest($request)->isSubmitted() && $sortForm->isValid()) {
            $parametres = $sortForm->getData();
            $triPar = $parametres['choix'];
            $triOrdre = $parametres['ordre'];
            return $this->redirectToRoute("series_index_page_recherche", ["page" => 1, "triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre, "initiales" => $initiales, "choixRecherche" => $choixRecherche]);
        }

        // Gestion reception formulaire de recherche par genre
        if($genreForm->handleRequest($request)->isSubmitted() && $genreForm->isValid()) {
            $parametres = $genreForm->getData();
            $leGenre = strtolower($parametres['genre']);
            return $this->redirectToRoute("series_index_page_recherche", ["page" => 1, "triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre, "initiales" => $initiales, "choixRecherche" => $choixRecherche]);
        }

        // Gestion reception formulaire de recherche d'une série
        if($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()) {
            // Change de recherche
            $parametres = $searchForm->getData();
            return $this->redirectToRoute("series_index_page_recherche", ["page" => 1, "triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre, "initiales" => $parametres['title'], "choixRecherche" => $parametres['choixRecherche']]);
        } else {
            // Récupération des séries recherche actuelle
            $lesSeries = $repository->getSeriesPagination($page, $nbElementsParPage, $triPar, $triOrdre, $leGenre, $choixRecherche, $initiales);
            $nbSeries = $repository->getNbSeries($triPar === 'note', $triPar === 'note-imdb', $leGenre, $choixRecherche, $initiales);
        }

        $nbPages = (($nbSeries%$nbElementsParPage) == 0) ? $nbSeries/$nbElementsParPage : ceil($nbSeries/$nbElementsParPage);
        if($nbSeries <= 0) { $nbPages = 1; }
        if($page>$nbPages) { return $this->redirectToRoute("series_index_page_recherche", ["page" => ($nbPages), "triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre, "initiales" => $initiales, "choixRecherche" => $choixRecherche]); }

        return $this->render('series/index.html.twig', [
            'series' => $lesSeries,
            'nbSeries' => $nbSeries,
            'nbPages' => $nbPages,
            'pageActuelle' => $page,
            'searchForm' => $searchForm->createView(),
            'initiales' => $initiales,
            'triPar' => $triPar,
            'triOrdre' => $triOrdre,
            'sortForm' => $sortForm->createView(),
            'choixRecherche' => $choixRecherche,
            'leGenre' => $leGenre,
            'genreForm' => $genreForm->createView()
        ]);
    }

    /**
     * @Route("/suivre/{add}/{page}/{email}/{imdb}/{isRecherche}/{triPar}/{triOrdre}/{initiales}/{leGenre}/{choixRecherche}/{route_from}/{route_from_attributs}", name="series_suivre", methods={"GET"})
     */
    public function suivreSerie($add, $page, User $user, Series $series, $isRecherche, $triPar = null, $triOrdre = null, $initiales = null, $leGenre = null, $choixRecherche = null, $route_from = null, $route_from_attributs = null): Response
    {
        if($add) {
            $user->addSeries($series);
        } else {
            $user->removeSeries($series);
        }
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $route = "";
        $attributs = null;
        if($route_from != null) {
            if($route_from_attributs != null) {
                return $this->redirectToRoute($route_from, $route_from_attributs);
            } else {
                return $this->redirectToRoute($route_from);
            }
        } else {
            if($page == 0) {
                $route = "series_show";
                $attributs = ["title" => $series->getTitle()];
            } else {
                if($isRecherche === 'true') {
                    $route = "series_index_page_recherche";
                    $attributs = ["triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre, "page" => $page, "choixRecherche" => $choixRecherche, "initiales" => $initiales];
                } else {
                    $route = "series_index_page";
                    $attributs = ["triPar" => $triPar, "triOrdre" => $triOrdre, "leGenre" => $leGenre, "page" => $page];
                }
            }
    
            return $this->redirectToRoute($route, $attributs);
        }        
    }

    /**
     * @Route("/suivre_episode/{id}/{add}/{title}", name="series_suivre_episode_utilisateur", methods={"GET"})
     */
    public function suivreSerieEpisodeUtilisateur($add, Episode $episode, $title = null): Response
    {
        $user = $this->getUser();

        if($add) {
            $user->addEpisode($episode);
        } else {
            $user->removeEpisode($episode);
        }
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        if($title != null) {
            return $this->redirectToRoute("series_show", ["title" => $title]);
        } else {
            return $this->redirectToRoute("utilisateur-mes-episodes-vu");
        }
    }

    /**
     * @Route("/voir/{title}", name="series_show", methods={"GET", "POST"})
     */
    public function show(Request $request, UserInterface $user = null, Series $series): Response
    {
        // Formulaire de d'ajout de note pour une série
        $ratingForm = null;
        if($user != null && $series->userHasComment($user)) { 
            $noteSerie = $series->getUserComment($user); 
            $noteS = $noteSerie->getValue();
            
            if($noteSerie->getComment() != null) {
                $commentS = $noteSerie->getComment();
                $ratingForm = $this->createForm(RatingType::class, null, ['note' => $noteS, 'commentaire' => $commentS, 'bouton' => 'Modifier']);
            } else {
                $ratingForm = $this->createForm(RatingType::class, null, ['note' => $noteS, 'bouton' => 'Modifier']);
            }
        } else {
            $ratingForm = $this->createForm(RatingType::class);
        }
        

        // Gestion reception formulaire d'ajout de note pour une série
        if($ratingForm->handleRequest($request)->isSubmitted() && $ratingForm->isValid()) {
            $parametres = $ratingForm->getData();

            if($user != null) {

                if($series->userHasComment($user)) {
                    $noteSerie = $series->getUserComment($user); 
                    if($noteSerie != null) {
                        $noteSerie->removeComment();

                        $noteSerie->setDate(new DateTime());
                        $noteSerie->setSeries($series);
                        $noteSerie->setUser($user);
                        $noteSerie->setValue($parametres['note']);
                    }

                    if(isset($parametres) && strcmp($parametres['commentaire'],"")) {
                        $noteSerie->setComment($parametres['commentaire']);
                    }

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($noteSerie);
                    $entityManager->flush();

                    return $this->redirectToRoute("series_show", ['title' => $series->getTitle()]);
                } else {
                    $noteSerie = new Rating();
                    $noteSerie->setDate(new DateTime());
                    $noteSerie->setSeries($series);
                    $noteSerie->setUser($user);
                    $noteSerie->setValue($parametres['note']);

                    if(isset($parametres) && strcmp($parametres['commentaire'],"")) {
                        $noteSerie->setComment($parametres['commentaire']);
                    }
        
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($noteSerie);
                    $entityManager->flush();

                    return $this->redirectToRoute("series_show", ['title' => $series->getTitle()]);
                }
            }
        }

        return $this->render('series/show.html.twig', [
            'series' => $series,
            'ratingForm' => $ratingForm->createView()
        ]);
    }

    /**
     * @Route("/poster/{id}", name="series_poster", methods={"GET"})
     */
    public function getPoster(Series $series): Response
    {
        $poster = stream_get_contents($series->getPoster());

        $response = new Response($poster);    
        $response->headers->set('Content-Type','image/jpeg');

        return $response;
    }

    /**
     * @Route("/suprimer/commentaire/{id}/{titre}/{self}/{isMyRatings}", name="series_supp_commentaire", methods={"GET"})
     */
    public function supprimerCommentaire(UserInterface $user = null, Rating $rating, $titre, $self, $isMyRatings = 'false'): Response
    {
        if($user != null) {
            if($user->getRoles() != null && in_array('ROLE_ADMIN', $user->getRoles())) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($rating);              
                $entityManager->flush();
            } else {
                if($user->getId() == $rating->getUser()->getId()) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($rating);              
                    $entityManager->flush();
                }
            }
        }

        if($isMyRatings !== 'true') {
            return $this->redirectToRoute("series_show", ['title' => $titre]);
        } else if ($self === 'moderation') {
            return $this->redirectToRoute("admin_voir_coms");
        } else {
            return $this->redirectToRoute("utilisateur-mes-notations");
        }
        
    }
}

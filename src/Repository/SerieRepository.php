<?php

namespace App\Repository;

use App\Entity\Series;
use App\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * @method Series|null find($id, $lockMode = null, $lockVersion = null)
 * @method Series|null findOneBy(array $criteria, array $orderBy = null)
 * @method Series[]    findAll()
 * @method Series[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SerieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Series::class);
    }

    public function getNbSeries($isNote, $isNoteIMDB, $leGenre = '', $choix_recherche = 'contient', $initiales = null) {
        $series = null;

        $isGenre = ($leGenre !== "" && $leGenre !== "tous-les-genres");
        if ($isGenre) { $leGenre = ucfirst($leGenre); }

        $optionsGroupBy = '';
        if($isGenre) {
            $optionsGroupBy = ', g.name';
        }

        $optionsSelect = "(count(s.id)) AS nb";
        if($isGenre) {
            $optionsSelect = 'g.name AS genre';
        }

        $rechercheDebut = '%';
        $rechercheFin = '%';
        if($choix_recherche === 'commence') {
            $rechercheDebut = ''; 
        } else if ($choix_recherche === 'fini') {
            $rechercheFin = '';
        }

        $series = $this->createQueryBuilder('s')
            ->select($optionsSelect)
        ;

        if($isNote) { 
            $series->innerJoin('s.ratings', 'r')
            ->groupBy('s.id' . $optionsGroupBy);
        }

        if($isNoteIMDB) { 
            $series->innerJoin('s.seasons', 'sea');
            $series->innerJoin('sea.episodes', 'epi')
            ->groupBy('s.id' . $optionsGroupBy);
        }

        // si recherche par genre 
        if($isGenre) {
            $series->innerJoin('s.genre', 'g')
            ->having('g.name = :genre')
            ->setParameter(':genre', $leGenre);
        }

        if($initiales != null && $initiales !== "") {
            $series->where('s.title LIKE :title')
            ->setParameter('title', $rechercheDebut.$initiales.$rechercheFin);
        } 

        $results = $series->getQuery()
            ->getResult()
        ;

        $nbLignes = 0;
        if($isNote || $isNoteIMDB || $isGenre) { 
            $nbLignes = count($results);
        } else {
            $nbLignes = $results[0]['nb'];
        }
        
        return $nbLignes;
    }

    public function isIMDBalreadyIN($imdb) {
        $series = $this->createQueryBuilder('s')
            ->select('(count(s.id)) AS nb')
            ->where('s.imdb = :imdb')
            ->setParameter(':imdb', $imdb)
            ->getQuery()
            ->getResult()
        ;

        return (($series[0]['nb'] > 0) ? true : false);
    }

    public function getSeriesPagination($page, $nbElements, $triPar, $ordre, $leGenre = "",  $choix_recherche = 'contient', $initiales = null) {
        $isGenre = ($leGenre !== "" && $leGenre !== "tous-les-genres");
        if ($isGenre) { $leGenre = ucfirst($leGenre); }

        $debut = ($page-1)*$nbElements;
        $optionsSelect = '';

        $rechercheDebut = '%';
        $rechercheFin = '%';
        if($choix_recherche === 'commence') {
            $rechercheDebut = ''; 
        } else if ($choix_recherche === 'fini') {
            $rechercheFin = '';
        }
        

        if ($triPar === 'titre') { $triPar = 's.'.'title'; } else if ($triPar === 'date-de-sortie') { $triPar = 's.'.'yearStart'; }
        if ($triPar === 'note') { $triPar = 'note_moyenne'; } else if ($triPar === 'note-imdb') { $triPar = 'note_moyenne_imdb'; } else if ($triPar === 'note-moyenne-generale') { $triPar = 'note_moyenne_generales'; }

        if ($ordre === 'croissant') { $ordre = 'ASC'; } else if ($ordre === 'decroissant') { $ordre = 'DESC'; }

        if($isGenre) {
            $optionsSelect = ', g.name AS genre';
        }

        $reponse = $this->createQueryBuilder('s')
                        ->select('s AS series, AVG(r.value) AS note_moyenne, AVG(epi.imdbrating) AS note_moyenne_imdb, AVG((r.value+epi.imdbrating)/2) AS note_moyenne_generales' . $optionsSelect);

        //jointure moyenne utilisateurs
        if ($triPar === 'note_moyenne') { 
            $reponse->innerJoin('s.ratings', 'r');
        } else {
            $reponse->leftJoin('s.ratings', 'r');            
        }

        // jointure imdb rating
        if ($triPar === 'note_moyenne_imdb') { 
            $reponse->innerJoin('s.seasons', 'sea');
            $reponse->innerJoin('sea.episodes', 'epi');  
        } else {
            $reponse->leftJoin('s.seasons', 'sea');
            $reponse->leftJoin('sea.episodes', 'epi');        
        }

        // si recherche par genre 
        if($isGenre) {
            $reponse->InnerJoin('s.genre', 'g')
            ->having('g.name = :genre')
            ->setParameter(':genre', $leGenre);
        }

        $optionsGroupBy = '';
        if($isGenre) {
            $optionsGroupBy = ', g.name';
        }
        $reponse->groupBy('s.id' . $optionsGroupBy);

        if($initiales != null && $initiales !== "") {
            $reponse->where('s.title LIKE :title')
            ->setParameter('title', $rechercheDebut.$initiales.$rechercheFin);
        } 

        $results = $reponse->orderBy($triPar, $ordre)
                ->setFirstResult($debut)
                ->setMaxResults($nbElements)
                ->getQuery()
                ->getResult()
        ;

        return $results;
    }

    // /**
    //  * @return Series[] Returns an array of Series objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Series
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

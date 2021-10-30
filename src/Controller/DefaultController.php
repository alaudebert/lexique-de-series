<?php

namespace App\Controller;

use App\Repository\SerieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/a-propos", name="a-propos")
     */
    public function aPropos(SerieRepository $repository): Response
    {
        $lesSeries = $repository->findAll();

        return $this->render('default/a-propos.html.twig', [
            'uneSerie' => $lesSeries[0],
            'series' => $lesSeries
        ]);
    }
}

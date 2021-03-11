<?php

namespace App\Controller;

use App\Entity\Emploi;
use App\Entity\Formation;
use App\Repository\EmploiRepository;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(FormationRepository $formationRepository, EmploiRepository $emploiRepository): Response
    {
        // $formations = $formationRepository->actu();
        // $emplois = $emploiRepository->actu();
        $formations = $this->entityManager->getRepository(Formation::class)->findAll();
        $emplois = $this->entityManager->getRepository(Emploi::class)->findAll();
        return $this->render('home/index.html.twig', [
            'formations' => $formations,
            'emplois' => $emplois
        ]);
    }

    /**
     * @Route("/mentions", name="mentions")
     */
    public function info(): Response
    {
        return $this->render('home/mentions.html.twig', [
        ]);
    }
}

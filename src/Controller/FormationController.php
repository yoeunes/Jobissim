<?php

namespace App\Controller;

use App\Entity\Message;
use App\Data\SearchData;
use App\Form\SearchForm;
use App\Entity\Formation;
use App\Form\FormationType;
use App\Entity\FormationLike;
use App\Form\CandidatureType;
use App\Form\EvalType;
use App\Repository\UserRepository;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FormationLikeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/formations", name="formations")
     */
    public function index(FormationRepository $repository, Request $request, PaginatorInterface $paginator): Response
    {

        $data = new SearchData();
        $data->page = $request->get('page', 1);
        
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);
               
        $formations = $repository->findSearch($data);
        [$min, $max] = $repository->findMinMax($data);

        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $formations = $paginator->paginate(
                $repository->search($_POST['search']), 
                $request->query->getInt('page', 1), 
                10
            );
        }

        if (isset($_POST['search2']) && !empty($_POST['search2'])) {
            $formations = $paginator->paginate(
                $repository->search2($_POST['search2']), 
                $request->query->getInt('page', 1), 
                10
            );
        }

        return $this->render('formation/index.html.twig', [
            'formations' => $formations,
            'form' => $form->createView(),
            'min' => $min,
            'max' => $max
        ]);
    }



    /**
     * @Route("/formation/{id}", name="formation")
     */
    public function show($id): Response
    {
        $formation = $this->entityManager->getRepository(Formation::class)->findOneBy(['id' => $id]);

        $formation->setClics($formation->getClics() + 1);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($formation);
        $manager->flush();

        if (!$formation) {
            return $this->redirectToRoute('formations');
        }

        return $this->render('formation/show.html.twig', [
            'formation' => $formation
        ]);
    }


    
    /**
     * @Route("/new-formation", name="formation_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formation->setAuteur($this->getUser());
            $formation->setClics(0);
            $formation->setCandidatures(0);
            $formation->setTotallike(0);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($formation);
            $entityManager->flush();

            return $this->redirectToRoute('formations');
        }

        return $this->render('formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/formations/favoris", name="favoris_formation")
     */
    public function favoris(FormationRepository $repository): Response
    {
        $user = $this->getUser();
        $formations = $repository->findFormationsLikedByUser($user);

        return $this->render('formation/favoris.html.twig', [
            'formations' => $formations,
        ]);
    }



    /**
     * Liker une formation
     * 
     * @Route("/formation/{id}/like", name="formation_like")
     *
     * @param Formation $formation
     * @param EntityManagerInterface $manager
     * @param FormationLikeRepository $likeRepo
     * @return Response
     */
    public function likes(Formation $formation, EntityManagerInterface $manager, FormationLikeRepository $likeRepo) : Response
    {
        $user = $this->getUser();

        if(!$user) return $this->json([
            'code'=>403,
            'message'=> "Non autorisé"
        ], 403);

        if($formation->isLikeByUser($user)) {
            $like = $likeRepo->findOneBy([
                'formation' => $formation,
                'user' =>  $user
            ]);

            $manager->remove($like);
            $manager->flush();

            return $this->json([
                'code'=>200,
                'message'=> "supprimé des favoris"
            ], 200);
        }

        $like = new FormationLike();
        $like->setFormation($formation)
            ->setUser($user);
        
        $formation->setTotallike($formation->getTotallike() + 1);
        $manager->persist($like, $formation);
        $manager->flush();

        return $this->json([
            'code'=> 200, 
            'message'=>'ajouté aux favoris'
        ], 200);
    }



    // Postuler


    /**
     * @Route("/formation/{id}/candidature", name="formation_candidature", methods={"GET","POST"})
     */
    public function candidature($id, Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(CandidatureType::class, $message);
        $form->handleRequest($request);
        $formation = $this->entityManager->getRepository(Formation::class)->findOneBy(['id' =>  $id]);
        
        if ($formation instanceof Formation) {
            $auteur = $formation->getAuteur();
            $nom = $formation->getNom();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $formation->setCandidatures($formation->getCandidatures() + 1);

            $message->setFromId($this->getUser());
            $message->setToId($auteur);
            // $message->setSubject("*Candidature : " . $nom);
            $message->setIsRead(0);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message, $formation);
            $entityManager->flush();

            return $this->redirectToRoute('candidature_confirmation');
        }

        return $this->render('candidature/new.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/formation/candidature/confirmation", name="candidature_confirmation", methods={"GET"})
     */
    public function confirmation(): Response
    {

        return $this->render('candidature/confirmation.html.twig', [
        ]);
    }



    
    //Editer et supprimer

    /**
     * @Route("/formation/{id}/edit", name="formation_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Formation $formation): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('formations');
        }

        return $this->render('formation/edit.html.twig', [
            'formation' => $formation,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/formation/{id}/edit", name="formation_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Formation $formation): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete'.$formation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($formation);
            $user->setCvonline(0);
            $entityManager->flush();
        }

        return $this->redirectToRoute('formations');
    }
}

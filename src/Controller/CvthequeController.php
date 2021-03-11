<?php

namespace App\Controller;

use App\Entity\CvLike;
use App\Entity\Message;
use App\Data\SearchData;
use App\Entity\Cvtheque;
use App\Form\CVUserType;
use App\Form\SearchForm;
use App\Form\ContactType;
use App\Form\CvthequeType;
use App\Form\ImageUserType;
use App\Repository\CvLikeRepository;
use App\Repository\CvthequeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CvthequeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/cvtheques", name="cvtheques")
     */
    public function index(CvthequeRepository $repository, Request $request,PaginatorInterface $paginator): Response
    {
        $data = new SearchData();
        $data->page = $request->get('page', 1);
        
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);
        $cvtheques = $repository->findSearch($data);
        [$min, $max] = $repository->findMinMax($data);

        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $cvtheques = $paginator->paginate(
                $repository->search($_POST['search']),
                $request->query->getInt('page', 1), 
                10
            );
        }

        if (isset($_POST['search2']) && !empty($_POST['search2'])) {
            $cvtheques = $paginator->paginate(
                $repository->search2($_POST['search2']),
                $request->query->getInt('page', 1), 
                10
            );
        }

        return $this->render('cvtheque/index.html.twig', [
            'controller_name' => 'CvthequeController',
            'cvtheques' => $cvtheques,
            'form' => $form->createView(),
            'min' => $min,
            'max' => $max
        ]);
    }


    /**
     * @Route("/cvtheque/{id}", name="cvtheque")
     */
    public function show($id): Response
    {
        $cvtheque = $this->entityManager->getRepository(Cvtheque::class)->findOneBy(['id' => $id]);

        if (!$cvtheque) {
            return $this->redirectToRoute('cvtheques');
        }

        return $this->render('cvtheque/show.html.twig', [
            'cvtheque' => $cvtheque,
        ]);
    }


    /**
     * @Route("/new-cvtheque", name="cvtheque_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {

        // Ajouter CV
        $user = $this->getUser();
        $formcv = $this->createForm(CVUserType::class, $user);

        $formcv->handleRequest($request);

        if ($formcv->isSubmitted() && $formcv->isValid()) {

                $cv = $formcv->get('cvFile')->getData();
                $user->setCv($cv);
                $this->entityManager->flush();

            return $this->redirectToRoute('cvtheque_new');

        }

        // Ajouter la cvtheque
        $cvtheque = new Cvtheque();
        $form = $this->createForm(CvthequeType::class, $cvtheque);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $cvtheque->setNom($this->getUser()->getLastname());
            $cvtheque->setPrenom($this->getUser()->getFirstname());
            if($this->getUser()->getImage() != '') {
                $cvtheque->setImage($this->getUser()->getImage());
            }
            $cvtheque->setReference($this->getUser());
            $cvtheque->setCv($this->getUser()->getCv());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cvtheque);
            $user->setCvonline(1);
            $entityManager->flush();

            return $this->redirectToRoute('cvtheques');
        }

        return $this->render('cvtheque/new.html.twig', [
            'cvtheque' => $cvtheque,
            'form' => $form->createView(),
            'formcv' => $formcv->createView()
        ]);
    }



    /**
     * @Route("/compte/cv", name="account_cv")
     */
    public function cv(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(CVUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

                $cv = $form->get('cvFile')->getData();
                $user->setCv($cv);
                $this->entityManager->flush();

            return $this->redirectToRoute('account');

        }

        return $this->render('account/cv.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/cvtheques/favoris", name="favoris_cvtheque")
     */
    public function favoris(CvthequeRepository $repository): Response
    {
        $user = $this->getUser();
        $cvtheques = $repository->findCvLikedByUser($user);

        return $this->render('cvtheque/favoris.html.twig', [
            'cvtheques' => $cvtheques,
        ]);
    }



    /**
     * Liker un cv
     * 
     * @Route("/cvtheque/{id}/like", name="cvtheque_like")
     *
     * @param Cvtheque $cvtheque
     * @param EntityManagerInterface $manager
     * @param CvLikeRepository $likeRepo
     * @return Response
     */
    public function likes(Cvtheque $cvtheque, EntityManagerInterface $manager, CvLikeRepository $likeRepo) : Response
    {
        $user = $this->getUser();

        if(!$user) return $this->json([
            'code'=>403,
            'message'=> "Non autorisé"
        ], 403);

        if($cvtheque->isLikeByUser($user)) {
            $like = $likeRepo->findOneBy([
                'cv' => $cvtheque,
                'user' =>  $user
            ]);

            $manager->remove($like);
            $manager->flush();

            return $this->json([
                'code'=>200,
                'message'=> "supprimé des favoris"
            ], 200);
        }

        $like = new CvLike();
        $like->setCv($cvtheque)
            ->setUser($user);
        
        $manager->persist($like);
        $manager->flush();

        return $this->json([
            'code'=> 200, 
            'message'=>'ajouté aux favoris'
        ], 200);
    }


    /**
     * @Route("/cvtheque/{id}/contact", name="cvtheque_contact", methods={"GET","POST"})
     */
    public function contact($id, Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(ContactType::class, $message);
        $form->handleRequest($request);
        $cvtheque = $this->entityManager->getRepository(Cvtheque::class)->findOneBy(['id' =>  $id]);
        
        if ($cvtheque instanceof Cvtheque) {
            $auteur = $cvtheque->getReference();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setFromId($this->getUser());
            $message->setToId($auteur);
            $message->setIsRead(0);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('contact_confirmation');
        }

        return $this->render('candidature/contact.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/cvtheque/contact/confirmation", name="contact_confirmation", methods={"GET"})
     */
    public function confirmation(): Response
    {

        return $this->render('candidature/confcontact.html.twig', [
        ]);
    }


    /**
     * @Route("/cvtheque/{id}/edit", name="cvtheque_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Cvtheque $cvtheque): Response
    {
        $form = $this->createForm(CvthequeType::class, $cvtheque);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cvtheques');
        }

        return $this->render('cvtheque/edit.html.twig', [
            'cvtheque' => $cvtheque,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/cvtheque/{id}/edit", name="cvtheque_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Cvtheque $cvtheque): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete'.$cvtheque->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cvtheque);
            $user->setCvonline(0);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cvtheques');
    }


}

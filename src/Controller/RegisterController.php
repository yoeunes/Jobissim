<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use App\Form\RegisterType2;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        $form2 = $this->createForm(RegisterType2::class, $user);
        $form2->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            $search_email= $this->entityManager->getRepository(User::class)->findByEmail($user->getEmail());

            if(!$search_email) {
                $password = $encoder->encodePassword($user, $user->getPassword());

                $user->setPassword($password);
                $user->setRoles(['ROLE_SIMPLE']);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                // Envoi d'email

                $mail = new Mail();

                $content = "Bienvenue sur notre plateforme Emploi et Formation. Ton inscription s'est bien passée, tu peux maintenant te connecter sur ton compte et profiter des avantages du site. À bientôt sur Jobissim ! ";

                $mail->send($user->getEmail(), $user->getFirstname(), "Bienvenue sur Jobissim", $content );

                $notification = "Vous êtes maintenant inscrit";

            } else {
                $notification = "L'email existe déjà";
            }

            return $this->redirectToRoute('app_login');

        }

        if ($form2->isSubmitted() && $form2->isValid()) {

            $user = $form2->getData();

            $search_email= $this->entityManager->getRepository(User::class)->findByEmail($user->getEmail());

            if(!$search_email) {
                $password = $encoder->encodePassword($user, $user->getPassword());

                $user->setPassword($password);

                if($form2["compte"]->getData() == 'Formateur') {
                    $user->setRoles(['ROLE_FORMATEUR']);
                } else if($form2["compte"]->getData() == 'Employeur') {
                    $user->setRoles(['ROLE_EMPLOYEUR']);
                } else if($form2["compte"]->getData() == 'Formateur_Employeur') {
                    $user->setRoles(['ROLE_EMPFORM']);
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();


                // Envoi d'email

                $mail = new Mail();

                $content = "Bienvenue sur notre plateforme Emploi et Formation. Ton inscription s'est bien passée, tu peux maintenant te connecter sur ton compte et profiter des avantages du site. À bientôt sur Jobissim ! ";

                $mail->send($user->getEmail(), $user->getLastname(), "Bienvenue sur Jobissim", $content );

                $notification = "Vous êtes maintenant inscrit";

            } else {
                $notification = "L'email existe déjà";
            }

            return $this->redirectToRoute('app_login');

        }

        return $this->render('register/index.html.twig', [
            'form'=> $form->createView(),
            'form2'=> $form2->createView(),
            'notification' => $notification
        ]);
    }
}

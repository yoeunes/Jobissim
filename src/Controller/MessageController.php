<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/message")
 */
class MessageController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * @Route("/", name="message_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {

        $users = $userRepository->findUsersWithoutCurrentUser($this->getUser('id'));

        return $this->render('message/index.html.twig', [
            'users' => $users,
        ]);
    }



    /**
     * @Route("/new", name="message_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setFromId($this->getUser());
            $message->setIsRead(0);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            $toId = $form["to_id"]->getData()->getEmail();
            $firstName = $form["to_id"]->getData()->getFirstName();

            $mail = new Mail();
            $content = "Bonjour ".$firstName."<br/> Vous avez reçu un nouveau message sur Jobissim.";
            $mail->send($toId, $firstName, 'Nouveau message sur Jobissim.', $content);

            return $this->redirectToRoute('message_index');
        }

        return $this->render('message/new.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/{id}", name="conversation")
     */
    public function show($id, UserRepository $userRepository, MessageRepository $messageRepository): Response
    {
        $users = $userRepository->findUsersWithoutCurrentUser($this->getUser('id'));
        $messages = $messageRepository->Message(['id' => $id], $this->getUser('id'));
        
        if (!$messages) {
            return $this->redirectToRoute('message_index');
        }

        return $this->render('message/conversation.html.twig', [
            'users' => $users,
            'messages' => $messages,
        ]);
    }




    /**
     * @Route("/{id}", name="message_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Message $message): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($message);
            $entityManager->flush();
        }

        return $this->redirectToRoute('message_index');
    }
}

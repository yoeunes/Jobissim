<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/discussion")
 */
class DiscussionController
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        Security $security,
        MessageRepository $messageRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager
    ) {
        $this->security = $security;
        $this->serializer = $serializer;
        $this->messageRepository = $messageRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/with/{id}")
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function with(User $user): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $discussion = $this->messageRepository->getDiscussion($currentUser, $user);

        $response = $this->serializer->serialize($discussion, 'json', ['groups' => 'public']);

        return new JsonResponse(json_decode($response));
    }

    /**
     * @Route("/store/{id}", methods={"POST"})
     *
     * @param Request $request
     * @param User    $receiver
     *
     * @return JsonResponse
     */
    public function store(Request $request, User $receiver): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $message = new Message();
        $message->setFromId($currentUser);
        $message->setToId($receiver);
        $message->setContent($request->get('content'));

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($message, 'json', ['groups' => 'public']);

        return new JsonResponse(json_decode($response));
    }
}
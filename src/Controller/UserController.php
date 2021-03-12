<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/users")
 */
class UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, Security $security, MessageRepository $messageRepository)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->security = $security;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @Route("/", name="chatusers")
     */
    public function index(): JsonResponse
    {
        $user = $this->security->getUser();

        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $users = $this->userRepository->search($_POST['search']);
        }

        $users = $this->userRepository->findAllExcept($user->getId());

        $countMessage = array_column($this->messageRepository->getMessageCountForUser($user), 'messages', 'sender');

        $response = json_decode($this->serializer->serialize($users, 'json', ['groups' => 'public']), true);
        foreach ($response as $index => $user) {
            $response[$index]['countMessages'] = $countMessage[$user['id']] ?? 0;
        }

        return new JsonResponse($response);
    }
}
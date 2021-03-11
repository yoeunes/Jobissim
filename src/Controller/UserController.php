<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, TokenStorageInterface $tokenStorage)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/")
     */
    public function index(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()->getUser('id');
        $users = $this->userRepository->findUsersWithoutCurrentUser($user);

        $response = $this->serializer->serialize($users, 'json', ['groups' => 'public']);

        return new JsonResponse(json_decode($response));
    }
}
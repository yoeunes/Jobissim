<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
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

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/")
     */
    public function index(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        $response = $this->serializer->serialize($users, 'json', ['groups' => 'public']);

        return new JsonResponse(json_decode($response));
    }
}
<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlayerController extends AbstractController
{
    #[Route('/api/players', name: 'get_players', methods: ['GET'])]
    public function getPlayers(UserRepository $userRepository): JsonResponse
    {
        $players = $userRepository->findAll();

        $data = array_map(function (User $user) {
            return [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'username' => $user->getUsername(),
                'email' => $user->getEmailAddress(),
                'status' => $user->getStatus(),
            ];
        }, $players);

        return $this->json($data);
    }

    #[Route('/api/players/{id}', name: 'get_player', methods: ['GET'])]
    public function getPlayer(User $user): JsonResponse
    {
        $data = [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'email' => $user->getEmailAddress(),
            'status' => $user->getStatus(),
        ];

        return $this->json($data);
    }

    #[Route('/api/players/{id}', name: 'update_player', methods: ['PUT'])]
    public function updatePlayer(User $user, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user->setFirstName($data['firstName'] ?? $user->getFirstName());
        $user->setLastName($data['lastName'] ?? $user->getLastName());
        $user->setUsername($data['username'] ?? $user->getUsername());
        $user->setEmailAddress($data['emailAddress'] ?? $user->getEmailAddress());
        $user->setStatus($data['status'] ?? $user->getStatus());

        $em->flush();

        return $this->json(['message' => 'User updated']);
    }

    #[Route('/api/players/{id}', name: 'delete_player', methods: ['DELETE'])]
    public function deletePlayer(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'User deleted']);
    }
}

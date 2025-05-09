<?php

namespace App\Controller;

use App\Entity\Registration;
use App\Entity\Tournament;
use App\Entity\User;
use App\Repository\RegistrationRepository;
use App\Repository\TournamentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends AbstractController
{
    #[Route('/api/tournaments/{id}/registrations', methods: ['GET'])]
    public function getRegistrations(int $id, TournamentRepository $tournamentRepo, RegistrationRepository $registrationRepo): JsonResponse
    {
        $tournament = $tournamentRepo->find($id);
        if (!$tournament) {
            return $this->json(['error' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        $registrations = $registrationRepo->findBy(['tournament' => $tournament]);

        return $this->json($registrations);
    }

    #[Route('/api/tournaments/{id}/registrations', methods: ['POST'])]
    public function registerToTournament(
        int $id,
        Request $request,
        TournamentRepository $tournamentRepo,
        UserRepository $userRepo,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $tournament = $tournamentRepo->find($id);
        if (!$tournament) {
            return $this->json(['error' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['player_id'])) {
            return $this->json(['error' => 'Missing player_id in request'], Response::HTTP_BAD_REQUEST);
        }

        $player = $userRepo->find($data['player_id']);
        if (!$player) {
            return $this->json(['error' => 'Player not found'], Response::HTTP_NOT_FOUND);
        }

        // Vérifie s'il est déjà inscrit
        $existing = $em->getRepository(Registration::class)->findOneBy([
            'player' => $player,
            'tournament' => $tournament
        ]);

        if ($existing) {
            return $this->json(['error' => 'Player already registered to this tournament'], Response::HTTP_CONFLICT);
        }

        $registration = new Registration();
        $registration->setPlayer($player);
        $registration->setTournament($tournament);
        $registration->setRegistrationDate(new \DateTime());
        $registration->setStatus('pending'); // ou 'confirmed' si c’est automatique

        $em->persist($registration);
        $em->flush();

        return $this->json($registration, Response::HTTP_CREATED);
    }

    #[Route('/api/tournaments/{idTournament}/registrations/{idRegistration}', methods: ['DELETE'])]
    public function deleteRegistration(
        int $idTournament,
        int $idRegistration,
        TournamentRepository $tournamentRepo,
        RegistrationRepository $registrationRepo,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $tournament = $tournamentRepo->find($idTournament);
        if (!$tournament) {
            return $this->json(['error' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        $registration = $registrationRepo->find($idRegistration);
        if (!$registration || $registration->getTournament()->getId() !== $idTournament) {
            return $this->json(['error' => 'Registration not found for this tournament'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($registration);
        $em->flush();

        return $this->json(['message' => 'Registration deleted successfully'], Response::HTTP_OK);
    }
}

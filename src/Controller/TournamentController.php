<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Entity\User;
use App\Repository\TournamentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/tournaments', name: 'api_tournaments_')]
class TournamentController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(TournamentRepository $tournamentRepository): JsonResponse
    {
        $tournaments = $tournamentRepository->findAll();
        return $this->json($tournaments, 200, [], ['groups' => 'tournament:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['tournamentName'], $data['startDate'], $data['endDate'], $data['description'], $data['organizerId'])) {
            return $this->json(['error' => 'Données invalides ou incomplètes.'], 400);
        }

        // Récupération de l'organisateur
        $organizer = $userRepository->find($data['organizerId']);
        if (!$organizer) {
            return $this->json(['error' => 'Organisateur non trouvé.'], 404);
        }

        $tournament = new Tournament();
        $tournament->setTournamentName($data['tournamentName']);
        $tournament->setStartDate(new \DateTime($data['startDate']));
        $tournament->setEndDate(new \DateTime($data['endDate']));
        $tournament->setDescription($data['description']);
        $tournament->setLocation($data['location'] ?? null);
        $tournament->setMaxParticipants($data['maxParticipants'] ?? 16);
        $tournament->setSport($data['sport'] ?? 'Inconnu');
        $tournament->setOrganizer($organizer);

        // Si un winner est précisé (optionnel)
        if (isset($data['winnerId'])) {
            $winner = $userRepository->find($data['winnerId']);
            if (!$winner) {
                return $this->json(['error' => 'Vainqueur non trouvé.'], 404);
            }
            $tournament->setWinner($winner);
        }

        $em->persist($tournament);
        $em->flush();

        return $this->json($tournament, 201, [], ['groups' => 'tournament:read']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Tournament $tournament): JsonResponse
    {
        return $this->json($tournament, 200, [], ['groups' => 'tournament:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Tournament $tournament, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides.'], 400);
        }

        // Mise à jour des champs simples
        if (isset($data['tournamentName'])) $tournament->setTournamentName($data['tournamentName']);
        if (isset($data['startDate'])) $tournament->setStartDate(new \DateTime($data['startDate']));
        if (isset($data['endDate'])) $tournament->setEndDate(new \DateTime($data['endDate']));
        if (isset($data['description'])) $tournament->setDescription($data['description']);
        if (isset($data['location'])) $tournament->setLocation($data['location']);
        if (isset($data['maxParticipants'])) $tournament->setMaxParticipants($data['maxParticipants']);
        if (isset($data['sport'])) $tournament->setSport($data['sport']);

        // Mise à jour de l'organisateur
        if (isset($data['organizerId'])) {
            $organizer = $userRepository->find($data['organizerId']);
            if (!$organizer) return $this->json(['error' => 'Organisateur non trouvé.'], 404);
            $tournament->setOrganizer($organizer);
        }

        // Mise à jour du gagnant
        if (isset($data['winnerId'])) {
            $winner = $userRepository->find($data['winnerId']);
            if (!$winner) return $this->json(['error' => 'Vainqueur non trouvé.'], 404);
            $tournament->setWinner($winner);
        }

        $em->flush();

        return $this->json($tournament, 200, [], ['groups' => 'tournament:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Tournament $tournament, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($tournament);
        $em->flush();

        return $this->json(null, 204);
    }
}

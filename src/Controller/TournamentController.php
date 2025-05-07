<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Repository\TournamentRepository;
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
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['tournamentName'], $data['startDate'], $data['endDate'], $data['description'])) {
            return $this->json(['error' => 'Données invalides ou incomplètes.'], 400);
        }

        $tournament = new Tournament();
        $tournament->setTournamentName($data['tournamentName']);
        $tournament->setStartDate(new \DateTime($data['startDate']));
        $tournament->setEndDate(new \DateTime($data['endDate']));
        $tournament->setDescription($data['description']);
        $tournament->setLocation($data['location'] ?? null);
        $tournament->setMaxParticipants($data['maxParticipants'] ?? 16);
        $tournament->setSport($data['sport'] ?? 'Inconnu');

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
    public function update(Request $request, Tournament $tournament, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides.'], 400);
        }

        if (isset($data['tournamentName'])) {
            $tournament->setTournamentName($data['tournamentName']);
        }

        if (isset($data['startDate'])) {
            $tournament->setStartDate(new \DateTime($data['startDate']));
        }

        if (isset($data['endDate'])) {
            $tournament->setEndDate(new \DateTime($data['endDate']));
        }

        if (isset($data['description'])) {
            $tournament->setDescription($data['description']);
        }

        if (isset($data['location'])) {
            $tournament->setLocation($data['location']);
        }

        if (isset($data['maxParticipants'])) {
            $tournament->setMaxParticipants($data['maxParticipants']);
        }

        if (isset($data['sport'])) {
            $tournament->setSport($data['sport']);
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

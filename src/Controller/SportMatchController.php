<?php

namespace App\Controller;

use App\Entity\SportMatch;
use App\Entity\Tournament;
use App\Entity\User;
use App\Repository\SportMatchRepository;
use App\Repository\TournamentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class SportMatchController extends AbstractController
{
    /**
     * @Route("/api/tournaments/{id}/sport-matchs", methods={"GET"})
     */
    public function getSportMatches(int $id, SportMatchRepository $sportMatchRepository): JsonResponse
    {
        $tournament = $sportMatchRepository->findBy(['tournament' => $id]);
        if (!$tournament) {
            return $this->json(['error' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($tournament);
    }

    /**
     * @Route("/api/tournaments/{id}/sport-matchs", methods={"POST"})
     */
    public function createSportMatch(
        int $id,
        Request $request,
        TournamentRepository $tournamentRepository,
        UserRepository $userRepository
    ): JsonResponse
    {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            return $this->json(['error' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $player1 = $userRepository->find($data['player1']);
        $player2 = $userRepository->find($data['player2']);

        if (!$player1 || !$player2) {
            return $this->json(['error' => 'Players not found'], Response::HTTP_NOT_FOUND);
        }

        $sportMatch = new SportMatch();
        $sportMatch->setTournament($tournament);
        $sportMatch->setPlayer1($player1);
        $sportMatch->setPlayer2($player2);
        $sportMatch->setMatchDate(new \DateTime());
        $sportMatch->setStatus('pending');  // par défaut, le statut est "en attente"

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($sportMatch);
        $entityManager->flush();

        return $this->json($sportMatch, Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/tournaments/{idTournament}/sport-matchs/{idSportMatch}", methods={"GET"})
     */
    public function getSportMatchDetails(int $idTournament, int $idSportMatch, SportMatchRepository $sportMatchRepository): JsonResponse
    {
        $sportMatch = $sportMatchRepository->findOneBy([
            'tournament' => $idTournament,
            'id' => $idSportMatch
        ]);

        if (!$sportMatch) {
            return $this->json(['error' => 'SportMatch not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($sportMatch);
    }

    /**
     * @Route("/api/tournaments/{idTournament}/sport-matchs/{idSportMatch}", methods={"PUT"})
     */
    public function updateSportMatch(
        int $idTournament,
        int $idSportMatch,
        Request $request,
        SportMatchRepository $sportMatchRepository,
        UserRepository $userRepository
    ): JsonResponse
    {
        $sportMatch = $sportMatchRepository->findOneBy([
            'tournament' => $idTournament,
            'id' => $idSportMatch
        ]);

        if (!$sportMatch) {
            return $this->json(['error' => 'SportMatch not found'], Response::HTTP_NOT_FOUND);
        }

        // Assurez-vous que l'utilisateur qui modifie le score est l'un des joueurs ou un administrateur
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        if ($user !== $sportMatch->getPlayer1() && $user !== $sportMatch->getPlayer2() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'You do not have permission to update this match'], Response::HTTP_FORBIDDEN);
        }

        if (isset($data['scorePlayer1'])) {
            $sportMatch->setScorePlayer1($data['scorePlayer1']);
        }

        if (isset($data['scorePlayer2'])) {
            $sportMatch->setScorePlayer2($data['scorePlayer2']);
        }

        $sportMatch->setStatus('completed');  // Change le statut en "terminé" si les deux scores sont fournis
        if ($sportMatch->getScorePlayer1() && $sportMatch->getScorePlayer2()) {
            $sportMatch->setStatus('completed');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return $this->json($sportMatch);
    }

    /**
     * @Route("/api/tournaments/{idTournament}/sport-matchs/{idSportMatch}", methods={"DELETE"})
     */
    public function deleteSportMatch(
        int $idTournament,
        int $idSportMatch,
        SportMatchRepository $sportMatchRepository
    ): JsonResponse
    {
        $sportMatch = $sportMatchRepository->findOneBy([
            'tournament' => $idTournament,
            'id' => $idSportMatch
        ]);

        if (!$sportMatch) {
            return $this->json(['error' => 'SportMatch not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($sportMatch);
        $entityManager->flush();

        return $this->json(['message' => 'Sport match deleted successfully'], Response::HTTP_OK);
    }
}

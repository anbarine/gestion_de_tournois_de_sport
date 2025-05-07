<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class Tournament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tournament:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tournament:read', 'tournament:write'])]
    private ?string $tournamentName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['tournament:read', 'tournament:write'])]
    private ?\DateTime $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['tournament:read', 'tournament:write'])]
    private ?\DateTime $endDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tournament:read', 'tournament:write'])]
    private ?string $location = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['tournament:read', 'tournament:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['tournament:read', 'tournament:write'])]
    private ?int $maxParticipants = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tournament:read', 'tournament:write'])]
    private ?string $sport = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'organizedTournaments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tournament:read', 'tournament:write'])]
    private ?User $organizer = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'wonTournaments')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['tournament:read', 'tournament:write'])]
    private ?User $winner = null;

    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'tournament')]
    private Collection $tournament_registration;

    #[ORM\OneToMany(targetEntity: SportMatch::class, mappedBy: 'tournament')]
    private Collection $tournament_sportmatch;

    public function __construct()
    {
        $this->tournament_registration = new ArrayCollection();
        $this->tournament_sportmatch = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTournamentName(): ?string
    {
        return $this->tournamentName;
    }

    public function setTournamentName(string $tournamentName): static
    {
        $this->tournamentName = $tournamentName;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;

        return $this;
    }

    public function getSport(): ?string
    {
        return $this->sport;
    }

    public function setSport(string $sport): static
    {
        $this->sport = $sport;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getWinner(): ?User
    {
        return $this->winner;
    }

    public function setWinner(?User $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    /**
     * @return Collection<int, Registration>
     */
    public function getTournamentRegistration(): Collection
    {
        return $this->tournament_registration;
    }

    public function addTournamentRegistration(Registration $tournamentRegistration): static
    {
        if (!$this->tournament_registration->contains($tournamentRegistration)) {
            $this->tournament_registration->add($tournamentRegistration);
            $tournamentRegistration->setTournament($this);
        }

        return $this;
    }

    public function removeTournamentRegistration(Registration $tournamentRegistration): static
    {
        if ($this->tournament_registration->removeElement($tournamentRegistration)) {
            if ($tournamentRegistration->getTournament() === $this) {
                $tournamentRegistration->setTournament(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SportMatch>
     */
    public function getTournamentSportmatch(): Collection
    {
        return $this->tournament_sportmatch;
    }

    public function addTournamentSportmatch(SportMatch $tournamentSportmatch): static
    {
        if (!$this->tournament_sportmatch->contains($tournamentSportmatch)) {
            $this->tournament_sportmatch->add($tournamentSportmatch);
            $tournamentSportmatch->setTournament($this);
        }

        return $this;
    }

    public function removeTournamentSportmatch(SportMatch $tournamentSportmatch): static
    {
        if ($this->tournament_sportmatch->removeElement($tournamentSportmatch)) {
            if ($tournamentSportmatch->getTournament() === $this) {
                $tournamentSportmatch->setTournament(null);
            }
        }

        return $this;
    }
}

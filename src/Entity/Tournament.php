<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class Tournament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $tournamentName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $endDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $maxPartipants = null;

    #[ORM\Column(length: 255)]
    private ?string $sport = null;

    #[ORM\ManyToOne(inversedBy: 'organizer')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    #[ORM\ManyToOne(inversedBy: 'winner')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $winner = null;

    /**
     * @var Collection<int, Registration>
     */
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'tournament')]
    private Collection $tournament_registration;

    /**
     * @var Collection<int, SportMatch>
     */
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

    public function getMaxPartipants(): ?int
    {
        return $this->maxPartipants;
    }

    public function setMaxPartipants(int $maxPartipants): static
    {
        $this->maxPartipants = $maxPartipants;

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
            // set the owning side to null (unless already changed)
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
            // set the owning side to null (unless already changed)
            if ($tournamentSportmatch->getTournament() === $this) {
                $tournamentSportmatch->setTournament(null);
            }
        }

        return $this;
    }
}

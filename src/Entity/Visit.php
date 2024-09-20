<?php

namespace App\Entity;

use App\Repository\VisitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
/*
#[ORM\Entity(repositoryClass: VisitRepository::class)]
class Visit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

/*
    #[ORM\Column(type: Types::TEXT)]
    private ?string $userFeedback = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $agentNotes = null;


    #[ORM\OneToOne(inversedBy: 'visit', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Appointment $appointment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getUserFeedback(): ?string
    {
        return $this->userFeedback;
    }

    public function setUserFeedback(string $userFeedback): static
    {
        $this->userFeedback = $userFeedback;

        return $this;
    }

    public function getAgentNotes(): ?string
    {
        return $this->agentNotes;
    }

    public function setAgentNotes(?string $agentNotes): static
    {
        $this->agentNotes = $agentNotes;

        return $this;
    }

    public function getAppointment(): ?Appointment
    {
        return $this->appointment;
    }

    public function setAppointment(Appointment $appointment): static
    {
        $this->appointment = $appointment;

        return $this;
    }
}

*/
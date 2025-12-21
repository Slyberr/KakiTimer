<?php

namespace App\Entity;

use App\Repository\SolveEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SolveEntityRepository::class)]
class SolveEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $time = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(length: 1000)]
    private ?string $scramble = null;

    #[ORM\Column(length: 20)]
    private ?string $eventType = null;

    #[ORM\Column]
    private ?int $sessionId = null;

    #[ORM\Column]
    private ?int $userAssociated = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTime(): ?float
    {
        return $this->time;
    }

    public function setTime(float $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getScramble(): ?string
    {
        return $this->scramble;
    }

    public function setScramble(string $scramble): static
    {
        $this->scramble = $scramble;

        return $this;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getSessionId(): ?int
    {
        return $this->sessionId;
    }

    public function setSessionId(int $sessionId): static
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getUserAssociated(): ?int
    {
        return $this->userAssociated;
    }

    public function setUserAssociated(int $userAssociated): static
    {
        $this->userAssociated = $userAssociated;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ContactFormRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactFormRepository::class)]
class ContactForm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'contactForms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ContactFormProspect $prospect = null;

    #[ORM\Column(length: 512)]
    private ?string $message = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProspect(): ?ContactFormProspect
    {
        return $this->prospect;
    }

    public function setProspect(?ContactFormProspect $prospect): static
    {
        $this->prospect = $prospect;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}

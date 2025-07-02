<?php

namespace App\Entity;

use App\Repository\ContactFormProspectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactFormProspectRepository::class)]
class ContactFormProspect
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    /**
     * @var Collection<int, ContactForm>
     */
    #[ORM\OneToMany(targetEntity: ContactForm::class, mappedBy: 'prospect', orphanRemoval: true)]
    private Collection $contactForms;

    public function __construct()
    {
        $this->contactForms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, ContactForm>
     */
    public function getContactForms(): Collection
    {
        return $this->contactForms;
    }

    public function addContactForm(ContactForm $contactForm): static
    {
        if (!$this->contactForms->contains($contactForm)) {
            $this->contactForms->add($contactForm);
            $contactForm->setProspect($this);
        }

        return $this;
    }

    public function removeContactForm(ContactForm $contactForm): static
    {
        if ($this->contactForms->removeElement($contactForm)) {
            // set the owning side to null (unless already changed)
            if ($contactForm->getProspect() === $this) {
                $contactForm->setProspect(null);
            }
        }

        return $this;
    }
}

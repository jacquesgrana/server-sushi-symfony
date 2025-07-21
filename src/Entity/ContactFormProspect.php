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

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $comment = null;

    /**
     * @var Collection<int, ContactForm>
     */
    #[ORM\OneToMany(targetEntity: ContactForm::class, mappedBy: 'contactFormProspect')]
    private Collection $contactForms;

    #[ORM\Column]
    private ?\DateTime $date = null;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

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
            $contactForm->setContactFormProspect($this);
        }

        return $this;
    }

    public function removeContactForm(ContactForm $contactForm): static
    {
        if ($this->contactForms->removeElement($contactForm)) {
            // set the owning side to null (unless already changed)
            if ($contactForm->getContactFormProspect() === $this) {
                $contactForm->setContactFormProspect(null);
            }
        }

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

    public function normalizeWithoutDependencies(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "firstName" => $this->firstName,
            "email" => $this->email,
            "phone" => $this->phone,
            "comment" => $this->comment,
            "contactForms" => [],
            "date" => $this->date
        ];
    }

    public function normalize(): array
    {
        if($this->contactForms === null || $this->contactForms->isEmpty()) {
            return $this->normalizeWithoutDependencies();
        }
        $contactForms = [];
        foreach ($this->contactForms as $contactForm) {
            $contactForms[] = $contactForm->normalizeWithoutDependencies();
        }
        return [
            "id" => $this->id,
            "name" => $this->name,
            "firstName" => $this->firstName,
            "email" => $this->email,
            "phone" => $this->phone,
            "comment" => $this->comment,
            "contactForms" => $contactForms,
            "date" => $this->date
        ];
    }
}

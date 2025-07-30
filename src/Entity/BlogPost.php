<?php

namespace App\Entity;

use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $rank = null;

    #[ORM\Column(length: 100)]
    private ?string $slug = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $intro = null;

    #[ORM\Column(length: 1024)]
    private ?string $text = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $modifiedAt = null;

    #[ORM\ManyToOne(inversedBy: 'blogPosts')]
    private ?User $author = null;

    /**
     * @var Collection<int, BlogTag>
     */
    #[ORM\ManyToMany(targetEntity: BlogTag::class, inversedBy: 'blogPosts')]
    private Collection $tags;

    #[ORM\Column(nullable: true)]
    private ?bool $isPublished = null;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->modifiedAt = new \DateTimeImmutable();
        $this->tags = new ArrayCollection();
        $this->isPublished = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(?string $intro): static
    {
        $this->intro = $intro;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeImmutable $modifiedAt): static
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, BlogTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(BlogTag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(BlogTag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(?bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function normalizeWithoutDependencies(): array
    {
        return [
            "id" => $this->id,
            "rank" => $this->rank,
            "slug" => $this->slug,
            "title" => $this->title,
            "intro" => $this->intro,
            "text" => $this->text,
            "imageName" => $this->imageName,
            "createdAt" => $this->createdAt,
            "modifiedAt" => $this->modifiedAt,
            "isPublished" => $this->isPublished,
            "author" => $this->author->normalizeWithoutDependencies(),
            "tags" => []
        ];
    }
    
    public function normalize(): array
    {
        if($this->tags === null || $this->tags->isEmpty()) {
            return $this->normalizeWithoutDependencies();
        }
        $tags = [];
        foreach ($this->tags as $tag) {
            $tags[] = $tag->normalizeWithoutDependencies();
        }

        return [
            "id" => $this->id,
            "rank" => $this->rank,
            "slug" => $this->slug,
            "title" => $this->title,
            "intro" => $this->intro,
            "text" => $this->text,
            "imageName" => $this->imageName,
            "createdAt" => $this->createdAt,
            "modifiedAt" => $this->modifiedAt,
            "isPublished" => $this->isPublished,
            "author" => $this->author->normalizeWithoutDependencies(),
            "tags" => $tags
        ];
    }



}

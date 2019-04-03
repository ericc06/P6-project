<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrickRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Trick
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=5000)
     */
    private $description;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @ORM\Column(name="last_update_date", type="datetime", nullable=true)
     */
    private $lastUpdateDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TrickGroup")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trickGroup;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Media",
     *     mappedBy="trick",
     *     cascade={"persist", "remove"}
     * )
     * @Assert\Valid()
     */
    private $medias;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->medias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getLastUpdateDate(): ?\DateTimeInterface
    {
        return $this->lastUpdateDate;
    }

    public function setLastUpdateDate(\DateTimeInterface $lastUpdateDate): self
    {
        $this->lastUpdateDate = $lastUpdateDate;

        return $this;
    }

    /**
     * Add media
     *
     * @param Media $media
     *
     * @return Trick
     */
    public function addMedia(Media $media): self
    {
        $this->medias[] = $media;
        $media->setTrick($this);

        return $this;
    }

    /**
     * Remove media
     *
     * @param Media $media
     */
    public function removeMedia(Media $media)
    {
        $this->medias->removeElement($media);
    }

    /**
     * Get media
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMedias(): ?Collection
    {
        return $this->medias;
    }

    /**
     * Set media
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setMedias(?Collection $medias): self
    {
        $this->medias = $medias;

        return $this;
    }

    public function getTrickGroup(): ?TrickGroup
    {
        return $this->trickGroup;
    }

    public function setTrickGroup(?TrickGroup $trickGroup): self
    {
        $this->trickGroup = $trickGroup;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersistTrick()
    {
        $this->initCreationDate();
        $this->initSlug();
    }

    public function initCreationDate()
    {
        $this->setCreationDate(new \Datetime());
    }

    public function initSlug()
    {
        $slug = strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($this->getName(), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));

        $this->setSlug($slug);
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateLastUpdateDate()
    {
        $this->setLastUpdateDate(new \Datetime());
    }

}

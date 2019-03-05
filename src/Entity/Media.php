<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MediaRepository")
 */
class Media
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $fileUrl;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $alt;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $mimeType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $defaultCover;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trick", inversedBy="medias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trick;

    /**
     * Constructor
     *
     * @param Trick $trick
     */
    public function __construct(Trick $trick = null)
    {
        $this->trick = $trick;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    public function setFileUrl(string $fileUrl): self
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getDefaultCover(): ?bool
    {
        return $this->defaultCover;
    }

    public function setDefaultCover(?bool $defaultCover): self
    {
        $this->defaultCover = $defaultCover;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MediaRepository")
 * @ORM\HasLifecycleCallbacks()
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
     * @ORM\Column(type="string", length=500, nullable=false)
     * @Assert\Expression(
     *     "not (this.getFileType() == 1 and this.getFileUrl() === null)",
     *     message="media.fileUrl.not_blank",
     *     groups={"media_creation"}
     * )
     */
    private $fileUrl;

    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\NotBlank(
     *     message="media.title.not_blank",
     *     groups={"media_creation"}
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\NotBlank(
     *     message="media.alt.not_blank",
     *     groups={"media_creation"}
     * )
     */
    private $alt;

    /**
     * @ORM\Column(type="smallint", length=1)
     *
     * File type 0 = image / 1 = video
     */
    private $fileType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $defaultCover = 0;

    /**
     * @Assert\Expression(
     *     "not (this.getFileType() == 0 and this.getFile() === null)",
     *     message="media.file.not_blank",
     *     groups={"media_creation"}
     * )
     * @Assert\File(
     *     maxSize = "1024k",
     *     mimeTypes = {
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg"
     *          },
     *     mimeTypesMessage = "media.file.invalid_image_file",
     *     maxSizeMessage = "media.file.too_large",
     *     groups={"media_creation"}
     * )
     * @Assert\Image(
     *     minWidth = 1920,
     *     maxWidth = 1920,
     *     minHeight = 1080,
     *     maxHeight = 1080,
     *     minWidthMessage = "media.file.required_dimensions",
     *     maxWidthMessage = "media.file.required_dimensions",
     *     minHeightMessage = "media.file.required_dimensions",
     *     maxHeightMessage = "media.file.required_dimensions",
     *     groups={"media_creation"}
     * )
     */
    private $file;

    private $tempFilename;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Trick",
     *     inversedBy="medias"
     * )
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

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getDefaultCover(): ?bool
    {
        return $this->defaultCover;
    }

    public function setDefaultCover($defaultCover): self
    {
        $this->defaultCover = $defaultCover;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    // On modifie le setter de File, pour prendre en compte
    // l'upload d'un fichier lorsqu'il en existe déjà un autre.
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;

        // On vérifie si on avait déjà un fichier pour cette entité
        if (null !== $this->fileUrl) {
            // On sauvegarde l'extension du fichier pour le supprimer plus tard
            $this->tempFilename = $this->fileUrl;
        }
    }

    public function emptyFile()
    {
        $this->file = null;
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

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif),
        // on ne fait rien.
        if (null === $this->file) {
            return;
        }

        // Le nom du fichier est son id, on doit juste stocker également
        // son extension.
        // Pour faire propre, on devrait renommer cet attribut en "extension",
        // plutôt que "url".
        $this->fileUrl = $this->file->guessExtension();

        // Et on génère l'attribut alt de la balise <img>, à la valeur
        // du nom du fichier sur le PC de l'internaute s'il n'est pas
        // renseigné dans le formulaire.
        if (null === $this->alt) {
            $this->alt = $this->file->getClientOriginalName();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
        if (null === $this->file) {
            return;
        }

        // Si on avait un ancien fichier, on le supprime
        if (null !== $this->tempFilename) {
            $oldFile = $this->getUploadRootDir() . '/' . $this->id . '.'
                . $this->tempFilename;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // On déplace le fichier envoyé dans le répertoire de notre choix
        $this->file->move(
            $this->getUploadRootDir(), // Le répertoire de destination
            $this->id . '.' . $this->fileUrl // Le nom du fichier à créer,
            // ici « id.extension »
        );

        // Updating the related trick update date
        $this->getTrick()->updateLastUpdateDate();
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        // On sauvegarde temporairement le nom du fichier, car il dépend de l'id
        $this->tempFilename = $this->getUploadRootDir() . '/' . $this->id . '.' . $this->fileUrl;

        // Updating the related trick update date
        $this->getTrick()->updateLastUpdateDate();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        // En PostRemove, on n'a pas accès à l'id,
        // on utilise notre nom sauvegardé
        if (file_exists($this->tempFilename)) {
            // On supprime le fichier
            unlink($this->tempFilename);
        }

        // Updating the related trick update date
        $this->getTrick()->updateLastUpdateDate();
    }

    public function getUploadDir()
    {
        // On retourne le chemin relatif vers l'image pour un navigateur
        return 'uploads/images';
    }

    protected function getUploadRootDir()
    {
        // On retourne le chemin relatif vers l'image pour notre code PHP
        return __DIR__ . '/../../public/' . $this->getUploadDir();
    }

    public function getFixturesPath()
    {
        return __DIR__ . '/../../src/DataFixtures/images/';
    }
}

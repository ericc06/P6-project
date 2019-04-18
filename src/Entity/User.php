<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Serializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 *
 */
class User implements UserInterface, Serializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=50)
     */
    private $username = "";

    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     */
    private $firstName = "";

    /**
     * @ORM\Column(name="last_name", type="string", nullable=true)
     */
    private $lastName = "";

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email = "";

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $password = "";

    /**
     * @Assert\File(
     *     maxSize = "1024k",
     *     mimeTypes = {
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg"
     *          },
     *     mimeTypesMessage = "file.invalid_image_file",
     *     maxSizeMessage = "file.too_large",
     *     groups={"user_creation"}
     * )
     * @Assert\Image(
     *     minWidth = 40,
     *     minHeight = 40,
     *     allowSquare = true,
     *     allowLandscape = false,
     *     allowPortrait = false,
     *     minWidthMessage = "user.avatar.required_dimensions",
     *     minHeightMessage = "user.avatar.required_dimensions",
     *     allowLandscapeMessage = "user.avatar.image_must_be_square",
     *     allowPortraitMessage = "user.avatar.image_must_be_square",
     *     groups={"user_creation"}
     * )
     */
    private $avatar;

    private $tempFilename;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $fileExtension;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isActiveAccount = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $activationToken;

    // @ORM\Column(type="json")
    /**
     * @ORM\Column(name="roles", type="array")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $pwdResetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $pwdTokenCreationDate;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Message",
     *     mappedBy="user",
     *     cascade={"persist", "remove"}
     * )
     * @Assert\Valid()
     */
    private $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    // On modifie le setter de File, pour prendre en compte
    // l'upload d'un fichier lorsqu'il en existe déjà un autre.
    public function setAvatar(UploadedFile $avatar)
    {
        $this->avatar = $avatar;

        // On vérifie si on avait déjà un fichier pour cette entité
        if (null !== $this->fileExtension) {
            // On sauvegarde l'extension du fichier pour le supprimer plus tard
            $this->tempFilename = $this->fileExtension;
        }
    }

    public function emptyFile()
    {
        $this->avatar = null;
    }

    public function getIsActiveAccount()
    {
        return $this->isActiveAccount;
    }

    public function setIsActiveAccount(bool $isActiveAccount): void
    {
        $this->isActiveAccount = $isActiveAccount;
    }

    public function getActivationToken()
    {
        return $this->activationToken;
    }

    public function setActivationToken(string $activationToken)
    {
        $this->activationToken = $activationToken;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // il est obligatoire d'avoir au moins un rôle si on est authentifié,
        // par convention c'est ROLE_USER
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getPwdResetToken(): ?string
    {
        return $this->pwdResetToken;
    }

    public function setPwdResetToken(?string $pwdResetToken): self
    {
        $this->pwdResetToken = $pwdResetToken;

        return $this;
    }

    public function getPwdTokenCreationDate(): ?\DateTimeInterface
    {
        return $this->pwdTokenCreationDate;
    }

    public function setPwdTokenCreationDate(
        ?\DateTimeInterface $pwdTokenCreationDate
    ): self {
        $this->pwdTokenCreationDate = $pwdTokenCreationDate;

        return $this;
    }

    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(string $fileExtension): self
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
        if (null === $this->avatar) {
            return;
        }

        // Le nom du fichier est son id, on doit juste stocker également
        // son extension.
        // Pour faire propre, on devrait renommer cet attribut en "extension"
        // plutôt que "url".
        $this->fileExtension = $this->avatar->guessExtension();

        // Et on génère l'attribut alt de la balise <img>, à la valeur du nom
        // du fichier sur le PC de l'internaute s'il n'est pas renseigné
        // dans le formulaire.
        /*if (null === $this->alt) {
            $this->alt = $this->avatar->getClientOriginalName();
        }
        */
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
        if (null === $this->avatar) {
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
        $this->avatar->move(
            $this->getUploadRootDir(), // Le répertoire de destination
            $this->id . '.' . $this->fileExtension // Le nom du fichier à créer,
            // ici « id.extension »
        );
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        // On sauvegarde temporairement le nom du fichier, car il dépend de l'id
        $this->tempFilename = $this->getUploadRootDir() . '/' . $this->id . '.'
            . $this->fileExtension;
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
    }

    public function getUploadDir()
    {
        // On retourne le chemin relatif vers l'image pour un navigateur
        return 'uploads/images/users';
    }

    protected function getUploadRootDir()
    {
        // On retourne le chemin relatif vers l'image pour notre code PHP
        return __DIR__ . '/../../public/' . $this->getUploadDir();
    }

    public function getFixturesPath()
    {
        return __DIR__ . '/../../src/DataFixtures/images/users';
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

        return $this;
    }

    // To avoid "Serialization of
    // 'Symfony\Component\HttpFoundation\File\UploadedFile' is not allowed"
    // on user profile edition submission.
    // See https://symfonycasts.com/screencast/symfony2-ep2/user-serialization
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->password,
            $this->fileExtension,
            $this->isActiveAccount
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->password,
            $this->fileExtension,
            $this->isActiveAccount
        ) = unserialize($serialized);
    }
}

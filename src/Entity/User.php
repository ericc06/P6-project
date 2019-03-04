<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="oc_user")
 *
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
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
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email = "";

    /**
     * @ORM\Column(type="string")
     */
    private $password ="";

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/jpeg", "image/png", "image/gif" })
     */
    private $avatar;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActiveAccount = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $activationToken;
    
    /**
     * @ORM\Column(type="json")
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

    public function setAvatar(file $avatar): void
    {
        $this->avatar = $avatar;
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

        // il est obligatoire d'avoir au moins un rôle si on est authentifié, par convention c'est ROLE_USER
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

    public function setPwdTokenCreationDate(?\DateTimeInterface $pwdTokenCreationDate): self
    {
        $this->pwdTokenCreationDate = $pwdTokenCreationDate;

        return $this;
    }
}

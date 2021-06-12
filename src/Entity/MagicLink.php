<?php

namespace App\Entity;

use App\Repository\MagicLinkRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MagicLinkRepository::class)
 */
class MagicLink
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=36)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expiresAt;

    public function __construct()
    {
        $this->expiresAt = (new \DateTime())->add(new \DateInterval('PT30M'));
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getUser() : ?User
    {
        return $this->user;
    }

    public function setUser(?User $user) : self
    {
        $this->user = $user;

        return $this;
    }

    public function getToken() : ?string
    {
        return $this->token;
    }

    public function setToken(string $token) : self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpiresAt() : ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt) : self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}

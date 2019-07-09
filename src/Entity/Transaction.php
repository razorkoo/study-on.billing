<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 */
class Transaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BillingUser", inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $bUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Course", inversedBy="transactions")
     */
    private $course;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $value;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiredat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBUser(): ?BillingUser
    {
        return $this->bUser;
    }

    public function setBUser(?BillingUser $bUser): self
    {
        $this->bUser = $bUser;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(?float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getExpiredat(): ?\DateTimeInterface
    {
        return $this->expiredat;
    }

    public function setExpiredat(?\DateTimeInterface $expiredat): self
    {
        $this->expiredat = $expiredat;

        return $this;
    }
}

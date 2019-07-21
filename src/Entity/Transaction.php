<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 */
class Transaction
{
    const TYPE_DEPOSIT = 0;
    const TYPE_PAYMENT = 1;
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

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdat;

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

    public function getExpiredat(): string
    {

        return $this->expiredat->format('Y-m-d H:i:s');
    }

    public function setExpiredat(\DateTime $date = null): self
    {
        if (!$date) {
            $expirationDate = new \DateTime();
            $expirationDate->modify($_ENV['EXP_PERIOD']);
            $this->expiredat = $expirationDate;
        } else {
            $this->expiredat = $date;
        }
        return $this;
    }

    public function getCreatedat(): ?\DateTimeInterface
    {
        return $this->createdat;
    }

    public function setCreatedat(?\DateTimeInterface $createdat): self
    {
        $this->createdat = $createdat;

        return $this;
    }
}

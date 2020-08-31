<?php

namespace App\Entity;

use App\Repository\DatiTransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DatiTransactionRepository::class)
 */
class DatiTransaction
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $currency_sent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $amount_sent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $sender_phone;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $sender_country_code;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $recipient_country_code;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $recipient_phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $recipient_account_num;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $account_ref;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_requested_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $step;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $step_description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $recipient_name;



    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCurrencySent(): ?string
    {
        return $this->currency_sent;
    }

    public function setCurrencySent(?string $currency_sent): self
    {
        $this->currency_sent = $currency_sent;

        return $this;
    }

    public function getAmountSent(): ?string
    {
        return $this->amount_sent;
    }

    public function setAmountSent(?string $amount_sent): self
    {
        $this->amount_sent = $amount_sent;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSenderPhone(): ?string
    {
        return $this->sender_phone;
    }

    public function setSenderPhone(string $sender_phone): self
    {
        $this->sender_phone = $sender_phone;

        return $this;
    }

    public function getSenderCountryCode(): ?string
    {
        return $this->sender_country_code;
    }

    public function setSenderCountryCode(string $sender_country_code): self
    {
        $this->sender_country_code = $sender_country_code;

        return $this;
    }

    public function getRecipientCountryCode(): ?string
    {
        return $this->recipient_country_code;
    }

    public function setRecipientCountryCode(string $recipient_country_code): self
    {
        $this->recipient_country_code = $recipient_country_code;

        return $this;
    }

    public function getRecipientPhone(): ?string
    {
        return $this->recipient_phone;
    }

    public function setRecipientPhone(string $recipient_phone): self
    {
        $this->recipient_phone = $recipient_phone;

        return $this;
    }

    public function getRecipientAccountNum(): ?string
    {
        return $this->recipient_account_num;
    }

    public function setRecipientAccountNum(?string $recipient_account_num): self
    {
        $this->recipient_account_num = $recipient_account_num;

        return $this;
    }

    public function getAccountRef(): ?string
    {
        return $this->account_ref;
    }

    public function setAccountRef(?string $account_ref): self
    {
        $this->account_ref = $account_ref;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getLastRequestedAt(): ?\DateTimeInterface
    {
        return $this->last_requested_at;
    }

    public function setLastRequestedAt(?\DateTimeInterface $last_requested_at): self
    {
        $this->last_requested_at = $last_requested_at;

        return $this;
    }

    public function getStep(): ?string
    {
        return $this->step;
    }

    public function setStep(?string $step): self
    {
        $this->step = $step;

        return $this;
    }

    public function getStepDescription(): ?string
    {
        return $this->step_description;
    }

    public function setStepDescription(?string $step_description): self
    {
        $this->step_description = $step_description;

        return $this;
    }

    public function getRecipientName(): ?string
    {
        return $this->recipient_name;
    }

    public function setRecipientName(?string $recipient_name): self
    {
        $this->recipient_name = $recipient_name;

        return $this;
    }
}

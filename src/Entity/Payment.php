<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", nullable=true)
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $booking_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $payment_channel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $system_transaction_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $provider_transaction_id;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $payment_status;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $gateway_confirmation_response = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $confirmation_meta_data = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $collector_transaction_reference;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $collector_customer_reference;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $collector_payment_status;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $collector_response_code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $collector_response_code_description;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $collector_metadata = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status_info;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status_description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $instructions_count;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $receipt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $line;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookingId(): ?int
    {
        return $this->booking_id;
    }

    public function setBookingId(?int $booking_id): self
    {
        $this->booking_id = $booking_id;

        return $this;
    }

    public function getPaymentChannel(): ?string
    {
        return $this->payment_channel;
    }

    public function setPaymentChannel(?string $payment_channel): self
    {
        $this->payment_channel = $payment_channel;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSystemTransactionId(): ?string
    {
        return $this->system_transaction_id;
    }

    public function setSystemTransactionId(?string $system_transaction_id): self
    {
        $this->system_transaction_id = $system_transaction_id;

        return $this;
    }

    public function getProviderTransactionId(): ?string
    {
        return $this->provider_transaction_id;
    }

    public function setProviderTransactionId(?string $provider_transaction_id): self
    {
        $this->provider_transaction_id = $provider_transaction_id;

        return $this;
    }

    public function getPaymentStatus(): ?bool
    {
        return $this->payment_status;
    }

    public function setPaymentStatus(?bool $payment_status): self
    {
        $this->payment_status = $payment_status;

        return $this;
    }

    public function getGatewayConfirmationResponse(): ?array
    {
        return $this->gateway_confirmation_response;
    }

    public function setGatewayConfirmationResponse(?array $gateway_confirmation_response): self
    {
        $this->gateway_confirmation_response = $gateway_confirmation_response;

        return $this;
    }

    public function getConfirmationMetaData(): ?array
    {
        return $this->confirmation_meta_data;
    }

    public function setConfirmationMetaData(?array $confirmation_meta_data): self
    {
        $this->confirmation_meta_data = $confirmation_meta_data;

        return $this;
    }

    public function getCollectorTransactionReference(): ?string
    {
        return $this->collector_transaction_reference;
    }

    public function setCollectorTransactionReference(?string $collector_transaction_reference): self
    {
        $this->collector_transaction_reference = $collector_transaction_reference;

        return $this;
    }

    public function getCollectorCustomerReference(): ?string
    {
        return $this->collector_customer_reference;
    }

    public function setCollectorCustomerReference(?string $collector_customer_reference): self
    {
        $this->collector_customer_reference = $collector_customer_reference;

        return $this;
    }

    public function getCollectorPaymentStatus(): ?bool
    {
        return $this->collector_payment_status;
    }

    public function setCollectorPaymentStatus(?bool $collector_payment_status): self
    {
        $this->collector_payment_status = $collector_payment_status;

        return $this;
    }

    public function getCollectorResponseCode(): ?string
    {
        return $this->collector_response_code;
    }

    public function setCollectorResponseCode(?string $collector_response_code): self
    {
        $this->collector_response_code = $collector_response_code;

        return $this;
    }

    public function getCollectorResponseCodeDescription(): ?string
    {
        return $this->collector_response_code_description;
    }

    public function setCollectorResponseCodeDescription(?string $collector_response_code_description): self
    {
        $this->collector_response_code_description = $collector_response_code_description;

        return $this;
    }

    public function getCollectorMetadata(): ?array
    {
        return $this->collector_metadata;
    }

    public function setCollectorMetadata(?array $collector_metadata): self
    {
        $this->collector_metadata = $collector_metadata;

        return $this;
    }

    public function getStatusInfo(): ?string
    {
        return $this->status_info;
    }

    public function setStatusInfo(?string $status_info): self
    {
        $this->status_info = $status_info;

        return $this;
    }

    public function getStatusDescription(): ?string
    {
        return $this->status_description;
    }

    public function setStatusDescription(?string $status_description): self
    {
        $this->status_description = $status_description;

        return $this;
    }

    public function getInstructionsCount(): ?int
    {
        return $this->instructions_count;
    }

    public function setInstructionsCount(?int $instructions_count): self
    {
        $this->instructions_count = $instructions_count;

        return $this;
    }

    public function getReceipt(): ?string
    {
        return $this->receipt;
    }

    public function setReceipt(?string $receipt): self
    {
        $this->receipt = $receipt;

        return $this;
    }

    public function getLine(): ?string
    {
        return $this->line;
    }

    public function setLine(?string $line): self
    {
        $this->line = $line;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at->format('Y-m-d H:i:s');
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}

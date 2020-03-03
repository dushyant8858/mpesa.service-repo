<?php

namespace App\Entity\Bookings;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BusRepository")
 */
class Bus
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
    private $agent_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $trade_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $route_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $schedule_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bus_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $promotion_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $referral_code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schedule_code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $operator;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $booking_channel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $payment_channel;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $passengers = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_passengers;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_children;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $route;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paybill;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $seats = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $departure_time;

    /**
     * @ORM\Column(type="datetime")
     */
    private $arrival_time;

    /**
     * @ORM\Column(type="float")
     */
    private $total_amount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $refunded_amount;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $booking_status;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $confirmation_response = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $operator_confirmation_retries;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $operator_query_status_retries;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $gateway_confirmation_response = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $sms;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $sms_confirmation_response = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $qr_receipt;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $qr_response = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $email_receipt;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $email_response = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sms_receipts_sent;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $client_confirmation_sent;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $client_confirmations_count;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $source;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $destination;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $line;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $custom_booking_no;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $user = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $booking_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_of_travel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $referral_source;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $referral_checked;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $booking_organisation_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $remote_reference;

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

    public function getAgentId(): ?int
    {
        return $this->agent_id;
    }

    public function setAgentId(?int $agent_id): self
    {
        $this->agent_id = $agent_id;

        return $this;
    }

    public function getTradeId(): ?string
    {
        return $this->trade_id;
    }

    public function setTradeId(?string $trade_id): self
    {
        $this->trade_id = $trade_id;

        return $this;
    }

    public function getRouteId(): ?int
    {
        return $this->route_id;
    }

    public function setRouteId(?int $route_id): self
    {
        $this->route_id = $route_id;

        return $this;
    }

    public function getScheduleId(): ?int
    {
        return $this->schedule_id;
    }

    public function setScheduleId(?int $schedule_id): self
    {
        $this->schedule_id = $schedule_id;

        return $this;
    }

    public function getBusId(): ?int
    {
        return $this->bus_id;
    }

    public function setBusId(?int $bus_id): self
    {
        $this->bus_id = $bus_id;

        return $this;
    }

    public function getPromotionId(): ?int
    {
        return $this->promotion_id;
    }

    public function setPromotionId(?int $promotion_id): self
    {
        $this->promotion_id = $promotion_id;

        return $this;
    }

    public function getReferralCode(): ?string
    {
        return $this->referral_code;
    }

    public function setReferralCode(?string $referral_code): self
    {
        $this->referral_code = $referral_code;

        return $this;
    }

    public function getScheduleCode(): ?string
    {
        return $this->schedule_code;
    }

    public function setScheduleCode(?string $schedule_code): self
    {
        $this->schedule_code = $schedule_code;

        return $this;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getBusChannel(): ?string
    {
        return $this->booking_channel;
    }

    public function setBusChannel(?string $booking_channel): self
    {
        $this->booking_channel = $booking_channel;

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

    public function getPassengers(): ?array
    {
        return $this->passengers;
    }

    public function setPassengers(?array $passengers): self
    {
        $this->passengers = $passengers;

        return $this;
    }

    public function getTotalPassengers(): ?int
    {
        return $this->total_passengers;
    }

    public function setTotalPassengers(?int $total_passengers): self
    {
        $this->total_passengers = $total_passengers;

        return $this;
    }

    public function getTotalChildren(): ?int
    {
        return $this->total_children;
    }

    public function setTotalChildren(?int $total_children): self
    {
        $this->total_children = $total_children;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getPaybill(): ?string
    {
        return $this->paybill;
    }

    public function setPaybill(?string $paybill): self
    {
        $this->paybill = $paybill;

        return $this;
    }

    public function getSeats(): ?array
    {
        return $this->seats;
    }

    public function setSeats(?array $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getDepartureTime(): ?string
    {
        return $this->departure_time->format('Y-m-d H:i:s');
    }

    public function setDepartureTime(\DateTimeInterface $departure_time): self
    {
        $this->departure_time = $departure_time;

        return $this;
    }

    public function getArrivalTime(): ?string
    {
        return $this->arrival_time->format('Y-m-d H:i:s');
    }

    public function setArrivalTime(\DateTimeInterface $arrival_time): self
    {
        $this->arrival_time = $arrival_time;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->total_amount;
    }

    public function setTotalAmount(float $total_amount): self
    {
        $this->total_amount = $total_amount;

        return $this;
    }

    public function getRefundedAmount(): ?float
    {
        return $this->refunded_amount;
    }

    public function setRefundedAmount(?float $refunded_amount): self
    {
        $this->refunded_amount = $refunded_amount;

        return $this;
    }

    public function getBusStatus(): ?bool
    {
        return $this->booking_status;
    }

    public function setBusStatus(?bool $booking_status): self
    {
        $this->booking_status = $booking_status;

        return $this;
    }

    public function getConfirmationResponse(): ?array
    {
        return $this->confirmation_response;
    }

    public function setConfirmationResponse(?array $confirmation_response): self
    {
        $this->confirmation_response = $confirmation_response;

        return $this;
    }

    public function getOperatorConfirmationRetries(): ?int
    {
        return $this->operator_confirmation_retries;
    }

    public function setOperatorConfirmationRetries(?int $operator_confirmation_retries): self
    {
        $this->operator_confirmation_retries = $operator_confirmation_retries;

        return $this;
    }

    public function getOperatorQueryStatusRetries(): ?int
    {
        return $this->operator_query_status_retries;
    }

    public function setOperatorQueryStatusRetries(?int $operator_query_status_retries): self
    {
        $this->operator_query_status_retries = $operator_query_status_retries;

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

    public function getSms(): ?string
    {
        return $this->sms;
    }

    public function setSms(?string $sms): self
    {
        $this->sms = $sms;

        return $this;
    }

    public function getSmsConfirmationResponse(): ?array
    {
        return $this->sms_confirmation_response;
    }

    public function setSmsConfirmationResponse(?array $sms_confirmation_response): self
    {
        $this->sms_confirmation_response = $sms_confirmation_response;

        return $this;
    }

    public function getQrReceipt(): ?string
    {
        return $this->qr_receipt;
    }

    public function setQrReceipt(?string $qr_receipt): self
    {
        $this->qr_receipt = $qr_receipt;

        return $this;
    }

    public function getQrResponse(): ?array
    {
        return $this->qr_response;
    }

    public function setQrResponse(?array $qr_response): self
    {
        $this->qr_response = $qr_response;

        return $this;
    }

    public function getEmailReceipt(): ?string
    {
        return $this->email_receipt;
    }

    public function setEmailReceipt(?string $email_receipt): self
    {
        $this->email_receipt = $email_receipt;

        return $this;
    }

    public function getEmailResponse(): ?array
    {
        return $this->email_response;
    }

    public function setEmailResponse(?array $email_response): self
    {
        $this->email_response = $email_response;

        return $this;
    }

    public function getSmsReceiptsSent(): ?int
    {
        return $this->sms_receipts_sent;
    }

    public function setSmsReceiptsSent(?int $sms_receipts_sent): self
    {
        $this->sms_receipts_sent = $sms_receipts_sent;

        return $this;
    }

    public function getClientConfirmationSent(): ?int
    {
        return $this->client_confirmation_sent;
    }

    public function setClientConfirmationSent(?int $client_confirmation_sent): self
    {
        $this->client_confirmation_sent = $client_confirmation_sent;

        return $this;
    }

    public function getClientConfirmationsCount(): ?int
    {
        return $this->client_confirmations_count;
    }

    public function setClientConfirmationsCount(?int $client_confirmations_count): self
    {
        $this->client_confirmations_count = $client_confirmations_count;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(?string $destination): self
    {
        $this->destination = $destination;

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


    public function getCustomBusNo(): ?string
    {
        return $this->custom_booking_no;
    }

    public function setCustomBusNo(?string $custom_booking_no): self
    {
        $this->custom_booking_no = $custom_booking_no;

        return $this;
    }

    public function getUser(): ?array
    {
        return $this->user;
    }

    public function setUser(?array $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBusDate(): ?string
    {
        return $this->booking_date->format('Y-m-d H:i:s');
    }

    public function setBusDate(\DateTimeInterface $booking_date): self
    {
        $this->booking_date = $booking_date;

        return $this;
    }

    public function getReferralSource(): ?string
    {
        return $this->referral_source;
    }

    public function setReferralSource(?string $referral_source): self
    {
        $this->referral_source = $referral_source;

        return $this;
    }

    public function getReferralChecked(): ?int
    {
        return $this->referral_checked;
    }

    public function setReferralChecked(?int $referral_checked): self
    {
        $this->referral_checked = $referral_checked;

        return $this;
    }

    public function getBusOrganisationId(): ?string
    {
        return $this->booking_organisation_id;
    }

    public function setBusOrganisationId(?string $booking_organisation_id): self
    {
        $this->booking_organisation_id = $booking_organisation_id;

        return $this;
    }

    public function getRemoteReference(): ?string
    {
        return $this->remote_reference;
    }

    public function setRemoteReference(?string $remote_reference): self
    {
        $this->remote_reference = $remote_reference;

        return $this;
    }

    public function getDateOfTravel(): ?string
    {
        return $this->date_of_travel->format('Y-m-d H:i:s');
    }

    public function setDateOfTravel(\DateTimeInterface $date_of_travel): self
    {
        $this->date_of_travel = $date_of_travel;

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

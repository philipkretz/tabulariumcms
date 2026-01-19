<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Store;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: "`order`")]
#[ORM\HasLifecycleCallbacks]
class Order
{
    // Order status constants
    public const STATUS_PENDING = "pending";
    public const STATUS_PAYMENT_RECEIVED = "payment_received";
    public const STATUS_PROCESSING = "processing";
    public const STATUS_SHIPPED = "shipped";
    public const STATUS_DELIVERED = "delivered";
    public const STATUS_CANCELLED = "cancelled";
    public const STATUS_REFUNDED = "refunded";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 50, unique: true)]
    private ?string $orderNumber = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $status = self::STATUS_PENDING;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $customer = null;

    // Guest customer information
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $guestEmail = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $guestName = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $guestPhone = null;

    // Customer details for order
    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $customerName = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $phone = null;

    // Shipping address
    #[ORM\Column(type: "text")]
    #[Assert\NotBlank]
    private ?string $shippingAddress = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $shippingAddressLine2 = null;

    #[ORM\Column(type: "string", length: 100)]
    private ?string $shippingCity = null;

    #[ORM\Column(type: "string", length: 20)]
    private ?string $shippingPostcode = null;

    #[ORM\Column(type: "string", length: 2)]
    private ?string $shippingCountry = null;

    // Billing address
    #[ORM\Column(type: "text")]
    #[Assert\NotBlank]
    private ?string $billingAddress = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $billingAddressLine2 = null;

    #[ORM\Column(type: "string", length: 100)]
    private ?string $billingCity = null;

    #[ORM\Column(type: "string", length: 20)]
    private ?string $billingPostcode = null;

    #[ORM\Column(type: "string", length: 2)]
    private ?string $billingCountry = null;

    #[ORM\ManyToOne(targetEntity: PaymentMethod::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PaymentMethod $paymentMethod = null;

    #[ORM\ManyToOne(targetEntity: ShippingMethod::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?ShippingMethod $shippingMethod = null;

    #[ORM\ManyToOne(targetEntity: Store::class)]
    #[ORM\JoinColumn(name: 'pickup_store_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Store $pickupStore = null;

    #[ORM\ManyToOne(targetEntity: VoucherCode::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?VoucherCode $voucherCode = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $subtotal = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $shippingCost = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $discount = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $taxAmount = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $total = "0.00";

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: "order", cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $customerNotes = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $adminNotes = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $trackingNumber = null;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $ipAddress = null;

    // Payment tracking fields
    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $paymentStatus = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $paymentTransactionId = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $paymentProviderOrderId = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): static
    {
        $this->customer = $customer;
        return $this;
    }

    public function getGuestEmail(): ?string
    {
        return $this->guestEmail;
    }

    public function setGuestEmail(?string $guestEmail): static
    {
        $this->guestEmail = $guestEmail;
        return $this;
    }

    public function getGuestName(): ?string
    {
        return $this->guestName;
    }

    public function setGuestName(?string $guestName): static
    {
        $this->guestName = $guestName;
        return $this;
    }

    public function getGuestPhone(): ?string
    {
        return $this->guestPhone;
    }

    public function setGuestPhone(?string $guestPhone): static
    {
        $this->guestPhone = $guestPhone;
        return $this;
    }

    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(string $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    public function getShippingCity(): ?string
    {
        return $this->shippingCity;
    }

    public function setShippingCity(string $shippingCity): static
    {
        $this->shippingCity = $shippingCity;
        return $this;
    }

    public function getShippingPostcode(): ?string
    {
        return $this->shippingPostcode;
    }

    public function setShippingPostcode(string $shippingPostcode): static
    {
        $this->shippingPostcode = $shippingPostcode;
        return $this;
    }

    public function getShippingCountry(): ?string
    {
        return $this->shippingCountry;
    }

    public function setShippingCountry(string $shippingCountry): static
    {
        $this->shippingCountry = $shippingCountry;
        return $this;
    }

    public function getBillingAddress(): ?string
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(string $billingAddress): static
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    public function getBillingCity(): ?string
    {
        return $this->billingCity;
    }

    public function setBillingCity(string $billingCity): static
    {
        $this->billingCity = $billingCity;
        return $this;
    }

    public function getBillingPostcode(): ?string
    {
        return $this->billingPostcode;
    }

    public function setBillingPostcode(string $billingPostcode): static
    {
        $this->billingPostcode = $billingPostcode;
        return $this;
    }

    public function getBillingCountry(): ?string
    {
        return $this->billingCountry;
    }

    public function setBillingCountry(string $billingCountry): static
    {
        $this->billingCountry = $billingCountry;
        return $this;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getShippingMethod(): ?ShippingMethod
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(ShippingMethod $shippingMethod): static
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    public function getVoucherCode(): ?VoucherCode
    {
        return $this->voucherCode;
    }

    public function setVoucherCode(?VoucherCode $voucherCode): static
    {
        $this->voucherCode = $voucherCode;
        return $this;
    }

    public function getSubtotal(): string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    public function getShippingCost(): string
    {
        return $this->shippingCost;
    }

    public function setShippingCost(string $shippingCost): static
    {
        $this->shippingCost = $shippingCost;
        return $this;
    }

    public function getDiscount(): string
    {
        return $this->discount;
    }

    public function setDiscount(string $discount): static
    {
        $this->discount = $discount;
        return $this;
    }

    public function getTaxAmount(): string
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(string $taxAmount): static
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getTotal(): string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }

    public function getCustomerNotes(): ?string
    {
        return $this->customerNotes;
    }

    public function setCustomerNotes(?string $customerNotes): static
    {
        $this->customerNotes = $customerNotes;
        return $this;
    }

    public function getAdminNotes(): ?string
    {
        return $this->adminNotes;
    }

    public function setAdminNotes(?string $adminNotes): static
    {
        $this->adminNotes = $adminNotes;
        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();

        // Generate unique order number if not set
        if (!$this->orderNumber) {
            $this->orderNumber = $this->generateOrderNumber();
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();

        // Set completed date when order is delivered
        if ($this->status === self::STATUS_DELIVERED && !$this->completedAt) {
            $this->completedAt = new \DateTimeImmutable();
        }
    }

    private function generateOrderNumber(): string
    {
        return "ORD-" . date("Ymd") . "-" . strtoupper(substr(uniqid(), -6));
    }

    public function getCustomerIdentifier(): string
    {
        if ($this->customer) {
            return $this->customer->getEmail();
        }

        return $this->guestEmail ?? "Guest";
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(?string $customerName): static
    {
        $this->customerName = $customerName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function __toString(): string
    {
        return $this->orderNumber ?? "Order #" . $this->id;
    }
    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getBillingAddressLine2(): ?string
    {
        return $this->billingAddressLine2;
    }

    public function setBillingAddressLine2(?string $billingAddressLine2): static
    {
        $this->billingAddressLine2 = $billingAddressLine2;
        return $this;
    }
    public function getShippingAddressLine2(): ?string
    {
        return $this->shippingAddressLine2;
    }

    public function setShippingAddressLine2(?string $shippingAddressLine2): static
    {
        $this->shippingAddressLine2 = $shippingAddressLine2;
        return $this;
    }


    public function getPickupStore(): ?Store
    {
        return $this->pickupStore;
    }

    public function setPickupStore(?Store $pickupStore): static
    {
        $this->pickupStore = $pickupStore;
        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function getPaymentTransactionId(): ?string
    {
        return $this->paymentTransactionId;
    }

    public function setPaymentTransactionId(?string $paymentTransactionId): static
    {
        $this->paymentTransactionId = $paymentTransactionId;
        return $this;
    }

    public function getPaymentProviderOrderId(): ?string
    {
        return $this->paymentProviderOrderId;
    }

    public function setPaymentProviderOrderId(?string $paymentProviderOrderId): static
    {
        $this->paymentProviderOrderId = $paymentProviderOrderId;
        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;
        return $this;
    }

    public function isPaid(): bool
    {
        return $this->paymentStatus === 'paid' || $this->paymentStatus === 'completed';
    }
}

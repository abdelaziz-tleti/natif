<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\State\StockAlertProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(processor: StockAlertProcessor::class),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['stockalert:read']],
    denormalizationContext: ['groups' => ['stockalert:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['product' => 'exact', 'status' => 'exact'])]
#[ApiFilter(BooleanFilter::class, properties: ['resolved'])]
class StockAlert
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_RESOLVED = 'resolved';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['stockalert:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['stockalert:read', 'stockalert:write'])]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['stockalert:read'])]
    private ?User $reportedBy = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['stockalert:read', 'stockalert:write'])]
    private ?string $message = null;

    #[ORM\Column(length: 20)]
    #[Groups(['stockalert:read', 'stockalert:write'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column]
    #[Groups(['stockalert:read', 'stockalert:write'])]
    private bool $resolved = false;

    #[ORM\Column]
    #[Groups(['stockalert:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Restaurant::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['stockalert:read'])]
    private ?Restaurant $restaurant = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getReportedBy(): ?User
    {
        return $this->reportedBy;
    }

    public function setReportedBy(?User $reportedBy): static
    {
        $this->reportedBy = $reportedBy;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;
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

    public function isResolved(): bool
    {
        return $this->resolved;
    }

    public function setResolved(bool $resolved): static
    {
        $this->resolved = $resolved;
        if ($resolved) {
            $this->status = self::STATUS_RESOLVED;
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;
        return $this;
    }
}

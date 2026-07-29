<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_billing_course_code', fields: ['code'])]
#[UniqueEntity('code')]
class Course
{
    public const TYPE_FREE = 'free';
    public const TYPE_RENT = 'rent';
    public const TYPE_BUY = 'buy';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $title = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $code = '';

    #[ORM\Column(length: 20)]
    #[Assert\Choice([self::TYPE_FREE, self::TYPE_RENT, self::TYPE_BUY])]
    private string $type = self::TYPE_FREE;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Transaction::class)]
    private Collection $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price === null ? null : number_format((float) $price, 2, '.', '');

        return $this;
    }

    public function isPaid(): bool
    {
        return $this->type !== self::TYPE_FREE;
    }
}

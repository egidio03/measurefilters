<?php

namespace App\Entity;

use App\Repository\FilterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilterRepository::class)]
class Filter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1)]
    private ?string $type = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column(length: 10)]
    private ?string $abbreviation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $first_line = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $second_line = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 7, nullable: true)]
    private ?string $air_quality_index = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $deviation_white = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $deviation_black = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $filtered_volume = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $solid_relationship = null;

    /**
     * @var Collection<int, Measurement>
     */
    #[ORM\OneToMany(targetEntity: Measurement::class, mappedBy: 'filter', orphanRemoval: true)]
    private Collection $measurements;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $method = null;



    public function __construct()
    {
        $this->measurements = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->air_quality_index = 0;
        $this->deviation_white = 0;
        $this->deviation_black = 0;
        $this->filtered_volume = 0;
        $this->solid_relationship = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->abbreviation . "" . $this->created_at->format('y') . str_pad((string)$this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(string $abbreviation): static
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function getFirstLine(): ?string
    {
        return $this->first_line;
    }

    public function setFirstLine(?string $first_line): static
    {
        $this->first_line = $first_line;

        return $this;
    }

    public function getSecondLine(): ?string
    {
        return $this->second_line;
    }

    public function setSecondLine(?string $second_line): static
    {
        $this->second_line = $second_line;

        return $this;
    }

    public function getAirQualityIndex(): ?string
    {
        return $this->air_quality_index;
    }

    public function setAirQualityIndex(?string $airQualityIndex): static
    {
        $this->air_quality_index = $airQualityIndex;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getDeviationWhite(): ?string
    {
        return $this->deviation_white;
    }

    public function setDeviationWhite(?string $deviation_white): static
    {
        $this->deviation_white = $deviation_white;

        return $this;
    }

    public function getDeviationBlack(): ?string
    {
        return $this->deviation_black;
    }

    public function setDeviationBlack(?string $deviation_black): static
    {
        $this->deviation_black = $deviation_black;

        return $this;
    }

    public function getFilteredVolume(): ?string
    {
        return $this->filtered_volume;
    }

    public function setFilteredVolume(?string $filtered_volume): static
    {
        $this->filtered_volume = $filtered_volume;

        return $this;
    }

    public function getSolidRelationship(): ?string
    {
        return $this->solid_relationship;
    }

    public function setSolidRelationship(?string $solid_relationship): static
    {
        $this->solid_relationship = $solid_relationship;

        return $this;
    }

    /**
     * @return Collection<int, Measurement>
     */
    public function getMeasurements(): Collection
    {
        return $this->measurements;
    }

    public function addMeasurement(Measurement $measurement): static
    {
        if (!$this->measurements->contains($measurement)) {
            $this->measurements->add($measurement);
            $measurement->setFilter($this);
        }

        return $this;
    }

    public function removeMeasurement(Measurement $measurement): static
    {
        if ($this->measurements->removeElement($measurement)) {
            // set the owning side to null (unless already changed)
            if ($measurement->getFilter() === $this) {
                $measurement->setFilter(null);
            }
        }

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): static
    {
        $this->method = $method;

        return $this;
    }
}

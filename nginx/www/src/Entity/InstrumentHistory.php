<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="instrument_history",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="date_name",
 *              columns={"date", "name"}
 *          ),
 *     },
 * )
 */
class InstrumentHistory implements Entity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="date", length=255)
     */
    private DateTimeInterface $date;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\Column(type="float")
     */
    private float $value;

    public function __construct(DateTimeInterface $date, string $name, float $value)
    {
        $this->date = $date;
        $this->value = $value;
        $this->name = $name;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

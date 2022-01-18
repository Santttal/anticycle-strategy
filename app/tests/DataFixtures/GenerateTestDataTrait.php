<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use Faker\Factory;
use Faker\Generator;

trait GenerateTestDataTrait
{
    /**
     * @var Generator
     */
    private $faker;

    public function getFaker(): Generator
    {
        if (!$this->faker) {
            $this->faker = Factory::create();
        }

        return $this->faker;
    }
}

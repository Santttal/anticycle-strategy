<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures\Factory\Smart;

use App\Entity\Category;
use App\Entity\InstrumentHistory;
use App\Entity\InstrumentPrice;
use App\Entity\PurchaseOperation;
use App\Entity\UsdRate;
use App\Tests\DataFixtures\GenerateTestDataTrait;
use jamesRUS52\TinkoffInvest\TICurrencyEnum;
use jamesRUS52\TinkoffInvest\TIOperationEnum;

class EntityFactory extends AbstractFactory
{
    use GenerateTestDataTrait;

    protected function addDefaults(array $data, array $defaults): array
    {
        return array_merge($defaults, $data);
    }

    public function createCategory(array $data = []): Category
    {
        $data = $this->addDefaults($data, [
            'name' => $this->getFaker()->word(),
        ]);

        return $this->create(Category::class, $data);
    }

    public function createInstrument(array $data = []): InstrumentHistory
    {
        $data = $this->addDefaults($data, [
            'figi' => $this->getFaker()->numerify('BBG000######'),
            'ticker' => strtoupper($this->getFaker()->randomLetter . $this->getFaker()->randomLetter . $this->getFaker()->randomLetter),
            'name' => $this->getFaker()->company,
            'currency' => $this->getFaker()->randomElement([TICurrencyEnum::RUB, TICurrencyEnum::USD])
        ]);

        return $this->create(InstrumentHistory::class, $data);
    }

    public function createInstrumentPrice(array $data = []): InstrumentPrice
    {
        $data = $this->addDefaults($data, [
            'priceAt' => $this->getFaker()->dateTime->setTime(3, 0, 0),
            'value' => $this->getFaker()->randomFloat(2, 1, 1000),
        ]);

        if (!array_key_exists('instrument', $data)) {
            $data['instrument'] = $this->createInstrument();
        }

        return $this->create(InstrumentPrice::class, $data);
    }

    public function createPurchaseOperation(array $data = []): PurchaseOperation
    {
        $data = $this->addDefaults($data, [
            'operationType' => $this->getFaker()->randomElement([TIOperationEnum::BUY, TIOperationEnum::SELL]),
            'quantity' => $this->getFaker()->numberBetween(1, 100),
            'price' => $this->getFaker()->randomFloat(2, 1, 1000),
            'externalId' => $this->getFaker()->numerify('############'),
            'operatedAt' => $this->getFaker()->dateTime,
            'usdRate' => $this->getFaker()->randomFloat(2, 65, 80),
        ]);

        if (!array_key_exists('instrument', $data)) {
            $data['instrument'] = $this->createInstrument();
        }

        return $this->create(PurchaseOperation::class, $data);
    }

    public function createUsdRate(array $data = []): UsdRate
    {
        $data = $this->addDefaults($data, [
            'value' => $this->getFaker()->randomFloat(2, 65, 80),
            'rateDateTime' => $this->getFaker()->dateTime,
        ]);

        return $this->create(UsdRate::class, $data);
    }
}

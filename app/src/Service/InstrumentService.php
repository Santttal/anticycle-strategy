<?php

namespace App\Service;

use App\Repository\InstrumentHistoryRepository;
use Carbon\Carbon;

class InstrumentService
{
    public function __construct(
        private InstrumentHistoryRepository $repository,
    ) {
    }

    public function getLastValue(string $instrumentName): float
    {
        $value = $this->repository->findLast($instrumentName)->getValue();

        return $this->round($value);
    }

    public function getDaysFromLast(): int
    {
        $lastDate = Carbon::make($this->repository->findLast(null)->getDate());
        $today = Carbon::today();

        $baseDiff = $today->diffInDays($lastDate);
        if ($today->isSaturday()) {
            --$baseDiff;
        }
        if ($today->isSunday()) {
            $baseDiff -= 2;
        }

        return $baseDiff;
    }

    /**
     * @return int[]
     */
    public function getValues(string $instrumentName, int $limit, string $avgInterval): array
    {
        $lastValue = $this->repository->findLast($instrumentName);
        $selectedDate = Carbon::make($lastValue->getDate());
        $values = [];

        for ($i = 0; $i < $limit; ++$i) {
            $instrumentValue = null;
            while (null === $instrumentValue) {
                $instrumentValue = $this->repository->findByDate($instrumentName, $selectedDate);
                if (null === $instrumentValue) {
                    $selectedDate->modify('-1 day');
                }
            }

            $averageValue = $this->repository->calculateAvg($instrumentName, $selectedDate, $avgInterval);
            $value = $this->round($instrumentValue->getValue());

            $values[] = [
                'date' => clone $selectedDate,
                'value' => $value,
                'avg' => $this->round($averageValue),
            ];

            $selectedDate->modify('-1 day');
        }

        return $values;
    }

    private function round(float $number): float
    {
        return $number > 1000 ? round($number) : round($number, 2);
    }
}

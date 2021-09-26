<?php

namespace App\Service;

use App\Entity\InstrumentHistory;
use App\Enum\InstrumentEnum;
use App\Repository\InstrumentHistoryRepository;
use App\Service\HistoricalData\UrlBuilder;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;

class SyncInstrumentService
{
    private const CHUNK_SIZE = 100;

    public function __construct(
        private InstrumentHistoryRepository $repository,
        private EntityManagerInterface $entityManager,
        private UrlBuilder $urlBuilder,
    ) {
    }

    public function sync(): void
    {
        $synced = array_fill_keys(InstrumentEnum::getValues(), []);
        $urls = [];
        $dbInstrumentValues = [];

        foreach (InstrumentEnum::getValues() as $instrumentName) {
            $startAt = $this->selectStartDate($instrumentName);
            $endAt = Carbon::today();
            $urls[$instrumentName] = $this->urlBuilder->create($instrumentName, $startAt, $endAt);
            $dbInstrumentValues[$instrumentName] = $this->repository->findAllInInterval($instrumentName, $startAt, $endAt);
        }
        $flippedUrls = array_flip($urls);

        $client = new Client();
        $response = $client->request('POST', 'http://multithread-loader/download',
            ['body' => json_encode(['urls' => array_values($urls)])]);

        $items = json_decode($response->getBody(), true)['items'];
        foreach ($items as $item) {
            $fileContext = $this->extractReportData($item['data']);
            $instrumentName = $flippedUrls[$item['url']];
            $counter = 0;
            foreach ($fileContext as $itemData) {
                $dateFromFile = $itemData[0];
                $valueFromFile = $itemData[1];

                if (in_array($dateFromFile, $synced[$instrumentName], true)) {
                    continue;
                }

                if ($this->existsInDb($dbInstrumentValues[$instrumentName], $dateFromFile)) {
                    continue;
                }

                $counter++;

                $instrumentValue = new InstrumentHistory(
                    Carbon::make($dateFromFile),
                    $instrumentName,
                    $valueFromFile
                );
                $this->repository->add($instrumentValue);
                $synced[$instrumentName][] = $dateFromFile;

                if ($counter === self::CHUNK_SIZE) {
                    $this->repository->save();
                    $this->entityManager->clear();
                }
            }

            $this->repository->save();
        }
    }

    private function extractReportData(string $rawData): array
    {
        $lines = explode(PHP_EOL, $rawData);
        $data = [];
        $prevValue = 0;
        foreach ($lines as $line) {

            $line = str_getcsv($line);
            if (!array_key_exists(1, $line) || $line[1] === '.') {
                $line[1] = $prevValue;
            }
            if (null !== $line[0]) {
                $data[] = $line;
                $prevValue = $line[1];
            }
        }

        array_shift($data);

        return $data;
    }

    private function selectStartDate(string $instrumentName): Carbon
    {
        $lastRecord = $this->repository->findLast($instrumentName);
        if (null === $lastRecord) {
            $startAt = Carbon::make('11 years ago');
        } else {
            $startAt = Carbon::make($lastRecord->getDate());
        }

        if ($startAt->format('N') > '5') {
            $startAt->modify('-2 days');
        }

        return $startAt;
    }

    private function existsInDb(array $dbInstrumentValues, string $dateFromFile): bool
    {
        return count(array_filter($dbInstrumentValues, function (InstrumentHistory $instrument) use ($dateFromFile) {
                return $instrument->getDate()->format('Y-m-d') === $dateFromFile;
            })) > 0;
    }
}

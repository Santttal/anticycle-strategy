<?php

namespace App\Service;

use App\Entity\InstrumentHistory;
use App\Repository\InstrumentHistoryRepository;
use Carbon\Carbon;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use InstrumentEnum;
use Psr\Log\LoggerInterface;

class AntiCycleSyncInstrumentService
{
    private const CHUNK_SIZE = 100;
    private const URLS = [
        InstrumentEnum::SP_500 => 'https://fred.stlouisfed.org/graph/fredgraph.csv?bgcolor=%%23e1e9f0&chart_type=line&drp=0&fo=open%%20sans&graph_bgcolor=%%23ffffff&height=450&mode=fred&recession_bars=on&txtcolor=%%23444444&ts=12&tts=12&width=1168&nt=0&thu=0&trc=0&show_legend=yes&show_axis_titles=yes&show_tooltip=yes&id=SP500&scale=left&cosd=%s&coed=%s&line_color=%%234572a7&link_values=false&line_style=solid&mark_type=none&mw=3&lw=2&ost=-99999&oet=99999&mma=0&fml=a&fq=Daily%%2C%%20Close&fam=avg&fgst=lin&fgsnd=2020-02-01&line_index=1&transformation=lin&vintage_date=2021-05-20&revision_date=2021-05-20&nd=2021-05-20',
        InstrumentEnum::GOLD => 'https://fred.stlouisfed.org/graph/fredgraph.csv?bgcolor=%%23e1e9f0&chart_type=line&drp=0&fo=open%%20sans&graph_bgcolor=%%23ffffff&height=450&mode=fred&recession_bars=on&txtcolor=%%23444444&ts=12&tts=12&width=1168&nt=0&thu=0&trc=0&show_legend=yes&show_axis_titles=yes&show_tooltip=yes&id=GOLDAMGBD228NLBM&scale=left&cosd=%s&coed=%s&line_color=%%234572a7&link_values=false&line_style=solid&mark_type=none&mw=3&lw=2&ost=-99999&oet=99999&mma=0&fml=a&fq=Daily&fam=avg&fgst=lin&fgsnd=2020-02-01&line_index=1&transformation=lin&vintage_date=2021-05-21&revision_date=2021-05-21&nd=1968-04-01',
        InstrumentEnum::HIGH_YIELD => 'https://fred.stlouisfed.org/graph/fredgraph.csv?bgcolor=%%23e1e9f0&chart_type=line&drp=0&fo=open%%20sans&graph_bgcolor=%%23ffffff&height=450&mode=fred&recession_bars=on&txtcolor=%%23444444&ts=12&tts=12&width=1168&nt=0&thu=0&trc=0&show_legend=yes&show_axis_titles=yes&show_tooltip=yes&id=BAMLH0A0HYM2&scale=left&cosd=%s&coed=%s&line_color=%%234572a7&link_values=false&line_style=solid&mark_type=none&mw=3&lw=2&ost=-99999&oet=99999&mma=0&fml=a&fq=Daily%%2C%%20Close&fam=avg&fgst=lin&fgsnd=2020-02-01&line_index=1&transformation=lin&vintage_date=2021-05-21&revision_date=2021-05-21&nd=1996-12-31',
        InstrumentEnum::T10Y2Y => 'https://fred.stlouisfed.org/graph/fredgraph.csv?bgcolor=%%23e1e9f0&chart_type=line&drp=0&fo=open%%20sans&graph_bgcolor=%%23ffffff&height=450&mode=fred&recession_bars=on&txtcolor=%%23444444&ts=12&tts=12&width=1168&nt=0&thu=0&trc=0&show_legend=yes&show_axis_titles=yes&show_tooltip=yes&id=T10Y2Y&scale=left&cosd=%s&coed=%s&line_color=%%234572a7&link_values=false&line_style=solid&mark_type=none&mw=3&lw=2&ost=-99999&oet=99999&mma=0&fml=a&fq=Daily&fam=avg&fgst=lin&fgsnd=2020-02-01&line_index=1&transformation=lin&vintage_date=2021-05-21&revision_date=2021-05-21&nd=1976-06-01',
    ];

    public function __construct(
        private InstrumentHistoryRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function sync()
    {
        $synced = [];
        $urls = [];
        $dbInstrumentValues = [];

        foreach (InstrumentEnum::getValues() as $instrumentName) {
            $startAt = $this->selectStartDate($instrumentName);
            $endAt = Carbon::today();
            $urls[$instrumentName] = sprintf(
                self::URLS[$instrumentName], $startAt->format('Y-m-d'), $endAt->format('Y-m-d')
            );
            $dbInstrumentValues[$instrumentName] = $this->repository->findAllInInterval($instrumentName, $startAt,
                $endAt);
        }
        $flippedUrls = array_flip($urls);

        $client = new Client();
        $response = $client->request('POST', 'http://multithread-loader/download',
            ['body' => json_encode(['urls' => array_values($urls)])]);

        $items = json_decode($response->getBody(), true)['items'];
        foreach ($items as $item) {
            $fileContext = $this->extractReportData($item['data']);
            $instrumentName = $flippedUrls[$item['url']];
            var_export($instrumentName);
            $counter = 0;
            foreach ($fileContext as $itemData) {
                $dateFromFile = $itemData[0];
                $valueFromFile = $itemData[1];

                if (in_array($dateFromFile, $synced, true)) {
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
                $synced[] = $dateFromFile;

                if ($counter === self::CHUNK_SIZE) {
                    $this->repository->save();
                    $this->entityManager->clear();
                }
            }

            $this->repository->save();
        }


        die;

//        echo "start sync $instrumentName {$startAt->format('Y-m-d')} - {$endAt->format('Y-m-d')}" . PHP_EOL;

        if ($startAt->format('Y-m-d') >= Carbon::today()->format('Y-m-d')) {
            return;
        }
        if ($startAt->format('Y-m-d') === $endAt->format('Y-m-d')) {
            return;
        }

        $fileContext = $this->getFileData(sprintf(
            self::URLS[$instrumentName],
            $startAt->format('Y-m-d'),
            $endAt->format('Y-m-d')
        ));
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

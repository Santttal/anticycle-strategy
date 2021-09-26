<?php

namespace App\Controller;

use App\Enum\InstrumentEnum;
use App\Service\InstrumentService;
use App\Service\SyncDecisionMaker;
use App\Service\SyncInstrumentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AntiCycleStrategyController extends AbstractController
{
    public function index(InstrumentService $antiCycleService, SyncInstrumentService $syncService, SyncDecisionMaker $decisionMaker)
    {
        $limit = 30;

        if ($decisionMaker->shouldSync()) {
            $syncService->sync();
            $decisionMaker->markAsSynced();
        }

        return $this->render(
            'anticycle.html.twig',
            [
                'days_from_last' => $antiCycleService->getDaysFromLast(),
                InstrumentEnum::SP_500 => [
                    'averages' => $antiCycleService->getValues(InstrumentEnum::SP_500, $limit, '-120 days'),
                    'current' => $antiCycleService->getLastValue(InstrumentEnum::SP_500),
                ],
                InstrumentEnum::GOLD => [
                    'averages' => $antiCycleService->getValues(InstrumentEnum::GOLD, $limit, '-200 days'),
                    'current' => $antiCycleService->getLastValue(InstrumentEnum::GOLD),
                ],
                InstrumentEnum::HIGH_YIELD => [
                    'averages' => $antiCycleService->getValues(InstrumentEnum::HIGH_YIELD, $limit, '-10 years'),
                    'current' => $antiCycleService->getLastValue(InstrumentEnum::HIGH_YIELD),
                ],
                InstrumentEnum::T10Y2Y => [
                    'averages' => $antiCycleService->getValues(InstrumentEnum::T10Y2Y, $limit, '-10 years'),
                    'current' => $antiCycleService->getLastValue(InstrumentEnum::T10Y2Y),
                ],
            ]
        );
    }
}

<?php

namespace App\Service\HistoricalData;

use App\Enum\InstrumentEnum;
use DateTimeInterface;

class UrlBuilder
{
    private const URLS = [
        InstrumentEnum::SP_500 => 'https://fred.stlouisfed.org/graph/fredgraph.csv?bgcolor=%%23e1e9f0&chart_type=line&drp=0&fo=open%%20sans&graph_bgcolor=%%23ffffff&height=450&mode=fred&recession_bars=on&txtcolor=%%23444444&ts=12&tts=12&width=1168&nt=0&thu=0&trc=0&show_legend=yes&show_axis_titles=yes&show_tooltip=yes&id=SP500&scale=left&cosd=%s&coed=%s&line_color=%%234572a7&link_values=false&line_style=solid&mark_type=none&mw=3&lw=2&ost=-99999&oet=99999&mma=0&fml=a&fq=Daily%%2C%%20Close&fam=avg&fgst=lin&fgsnd=2020-02-01&line_index=1&transformation=lin&vintage_date=2021-05-20&revision_date=2021-05-20&nd=2021-05-20',
        InstrumentEnum::GOLD => 'https://fred.stlouisfed.org/graph/fredgraph.csv?bgcolor=%%23e1e9f0&chart_type=line&drp=0&fo=open%%20sans&graph_bgcolor=%%23ffffff&height=450&mode=fred&recession_bars=on&txtcolor=%%23444444&ts=12&tts=12&width=1168&nt=0&thu=0&trc=0&show_legend=yes&show_axis_titles=yes&show_tooltip=yes&id=GOLDAMGBD228NLBM&scale=left&cosd=%s&coed=%s&line_color=%%234572a7&link_values=false&line_style=solid&mark_type=none&mw=3&lw=2&ost=-99999&oet=99999&mma=0&fml=a&fq=Daily&fam=avg&fgst=lin&fgsnd=2020-02-01&line_index=1&transformation=lin&vintage_date=2021-05-21&revision_date=2021-05-21&nd=1968-04-01',
        InstrumentEnum::HIGH_YIELD => 'https://fred.stlouisfed.org/graph/fredgraph.csv?bgcolor=%%23e1e9f0&chart_type=line&drp=0&fo=open%%20sans&graph_bgcolor=%%23ffffff&height=450&mode=fred&recession_bars=on&txtcolor=%%23444444&ts=12&tts=12&width=1168&nt=0&thu=0&trc=0&show_legend=yes&show_axis_titles=yes&show_tooltip=yes&id=BAMLH0A0HYM2&scale=left&cosd=%s&coed=%s&line_color=%%234572a7&link_values=false&line_style=solid&mark_type=none&mw=3&lw=2&ost=-99999&oet=99999&mma=0&fml=a&fq=Daily%%2C%%20Close&fam=avg&fgst=lin&fgsnd=2020-02-01&line_index=1&transformation=lin&vintage_date=2021-05-21&revision_date=2021-05-21&nd=1996-12-31',
        InstrumentEnum::T10Y2Y => 'https://fred.stlouisfed.org/graph/fredgraph.csv?bgcolor=%%23e1e9f0&chart_type=line&drp=0&fo=open%%20sans&graph_bgcolor=%%23ffffff&height=450&mode=fred&recession_bars=on&txtcolor=%%23444444&ts=12&tts=12&width=1168&nt=0&thu=0&trc=0&show_legend=yes&show_axis_titles=yes&show_tooltip=yes&id=T10Y2Y&scale=left&cosd=%s&coed=%s&line_color=%%234572a7&link_values=false&line_style=solid&mark_type=none&mw=3&lw=2&ost=-99999&oet=99999&mma=0&fml=a&fq=Daily&fam=avg&fgst=lin&fgsnd=2020-02-01&line_index=1&transformation=lin&vintage_date=2021-05-21&revision_date=2021-05-21&nd=1976-06-01',
    ];

    public function create(string $name, DateTimeInterface $startAt, DateTimeInterface $endAt): string
    {
        return sprintf(self::URLS[$name], $startAt->format('Y-m-d'), $endAt->format('Y-m-d'));
    }
}

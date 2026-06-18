<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;
use IntlCalendar;

/**
 * Calcule les jours fériés tunisiens SANS API externe.
 * - Jours civils : dates grégoriennes fixes (récurrentes chaque année).
 * - Fêtes religieuses : converties depuis le calendrier hégirien (Umm al-Qura)
 *   via l'extension intl. Les dates religieuses restent approximatives
 *   (dépendantes de l'observation lunaire officielle) — ajustables à la main.
 */
class HolidayService
{
    /** [jour, mois, nom] — jours fériés civils tunisiens (grégoriens, fixes). */
    protected const CIVIL = [
        [1, 1, 'Nouvel An'],
        [14, 1, 'Fête de la Révolution et de la Jeunesse'],
        [20, 3, 'Fête de l\'Indépendance'],
        [9, 4, 'Jour des Martyrs'],
        [1, 5, 'Fête du Travail'],
        [25, 7, 'Fête de la République'],
        [13, 8, 'Fête de la Femme'],
        [15, 10, 'Fête de l\'Évacuation'],
    ];

    /** [mois hégirien, jour, nom, nb de jours] — fêtes religieuses. */
    protected const RELIGIOUS = [
        [1, 1, 'Nouvel An de l\'Hégire', 1],
        [3, 12, 'Mouled (Naissance du Prophète)', 1],
        [10, 1, 'Aïd El-Fitr', 2],
        [12, 10, 'Aïd El-Idha', 2],
    ];

    /** Tous les jours fériés (civils + religieux) pour une année grégorienne. */
    public static function forYear(int $year): array
    {
        $out = [];

        foreach (self::CIVIL as [$d, $m, $name]) {
            $out[] = ['date' => Carbon::create($year, $m, $d), 'name' => $name, 'type' => 'civil'];
        }

        foreach (self::religiousForYear($year) as $h) {
            $out[] = $h;
        }

        usort($out, fn ($a, $b) => $a['date'] <=> $b['date']);

        return $out;
    }

    /** Fêtes religieuses tombant dans l'année grégorienne donnée. */
    protected static function religiousForYear(int $year): array
    {
        if (! class_exists(IntlCalendar::class)) {
            return []; // intl indisponible : on ignore les fêtes religieuses
        }

        $out = [];
        // Les années hégiriennes qui chevauchent une année grégorienne (≈ year - 579/578).
        foreach ([$year - 579, $year - 578, $year - 577] as $hYear) {
            foreach (self::RELIGIOUS as [$hMonth, $hDay, $name, $days]) {
                $greg = self::hijriToGregorian($hYear, $hMonth, $hDay);
                if ($greg && (int) $greg->year === $year) {
                    for ($i = 0; $i < $days; $i++) {
                        $out[] = [
                            'date' => $greg->copy()->addDays($i),
                            'name' => $days > 1 ? $name . ' (jour ' . ($i + 1) . ')' : $name,
                            'type' => 'religieux',
                        ];
                    }
                }
            }
        }

        return $out;
    }

    protected static function hijriToGregorian(int $hYear, int $hMonth, int $hDay): ?Carbon
    {
        try {
            $cal = IntlCalendar::createInstance('UTC', 'en@calendar=islamic-umalqura');
            $cal->clear();
            $cal->set($hYear, $hMonth - 1, $hDay); // mois indexé à 0
            return Carbon::createFromTimestampMs($cal->getTime(), 'UTC')->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /** Enregistre/maj les jours fériés d'une année en base. Retourne le nb traité. */
    public static function sync(int $year): int
    {
        $count = 0;
        foreach (self::forYear($year) as $h) {
            Holiday::updateOrCreate(
                ['date' => $h['date']->toDateString(), 'name' => $h['name']],
                ['type' => $h['type']],
            );
            $count++;
        }

        return $count;
    }

    public static function isHoliday(string|Carbon $date): ?Holiday
    {
        $d = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return Holiday::whereDate('date', $d)->first();
    }
}

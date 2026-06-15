<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Student;

/**
 * Calcule le bulletin trimestriel d'un élève :
 * moyenne par matière (sur 20, pondérée par coefficient), moyenne générale, rang.
 */
class ReportCardService
{
    /** Bulletin complet d'un élève pour un trimestre. */
    public static function forStudent(Student $student, string $term): array
    {
        $grades = Grade::where('student_id', $student->id)->where('term', $term)
            ->with('subject')->get();

        $lines = $grades->map(function ($g) {
            $note = $g->normalized; // sur 20
            $coef = (float) $g->coefficient ?: 1;
            return [
                'subject' => $g->subject?->name ?? '—',
                'note'    => $note,
                'coef'    => $coef,
                'points'  => round($note * $coef, 2),
                'comment' => $g->comment,
            ];
        })->sortBy('subject')->values();

        $totalCoef   = (float) $lines->sum('coef');
        $totalPoints = (float) $lines->sum('points');
        $average     = $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : null;

        [$rank, $classSize] = self::rankInClass($student, $term);

        return [
            'student'     => $student,
            'term'        => $term,
            'termLabel'   => Grade::$terms[$term] ?? $term,
            'lines'       => $lines->toArray(),
            'totalCoef'   => $totalCoef,
            'totalPoints' => round($totalPoints, 2),
            'average'     => $average,
            'mention'     => self::mention($average),
            'rank'        => $rank,
            'classSize'   => $classSize,
            'hasGrades'   => $lines->isNotEmpty(),
        ];
    }

    /** Rang de l'élève dans sa classe pour le trimestre + effectif noté. */
    public static function rankInClass(Student $student, string $term): array
    {
        if (! $student->classroom_id) {
            return [null, 0];
        }

        $classmates = Student::where('classroom_id', $student->classroom_id)->pluck('id');
        $averages = [];
        foreach ($classmates as $sid) {
            $grades = Grade::where('student_id', $sid)->where('term', $term)->get();
            if ($grades->isEmpty()) {
                continue;
            }
            $coef = (float) $grades->sum('coefficient') ?: 1;
            $points = $grades->sum(fn ($g) => $g->normalized * ((float) $g->coefficient ?: 1));
            $averages[$sid] = $points / $coef;
        }

        if (empty($averages) || ! isset($averages[$student->id])) {
            return [null, count($averages)];
        }

        arsort($averages);
        $rank = array_search($student->id, array_keys($averages), true) + 1;

        return [$rank, count($averages)];
    }

    public static function mention(?float $average): ?string
    {
        if ($average === null) {
            return null;
        }
        return match (true) {
            $average >= 16 => 'Félicitations',
            $average >= 14 => 'Très bien',
            $average >= 12 => 'Bien',
            $average >= 10 => 'Assez bien',
            default        => 'Insuffisant',
        };
    }
}

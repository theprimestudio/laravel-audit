<?php

namespace ThePrimeStudio\Audit\Services;

use ThePrimeStudio\Audit\Models\AuditRun;

class ScoringService
{
    public function calculate(AuditRun $run): void
    {
        $issues = $run->issues()->get();
        $uniqueIssues = $issues->unique('type');

        $scores = [
            'security' => 100,
            'seo' => 100,
            'performance' => 100,
            'reliability' => 100,
            'laravel' => 100,
        ];

        foreach ($uniqueIssues as $issue) {
            $deduction = match ($issue->severity) {
                'critical' => 25,
                'high' => 15,
                'medium' => 10,
                'low' => 5,
                default => 0,
            };

            $category = $issue->category;
            if (isset($scores[$category])) {
                $scores[$category] = max(0, $scores[$category] - $deduction);
            }
        }

        $weights = config('audit.scoring.weights', [
            'security' => 0.30,
            'seo' => 0.25,
            'performance' => 0.20,
            'reliability' => 0.15,
            'laravel' => 0.10,
        ]);

        $overallScore = 0.0;
        foreach ($scores as $category => $score) {
            $weight = $weights[$category] ?? 0.0;
            $overallScore += $score * $weight;
        }

        $run->update([
            'overall_score' => $overallScore,
            'seo_score' => $scores['seo'],
            'security_score' => $scores['security'],
            'performance_score' => $scores['performance'],
            'reliability_score' => $scores['reliability'],
            'laravel_score' => $scores['laravel'],
            'issues_found' => $issues->count(),
        ]);
    }
}

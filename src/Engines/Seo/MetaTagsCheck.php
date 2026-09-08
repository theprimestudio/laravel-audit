<?php

namespace ThePrimeStudio\Audit\Engines\Seo;

use ThePrimeStudio\Audit\Contracts\AuditCheck;
use ThePrimeStudio\Audit\Contracts\AuditContext;

class MetaTagsCheck implements AuditCheck
{
    public function name(): string
    {
        return 'SEO Meta Tags Check';
    }

    public function category(): string
    {
        return 'seo';
    }

    public function run(AuditContext $context): array
    {
        $dom = $context->getDom();
        if (!$dom) {
            return [];
        }

        $results = [];

        // Check Title tag
        $titles = $dom->getElementsByTagName('title');
        if ($titles->length === 0) {
            $results[] = [
                'failed' => true,
                'type' => 'missing_title',
                'severity' => 'high',
                'title' => 'Missing Page Title',
                'description' => 'The page is missing a <title> tag in the HTML head.',
                'recommendation' => 'Add a unique, descriptive <title> tag between 50-60 characters.',
            ];
        } else {
            $titleText = trim($titles->item(0)->textContent);
            if (strlen($titleText) < 10) {
                $results[] = [
                    'failed' => true,
                    'type' => 'short_title',
                    'severity' => 'low',
                    'title' => 'Short Page Title',
                    'description' => 'The title tag is too short (' . strlen($titleText) . ' chars).',
                    'recommendation' => 'Ensure the title tag has enough descriptive detail (at least 10 chars).',
                ];
            }
        }

        // Check Meta Description
        $metas = $dom->getElementsByTagName('meta');
        $hasDescription = false;
        foreach ($metas as $meta) {
            if (strtolower($meta->getAttribute('name')) === 'description') {
                $hasDescription = true;
                $descText = trim($meta->getAttribute('content'));
                if (strlen($descText) < 50) {
                    $results[] = [
                        'failed' => true,
                        'type' => 'short_meta_description',
                        'severity' => 'low',
                        'title' => 'Short Meta Description',
                        'description' => 'The description tag is too short (' . strlen($descText) . ' chars).',
                        'recommendation' => 'Ensure description tag has between 120-160 characters for best display.',
                    ];
                }
                break;
            }
        }

        if (!$hasDescription) {
            $results[] = [
                'failed' => true,
                'type' => 'missing_meta_description',
                'severity' => 'medium',
                'title' => 'Missing Meta Description',
                'description' => 'The page is missing a meta description.',
                'recommendation' => 'Add a unique meta description tag with a summarizing description.',
            ];
        }

        return $results;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Exam;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Service;
use App\Models\University;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Loads all public website content from database/seeders/content.
 * Safe to re-run: every record is upserted by its natural key.
 */
class ContentSeeder extends Seeder
{
    private string $dir;

    public function run(): void
    {
        $this->dir = database_path('seeders/content');

        DB::transaction(function () {
            $this->destinations();
            $this->pages();
            $this->legalPages();
            $this->services();
            $this->exams();
            $this->universities();
        });
    }

    private function json(string $file): array
    {
        $path = $this->dir.'/'.$file;
        $data = json_decode(File::get($path), true);

        if (! is_array($data)) {
            throw new RuntimeException("Invalid JSON in {$path}");
        }

        return $data;
    }

    private function destinations(): void
    {
        foreach ($this->json('destinations.json') as $row) {
            Destination::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }

    private function pages(): void
    {
        $destinations = Destination::pluck('id', 'slug');

        foreach (File::files($this->dir.'/pages') as $i => $file) {
            $doc = json_decode($file->getContents(), true);

            Page::updateOrCreate(['slug' => $doc['slug']], [
                'kind' => $doc['kind'],
                'destination_id' => $destinations[$doc['destination']] ?? null,
                'title' => $doc['title'],
                'nav_label' => $doc['nav_label'] ?? null,
                'meta_description' => $doc['meta_description'] ?? null,
                'sections' => $doc['sections'],
                'sort_order' => $i,
            ]);
        }
    }

    private function legalPages(): void
    {
        $legal = [
            'privacy-policy' => 'Privacy Policy',
            'terms-conditions' => 'Terms & Conditions',
            'disclaimer' => 'Legal Disclaimer',
            'refund-policy' => 'Refund Policy',
        ];

        $order = 100;
        foreach ($legal as $slug => $title) {
            $html = File::get($this->dir."/legal/{$slug}.html");

            // The first paragraph holds "Last Updated: …" – store it as metadata instead of body text.
            $html = preg_replace('#^<p>Last Updated:[^<]*</p>\s*#', '', $html);

            Page::updateOrCreate(['slug' => $slug], [
                'kind' => 'legal',
                'title' => $title,
                'nav_label' => $title,
                'meta_description' => "{$title} of EUROPA FUSION PRIVATE LIMITED – how we handle your information, our services and your rights.",
                'body' => trim($html),
                'last_updated' => '2026-01-01',
                'sort_order' => $order++,
            ]);
        }
    }

    private function services(): void
    {
        foreach ($this->json('services.json') as $row) {
            Service::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }

    private function exams(): void
    {
        $data = $this->json('exam-prep.json');

        foreach ($data['exams'] as $row) {
            Exam::updateOrCreate(['code' => $row['code'], 'category' => $row['category']], $row);
        }

        Faq::where('group', 'exam-prep')->delete();
        foreach ($data['faqs'] as $i => $row) {
            Faq::create($row + ['sort_order' => $i + 1]);
        }
    }

    private function universities(): void
    {
        foreach ($this->json('universities.json') as $i => $row) {
            University::updateOrCreate(['name' => $row['name']], $row + ['sort_order' => $i + 1]);
        }
    }
}

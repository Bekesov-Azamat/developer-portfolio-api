<?php

namespace App\Repositories;

use App\Contracts\Repositories\ContactMetricsRepository;
use App\Data\Metrics\ContactMetricsSnapshot;
use App\Enums\AiStatus;
use App\Enums\MailStatus;
use App\Enums\ProcessingStatus;
use App\Models\ContactSubmission;
use BackedEnum;

final class EloquentContactMetricsRepository implements ContactMetricsRepository
{
    public function snapshot(): ContactMetricsSnapshot
    {
        return new ContactMetricsSnapshot(
            totalSubmissions: ContactSubmission::query()->count(),
            processing: $this->countsBy(
                column: 'processing_status',
                values: array_column(
                    ProcessingStatus::cases(),
                    'value',
                ),
            ),
            ai: $this->countsBy(
                column: 'ai_status',
                values: array_column(
                    AiStatus::cases(),
                    'value',
                ),
            ),
            ownerMail: $this->countsBy(
                column: 'owner_mail_status',
                values: array_column(
                    MailStatus::cases(),
                    'value',
                ),
            ),
            userMail: $this->countsBy(
                column: 'user_mail_status',
                values: array_column(
                    MailStatus::cases(),
                    'value',
                ),
            ),
            aiTotalTokens: (int) ContactSubmission::query()
                ->sum('ai_total_tokens'),
        );
    }

    /**
     * @param  list<string>  $values
     * @return array<string, int>
     */
    private function countsBy(
        string $column,
        array $values,
    ): array {
        /** @var array<string, int> $counts */
        $counts = array_fill_keys($values, 0);

        $rows = ContactSubmission::query()
            ->select($column)
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy($column)
            ->get();

        foreach ($rows as $row) {
            $status = $row->getAttribute($column);
            $aggregate = $row->getAttribute('aggregate');

            if ($status instanceof BackedEnum) {
                $status = $status->value;
            }

            if (
                ! is_string($status)
                || ! array_key_exists($status, $counts)
            ) {
                continue;
            }

            $counts[$status] = is_numeric($aggregate)
                ? (int) $aggregate
                : 0;
        }

        return $counts;
    }
}

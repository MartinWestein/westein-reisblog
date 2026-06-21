<?php

namespace App\Actions\Subscribers;

use App\Models\Subscriber;
use League\Csv\Writer;

class ExportSubscribersAction
{
    /**
     * @param  array{status?: string, search?: string}  $filters
     */
    public function execute(array $filters = []): string
    {
        $query = Subscriber::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        match ($filters['status'] ?? 'all') {
            'pending' => $query->pending(),
            'active' => $query->active(),
            'unsubscribed' => $query->unsubscribed(),
            default => null,
        };

        $csv = Writer::createFromString();
        $csv->insertOne(['email', 'name', 'status', 'aangemeld_op', 'bevestigd_op', 'uitgeschreven_op']);

        $query->orderBy('email')->chunkById(500, function ($subscribers) use ($csv) {
            foreach ($subscribers as $subscriber) {
                $csv->insertOne([
                    $subscriber->email,
                    $subscriber->name ?? '',
                    $subscriber->status(),
                    $subscriber->created_at?->toIso8601String() ?? '',
                    $subscriber->confirmed_at?->toIso8601String() ?? '',
                    $subscriber->unsubscribed_at?->toIso8601String() ?? '',
                ]);
            }
        });

        return $csv->toString();
    }
}

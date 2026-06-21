<?php

namespace App\Actions\Subscribers;

class ImportSubscribersResult
{
    /**
     * @param  array<int, array{row: int, email: string, reason: string}>  $errors
     */
    public function __construct(
        public readonly int $created = 0,
        public readonly int $existing = 0,
        public readonly int $unsubscribed = 0,
        public readonly array $errors = [],
        public readonly ?string $errorReportToken = null,
    ) {}

    public function totalProcessed(): int
    {
        return $this->created + $this->existing + $this->unsubscribed + count($this->errors);
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}

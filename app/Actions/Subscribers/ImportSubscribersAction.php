<?php

namespace App\Actions\Subscribers;

use App\Models\Subscriber;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;

class ImportSubscribersAction
{
    private const ERROR_REPORT_DISK = 'local';

    private const ERROR_REPORT_DIR = 'imports/subscriber-errors';

    public function execute(UploadedFile $file): ImportSubscribersResult
    {
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0);

        $errors = [];
        $created = 0;
        $existing = 0;
        $unsubscribed = 0;

        $records = (new Statement)->process($csv);

        foreach ($records as $rowNumber => $record) {
            // rowNumber is 1-based vanaf de header; +1 voor mens-leesbaar regelnummer in 't bestand
            $displayRow = $rowNumber + 1;

            $email = strtolower(trim((string) ($record['email'] ?? '')));
            $name = trim((string) ($record['name'] ?? '')) ?: null;

            if ($email === '') {
                $errors[] = [
                    'row' => $displayRow,
                    'email' => '',
                    'reason' => __('Lege e-mailkolom.'),
                ];

                continue;
            }

            $validator = Validator::make(
                ['email' => $email],
                ['email' => ['email:rfc', 'max:255']]
            );

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $displayRow,
                    'email' => $email,
                    'reason' => __('Ongeldig e-mailadres.'),
                ];

                continue;
            }

            $subscriber = Subscriber::where('email', $email)->first();

            if ($subscriber !== null) {
                if ($subscriber->isUnsubscribed()) {
                    $unsubscribed++;
                } else {
                    $existing++;
                }

                continue;
            }

            Subscriber::create([
                'email' => $email,
                'name' => $name,
            ]);

            $created++;
        }

        $token = null;
        if (count($errors) > 0) {
            $token = $this->writeErrorReport($errors);
        }

        return new ImportSubscribersResult(
            created: $created,
            existing: $existing,
            unsubscribed: $unsubscribed,
            errors: $errors,
            errorReportToken: $token,
        );
    }

    /**
     * Schrijf de foutrijen naar een tijdelijke CSV.
     * Returns een token waarmee de admin het bestand later kan downloaden.
     *
     * @param  array<int, array{row: int, email: string, reason: string}>  $errors
     */
    private function writeErrorReport(array $errors): string
    {
        $token = Str::ulid()->toBase32();
        $filename = "{$token}.csv";
        $path = self::ERROR_REPORT_DIR."/{$filename}";

        Storage::disk(self::ERROR_REPORT_DISK)->makeDirectory(self::ERROR_REPORT_DIR);

        $csv = Writer::createFromString();
        $csv->insertOne([__('regel'), __('email'), __('reden')]);
        foreach ($errors as $error) {
            $csv->insertOne([$error['row'], $error['email'], $error['reason']]);
        }

        Storage::disk(self::ERROR_REPORT_DISK)->put($path, $csv->toString());

        return $token;
    }

    /**
     * Pad naar het foutrapport voor de gegeven token. Gebruikt door downloadErrorReport().
     */
    public static function errorReportPath(string $token): string
    {
        return self::ERROR_REPORT_DIR."/{$token}.csv";
    }

    public static function errorReportDisk(): string
    {
        return self::ERROR_REPORT_DISK;
    }
}

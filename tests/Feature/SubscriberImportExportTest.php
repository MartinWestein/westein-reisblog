<?php

use App\Actions\Subscribers\ExportSubscribersAction;
use App\Actions\Subscribers\ImportSubscribersAction;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'subscribers.manage', 'guard_name' => 'web']);

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web'])
        ->givePermissionTo('subscribers.manage');
    Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);
});

function uploadCsv(string $content, string $filename = 'test.csv'): UploadedFile
{
    $tmp = tempnam(sys_get_temp_dir(), 'csv');
    file_put_contents($tmp, $content);

    return new UploadedFile($tmp, $filename, 'text/csv', null, true);
}

// --------------------------------------------------------------------
// Import: aggregaat-tellers
// --------------------------------------------------------------------

it('importeert nieuwe abonnees als pending', function () {
    $action = new ImportSubscribersAction;
    $csv = "email,name\nnew1@voorbeeld.nl,Anna\nnew2@voorbeeld.nl,Bart\n";

    $result = $action->execute(uploadCsv($csv));

    expect($result->created)->toBe(2)
        ->and($result->existing)->toBe(0)
        ->and($result->errors)->toBeEmpty()
        ->and(Subscriber::count())->toBe(2);

    foreach (Subscriber::all() as $sub) {
        expect($sub->status())->toBe('pending');
    }
});

it('telt bestaande abonnees als al bekend (silent skip, AVG)', function () {
    Subscriber::factory()->confirmed()->create(['email' => 'bestaat@voorbeeld.nl']);

    $action = new ImportSubscribersAction;
    $csv = "email,name\nbestaat@voorbeeld.nl,X\nnew@voorbeeld.nl,Y\n";

    $result = $action->execute(uploadCsv($csv));

    expect($result->created)->toBe(1)
        ->and($result->existing)->toBe(1)
        ->and(Subscriber::count())->toBe(2);
});

it('telt uitgeschreven abonnees apart en respecteert hun keuze', function () {
    Subscriber::factory()->unsubscribed()->create(['email' => 'uitgeschreven@voorbeeld.nl']);

    $action = new ImportSubscribersAction;
    $csv = "email,name\nuitgeschreven@voorbeeld.nl,X\n";

    $result = $action->execute(uploadCsv($csv));

    expect($result->created)->toBe(0)
        ->and($result->existing)->toBe(0)
        ->and($result->unsubscribed)->toBe(1)
        ->and(Subscriber::where('email', 'uitgeschreven@voorbeeld.nl')->first()->isUnsubscribed())->toBeTrue();
});

it('rapporteert ongeldige emails per regel', function () {
    Storage::fake('local');

    $action = new ImportSubscribersAction;
    $csv = "email,name\ngoed@voorbeeld.nl,Goede\ngeen-apenstaartje,Bart\n,LegeEmail\n";

    $result = $action->execute(uploadCsv($csv));

    expect($result->created)->toBe(1)
        ->and($result->errors)->toHaveCount(2)
        ->and($result->errors[0]['row'])->toBe(3)
        ->and($result->errors[1]['row'])->toBe(4)
        ->and($result->errorReportToken)->not->toBeNull();
});

it('schrijft een foutrapport-CSV op disk bij fouten', function () {
    Storage::fake('local');

    $action = new ImportSubscribersAction;
    $csv = "email,name\nongeldig,X\n";

    $result = $action->execute(uploadCsv($csv));

    expect($result->errorReportToken)->not->toBeNull();
    Storage::disk('local')->assertExists(
        ImportSubscribersAction::errorReportPath($result->errorReportToken)
    );
});

it('schrijft geen foutrapport als er geen fouten zijn', function () {
    Storage::fake('local');

    $action = new ImportSubscribersAction;
    $csv = "email,name\ngoed@voorbeeld.nl,X\n";

    $result = $action->execute(uploadCsv($csv));

    expect($result->errorReportToken)->toBeNull();
});

// --------------------------------------------------------------------
// Import: controller route
// --------------------------------------------------------------------

it('importeert via de controller met flash-bericht', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $csv = "email,name\nflash@voorbeeld.nl,Flash\n";
    $file = uploadCsv($csv);

    $this->actingAs($editor)
        ->post(route('admin.subscribers.import'), ['file' => $file])
        ->assertRedirect(route('admin.subscribers.index'))
        ->assertSessionHas('success');

    expect(Subscriber::where('email', 'flash@voorbeeld.nl')->exists())->toBeTrue();
});

it('zet warning-flash plus download-action bij import met fouten', function () {
    Storage::fake('local');

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $csv = "email,name\nongeldig,X\n";
    $file = uploadCsv($csv);

    $this->actingAs($editor)
        ->post(route('admin.subscribers.import'), ['file' => $file])
        ->assertRedirect(route('admin.subscribers.index'))
        ->assertSessionHas('warning')
        ->assertSessionHas('flash_action_url')
        ->assertSessionHas('flash_action_label');
});

it('weigert lid bij import', function () {
    $member = User::factory()->create();
    $member->assignRole('lid');

    $csv = "email,name\nx@voorbeeld.nl,X\n";
    $file = uploadCsv($csv);

    $this->actingAs($member)
        ->post(route('admin.subscribers.import'), ['file' => $file])
        ->assertForbidden();
});

it('downloadt foutrapport via signed token', function () {
    Storage::fake('local');

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $action = new ImportSubscribersAction;
    $csv = "email,name\nongeldig,X\n";
    $result = $action->execute(uploadCsv($csv));

    $this->actingAs($editor)
        ->get(route('admin.subscribers.import-errors', $result->errorReportToken))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

it('geeft 404 voor onbekende foutrapport-token', function () {
    Storage::fake('local');

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get(route('admin.subscribers.import-errors', 'niet-bestaande-token'))
        ->assertNotFound();
});

it('downloadt een import-template', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get(route('admin.subscribers.import-template'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

// --------------------------------------------------------------------
// Export
// --------------------------------------------------------------------

it('exporteert alle abonnees naar CSV', function () {
    Subscriber::factory()->count(3)->confirmed()->create();
    Subscriber::factory()->count(2)->pending()->create();

    $action = new ExportSubscribersAction;
    $csv = $action->execute();

    $lines = explode("\n", trim($csv));

    expect($lines[0])->toBe('email,name,status,aangemeld_op,bevestigd_op,uitgeschreven_op')
        ->and(count($lines))->toBe(6); // 1 header + 5 abonnees
});

it('respecteert status-filter bij export', function () {
    Subscriber::factory()->count(3)->confirmed()->create();
    Subscriber::factory()->count(2)->pending()->create();

    $action = new ExportSubscribersAction;
    $csv = $action->execute(['status' => 'pending']);

    $lines = explode("\n", trim($csv));
    expect(count($lines))->toBe(3); // header + 2 pending
});

it('respecteert search-filter bij export', function () {
    Subscriber::factory()->create(['email' => 'anna@voorbeeld.nl']);
    Subscriber::factory()->create(['email' => 'bart@voorbeeld.nl']);

    $action = new ExportSubscribersAction;
    $csv = $action->execute(['search' => 'anna']);

    $lines = explode("\n", trim($csv));
    expect(count($lines))->toBe(2) // header + 1 match
        ->and($csv)->toContain('anna@voorbeeld.nl')
        ->not->toContain('bart@voorbeeld.nl');
});

it('biedt export-route alleen aan met juiste permissie', function () {
    $member = User::factory()->create();
    $member->assignRole('lid');

    $this->actingAs($member)
        ->get(route('admin.subscribers.export'))
        ->assertForbidden();
});

it('downloadt export via controller', function () {
    Subscriber::factory()->confirmed()->create();

    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get(route('admin.subscribers.export'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

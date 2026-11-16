<?php

use App\Models\EdicionVarianza;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
});

it('permite crear una edicion con enlace externo a la nube', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');

    $postData = [
        'ev_titulo' => 'Revista Varianza No 20',
        'ev_numero' => '20',
        'ev_anio' => 2026,
        'ev_mes' => 'Julio - Diciembre',
        'ev_slug' => 'revista-varianza-no-20',
        'ev_editorial' => 'IETA - UMSA',
        'ev_enlace_url' => 'https://drive.google.com/file/d/varianza20/view',
        'ev_estado' => true,
    ];

    $response = $this->actingAs($admin)->post(route('admin.ediciones-varianza.store'), $postData);
    $response->assertRedirect();

    $edicion = EdicionVarianza::where('ev_slug', 'revista-varianza-no-20')->first();
    expect($edicion)->not->toBeNull();
    expect($edicion->ev_enlace_url)->toBe('https://drive.google.com/file/d/varianza20/view');
    expect($edicion->enlace_url)->toBe('https://drive.google.com/file/d/varianza20/view');
});

it('permite actualizar el enlace externo de una edicion existente', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');

    $edicion = EdicionVarianza::create([
        'ev_titulo' => 'Revista Varianza No 21',
        'ev_numero' => '21',
        'ev_anio' => 2026,
        'ev_slug' => 'revista-varianza-no-21',
        'ev_enlace_url' => 'https://drive.google.com/file/d/old/view',
    ]);

    $updateData = [
        'ev_titulo' => 'Revista Varianza No 21 Actualizada',
        'ev_numero' => '21',
        'ev_anio' => 2026,
        'ev_slug' => 'revista-varianza-no-21',
        'ev_enlace_url' => 'https://www.dropbox.com/s/varianza21/completo.pdf?dl=0',
    ];

    $response = $this->actingAs($admin)->put(route('admin.ediciones-varianza.update', $edicion->id), $updateData);
    $response->assertRedirect(route('admin.ediciones-varianza.show', $edicion->id));

    $edicion->refresh();
    expect($edicion->ev_enlace_url)->toBe('https://www.dropbox.com/s/varianza21/completo.pdf?dl=0');
});

it('extrae automaticamente la primera pagina de un archivo PDF como portada', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');

    // Crear un archivo PDF mínimo válido para la prueba
    $pdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 300 300]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000010 00000 n \n0000000060 00000 n \n0000000117 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n200\n%%EOF";
    $tempPdf = tempnam(sys_get_temp_dir(), 'test_pdf_').'.pdf';
    file_put_contents($tempPdf, $pdfContent);

    $uploadedFile = new UploadedFile(
        $tempPdf,
        'revista_prueba.pdf',
        'application/pdf',
        null,
        true
    );

    $postData = [
        'ev_titulo' => 'Revista Varianza con Portada Auto',
        'ev_numero' => '22',
        'ev_anio' => 2026,
        'ev_slug' => 'revista-varianza-auto-portada',
        'extraer_portada_de_pdf' => true,
        'ev_archivo' => $uploadedFile,
    ];

    $response = $this->actingAs($admin)->post(route('admin.ediciones-varianza.store'), $postData);
    $response->assertRedirect();

    $edicion = EdicionVarianza::where('ev_slug', 'revista-varianza-auto-portada')->first();
    expect($edicion)->not->toBeNull();

    // Comprobar que la colección 'ev_archivo' tiene el archivo
    expect($edicion->getFirstMedia('ev_archivo'))->not->toBeNull();

    // Comprobar que la colección 'ev_portada' se generó exitosamente
    expect($edicion->getFirstMedia('ev_portada'))->not->toBeNull();
    expect($edicion->portada_url)->not->toBeNull();

    @unlink($tempPdf);
});

it('permite regenerar la portada desde un PDF existente al editar', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');

    $pdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 300 300]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000010 00000 n \n0000000060 00000 n \n0000000117 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n200\n%%EOF";
    $tempPdf = tempnam(sys_get_temp_dir(), 'test_pdf_').'.pdf';
    file_put_contents($tempPdf, $pdfContent);

    $uploadedFile = new UploadedFile(
        $tempPdf,
        'revista_prueba.pdf',
        'application/pdf',
        null,
        true
    );

    $edicion = EdicionVarianza::create([
        'ev_titulo' => 'Revista Varianza No 23',
        'ev_numero' => '23',
        'ev_anio' => 2026,
        'ev_slug' => 'revista-varianza-no-23',
    ]);
    $edicion->addMedia($uploadedFile)->toMediaCollection('ev_archivo');

    // Inicialmente no tiene portada
    expect($edicion->getFirstMedia('ev_portada'))->toBeNull();

    // Actualizar con flag extraer_portada_de_pdf = true sin subir nuevo archivo
    $updateData = [
        'ev_titulo' => 'Revista Varianza No 23 Con Portada',
        'ev_numero' => '23',
        'ev_anio' => 2026,
        'ev_slug' => 'revista-varianza-no-23',
        'extraer_portada_de_pdf' => true,
    ];

    $response = $this->actingAs($admin)->put(route('admin.ediciones-varianza.update', $edicion->id), $updateData);
    $response->assertRedirect();

    $edicion->refresh();
    expect($edicion->getFirstMedia('ev_portada'))->not->toBeNull();
    expect($edicion->portada_url)->not->toBeNull();

    @unlink($tempPdf);
});

it('extrae automaticamente la portada desde un enlace externo de Google Drive', function () {
    $admin = User::factory()->withTwoFactor()->create();
    $admin->assignRole('administrador');

    // Imagen PNG básica simulada
    $fakePng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

    Http::fake([
        'https://drive.google.com/thumbnail*' => Http::response($fakePng, 200, ['Content-Type' => 'image/png']),
    ]);

    $postData = [
        'ev_titulo' => 'Revista Varianza No 24 Test Drive',
        'ev_numero' => '24',
        'ev_anio' => 2026,
        'ev_slug' => 'revista-varianza-no-24-test-drive',
        'ev_enlace_url' => 'https://drive.google.com/file/d/1WTDsRbGEbFlH2CuPdsmm7MjXVyrmETtQ/view',
        'extraer_portada_de_pdf' => true,
    ];

    $response = $this->actingAs($admin)->post(route('admin.ediciones-varianza.store'), $postData);
    $response->assertRedirect();

    $edicion = EdicionVarianza::where('ev_slug', 'revista-varianza-no-24-test-drive')->first();
    expect($edicion)->not->toBeNull();
    expect($edicion->getFirstMedia('ev_portada'))->not->toBeNull();
    expect($edicion->portada_url)->not->toBeNull();
});

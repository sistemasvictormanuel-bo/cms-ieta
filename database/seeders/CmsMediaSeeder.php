<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CmsMediaSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/media.json';
        $seedStorageDir = __DIR__.'/data/storage';

        if (! file_exists($jsonPath)) {
            return;
        }

        $mediaRecords = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($mediaRecords)) {
            return;
        }

        foreach ($mediaRecords as $media) {
            DB::table('media')->updateOrInsert(
                ['id' => $media['id']],
                [
                    'model_type' => $media['model_type'],
                    'model_id' => $media['model_id'],
                    'uuid' => $media['uuid'],
                    'collection_name' => $media['collection_name'],
                    'name' => $media['name'],
                    'file_name' => $media['file_name'],
                    'mime_type' => $media['mime_type'],
                    'disk' => $media['disk'],
                    'conversions_disk' => $media['conversions_disk'] ?? $media['disk'],
                    'size' => $media['size'],
                    'manipulations' => is_array($media['manipulations']) ? json_encode($media['manipulations']) : ($media['manipulations'] ?? '[]'),
                    'custom_properties' => is_array($media['custom_properties']) ? json_encode($media['custom_properties']) : ($media['custom_properties'] ?? '[]'),
                    'generated_conversions' => is_array($media['generated_conversions']) ? json_encode($media['generated_conversions']) : ($media['generated_conversions'] ?? '[]'),
                    'responsive_images' => is_array($media['responsive_images']) ? json_encode($media['responsive_images']) : ($media['responsive_images'] ?? '[]'),
                    'order_column' => $media['order_column'] ?? null,
                    'created_at' => $media['created_at'] ?? now(),
                    'updated_at' => $media['updated_at'] ?? now(),
                ]
            );

            // Asegurar la presencia física del archivo en el almacenamiento público
            $targetDir = storage_path("app/public/{$media['id']}");
            $targetFile = "{$targetDir}/{$media['file_name']}";
            $sourceFile = "{$seedStorageDir}/{$media['id']}/{$media['file_name']}";

            if (! file_exists($targetFile) && file_exists($sourceFile)) {
                File::ensureDirectoryExists($targetDir);
                File::copy($sourceFile, $targetFile);
            }
        }

        // Sincronizar imágenes del editor multimedia si existen en seeders
        $sourceEditor = "{$seedStorageDir}/editor";
        $targetEditor = storage_path('app/public/editor');
        if (file_exists($sourceEditor)) {
            File::ensureDirectoryExists($targetEditor);
            File::copyDirectory($sourceEditor, $targetEditor);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('media', 'id'), COALESCE(MAX(id), 1)) FROM media;");
        }
    }
}

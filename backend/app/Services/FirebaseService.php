<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FirebaseService
{
    /**
     * Upload an uploaded file to Firebase (Google Cloud Storage) if configured.
     * Falls back to local storage if Google client isn't available or not configured.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $pathPrefix
     * @return string Publicly-accessible URL
     */
    public function upload($file, ?string $pathPrefix = null): string
    {
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-.]/', '_', $file->getClientOriginalName());
        $path = trim(($pathPrefix ?? 'uploads') . '/' . $fileName, '/');

        // If Google Cloud Storage client is available and env is configured, use it
        if (class_exists('\Google\Cloud\Storage\StorageClient') && env('FIREBASE_STORAGE_BUCKET')) {
            try {
                $storage = new \Google\Cloud\Storage\StorageClient([
                    'keyFilePath' => env('GOOGLE_APPLICATION_CREDENTIALS') ?: null,
                ]);

                $bucketName = env('FIREBASE_STORAGE_BUCKET');
                $bucket = $storage->bucket($bucketName);

                $object = $bucket->upload(fopen($file->getRealPath(), 'r'), [
                    'name' => $path,
                    'predefinedAcl' => 'publicRead',
                ]);

                return sprintf('https://storage.googleapis.com/%s/%s', $bucketName, $path);
            } catch (\Throwable $e) {
                // Fall through to local storage fallback
            }
        }

        // Local storage fallback (storage/app/public/uploads/...)
        $storedPath = $file->storeAs('public/' . ($pathPrefix ?? 'uploads'), $fileName);

        // If storedPath is like public/uploads/..., expose via /storage/ URL
        $appUrl = env('APP_URL', '');
        if ($appUrl) {
            return rtrim($appUrl, '/') . '/' . ltrim(str_replace('public/', 'storage/', $storedPath), '/');
        }

        return $storedPath;
    }
}

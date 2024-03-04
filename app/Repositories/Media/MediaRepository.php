<?php

namespace App\Repositories\Media;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaRepository
{
    public static function attachMediaToModel(Model $model, UploadedFile $file, string $mediaType): Media
    {
        $storagePath = config('media-path.' . $mediaType . '.path', 'public/default');
        $disk = config('media-path.' . $mediaType . '.disk', 'public');
        $path = $file->store($storagePath, $disk);
        $fullUrl = Storage::url($path);

        $mediaData = [
            'file_name' => $fullUrl,
            'mime_type' => $file->getMimeType(),
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
        ];

        return Media::create($mediaData);
    }

    public static function detachMediaFromModel(Model $model, string $mediaType): void
    {
        Media::where('model_type', get_class($model))
            ->where('model_id', $model->getKey())
            ->where('file_name', $mediaType)
            ->delete();
    }
}


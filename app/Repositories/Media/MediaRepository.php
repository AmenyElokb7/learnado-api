<?php

namespace App\Repositories\Media;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class MediaRepository
{

    public static function detachMediaFromModel(Model $model, int $mediaId): void
    {
        $media = Media::find($mediaId);
        if ($media && $media->model_id === $model->getKey() && $media->model_type === get_class($model)) {
            $media->delete();
        }
    }

    public static function attachOrUpdateMediaForModel(Model $model, UploadedFile $file, ?int $mediaId = null, $title = null): Media
    {
        $disk = config('media-path.' . get_class($model) . '.disk', 'public');
        $storagePath = config('media-path.' . get_class($model) . '.path', 'default');

        $path = $file->store($storagePath, $disk);


        $mediaData = [
            'file_name' => $path,
            'mime_type' => $file->getMimeType(),
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'title' => $title,
        ];

        $media = $mediaId ? Media::find($mediaId) : new Media;

        if ($media) {
            $media->fill($mediaData);

            if ($media->isDirty()) {
                $media->save();
            } else {
                Log::info('Media is not dirty');
            }
        }

        return $media;
    }
}


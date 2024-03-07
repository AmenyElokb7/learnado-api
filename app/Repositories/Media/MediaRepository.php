<?php

namespace App\Repositories\Media;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaRepository
{
    public static function attachMediaToModel(Model $model, UploadedFile $file): Media
    {
        $storagePath = config('media-path.' . get_class($model) . '.path', 'api/default');
        $disk = config('media-path.' . get_class($model) . '.disk', 'public');
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

    public static function detachMediaFromModel(Model $model, int $mediaId): void
    {
        $media = Media::find($mediaId);
        if ($media && $media->model_id === $model->getKey() && $media->model_type === get_class($model)) {
            $media->delete();
        }
    }

    /**
     * @param Model $model
     * @param UploadedFile $file
     * @param int $mediaId
     * @return Media
     */
    public static function updateMediaFromModel(Model $model, UploadedFile $file, int $mediaId): Media
    {
        $media = Media::find($mediaId);
        $storagePath = config('media-path.' . $media->model_type . '.path', 'api/default');
        $disk = config('media-path.' . $media->model_type . '.disk', 'public');
        $path = $file->store($storagePath, $disk);
        $fullUrl = Storage::url($path);

        if ($media && $media->model_id === $model->getKey() && $media->model_type === get_class($model)) {

            $media->file_name = $fullUrl;
            $media->mime_type = $file->getMimeType();
            $media->save();
            return $media;
        }
        return new Media();
    }
}


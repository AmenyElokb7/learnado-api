<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed model_type
 * @property mixed model_id
 * @property mixed file_name
 * @property mixed mime_type
 * @property mixed external_url
 */
class Media extends Model
{

    protected $fillable = ['model_type', 'model_id', 'file_name', 'mime_type', 'external_url'];

    public function model()
    {
        return $this->morphTo();
    }
}

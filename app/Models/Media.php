<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed model_type
 * @property mixed model_id
 * @property mixed file_name
 * @property mixed mime_type
 */
class Media extends Model
{
    use HasFactory, SoftDeletes;
    protected $dateFormat = 'U';

    protected $fillable = ['model_type', 'model_id', 'file_name', 'mime_type','title', 'created_at', 'updated_at'];

    public function model()
    {
        return $this->morphTo();
    }
}

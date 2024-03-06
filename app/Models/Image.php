<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::deleted(function (Image $image) {
            Storage::delete($image->path);
        });
        static::saving(function (Image $image) {
            $image->alt = $image->alt ?? $image->post->title;
            $image->credit = $image->credit ?? $image->post->user->name;
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}

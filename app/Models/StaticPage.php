<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'meta_description',
        'content_en',
        'content_fr',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

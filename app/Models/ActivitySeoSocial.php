<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivitySeoSocial extends Model
{
    protected $table = 'activity_seo_social';
    protected $primaryKey = 'seo_id';
    public $timestamps = true;

    protected $fillable = [
        'activity_id',
        'service_id',
        'short_description',
        'full_description',
        'highlights',
        'seo_title',
        'seo_description',
        'keywords_tags',
        'og_title',
        'og_description',
        'og_image_path',
    ];

    protected $casts = [
        'keywords_tags' => 'array',
    ];

    /**
     * Relationship to Activity
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * Get keywords as array
     */
    public function getKeywordsArray()
    {
        return $this->keywords_tags ?? [];
    }
}

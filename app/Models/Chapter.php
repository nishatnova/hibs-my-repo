<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'leader_id',
        'chapter_name',
        'image',
        'contact_email',
        'contact_phone',
        'city',
        'state_province',
        'address',
        'intro',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getImageAttribute($value)
    {
        if ($value) {
            // If it already starts with 'http', return as is
            if (str_starts_with($value, 'http')) {
                return $value;
            }
            // Otherwise, add the asset path
            return asset($value);
        }
        
    }


    /**
     * Get the leader for this chapter.
     */

    public function leader()
    {
        return $this->belongsTo(Leader::class);
    }

}

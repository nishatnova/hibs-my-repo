<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leader extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'chapter_id',
        'journey_description',
        'anchor_reason',
        'willingness',
        'is_agree',
        'is_approved',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_agree' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the leader.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the chapter that owns the leader.
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

}

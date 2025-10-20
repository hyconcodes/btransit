<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'vehicle_name',
        'plate_number',
        'status',
        'is_available',
        'vehicle_photo_path',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'vehicle_last_updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }
}
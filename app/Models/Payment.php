<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ride_id',
        'amount',
        'reference',
        'payment_method',
        'status',
        'paid_at',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }
}
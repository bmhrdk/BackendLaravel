<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'merek',
        'tipe',
        'keluhan',
        'nomor_antrian',
        
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);  // Using default foreign key 'user_id'
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailService extends Model
{
    use HasFactory;
    protected $fillable = [
        'kerusakan',
        'biaya_kerusakan',
        'estimasi',
        'service_id',
    ];
    public function service()
    {
        return $this->belongsTo(Service::class);  // Using default foreign key 'user_id'
    }
}

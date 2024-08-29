<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'address',
        'phone',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);  // Using default foreign key 'user_id'
    }
    public function service()
    {
        return $this->hasMany(Service::class);
    }
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }
    

}

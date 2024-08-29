<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $fillable = [
        'merek',
        'tipe',
        'stok',
        'harga',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);  // Using default foreign key 'user_id'
    }
    public function inventorydetail()
    {
        return $this->hasMany(InventoryDetail::class);  // Using default foreign key 'user_id'
    }
}

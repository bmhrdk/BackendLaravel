<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'service_id',
        'inventory_id',
        'jumlah_sparepart',
        'harga_satuan',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);  // Using default foreign key 'user_id'
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);  // Using default foreign key 'user_id'
    }


}

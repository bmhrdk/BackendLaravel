<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'merek',
        'tipe',
        'diagnosa_awal',
        'status',
        'admin_created_id',
        'technician_created_id',
        'admin_processed_id',
        'admin_finished_id',
        'customer_id',
    ];
    public function adminCreated()
    {
        return $this->belongsTo(Admin::class, 'admin_created_id');
    }
    
    public function technicianCreated()
    {
        return $this->belongsTo(Technician::class, 'technician_created_id');
    }
    
    public function adminProcessed()
    {
        return $this->belongsTo(Admin::class, 'admin_processed_id');
    }
    
    public function adminFinished()
    {
        return $this->belongsTo(Admin::class, 'admin_finished_id');
    }    
    public function customer()
    {
        return $this->belongsTo(Customer::class);  // Using default foreign key 'user_id'
    }
    public function detailservice()
    {
        return $this->hasOne(DetailService::class);  // Using default foreign key 'user_id'
    }
    public function inventorydetail()
    {
        return $this->hasMany(InventoryDetail::class);  // Using default foreign key 'user_id'
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedSparepart extends Model
{
    protected $table = 'used_spareparts';
    protected $fillable = [
        'spareparts_id',
        'maintenance_id',
        'incident_id',
        'request_id',
        'qty',
        'note'
    ];
    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class, 'spareparts_id');
    }
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_id');
    }
    public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id');
    }
    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }
}

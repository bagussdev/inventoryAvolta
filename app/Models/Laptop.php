<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laptop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hostname',
        'serialnumber',
        'model',
        'brand',
        'location',
        'typewindows',
        'user',
        'iprealvnc',
        'osstatus',
        'status',
        'created_by',
    ];

    protected static function booted()
    {
        static::creating(function ($desktop) {
            // Hanya generate kalau hostname kosong (bukan dari import)
            if (!$desktop->hostname) {
                $desktop->hostname = self::generateNextHostname();
            }
        });
    }

    public static function generateNextHostname(): string
    {
        $prefix = 'WSMPPADPS';

        // Ambil angka terbesar dari hostname yang ada (termasuk soft delete)
        $lastNumber = self::withTrashed()
            ->where('hostname', 'like', $prefix . '-%')
            ->selectRaw("MAX(CAST(SUBSTRING(hostname, ? + 2) AS UNSIGNED)) as max_number", [strlen($prefix)])
            ->value('max_number');

        $next = ($lastNumber ?? 0) + 1;

        // Format dengan leading zero (0001, 0002, dst.)
        return sprintf("%s-%04d", $prefix, $next);
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function store()
    {
        return $this->belongsTo(Store::class, 'location');
    }
}

<?php

namespace App\Modules\Classroom\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AttendanceSession extends Model
{
    protected $table = 'attendance_sessions';

    protected $fillable = [
        'teacher_id',
        'date',
        'session_label',
        'latitude',
        'longitude',
        'radius_meters',
        'expires_at',
        'token',
        'is_active',
    ];

    protected $casts = [
        'date'       => 'date',
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
        'latitude'   => 'float',
        'longitude'  => 'float',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class, 'session_id');
    }

    /** Is this session still accepting check-ins? */
    public function isOpen(): bool
    {
        return $this->is_active && $this->expires_at->isFuture();
    }

    /**
     * Haversine great-circle distance in metres between two coordinates.
     */
    public static function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $earthRadius = 6371000; // metres
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return (int) round($earthRadius * $c);
    }
}

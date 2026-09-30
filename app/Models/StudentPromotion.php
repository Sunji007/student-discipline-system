<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPromotion extends Model
{
    protected $table = 'student_promotions';
    protected $primaryKey = 'promotion_id';

    protected $fillable = [
        'academic_year',
        'semester_id',
        'evaluated_semester_id',
        'promoted_count',
        'graduated_count',
        'retained_count',
        'promoted_at',
    ];

    protected $casts = [
        'academic_year'   => 'integer',
        'promoted_count'  => 'integer',
        'graduated_count' => 'integer',
        'retained_count'  => 'integer',
        'promoted_at'     => 'datetime',
    ];

    public static function isYearPromoted(int $year): bool
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('student_promotions')) {
            return static::where('academic_year', $year)->exists();
        }

        return (bool) cache()->get("auto_promoted_academic_year_{$year}", false);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformantReport extends Model
{
    protected $primaryKey = 'ReportID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'ReportID', 'Title', 'Category', 'Description', 'IsAnonymous', 'ReporterName',
        'ReporterID', 'StudentID', 'EvidencePath', 'Status', 'Remarks', 'ReportDate'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->ReportID)) {
                $model->ReportID = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($model->ReportDate)) {
                $model->ReportDate = now();
            }
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'ReporterID', 'UserID');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'StudentID', 'StudentID');
    }
}

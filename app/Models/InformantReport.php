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
        'ReporterID', 'StudentID', 'EvidencePath', 'Status', 'Remarks', 'ReportDate', 'semester_id'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->ReportID)) {
                $last = static::orderByRaw("CAST(SUBSTRING(ReportID, 5) AS UNSIGNED) DESC")->first();
                $nextNum = 1;
                if ($last && preg_match('/INF-(\d+)/', $last->ReportID, $m)) {
                    $nextNum = intval($m[1]) + 1;
                } else {
                    $nextNum = static::count() + 1;
                }
                $candidate = 'INF-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
                while (static::where('ReportID', $candidate)->exists()) {
                    $nextNum++;
                    $candidate = 'INF-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
                }
                $model->ReportID = $candidate;
            }
            if (empty($model->ReportDate)) {
                $model->ReportDate = now();
            }
            if (empty($model->semester_id)) {
                if (session()->has('selected_semester_id')) {
                    $model->semester_id = session('selected_semester_id');
                } else {
                    $activeId = \App\Models\Semester::where('is_active', true)->value('semester_id')
                        ?: \App\Models\Semester::value('semester_id');
                    $model->semester_id = $activeId;
                }
            }
            if (!isset($model->IsAnonymous)) {
                $model->IsAnonymous = true;
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

    public function getInvolvedStudentsAttribute()
    {
        if (empty($this->StudentID)) {
            return collect();
        }
        $ids = array_filter(array_map('trim', preg_split('/[\s,;]+/', $this->StudentID)));
        if (empty($ids)) {
            return collect();
        }
        return Student::whereIn('StudentID', $ids)->get();
    }

    public function getEvidencePathsAttribute()
    {
        if (empty($this->EvidencePath)) {
            return [];
        }
        $decoded = json_decode($this->EvidencePath, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (str_contains($this->EvidencePath, '|')) {
            return explode('|', $this->EvidencePath);
        }
        return [$this->EvidencePath];
    }
}

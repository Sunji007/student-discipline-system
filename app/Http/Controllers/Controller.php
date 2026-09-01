<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getSelectedSemesterId()
    {
        if (session()->has('selected_semester_id')) {
            return session('selected_semester_id');
        }
        
        $activeId = \App\Models\Semester::where('is_active', true)->value('semester_id');
        if ($activeId) {
            session(['selected_semester_id' => $activeId]);
            return $activeId;
        }
        
        $firstId = \App\Models\Semester::value('semester_id');
        if ($firstId) {
            session(['selected_semester_id' => $firstId]);
            return $firstId;
        }
        
        return null;
    }
}

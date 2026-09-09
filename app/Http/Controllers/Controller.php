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
        
        $activeSemester = \App\Models\Semester::current();
        if ($activeSemester) {
            session(['selected_semester_id' => $activeSemester->semester_id]);
            return $activeSemester->semester_id;
        }
        
        return null;
    }
}

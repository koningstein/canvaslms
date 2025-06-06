<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Toon de cursus selector pagina
     */
    public function index()
    {
        return view('courses.index');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ToolsController extends Controller
{
    /**
     * Show tools page
     */
    public function index()
    {
        return view('admin.tools.index');
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\StorageService;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function home()
    {
        return redirect()->route('explorer');
    }

    public function explorer()
    {
        return view('explorer');
    }
}

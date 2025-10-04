<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Alexusmai\LaravelFileManager\Controllers\FileManagerController as BaseFileManagerController;

class FileManagerController extends BaseFileManagerController
{
    public function index()
    {
        return view('file-manager.index');
    }
}

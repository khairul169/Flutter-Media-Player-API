<?php

namespace App\Http\Controllers;

class MediaController extends Controller
{
    public function index()
    {
        return response('CloudMediaPlayer API');
    }
}

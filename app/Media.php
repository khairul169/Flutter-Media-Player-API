<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Media extends Model
{
    protected $fillable = [
        'title', 'artist', 'album', 'year', 'duration', 'media_fname', 'cover_fname'
    ];
    protected $hidden = [];
}

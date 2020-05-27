<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'title', 'artist', 'album', 'year', 'duration', 'filename'
    ];
    protected $hidden = ['filename'];
}

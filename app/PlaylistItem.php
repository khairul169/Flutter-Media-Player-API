<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlaylistItem extends Model
{
    protected $fillable = ['playlist_id', 'media_id'];
    protected $hidden = [];
}

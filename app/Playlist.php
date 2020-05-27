<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = ['title', 'description'];
    protected $hidden = [];

    public function items()
    {
        return $this->hasManyThrough('App\Media', 'App\PlaylistItem', 'playlist_id', 'id', 'id', 'media_id');
    }
}

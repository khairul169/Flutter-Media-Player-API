<?php

namespace App\Http\Controllers;

use App\Playlist;
use App\PlaylistItem;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function index()
    {
        try {
            $result = Playlist::query()->with('items')->get();
            return $this->result($result);
        } catch (QueryException $e) {
            return $this->error($e->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            $playlist = Playlist::query()->create($request->all());
            return $this->result($playlist);
        } catch (QueryException $e) {
            return $this->error($e->getMessage());
        }
    }

    public function addItem($id, Request $request)
    {
        $mediaId = $request->get('media_id');
        if (!isset($mediaId)) {
            return $this->error('Media id empty.');
        }

        try {
            $item = PlaylistItem::query()->create([
                'playlist_id' => $id,
                'media_id' => $mediaId,
            ]);
            return $this->result($item);
        } catch (QueryException $e) {
            return $this->error($e->getMessage());
        }
    }
}

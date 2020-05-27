<?php

namespace App\Http\Controllers;

use App\Media;
use App\Playlist;
use App\PlaylistItem;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use App\Utils;

class MediaController extends Controller
{
    public function index()
    {
        $result = Media::query()->get();
        return $this->result($result);
    }

    public function getMedia($id)
    {
        $media = Media::query()->find($id);
        if (!$media) {
            return $this->error('Media not found');
        }
        return $this->result($media);
    }

    public function upload(Request $request)
    {
        // Get uploaded file
        $file = $request->file('media') ?? null;
        if (!$file) {
            return $this->error('No file');
        }

        // Add to Playlist on uploaded
        $playlistId = $request->get('playlist_id', 0);
        $playlist = Playlist::query()->find($playlistId);

        if (!$playlist) {
            return $this->error('Cannot find playlist');
        }

        // File data
        $filename = $file->getClientOriginalName();
        $ext = substr($filename, strrpos($filename, '.') + 1);
        $filename = substr($filename, 0, strrpos($filename, '.'));

        if ($ext != 'mp3' && $ext != 'flac') {
            return $this->error('Media not supported');
        }

        // Media id3 tags
        $meta_tags = Utils::getID3Tags($file->getRealPath());

        // Media data
        $title = $meta_tags['title'] ?? '';
        $title = !empty($title) ? $title : $filename;
        $artist = $meta_tags['artist'] ?? '';
        $album = $meta_tags['album'] ?? '';
        $year = $meta_tags['year'] ?? 0;
        $duration = $meta_tags['duration'] ?? 0.0;
        $cover_img = $meta_tags['image'] ?? null;

        // Save uploaded file
        $media_fname = $this->saveMedia($file, $ext);
        $cover_fname = $this->createCoverImage($cover_img);

        if (!$media_fname) {
            return $this->error('Cannot save media');
        }

        try {
            // Create media
            $media = Media::query()->create([
                'title' => $title,
                'artist' => $artist,
                'album' => $album,
                'year' => $year,
                'duration' => $duration,
                'media_fname' => $media_fname,
                'cover_fname' => $cover_fname,
            ]);

            // Add media to playlist
            PlaylistItem::query()->create([
                'playlist_id' => $playlist->id,
                'media_id' => $media->id,
            ]);

            return $this->result($media);
        } catch (QueryException $error) {
            return $this->error('Cannot insert data. ' . $error->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        try {
            $media = Media::query()->findOrFail($id);
            $media->update($request->all());
            return $this->result($media);
        } catch (Exception $error) {
            return $this->error($error->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            Media::query()->findOrFail($id)->delete();
            return $this->result(true);
        } catch (Exception $error) {
            return $this->error($error->getMessage());
        }
    }

    public function downloadMedia($filename)
    {
        $path = $this->getMediaPath($filename);

        if (!file_exists($path)) {
            return response(null, 404);
        }

        $headers = ['Content-type' => 'audio/mpeg'];
        return response()->download($path, $filename, $headers);
    }

    public function getCoverImage($filename)
    {
        $path = $this->getCoverPath($filename);

        if (!file_exists($path)) {
            return redirect('/images/cover_default.png');
        }

        $headers = ['Content-type' => 'image/jpeg'];
        return response()->download($path, $filename, $headers, 'inline');
    }

    private function getMediaPath($file = null)
    {
        return storage_path() . '/media' . ($file ? ('/' . $file) : '');
    }

    private function getCoverPath($file = null)
    {
        return storage_path() . '/cover_image' . ($file ? ('/' . $file) : '');
    }

    private function saveMedia($file, $type)
    {
        $filename = substr(md5(time()), 5, 16) . ".$type";
        $path = $this->getMediaPath();

        // Create upload dir if not exist
        if (!file_exists($path)) {
            mkdir($path, 777, true);
        }

        // Move uploaded file
        if (!$file->move($path, $filename)) {
            return null;
        }
        return $filename;
    }

    private function createCoverImage($image)
    {
        $filename = substr(md5('img' . time()), 5, 16) . ".jpg";
        $path = $this->getCoverPath($filename);

        if (file_put_contents($path, $image) != false) {
            return $filename;
        }

        return null;
    }
}

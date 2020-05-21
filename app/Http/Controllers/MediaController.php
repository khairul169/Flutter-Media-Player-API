<?php

namespace App\Http\Controllers;

use App\Media;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use App\Utils;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $result = Media::all()->toArray();
        foreach ($result as $key => $value) {
            $result[$key] = $this->mapMediaResult($request, $value);
        }
        return $this->result($result);
    }

    public function getMedia($id, Request $request)
    {
        $media = Media::query()->find($id);
        if (!$media) {
            return $this->error('Media not found');
        }

        $data = $media->toArray();
        $data = $this->mapMediaResult($request, $data);
        return $this->result($data);
    }

    private function mapMediaResult($request, $media)
    {
        $root = $request->root();
        $id = $media['id'];

        $media['image'] = "$root/media/image/$id";
        $media['url'] = "$root/media/get/$id";

        return $media;
    }

    public function upload(Request $request)
    {
        // Get uploaded file
        $file = $request->file('media') ?? null;
        if (!$file) {
            return $this->error('No file');
        }

        // File data
        $filename = $file->getClientOriginalName();
        $ext = substr($filename, strrpos($filename, '.') + 1);
        $filename = substr($filename, 0, strrpos($filename, '.'));

        switch ($ext) {
            case 'mp3':
            case 'flac':
                break;
            default:
                return $this->error('Media not supported');
        }

        // Media id3 tags
        $meta_tags = Utils::getID3Tags($file->getRealPath());

        // Media title
        $title = $request->get('title', $meta_tags['title'] ?? '');
        $title = !empty($title) ? $title : $filename;
        $artist = $request->get('artist', $meta_tags['artist'] ?? '');
        $album = $request->get('album', $meta_tags['album'] ?? '');
        $year = $request->get('year', $meta_tags['year'] ?? 0);

        // Set local filename
        $localname = 'user-' . substr(md5(time()), 5, 16) . ".$ext.media";
        $path = $this->getMediaPath();

        // Create upload dir if not exist
        if (!file_exists($path)) {
            mkdir($path, 777, true);
        }

        // Move uploaded file
        if (!$file->move($path, $localname)) {
            return $this->error('Cannot move file');
        }

        try {
            $media = Media::query()->create([
                'title' => $title,
                'artist' => $artist,
                'album' => $album,
                'year' => $year,
                'filename' => $localname,
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

    public function download($id)
    {
        $media = Media::query()->find($id);
        if (!$media) {
            return response(null, 404);
        }

        $path = $this->getMediaPath($media->filename);
        if (!file_exists($path)) {
            return response(null, 404);
        }

        $headers = [
            'Content-type' => 'audio/mpeg',
        ];
        return response()->download($path, $media->filename, $headers);
    }

    public function getAlbumArt($id)
    {
        $media = Media::query()->find($id);
        if (!$media) {
            return redirect('/images/cover_default.png');
        }

        $path = $this->getMediaPath($media->filename);
        $tags = Utils::getID3Tags($path);
        $image = $tags['image'] ?? null;

        if ($image) {
            return response($image)->header('Content-Type', 'image/jpeg');
        }

        return redirect('/images/cover_default.png');
    }

    private function getMediaPath($file = null)
    {
        return storage_path() . '/media' . ($file ? '/' . $file : '');
    }
}

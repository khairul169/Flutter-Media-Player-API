<?php

namespace App\Http\Controllers;

use App\Media;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use duncan3dc\MetaAudio\Tagger;
use Exception;

class MediaController extends Controller
{
    public function index()
    {
        $result = Media::all();
        return $this->result($result);
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return $this->error('No file');
        }

        // File
        $file = $request->file('file');
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

        $tagger = new Tagger;
        $tagger->addDefaultModules();
        $metatags = $tagger->open($file->getRealPath());

        // Media title
        $title = $metatags->getTitle();
        $title = $request->get('title', !empty($title) ? $title : $filename);
        $artist = $request->get('artist', $metatags->getArtist());
        $album = $request->get('album', $metatags->getAlbum());
        $year = $request->get('year', $metatags->getYear());

        // Set local filename
        $localname = 'user-' . substr(md5(time()), 5, 16) . ".$ext.media";
        $path = storage_path() . '/media';

        // Create upload dir if not exist
        if (!file_exists($path)) {
            mkdir($path, 777, true);
            touch($path . '/.gitignore');
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
}

<?php

namespace App;

use getID3;

class Utils
{
    /**
     * Get id3 meta tags
     */

    static function getID3Tags($path)
    {
        $id3 = new getID3;
        $file_id3 = $id3->analyze($path);
        $meta_tags = [];

        if (!isset($file_id3['tags'])) {
            return $meta_tags;
        }

        // Get tags
        $meta_tags['duration'] = $file_id3['playtime_seconds'] ?? 0.0;
        $id3_tags = $file_id3['tags']['id3v2'] ?? $file_id3['tags']['id3v1'];

        if ($id3_tags) {
            foreach ($id3_tags as $key => $value) {
                $meta_tags[$key] = $value[0] ?? null;
            }
        }

        if (isset($file_id3['comments'])) {
            // Cover image
            $img = $file_id3['comments']['picture'][0];
            $meta_tags['image'] = isset($img) ? $img['data'] : null;
        }

        return $meta_tags;
    }
}

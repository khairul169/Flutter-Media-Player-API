<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Return result data
     */

    protected function result($data)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Return error message
     */

    protected function error($message = 'Error unexpected')
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ]);
    }
}

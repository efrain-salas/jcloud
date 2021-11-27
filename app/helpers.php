<?php

use App\Models\File;
use App\Models\Folder;
use App\Services\HelperService;

if ( ! function_exists('object')) {
    function object(string $objectKey): File|Folder
    {
        [$type, $id] = explode('_', $objectKey);
        if ($type == 'file') {
            return File::withTrashed()->find($id);
        } else {
            return Folder::withTrashed()->find($id);
        }
    }
}

if ( ! function_exists('jcloud')) {
    function jcloud(): HelperService
    {
        return new HelperService();
    }
}

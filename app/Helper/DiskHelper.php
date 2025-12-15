<?php

namespace App\Helper;

use Illuminate\Support\Facades\Storage;

class DiskHelper
{
    /**
     * Return the s3 storage disk.
     *
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    public static function getS3Disk()
    {
        return Storage::disk('s3');
    }
}

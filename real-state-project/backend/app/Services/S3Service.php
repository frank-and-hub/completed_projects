<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class S3Service
{

    /**
     * Upload a file to the S3 bucket.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     */
    public function upload($file, $path, $key = null)
    {
        $originalName = $file->getClientOriginalName();
        $fileName = time() . '_' . str_replace([' ', '/', '-'], '_', $originalName);
        if ($key) {
            $fileName = $key;
        }
        $filePath = $file->storeAs($path, $fileName, 'aws_storage');
        return $filePath ? Storage::disk('s3')->url($filePath) : null;
    }

    /**
     * Retrieve a file from the S3 bucket.
     *
     * @param  string  $filename
     * @return mixed
     */

    public function getFileUrl($filePath)
    {
        return Storage::disk('s3')->url($filePath);
    }

    /**
     * Delete a file from the S3 bucket.
     *
     * @param  string  $filename
     * @return bool
     */

    public function deleteFile($filePath)
    {
        return Storage::disk('s3')->delete($filePath);
    }

    /**
     * Update a file on the S3 bucket (replace an existing file).
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $filename
     * @return string
     */
    // public function updateFile($file, $filename)
    // {
    //     $this->deleteFile($filename); // Delete the old file
    //     return $this->upload($file); // Upload the new file
    // }

    /**
     * List all files in the 'uploads' directory on S3.
     *
     * @return array
     */
    public function listFiles()
    {
        return Storage::disk('s3')->files('uploads');
    }

    public function fileExists($filePath)
    {
        return Storage::disk('s3')->exists($filePath);
    }


}

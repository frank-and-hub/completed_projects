<?php
namespace App\Services;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use Storage;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\App;
class ImageUpload
{
    public static function upload($file, $folder, $fileName)
    {
        if ($fileName) {
            $filePath = 'asset/' . $folder . '/' . $fileName;
        } else {
            $fileName = $file->getClientOriginalName();
            $filePath = 'asset/' . $folder . '/' . $fileName;
        }
        $result = Storage::disk('s3')->put($filePath, file_get_contents($file));
        // dd($result);
        return $result;
    }
    public static function generatePreSignedUrl($folderName)
    {
        $s3 = App::make('s3');
        // $s3 = new S3Client([
        //     'version' => 'latest',
        //     'region' => env('AWS_DEFAULT_REGION'),
        //     'credentials' => [
        //         'key' => env('AWS_ACCESS_KEY_ID'),
        //         'secret' => env('AWS_SECRET_ACCESS_KEY'),
        //     ]
        //     ]);
        // $s3 = new S3Client([
        // 	'region' => 'us-east-1',
        // 	'version' => 'latest',
        // 	'credentials' => [
        // 		'key' => config('services.ses.key'),
        //      'secret' => config('services.ses.secret'),
        // 	],
        // ]);
        // $folderName = 'asset';
        $objectKey = 'asset/' . $folderName;
        //$objectKey = $folderName . '/'.$fileName;
        $objects = $s3->getCommand('GetObject', [
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $objectKey,
        ]);
        $expiration = '3600';
        $presignedUrl = $s3->createPresignedRequest($objects, '+' . $expiration . ' seconds')->getUri()->__toString();
        return $presignedUrl;
    }
    public static function fileExists($file)
    {
        try {
            $filePath = 'asset/' . $file;
            // Check the file from the S3 storage disk exists or not
            return Storage::disk('s3')->exists($filePath);
        } catch (\Exception $e) {
            // Log or handle the exception
            return false;
        }
    }
    public static function deleteImage($file)
    {
        $filePathToDelete = 'asset/' . $file;
        // Delete the file from the S3 storage disk
        $result = Storage::disk('s3')->delete($filePathToDelete);
        return $result;
    }
    public static function ImageCopy($image_path, $old_folder, $new_folder)
    {
        // Assuming you already have an S3 client configured
        $s3 = new S3Client([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            // Add other necessary configuration options
        ]);
        // Extract the file name from the presigned URL
        $presignedUrl = $image_path;
        $fileName = basename(parse_url($presignedUrl, PHP_URL_PATH));
        // Specify the source and destination keys
        $sourceObjectKey = $old_folder . $fileName; // 'asset/employee/'
        $destinationObjectKey = $new_folder . $fileName; //'asset/profile/member_avatar/'
        // Copy the object to the destination
        $s3->copyObject([
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $destinationObjectKey,
            'CopySource' => env('AWS_BUCKET') . '/' . $sourceObjectKey,
        ]);
    }
}
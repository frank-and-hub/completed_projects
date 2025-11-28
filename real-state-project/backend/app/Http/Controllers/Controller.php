<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use TCPDF;

abstract class Controller
{
    /**
     * Abstract Controller class providing utility methods for file handling,
     * encryption, time conversion, and email sending.
     *
     * Methods:
     *
     * - __imageSave($request, $key, $folder_name, $old_img): ?string
     *   Handles saving an image file to a specified folder, optionally deleting an old image.
     *
     * - __property_image($file, $folder_name, $old_img): ?string
     *   Handles saving a property image file to a specified folder, optionally deleting an old image.
     *
     * - __admin_image($file, $folder_name, $old_img): ?string
     *   Handles saving an admin image file to a specified folder, optionally deleting an old image.
     *
     * - __contractSave($file, $key, $folder_name, $old_file): ?string
     *   Handles saving a contract file (PDF or uploaded file) to a specified folder, optionally deleting an old file.
     *
     * - __encryptPdf($fileContent)
     *   Encrypts the content of a PDF file using Laravel's Crypt facade.
     *
     * - __decryptPdf($fileContent)
     *   Decrypts the content of a PDF file using Laravel's Crypt facade.
     *
     * - getFileUrl($path, $dummyPath): ?string
     *   Retrieves the URL of a file if it exists, or returns a dummy path if provided.
     *
     * - SendMail(array $email, string $subject, string $body)
     *   Sends an email using the Helper class's sendOneSignalMail method.
     *
     * - convertToSouthAfricaTime($createdAtUTC, $timeZone, $time): string
     *   Converts a UTC timestamp to South Africa time (Africa/Johannesburg) and formats it.
     *
     * - convertToForShow($dateTime, $timezone): DateTime
     *   Converts a given date and time to a specified timezone or the user's timezone.
     *
     * - __checkIfFileExists($filename, $filepath): JsonResponse
     *   Checks if a file exists in the specified filepath using the S3 service.
     *
     * - __deleteFile($filename, $filepath): JsonResponse
     *   Deletes a file from the specified filepath using the S3 service.
     *
     * Properties:
     *
     * - $errorStatus: int
     *   HTTP status code for errors (default: 500).
     *
     * - $successStatus: int
     *   HTTP status code for success (default: 200).
     *
     * - $validationStatus: int
     *   HTTP status code for validation errors (default: 400).
     *
     * - $unauthStatus: int
     *   HTTP status code for unauthorized access (default: 401).
     *
     * - $notFoundStatus: int
     *   HTTP status code for resource not found (default: 404).
     *
     * - $invalidPermission: int
     *   HTTP status code for invalid permissions (default: 403).
     */

    protected $errorStatus = 500;
    protected $successStatus = 200;
    protected $validationStatus = 400;
    protected $unauthStatus = 401;
    protected $notFoundStatus = 404;
    protected $invalidPermission = 403;

    protected function __imageSave($request, $key = '', $folder_name = '', $old_img = ''): ?string
    {
        $fileName = null;
        if ($request->hasFile($key) && !empty($key) && !empty($folder_name)) {
            $image = $request->file($key);
            $originalName = $image->getClientOriginalName();
            $file_name = time() . '_' . str_replace([' ', '/', '-'], '_', $originalName);
            log::info($file_name);
            $fileName = $image->storeAs($folder_name, $file_name);
            if (!empty($old_img)) {
                if (Storage::exists($old_img)) {
                    Storage::delete($old_img);
                }
            }
        }

        return $fileName ?: null; // Return filename or null
    }

    protected function __property_image($file, $folder_name = '', $old_img = ''): ?string
    {
        if (!empty($folder_name)) {
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . str_replace([' ', '/', '-'], '_', $originalName);
            $filePath = $file->storeAs($folder_name, $fileName);

            if (!empty($old_img) && Storage::exists($old_img)) {
                Storage::delete($old_img);
            }

            return $filePath;
        }

        return null;
    }

    protected function __admin_image($file, $folder_name = '', $old_img = ''): ?string
    {
        if (!empty($folder_name)) {
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . str_replace([' ', '/', '-'], '_', $originalName);
            $filePath = $file->storeAs($folder_name, $fileName);

            if (!empty($old_img) && Storage::exists($old_img)) {
                Storage::delete($old_img);
            }

            return $filePath;
        }

        return null;
    }

    protected function __contractSave($file, $key = null, $folder_name = '', $old_file = ''): ?string
    {
        if (!empty($folder_name)) {
            if (!empty($old_file)) {
                if (Storage::exists($old_file)) {
                    Storage::delete($old_file);
                }
            }
            if ($file instanceof \Barryvdh\DomPDF\Pdf) {
                if ($key) {
                    // $fileName = $file->storeAs($folder_name, $key);
                    $fileName = "$folder_name/$key";
                    $directory = storage_path("app/public/$folder_name");
                    if (!file_exists($directory)) {
                        mkdir($directory, 0775, true);
                    }
                    $file->save(storage_path("app/public/$fileName"));
                } else {
                    $originalName = $file->getClientOriginalName();
                    $file_name = time() . '_' . str_replace([' ', '/', '-'], '_', $originalName);
                    log::info($file_name);
                    $fileName = $file->storeAs($folder_name, $file_name);
                }
            } else {
                $originalName = $file->getClientOriginalName();
                $file_name = time() . '_' . str_replace([' ', '/', '-'], '_', $originalName);
                log::info($file_name);
                $fileName = $file->storeAs($folder_name, $file_name);
            }
        }
        return $fileName ? Storage::url($fileName) : null;
    }

    protected function __encryptPdf($fileContent)
    {
        try {
            $pdf = Crypt::encryptString($fileContent);
            return $pdf;
        } catch (\Exception $e) {
            throw new \Exception('File encryption failed: ' . $e->getMessage());
        }
    }

    protected function __decryptPdf($fileContent)
    {
        try {
            $pdf = Crypt::decryptString($fileContent);
            return $pdf;
        } catch (\Exception $e) {
            throw new \Exception('File decryption failed: ' . $e->getMessage());
        }
    }

    function getFileUrl($path, $dummyPath = null)
    {
        return Storage::exists($path) ? asset("storage/$path") : ($dummyPath ? asset($dummyPath) : null);
    }

    protected function SendMail(array $email, string $subject, string $body)
    {
        return Helper::sendOneSignalMail($email, $subject, $body);
    }

    function convertToSouthAfricaTime($createdAtUTC, $timeZone = 'Africa/Johannesburg', $time = true)
    {
        // Ensure the input is a Carbon instance
        $createdAtUTC = Carbon::parse($createdAtUTC);

        // Convert the timestamp to South Africa timezone (Africa/Johannesburg)
        if ($time)
            $createdAtInSouthAfrica = dateF2($createdAtUTC->setTimezone($timeZone)->format('d M y, g:i A'));
        else
            $createdAtInSouthAfrica = dateF($createdAtUTC->setTimezone($timeZone)->format('d M y'));

        // Return the converted timestamp
        return $createdAtInSouthAfrica;
    }

    function convertToForShow($dateTime, $timezone = null)
    {
        $auth = auth()?->user();
        if (!$timezone) {
            $timezone = ($auth && $auth->timeZone) ?  $auth->timeZone : 'Africa/Johannesburg';
        }

        // Use today's date with the provided time
        $today = new DateTime($dateTime);

        return $today->setTimezone(new DateTimeZone($timezone));
    }
}

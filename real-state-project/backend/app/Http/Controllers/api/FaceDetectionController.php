<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FaceDetectionController extends Controller
{
    protected $rekognition;
    protected $settings;

    protected $token;
    protected $s3Client;

    public function __construct()
    {
        $this->rekognition = new RekognitionClient([
            'region' => "eu-west-1", //config('services.aws.face_liveness.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('services.aws.face_liveness.access_key'),
                'secret' => config('services.aws.face_liveness.secret_access_key'),
            ],
        ]);

        $this->token = Str::uuid();

        $this->settings = [
            'ClientRequestToken' => $this->token,
            'KmsKeyId' => '',
            'Settings' => [
                'AuditImagesLimit' => 3,
                'OutputConfig' => [
                    'S3Bucket' => "amplify-pocketpropertywebapp-dev-11808-deployment",
                    'S3KeyPrefix' => "face-liveness/",
                ],
            ],
            'Video' => [
                'S3Object' => [
                    'S3Bucket' => "amplify-pocketpropertywebapp-dev-11808-deployment",
                    'S3KeyPrefix' => "face-liveness/",
                ],
            ],
        ];

        $this->s3Client = new S3Client([
            'region' => "eu-west-1",
            'version' => 'latest',
            'credentials' => [
                'key' => config('services.aws.face_liveness.access_key'),
                'secret' => config('services.aws.face_liveness.secret_access_key'),
            ],
        ]);
    }

    public function createSession(Request $request)
    {
        $result = $this->rekognition->createFaceLivenessSession([$this->settings]);
        Log::info('this is a session Id  while createSession - > ' . $result['SessionId']);
        return response()->json(['sessionId' => $result['SessionId']]);
    }

    public function getSessionResults(Request $request)
    {
        $request->validate([
            'sessionId' => 'required|string|min:36',
        ]);
        $filePath = null;
        $sessionId = $request->input('sessionId');
        try {
            log::info('this is a session Id  while getSessionResults - > ' . $sessionId);
            $result = $this->rekognition->getFaceLivenessSessionResults([
                'SessionId' => $sessionId,
            ]);

            $resultArray = $result->toArray();
            log::info($resultArray);

            if (empty($resultArray) || !isset($resultArray['Confidence']) || !isset($resultArray['Status'])) {
                return response()->json(['error' => 'No valid results found for this session'], 404);
            }

            $imageData = $resultArray['ReferenceImage']['Bytes'];

            if ($resultArray['Confidence'] >= 50) {
                $folderPath = 'images';
                $imageName = 'reference_image_' . uniqid() . '.jpg';
                $path = Storage::disk('public')->put($folderPath . '/' . $imageName, $imageData);
                $filePath = '/storage/' . $folderPath . '/' . $imageName;
            }

            $data = [
                'confidence' => $resultArray['Confidence'] ?? null,
                'status' => $resultArray['Status'] ?? null,
            ];

            return response()->json([
                'result' => true,
                'data' => $data,
                'path' => $filePath,
            ]);
        } catch (AwsException $e) {
            Log::error('AWS Rekognition Error:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'result' => false,
                'message' => 'AWS Rekognition Error',
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error('General Error:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'result' => false,
                'message' => 'General Error',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }
}

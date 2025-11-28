<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\CreditReport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CreditReportController extends Controller
{
    private $secret_key;
    protected $is_s3bucket; // Set this to true if you are using S3 bucket
    private $apiKey;
    private $govSite;

    public function __construct()
    {
        $this->apiKey = config('services.getVerified.api_key');
        $this->secret_key = config('services.getVerified.webhook_secret');
        $this->govSite = config('services.getVerified.gov_site');
        $this->is_s3bucket = false;
    }

    public function handleWebhook(Request $request)
    {
        Log::info('Received webhook for credit report: ', $request->all());

        $data = $request->validate([
            'event' => 'required|string',
            'data' => 'required|array',
            'data.applicant_first_name' => 'required|string',
            'data.applicant_last_name' => 'required|string',
            'data.applicant_email_address' => 'required|email',
            'data.applicant_mobile_number' => 'required|string|regex:/^(\+?\d{1,4}[\s-]?)?(\(?\d{1,5}\)?[\s-]?)?[\d\s-]{3,15}$/',
            'data.applicant_date_of_birth' => 'required|date_format:Y-m-d H:i:s',
            'data.applicant_identity_number' => 'required|string|regex:/^[a-zA-Z0-9]+$/',
            'data.applicant_marital_status' => 'required|string',
            'data.applicant_documents_identity_document' => 'required|array|min:1',
            'data.applicant_documents_identity_document.*.id' => 'required|string',
            'data.applicant_documents_identity_document.*.name' => 'required|string',
            'data.applicant_documents_identity_document.*.size' => 'required|integer|min:1',
            'data.applicant_documents_identity_document.*.type' => 'required|string',
            'data.applicant_documents_identity_document.*.url' => 'required|url',
            'data.applicant_documents_photo' => 'required|array|min:1',
            'data.applicant_documents_photo.*.id' => 'required|string',
            'data.applicant_documents_photo.*.name' => 'required|string',
            'data.applicant_documents_photo.*.size' => 'required|integer|min:1',
            'data.applicant_documents_photo.*.type' => 'required|string',
            'data.applicant_documents_photo.*.url' => 'required|url',
            'data.signature' => 'required|string',
            'data.credit_report_pdf' => 'required|string|url',
        ]);

        $signature = $request->header('X-Signature');
        if (!$this->isValidSignature($signature, $request->getContent(), $this->secret_key)) {
            return response()->json(['status' => 'invalid signature'], 400);
        }

        try {
            DB::beginTransaction();
            // if (true) {
            $user = User::where('email', $data['data']['applicant_email_address'])
                ->where('phone', $data['data']['applicant_mobile_number'])
                ->first();
            // } else {
            //     $user = User::where('email', 'info@mediprice.net')
            //         ->wherePhone('611161633')
            //         ->first();
            // }
            if (!$user) {
                return response()->json(['status' => 'tenant not found'], 404);
            }

            $signatureFilePath = $this->storeSignatureImage($data['data']['signature']);

            // Store the credit report
            $insertedData = [
                'user_id' => $user->id,
                'first_name' => $data['data']['applicant_first_name'],
                'last_name' => $data['data']['applicant_last_name'],
                'email' => $data['data']['applicant_email_address'],
                'phone_number' => $data['data']['applicant_mobile_number'],
                'date_of_birth' => $data['data']['applicant_date_of_birth'],
                'identity_number' => $data['data']['applicant_identity_number'],
                'marital_status' => $data['data']['applicant_marital_status'],
                'signature' => $signatureFilePath,
                'documents_identity_document' => json_encode($data['data']['applicant_documents_identity_document']),
                'documents_photo' => json_encode($data['data']['applicant_documents_photo']),
                'report_date' => now(),
                'credit_report_pdf' => $data['data']['credit_report_pdf'],
                'data' => json_encode($data),
            ];

            $creditReport = CreditReport::updateOrCreate(
                ['user_id' => $user->id,],
                $insertedData
            );

            $creditReport->save();

            if (isUrlReachable($creditReport->credit_report_pdf)) {
                $this->downloadAndEncryptPdf($creditReport->id);
            }
            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    private function isValidSignature($signature, $content, $secret)
    {
        $calculatedSignature = hash_hmac('sha256', $content, $secret);
        return hash_equals($calculatedSignature, $signature);
    }

    public function storeSignatureImage($base64String)
    {
        list($type, $data) = explode(';', $base64String);
        list(, $data) = explode(',', $data);
        $imageData = base64_decode($data);

        $folderPath = 'signature';
        $imageName = 'signature_' . uniqid() . '.png';

        if (!File::exists(storage_path('app/public/signatures'))) {
            File::makeDirectory(storage_path('app/public/signatures'), 0755, true);
        }

        Storage::disk('public')->put($folderPath . '/' . $imageName, $imageData);
        $filePath = '/storage/' . $folderPath . '/' . $imageName;

        return $filePath;
    }

    public function getCreditReport($idNumber)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->govSite . '/credit-report', [
                'id_number' => $idNumber,
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                return 'Failed to fetch credit report';
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function downloadAndEncryptPdf($id = null)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }
        try {
            $creditReport = CreditReport::with(['user'])->find($id);
            if (!$creditReport) {
                return response()->json(['error' => 'Credit report not found'], 404);
            }
            $url = $creditReport->credit_report_pdf;
            $response = Http::timeout(300)->get($url);

            if ($response->successful()) {
                $newFileName = 'credit_report_' . $creditReport->user_id . '.pdf';
                $originalPdfContent = $response->body();

                $encryptedPdf = $this->__encryptPdf($originalPdfContent);

                $directoryPath = storage_path('app/public/credit_report');
                if (!is_dir($directoryPath)) {
                    mkdir($directoryPath, 0777, true);
                }

                $filePath = $directoryPath . '/' . $newFileName;
                $path = 'credit_report/' . $newFileName;

                if ($this->is_s3bucket) {
                    Storage::disk('s3')->put($path, $encryptedPdf);
                } else {
                    file_put_contents($filePath, $encryptedPdf);
                    Storage::put($path, $encryptedPdf);
                }

                $creditReport->update([
                    'credit_report_pdf' => $path,
                ]);

                return response()->json(['message' => 'File successfully saved'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function downloadAndDecryptPdf(Request $request, $t, $id = null)
    {
        $time = Crypt::decrypt($request->t ?? $t);

        if (!$time) {
            return response()->json(['error' => 'Invalid timestamp'], 400);
        }

        $carbonTime = Carbon::createFromTimestamp($time);
        $now = Carbon::now();

        if ($carbonTime->isBefore($now)) {
            return response()->json(['error' => 'Time expired'], 410);
        }

        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $creditReport = CreditReport::with(['user'])->find($id);

        if (!$creditReport) {
            return response()->json(['error' => 'Credit report not found'], 404);
        }

        try {

            $path = $creditReport->credit_report_pdf;

            if ($this->is_s3bucket) {
                $encryptedContent = Storage::disk('s3')->get($path);
            } else {
                $encryptedContent = Storage::get($path);
            }

            if (!$encryptedContent) {
                return response()->json(['error' => 'Encrypted PDF not found'], 404);
            }

            $decryptedPdf = $this->__decryptPdf($encryptedContent);

            return response($decryptedPdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="credit_report.pdf"')
                ->header('Pragma', 'no-cache');
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Models\Contactus;
use App\Models\EnquiryEmail;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Auth\Recaller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ContactusController extends Controller
{
    public function index(Request $request)
    {
        $active_page = 'enquiry';
        $title = 'Enquiry';
        $enquiry_email = EnquiryEmail::first(['id', 'email']);
        if ($request->ajax()) {
            $data = Contactus::latest()->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                // ->addColumn('enquiry_email', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->rawColumns(['created_at'])
                ->make(true);
        }
        return view('enquiry.index', compact('active_page', 'enquiry_email', 'title'));
    }
    public function contact_us(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'subject' => 'required',
                'message' => 'required',
                're_captcha' => 'required',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return ResponseBuilder::error($errors, $this->validationStatus);
            }

            // Verify reCAPTCHA
            $recaptchaResponse = $request->input('re_captcha');
            $recaptchaSecret = config('services.recaptcha.secret_key');
            $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';

            $response = Http::asForm()->post($recaptchaUrl, [
                'secret' => $recaptchaSecret,
                'response' => $recaptchaResponse
            ]);

            $recaptchaData = $response->json();

            if (!$recaptchaData['success']) {
                return ResponseBuilder::error(__('auth.robot'), $this->validationStatus);
            }
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
            ];
            $insert = Contactus::create($data);
            if ($insert) {
                $this->sendEmail($insert);
                return ResponseBuilder::success(null, __('auth.request_submit'), $this->successStatus);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }

    public function sendEmail($user)
    {
        $enquiry_email = EnquiryEmail::first(['id', 'email']);
        $email = [$enquiry_email->email];
        $subject = 'PocketProperty Enquiries';
        $body = "Hello Admin,<br><br>" .
            "This is to notify you that the user with the following details has requested to enquiries<br><br>" .
            "<b>Name :- </b>" . $user->name . "<br>" .
            "<b>Email :- </b>" . $user->email . "<br>" .
            "<b>Subject :- </b>" . $user->subject . "<br>" .
            "<b>Message :- </b>" . $user->message . "<br>" .
            "Please take appropriate action as necessary.<br><br>" .
            "Regards,<br>" .
            "PocketProperty";
        $this->SendMail($email, $subject, $body);
    }

    public function email_update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'serverside_error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            $email_id = $request->email_id;
            EnquiryEmail::where('id', $email_id)->update(['email' => $request->email]);
            return response()->json([
                'status' => 'success',
                'msg' =>  'Email Update Successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Something Went Wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
}

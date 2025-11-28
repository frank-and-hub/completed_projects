<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreEmailRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Models\{Email, Login, Record, User};
use Illuminate\Http\Request;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class EmailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $details = Login::whereUserId(auth()->user()->id)->first('password');
        $env = env('MAIL_PASSWORD');
        $app_password = app_password();
        return view('layouts.email', compact('app_password'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required',
            'app_password' => 'required',
        ]);
        $subject = trim($request->subject);
        $file = $request->file('file');
        $auth = User::whereId($request->use_id)->first();
        $authId = $auth->id;
        $authEmail = $auth->email;
        $message = trim($request->message);
        $pattern = '/{{(.*?)}}/';
        $fieldVariable = $subjectVariable = [];
        if (preg_match_all($pattern, $message, $matches)) {
            array_push($fieldVariable, $matches[1]);
        }
        if (preg_match_all($pattern, $subject, $matches)) {
            array_push($subjectVariable, $matches[1]);
        }
        $fieldVariable = $fieldVariable[0] ?? [];
        $subjectVariable = $subjectVariable[0] ?? [];
        // $message = str_replace('}}','"])}}',str_replace('{{','{{($data["',$message));
        // remove all html extra tags from message.
        $template = preg_replace('/\n/', '<span>', $message);
        $template = str_replace('input', 'span', $template);
        $message = str_replace('<p><br></p>', '', $template);

        $all['message'] = $message;
        $f = $this->uploadfile($request);
        $insert = [
            'user_id' => $authId,
            'email' => $f['filePath'],
            'file_name' => $f['name'],
            'email_count' => 0,
            'subject' => $f['subject'] ?? null,
            'description' => $message,
            'send_date' => date('Y-m-d'),
            'send_time' => date('H:i:s'),
            'status' => '0',
            'user_email' => $authEmail,
            'created_at' => date('Y-m-d H:i:s'),
            'message' => ($message), //strip_tags($message)
            'sent_email_count' => '0',
        ];
        $details = Email::insertGetId($insert);
        $app_password = str_replace(' ', '', trim($request->app_password));
        Login::whereUserId($authId)->update(['password' => $app_password]);
        DB::beginTransaction();
        try {
            $record = $this->getFileSendEmail($file, $subject, $authId, $all, $details, $fieldVariable, $subjectVariable);
            if ($record['status']) {
                $this->deleteFile($f['filePath']);
                Email::whereId($details)->update(['status' => '1']);
            } else {
                DB::rollBack();
                Email::find($details)->delete();
                return back()->with('alert', 'have issue with your message please check again !');
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->with($ex->getMessage());
        }
        return redirect()->route('emails.show', [$details]);
    }
    public function uploadfile(Request $request)
    {
        $filePath = $request->file('file')->store('public/email/csv');
        $fileName = $request->file('file')->getClientOriginalName();
        $absolutePath = Storage::path($filePath);
        $return = [
            'filePath' => $absolutePath,
            'name' => $fileName
        ];
        return $return;
    }
    public function envUpdate($authemail, $app_password, $details)
    {
        //
    }

    public function updateEnv(Request $request)
    {
        $array = [];
        foreach ($request->form as $val) {
            $array[$val['name']] = $val['value'];
        }
        $authId = $array['id'];
        $app_password = str_replace(' ', '', trim($array['apppassword']));
        $result = Login::whereUserId($authId)->update(['password' => $app_password]);
        return ['result' => $result, 'app_password' => $app_password];
    }

    public function sendEmail(Request $request)
    {
        $batchSize = 1; // Adjust batch size as needed
        $response = ['response' => false, 'message' => 'No records to process'];
        $array = [];
        $start = $request["start"];
        $limit = $request["limit"];
        foreach ($request->form as $k => $val) {
            $array[$val['name']] = $val['value'];
        }
        $emailId = $array['email_id'];
        $authId = $array['id'];
        $result = 'next';
        $email = Email::whereId($emailId)->whereUserId($authId)
            ->whereStatus('1');
        $totalResults = $email->value('email_count');
        if (($start + $limit) >= $totalResults) {
            $result = 'finished';
        }
        DB::beginTransaction();
        try {
            $data = Record::whereUserId($authId)
                ->whereEmailId($emailId)
                ->whereStatus('0')
                ->whereNotNull('data')
                ->take($batchSize)
                ->get();
            
            if ($data->isEmpty()) {
                $result = 'completed';
                $msg = "All emails are already sent !";
            } else {
                $data->each(function ($val) use ($email) {
                    $time = date('Y-m-d H:i:s');
                    
                    $message = json_decode($val->data);
                    $to = trim($val->file);
                    $subject = trim($val->subject);
                    // update config file 
                    $this->configCode($val->id);
                    // send emails
                    $this->toSend($subject, $message, $to);
                    $val->update(['status' => '1']);
                    $sent_email_count = $email->value('sent_email_count');
                    $email->update(['sent_email_count' => ($sent_email_count + 1)]);
                    
                });
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            $result = 'error';
            $msg = $ex->getMessage();
        }
        $response = [
            'result' => $result,
            'start' => $start,
            'limit' => $limit,
            'totalResults' => $totalResults,
            'tokenName' => $request->_token,
            'msg' => $msg ?? null,
        ];
        return response()->json($response);
    }

    public function deleteFile($filePath)
    {
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }
    }

    public function getFileSendEmail($file, $subject, $authId, $all, $id, $fieldVariable, $subjectVariable)
    {
        $b = $insert = [];
        $e = 0;
        $to = $placeholder = $sub = $msg = '';
        $currentTime = date('Y-m-d H:i:s');
        try {
            DB::beginTransaction();
            if (($handle = fopen($file, 'r')) !== false) {
                $fields = fgetcsv($handle, 20000, ',');
                while (($data = fgetcsv($handle, 20000, ',')) !== false) {
                    $to = $data[0];
                    $name = $data[1];
                    $company_name = $data[2];
                    $s = $subject;
                    $m = $all['message'];
                    $all[] = $data;
                    for ($i = 1; $i < count($data); $i++) {
                        $placeholder = '{{' . $fields[$i] . '}}';
                        // making subject dynamic
                        $sub = $this->filtering($subjectVariable, $subject, $placeholder, $fields[$i], $data[$i]);
                        // making message dynamic
                        if (!empty($fieldVariable)) {
                            foreach ($fieldVariable as $val) {
                                if ($fields[$i] == $val) {
                                    if (Str::contains($m, $placeholder)) {
                                        $m = str_replace($placeholder, $data[$i], $m);
                                    }
                                }
                            }
                        }
                        $msg = !(Str::contains($m, '{{')) ? $m : '';
                        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
                            $e++;
                            if ($sub != '') {
                                $time = date('Y-m-d H:i:s', strtotime("+$e minute", strtotime($currentTime)));
                                $insert[] = [
                                    'user_id' => $authId,
                                    'file' => trim($to),
                                    'subject' => trim($sub),
                                    'email_id' => $id,
                                    'status' => '0',
                                    'name' => $name,
                                    'data' => json_encode($msg),
                                    'company_name' => $company_name,
                                    'time' => $time,
                                ];
                            }
                            // if($msg != ''){
                            //     $b[] = ['data'=> json_encode($msg),'subject' => trim($sub)];
                            // }
                        }
                    }
                }
                fclose($handle);
            }
            Email::whereId($id)->update(['email_count' => count($insert), 'subject' => $subject]);
            Record::insert($insert);
            DB::commit();
            $response = ['status' => true, 'message' => 'records created sucessfully !'];
        } catch (\Exception $ex) {
            DB::rollBack();
            $response = ['status' => false, 'message' => $ex->getLine() . ' - ' . $ex->getMessage()];
        }
        return $response;

    }

    public function toSend($subject, $message, $to)
    {
        Mail::to($to)->send(new SendEmail(trim($subject), (array) $message));
    }

    public function queue(Request $request)
    {
        $failedJobs = DB::table('failed_jobs')->get();

        if ($failedJobs->isNotEmpty()) {

            $failedJobs->each(function ($failedJob) {
                $payload = json_decode($failedJob->payload);
                $jobData = $payload->data;

                Artisan::call('queue:retry', [
                    'id' => $failedJob->id // Pass the failed job ID to retry
                ]);
            });

            Artisan::call('queue:retry');
        }
        Artisan::call('queue:work');

        Artisan::call('queue:listen');
        return 'all queue are run successfully';
    }

    public function show(Request $request, $id)
    {
        $data['email'] = Email::whereId($id)->first();
        $data['record'] = Record::whereEmailId($id)->orderBy('id','desc')->get();
        return view('admin.show', $data);
    }
    public function configCode($id)
    {
        $details = Record::whereId($id)->value('user_id');
        $user = User::find($details);
        $login = Login::whereUserId($details)->first();
        config([
            'mail.mailers.smtp.host' => "smtp.gmail.com",
            'mail.mailers.smtp.encryption' => "tls",
            'mail.mailers.smtp.username' => $user->email ?? null,
            'mail.mailers.smtp.password' => $login->password ?? null,
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.from' => [
                'address' => $user->email ?? null,
                'name' => $user->name ?? null,
            ],
        ]);
    }
    public function filtering($array, $text, $placeholder, $fields, $data)
    {
        if (empty($array)) {
            $message = $text;
        } else {
            foreach ($array as $val) {
                if ($fields == $val) {
                    if (Str::contains($text, $placeholder)) {
                        $text = str_replace($placeholder, $data, $text);
                    }
                } else {
                    $text = '';
                }
            }
            $message = $text;
        }
        return $message;
    }
}

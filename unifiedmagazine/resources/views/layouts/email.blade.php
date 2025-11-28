@extends('layouts.app')

@section('content')
<style>
#hidden_message {
  overflow: hidden; /* Hide scrollbars */
}
</style>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ __('Send Email') }}</h5>
        {{Form::open(['url'=>route('emails.store'),'method'=>'post','id'=>'registrationForm','class'=>'row g-3','enctype'=>'multipart/form-data','files'=>'true'])}}
        <div class="col-md-12">
            <div class="form-floating">
                {{Form::file('file',['id'=>'to','class'=>'form-control'])}}
                <label for="file">To</label>
            </div>
        </div>
        {{Form::hidden('app_password',app_password()??null,['id'=>'app_password','class'=>'form-control','placeholder'=>'Enter App Password','autocomplete'=>'off'])}}
        <div class="col-md-12">
            <div class="form-floating">
                {{Form::text('subject','',['id'=>'subject','class'=>'form-control','placeholder'=>'','autocomplete'=>'off'])}}
                <label for="subject">Subject</label>
            </div>
        </div>
        <div class="col-12">
                <label for="message">Message</label>
            <div class="form-floating">
        <div class="quill-editor-full" id="message"></div>
    </div>
    {{Form::hidden('use_id',auth()->user()->id,['id'=>'user_id','class'=>'form-control'])}}
    {{Form::hidden('message','',['id'=>'hidden_message','class'=>'form-control','style'=>'height: 300px;','placeholder'=>'','rows'=>'5','required'=>'true','autocomplete'=>'off'])}}
        </div>
        <div class="text-center d-grid gap-2 mt-3">
            {{Form::button('Send',['class'=>'btn btn-primary','id'=>'send_email_btn'])}}
        </div>
        {{Form::close()}}
    </div>
</div>
<button class="btn invisible btn-outline-primary send_emails" data-extension="0" id="send_emails">Send Emails</button>
<button class="btn d-none" id="modelapppassword" data-bs-toggle="modal" data-bs-target="#apppasswordupdate"></button>
<div class="modal fade" id="apppasswordupdate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        {{ Form::open(['url'=>'#','method'=>'POST','id'=>'updateAppPassword','class'=>'modal-content','name'=>'updateAppPassword']) }}
            <div class="modal-header">
                <h5 class="modal-title">Upload App Password Details</h5>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <label for="app_password" class="col-sm-4 col-form-label">App-Password</label>
                    <div class="col-sm-8">
                        {{Form::text('apppassword',null,['id'=>'apppassword','class'=>'form-control','placeholder'=>'Enter your App Password','autocomplete'=>'off','required'=>true])}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                {{Form::hidden('id',auth()->user()->id,['id'=>'user_id','class'=>'form-control'])}}
                {{Form::button('submit',['id'=>'update_app_password_details_submit',"data-bs-dismis's"=>'modal','class'=>'btn btn-primary','data-bs-dismiss'=>'modal'])}}
            </div>
        {{Form::close()}}
    </div>
</div>
@include('layouts.script')
@endsection

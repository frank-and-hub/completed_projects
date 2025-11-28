<div class="card">
    <div class="card-header d-none"> </div><br>
        <div class="card-body">
            <div class="row mb-3">
              <label for="inputDate" class="col-sm-2 col-form-label">Email : </label>
              <div class="col-sm-4">
{{$email->user_email}}
              </div>
              <label for="inputDate" class="col-sm-2 col-form-label">Csv File : </label>
              <div class="col-sm-4">
{{$email->file_name}}
              </div>
            </div>
            <div class="row mb-3">
            <label for="inputDate" class="col-sm-2 col-form-label">Total Emails : </label>
              <div class="col-sm-4">
{{$email->email_count}}
              </div>
              <label for="inputDate" class="col-sm-2 col-form-label">Mails Sent : </label>
              <div class="col-sm-4" id="totalemailswent">
{{$email->sent_email_count}}
              </div>
                <div class="d-none">
                {{ Form::open(['url'=>'#','method'=>'POST','id'=>'start_email_sending_report','class'=>'','name'=>'start_email_sending_report']) }}                    
                    {{ Form::hidden('email_id',$email->id,['id'=>'email_id','class'=>'form-control']) }}        
                    {{ Form::hidden('id',auth()->user()->id,['id'=>'user_id','class'=>'form-control']) }}
                {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
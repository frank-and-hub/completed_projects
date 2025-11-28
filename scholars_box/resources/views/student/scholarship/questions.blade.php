@forelse($scholarships as $k => $v)
        @foreach($v->scholarshipQuestionApplication as $key => $val)
        <div class="col-md-12">
            <div class="form-box-one row {{$val->type}}">
                <label for="" >{{$val->question}}*</label>
                @if($val->type == 'radio')
                    @foreach($val->scholarshipOptionsApplications as $key => $value )
                        <label class="col-md-3" for="{{$value->keys_name . '_' . $key}}">{{$value->options}}</label>
                        <input class="col-sm-1" style"width:10px"; type="{{$val->type}}" id="{{$value->keys_name . '_' . $key}}" name="radio[{{$val->id}}][]" value="{{$value->options}}" required>
                        <script>
                            $(document).ready(function(){
                                 $('#{{$value->keys_name . '_' . $key}}').prop('required',true);                             
                            });
                        </script>
                    @endforeach
                @elseif($val->type == 'checkbox')
                    @foreach($val->scholarshipOptionsApplications as $key => $value)
                        <label class="col-md-3">
                            <input class="" type="{{$val->type}}" style"width:10px"; data-question="{{$value->keys_name . '_' . $key}}" id="{{$value->keys_name . '_' . $key}}" name="checkbox[{{$val->id}}][]" value="{{$value->options}}" required>
                            {{$value->options}}
                        </label>
                        <script>
                            $(document).ready(function(){
                                $('#{{$value->keys_name . '_' . $key}}').prop('required',true);
                            });
                        </script>
                    @endforeach
                @elseif($val->type == 'document')
                    <label class="col-md-5">
                        <?php
                        $replacedQue = preg_replace('/[\/() -?%]/', '_', $val->question);
                        $dynamicId = $val->keys_name . '_' . $key.'_'.$k.'_'.$replacedQue;
                        ?>
                        <input class="" type="file" style"width:10px"; data-question="{{$val->keys_name . '_' . $key}}" id="{{$dynamicId}}" name="document[{{$val->id}}][]" required>
                    </label>
                    <input type="hidden" class="col-md-1" name="hidden_{{$dynamicId}}" id="hidden_{{$dynamicId}}" value="{{$val->options}}" />
                    <div  class="col-md-5" id="{{'question_file_' . $key.'_'.$k.'_'.$replacedQue}}" ></div>
                    <div class="col-md-1" style="margin-top: 21px">
                        <div class="form-box-one mb-0">
                            <!-- <input class="sec-btn-one" id="uqdubtn_{{$dynamicId}}" type="button" style=" padding: 12px 20px; height: 49px" value="Upload"> -->
                        </div>
                    </div>
                    <script>
                        $(document).ready(function(){
                            $('#{{$dynamicId}}').on("change",function(){
                                // $('#uqdubtn_{{$dynamicId}}').prop('disabled',false);
                                var fileName_{{$k}}_{{$key}} = $(this).val().split('\\').pop(); 
                                $('#{{"question_file_" . $key.'_'.$k.'_'.$replacedQue}}').text(fileName_{{$k}}_{{$key}});

                                var documentFile{{$k}} = $('#{{$dynamicId}}')[0].files[0];
                                if (!documentFile{{$k}}) {
                                    // $('#uqdubtn_{{$dynamicId}}').prop('disabled',true);
                                    new Noty({
                                        type: 'error',
                                        text: 'Please fill in all required fields.'
                                    }).show();
                                    return;
                                }else{
                                    // $('#uqdubtn_{{$dynamicId}}').prop('disabled',false);
                                }
                                var scholarship_id = $("input[name='scholarship_id']").val();
                                var formData = new FormData();
                                formData.append('user_id', {{auth()->user()->id}});
                                formData.append('scholarship_id', scholarship_id);
                                formData.append('document', documentFile{{$k}});
                                formData.append('question', {{$val->id}});
                    
                                $.ajax({
                                    url: "{{ route('Student.updateQuestionDocument') }}",
                                    type: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    cache: false,
                                    success: function (response) {
                                        new Noty({
                                            text: 'Documents updated successfully!'
                                        }).show();
                                        // $('#uqdubtn_{{$dynamicId}}').prop('disabled',true);
                                    },
                                    error: function (jqXHR) {
                                        var response = $.parseJSON(jqXHR.responseText);
                                        if (response && response.errors && response.errors.document) {
                                            var errorMessage = response.errors.document[0];
                                            new Noty({
                                                type: 'error',
                                                text: errorMessage
                                            }).show();
                                        } else {
                                            new Noty({
                                                type: 'error',
                                                text: 'An unexpected error occurred.'
                                            }).show();
                                        }
                                        // $('#uqdubtn_{{$dynamicId}}').prop('disabled',false);
                                    }
                                });
                            });
                            $('#{{$dynamicId}}').prop('required',true);
                        
                            $('#uqdubtn_{{$dynamicId}}').on('click',(event)=>{
                                // event.preventDefault();                                
                            });
                        });
                    </script>
                @else
                    <textarea type="text" name="textarea[{{($val->id)}}][]" class="col-md-12 form-input-one" id='textarea_more_details_about_your_document{{$key}}' placeholder="" required=true></textarea>
                    <script>
                        $(document).ready(function(){
                             $('#textarea_more_details_about_your_document{{$key}}').prop('required',true);                            
                        });
                    </script>
                @endif
            </div>
        </div>
        @endforeach
    @empty
@endforelse

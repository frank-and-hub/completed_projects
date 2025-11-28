<?php
$type = ['1' => 'Month', '0' => 'Year'];
$compounding = ['3' => 'Quarterly', '6' => 'Half Yearly', '12' => 'Annually'];
?>
<div class="modal fade show bd-example-modal-lg" id="commissionLoanModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    style="padding-right: 17px; display: block;" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commissionLoanModelLabel">Investment Plan Commission </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
                <div class="card">
                    <div class="card-body">
                        
                        <div class="form-group row col-sm-12 col-lg-12">
                            <input class="plan_id" name="plan_id" type="hidden" value="{{$data['planTenures']->plan_id}}">
                            {{-- <div class="col-md-4">
                                <label for="tenure_type">Tenure Type</label>
                                <input class="form-control model_tenure_type" readonly="" name="tenure_type" type="text" value="{{$data['planTenures']->tenure_type}}" planceholder="{{ ($data['planTenures']->tenure_type)==1 ? 'Month' : 'Year'}}" id="tenure_type">
                            </div> --}}
                            <div class="col-md-4">
                                <label for="tenure">Tenure</label>
                                <input class="form-control model_tenure" readonly="" name="tenure" type="text" value="{{ $data['planTenures']->tenure }}" id="tenure">
                            </div>
                            <div class="col-md-4">
                                <label for="effect_from">Effective From</label>
                                <input id="effect_from" class="form-control" autocomplete="off" name="effect_from" readonly type="text" value="{{ date('d/m/Y', strtotime($data['planTenures']->effective_from)) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="roi">ROI</label>
                                <input id="roi" class="form-control" autocomplete="off" name="roi" readonly type="text" value="{{ $data['planTenures']->roi }}">
                            </div>
                            <div class="col-md-4">
                                <label for="spl_roi">SPL ROI</label>
                                <input id="spl_roi" class="form-control" autocomplete="off" name="spl_roi" readonly type="text" value="{{ $data['planTenures']->spl_roi }}">
                            </div>
                            <div class="col-md-4">
                                <label for="effect_from">Compounding</label>
                                <input id="compounding" class="form-control" autocomplete="off" name="compounding" readonly type="text" value="{{ $compounding[$data['planTenures']->compounding] }}">
                            </div>
                        </div>
                        @if($data['commisssion'])
                        <div class="form-group row col-sm-12 col-lg-12">
                            <div class="col-sm-6 col-lg-12">
                                <table class="col-12">
                                    <tbody>
                                        <tr>
                                            <th class="text-center">S/No</th>
                                            <th class="text-center">Carder</th>
                                            <th class="text-center">Collector Per.</th>
                                            <th class="text-center">Associate Per.</th>
                                            <th class="text-center">Effective From</th>
                                            <th class="text-center">Tenure To</th>
                                            <th class="text-center">Tenure From</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                        @foreach ($data['commisssion'] as $key => $item)
                                            <tr>
                                                <td>
{{$key}}
                                                </td>
                                                <td>
                                                    <input name="carder_id" class="carder_id" type="hidden" value="{{ $item->carder->id }}">
                                                    <input class="form-control carder_name" readonly="" name="carder_name" type="text" value="{{ $item->carder->name }}">
                                                </td>
                                                <td>
                                                    <input class="form-control text-center collector_per" readonly="" name="collector_per" type="text" value="{{ $item->collector_per }}">
                                                </td>
                                                <td>
                                                    <input class="form-control text-center associate_per" readonly="" name="associate_per" type="text" value="{{ $item->associate_per }}">
                                                </td>
                                                <td> 
                                                    <input class="form-control text-center effective_from" readonly="" autocomplete="off" name="effective_from" type="text" value="{{ date('d/m/Y', strtotime($item->effective_from)) }}">
                                                </td>
                                                <td> 
                                                    <input class="form-control text-center tenure_to" readonly="" autocomplete="off" name="tenure_to" type="text" value="{{ $item->tenure_to }}">
                                                </td>
                                                <td> 
                                                    <input class="form-control text-center tenure_from" readonly="" autocomplete="off" name="tenure_from" type="text" value="{{ $item->tenure_from }}">
                                                </td>
                                                <td style="text-align : center;">
@if(!$item->effective_to)
                                                    <button class="btn btn-block btn-secondary btn-sm text-center edit legitRipple" title="Edit" id="edit" data-id="169"><i class="fa fa-edit"></i></button>
                                                    <button class="btn btn-block btn-primary btn-sm d-none update legitRipple" title="Update" data-id="{{$item->id}}" data-tenure_type="{{$item->tenure_type}}"><i class="fa fa-upload"></i></button>
@endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            
        </div>
    </div>
</div>
<style>
    #commissionLoanModel {
        scrollbar-width: none;
        -webkit-scrollbar-width: none;
        overflow: scroll;
    }
</style>
<script>
    $(".update").on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var plan_id = $(".plan_id").val();
        var tenure_type = $(this).data("tenure_type");
        var tenure = $(".model_tenure").val();
        var tenure_to =  $(this).parent().siblings().find('.tenure_to').val();
        var tenure_from =  $(this).parent().siblings().find('.tenure_from').val();
        var collector_per =  $(this).parent().siblings().find('.collector_per').val();
        var associate_per =  $(this).parent().siblings().find('.associate_per').val();
        var effective_from = $(this).parent().siblings().find('.effective_from').val();
        var carder_id = $(this).parent().siblings().find('.carder_id').val();

        $.ajax({
            url: "{{ route('admin.investment.commission.update') }}",
            type: 'POST',
            data: {
                'id': id,
                'plan_id': plan_id,
                'tenure_type': tenure_type,
                'tenure': tenure,
                'collector_per': collector_per,
                'associate_per': associate_per,
                'effective_from': effective_from,
                'tenure_from': tenure_from,
                'tenure_to': tenure_to,
                'carder_id': carder_id,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(e) {
                if (e.data > 0) {
                    $(".update").addClass('d-none');
                    $(".edit").removeClass('d-none');
                    $(".tenure_to").prop("readonly", true);
                    $(".tenure_from").prop("readonly", true);
                    $(".effective_from").prop("readonly", true); 
                    $(".collector_per").prop("readonly", true);
                    $(".associate_per").prop("readonly", true); 
                    swal({
                                    title: 'Success',
                                    type: 'success',
                                    text: 'Commission updated successfully !',
                                    showDenyButton: true,
                                    showCancelButton: true
                                }, function(isConfirm) {
                                    $('.close').click();
                                });
                    
                } else {
                    swal('Error!','Sorry there was an error to update data.','error');
                }
            }
        });
    });
    $(".edit").on('click', function(e) {
        e.preventDefault();
        $(this).addClass('d-none');
        $(this).next().removeClass('d-none');
        $(this).parent().siblings().find('.collector_per').attr('readonly', false);
        $(this).parent().siblings().find('.tenure_to').attr('readonly', false);
        $(this).parent().siblings().find('.tenure_from').attr('readonly', false);
        $(this).parent().siblings().find('.associate_per').attr('readonly', false);
        let datee = $(this).parent().siblings().find('.effective_from').val();
        $(this).parent().siblings().find('.effective_from').datepicker({
            format: "dd/mm/yyyy",
            autoclose: true,
            todayHighlight: true,
            startDate: datee,
        }).attr('readonly', false);
    });
</script>

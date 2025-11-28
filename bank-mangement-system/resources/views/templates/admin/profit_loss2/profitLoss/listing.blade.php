@extends('templates.admin.master')



@section('content')
    <?php
        $date_filter = '';
        $date_filter1 = '';
        $branch_filter = '';
        $head = '';
        $end_date_filter = '';
        $end_date_filter1 = '';
        if (isset($_GET['date'])) {
            $date_filter = $_GET['date'];
            if ($date_filter != '') {
                $date_filter1 = date('d/m/Y', strtotime(convertDate($date_filter)));
            }
        }
        if (isset($_GET['branch_id'])) {
            $branch_filter = $_GET['branch_id'];
        }        
        if (isset($_GET['head_id'])) {
            $head_id = $_GET['head_id'];
        }        
        $headDetail = App\Models\AccountHeads::where('head_id', $head_id)->first();        
        if (isset($_GET['end_date'])) {
            $end_date_filter = $_GET['end_date'];
            if ($end_date_filter != '') {
                $end_date_filter1 = $end_date = date('d/m/Y', strtotime(convertDate($end_date_filter)));
            }
        }
        $finacialYear = getFinacialYear();
        $branchIddd = ($branch_filter != '') ? getBranchDetail($branch_filter)->state_id : 33;
        $globalDate1 = headerMonthAvailability(date('d'), date('m'), date('Y'), $branchIddd);
        $startDate = date('d/m/Y', strtotime($finacialYear['dateStart']));
        $endDatee = date('d/m/Y', strtotime(convertDate($globalDate1)));
    ?>
    <div class="content">
        <div class="row">
            @include($filter)
            <div class="col-md-12" id="hidden-table">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">{{ $headDetail->sub_head }}</h6>
                        <div class="">
                            {{-- <button type="button" class="btn bg-dark legitRipple export_report ml-2" data-extension="1" style="float: right;">Export pdf</button> --}}
                            <button type="button" class="btn bg-dark legitRipple export_report ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    @forelse($array as $key=>$val)
                                        <td>{{ str_replace('_', ' ', ucwords($key)) }}</td>
                                    @empty
                                        <td></td>
                                    @endforelse
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
    @include($script)
@stop

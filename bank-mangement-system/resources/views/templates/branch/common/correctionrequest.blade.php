@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Correction Listing</h3>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">{{ $title }} Correction Listing</h3>
                        <input type="hidden" name="type" id="type" value="{{ $type }}">
                    </div>
                    <div class="table-responsive">
                        <table id="branch-correction-listing" class="table table-flush">
                            <thead class="">
                                <tr>
                                    <th>S/N</th>
                                    <th>Transaction Date</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
                                    <th>In context to</th>
                                    <th>Correction</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejection-view" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
      <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 600px !important; ">
        <div class="modal-content">
          <div class="modal-body p-0">
            <div class="card bg-white border-0 mb-0">
              <div class="card-header bg-transparent pb-2ß">
                <div class="text-dark text-center mt-2 mb-3">View Admin  Remark</div>
              </div>
              <div class="card-body px-lg-5 py-lg-5">
                  <div class="form-group row">
                    <!-- <label class="col-form-label col-lg-2">Corrections</label> -->
                    <div class="col-lg-12 corrections-rejected">              
                    </div>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
@stop

@section('script')
@include('templates.branch.common.partials.script')
@stop

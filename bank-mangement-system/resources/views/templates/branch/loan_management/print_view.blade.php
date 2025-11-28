@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Download Loan PDF</h3>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Loans</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table datatable-show-all">
                          <thead>
                              <tr>
                                  <th>S/N</th>
                                  <th>Print Type</th>
                                  <th>Action</th>
                              </tr>
                          </thead>  
                          <tbody>
                              <tr>
                                  <td>1</td>
                                  <td>Print Loan Form</td>
                                  <td>
                                    <a href="{{ $formPrintUrl }}" target="_blank">
                                      <i class="fa fa-print" aria-hidden="true"></i></a>
                                  </td>
                              </tr>
                          </tbody>                  
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
@include('templates.branch.loan_management.partials.script')
@stop

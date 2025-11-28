@extends('templates.admin.master')



@section('content')

<div class="content">

  <div class="row">

      <div class="col-md-12">

        <!-- Basic layout-->

        <div class="card">

          <div class="card-header header-elements-inline">

            <h3 class="mb-0">SSB Form</h3>

                <div class="header-elements">

                  <div class="list-icons">

                </div>

              </div>

          </div>

          

          <div class="card-body">

            <div class="text-right">

                <a href="{{route('admin.allrenew.updaterenewaltransaction')}}" class="btn btn-primary submit-renew-form">Submit</a>

              </div>

          </div>

        </div>

        <!-- /basic layout -->

      </div>

    </div>

</div>

@stop



@extends('templates.admin.master')



@section('content')

<div class="content">

  <div class="row">

      <div class="col-md-12">

        <!-- Basic layout-->

        <div class="card">

          <div class="card-header header-elements-inline">

            <h3 class="mb-0">Renewal Form</h3>

                <div class="header-elements">

                  <div class="list-icons">

                </div>

              </div>

          </div>

          

          <div class="card-body">

            <form action="{{route('admin.renew.updaterenewaltransaction')}}" method="post">

            @csrf


                <div class="form-group row">

                  <label class="col-form-label col-lg-2">Enter Account Numbers<sup>*</sup></label>

                  <div class="col-lg-10">

                    <input type="text" name="accountnumbers[]" id="accountnumbers[]" class="form-control" placeholder="Account Numbers" value="" autocomplete="off" required="">

                  </div>

                </div>


                <div class="text-right">

                  <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit-renew-form">

                </div>

            </form>

          </div>

        </div>

        <!-- /basic layout -->

      </div>

    </div>

</div>

@stop



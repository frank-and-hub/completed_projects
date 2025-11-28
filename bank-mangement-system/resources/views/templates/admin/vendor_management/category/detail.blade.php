@extends('templates.admin.master')

@section('content')

<div class="content"> 
  <div class="row"> 
        @if ($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif

    <div class="col-md-12">
      <div class="card">
        <div class="card-header header-elements-inline">
          <h4 class="card-title mb-3">Designation Details</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">Designation Name </label>
                <div class="col-lg-7 ">
                  {{$designation->designation_name}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">Category</label>
                <div class="col-lg-7 ">
                  @if($designation->category==1)
                    On-rolled
                  @elseif($designation->category==2)
                    Contract
                  @else
                    Default
                  @endif
                </div>
              </div>
            </div>
            <?php
            $sum=$designation->basic_salary+$designation->daily_allowances+$designation->hra+$designation->hra_metro_city+$designation->uma +$designation->convenience_charges+$designation->maintenance_allowance+$designation->communication_allowance+$designation->prd+$designation->ia+$designation->ca+$designation->fa
                ;
                $deduction=$designation->pf+$designation->tds;
                $total=$sum-$deduction;
            ?>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">Gross Salary</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$total, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">Basic Salary</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->basic_salary, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">Daily Allowances</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->daily_allowances, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">HRA</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->hra, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">HRA Metro City</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->hra_metro_city, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">UMA</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->uma, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">Convenience Charges</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->convenience_charges, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">Maintenance Allowance</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->maintenance_allowance, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">Communication Allowance</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->communication_allowance, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">PRD</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->prd, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">IA</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->ia, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">CA</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->ca, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">FA</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->fa, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">PF</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->pf, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">TDS</label>
                <div class="col-lg-7 ">
                  {{ number_format((float)$designation->tds, 2, '.', '')}}
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">Status</label>
                <div class="col-lg-7 ">
                  @if($designation->status==1)
                    Active
                  @elseif($designation->status==0)
                    Inactive
                  @else
                    Deleted
                  @endif
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-lg-5">Created At</label>
                <div class="col-lg-7 ">
                  {{date("d/m/Y h:i:s a", strtotime($designation->created_at))}}
                </div>
              </div>
            </div>



          </div>
        </div>
      </div>
    </div>

        

  </div>
</div>
@stop
@extends('admin.layout.master')

@push('plugin-styles')
<!-- Plugin css import here -->
@endpush

@section('content')
<?php
$district = \App\Models\CountryData\District::whereStatus('active')->get();
$state = \App\Models\CountryData\State::whereStatus('active')->get();
$gujarat = \App\Models\User::where('state', 'Gujarat')->count();
?>
<!-- Page content here -->
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-3 mb-md-0">Dashboard</h4>
    <button type="button" class="btn btn-primary" id="printButton">Print</button>
  </div>
  <div class="d-flex align-items-center flex-wrap text-nowrap">

  </div>
</div>

<script>
  var sidebarToggler = document.querySelector('.sidebar-toggler');

  // Remove the 'not-active' class and add the 'active' class
  if (sidebarToggler) {
    sidebarToggler.classList.remove('active');
    sidebarToggler.classList.add('not-active');
  }
  document.getElementById('printButton').addEventListener('click', function() {
    window.print();
  });
</script>
<form action="#" method="POST" id="dashboard_filter_data" class="dashboard_filter_data"> @csrf
  <!-- 1a -->
  <div class="row mb-4">
    <div class="accordion" id="accordionExample">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne"> Advanced Filters</button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"  data-bs-parent="#accordionExample">
          <div class="accordion-body">
            <!-- 2c -->
            <div class="row mb-4">
              <div class="col-lg-5 col-xl-12 grid-margin grid-margin-xl-0 stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex">
                      <div class="dropdown col mx-2">
                        <label for="category" aria-labelledby="House_Type">Category</label>
                        <select name="category" id="category" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <option class="dropdown-item" value=''>Please Select</option>
                          <option class="dropdown-item" value="general">General</option>
                          <option class="dropdown-item" value="obc c">OBC C</option>
                          <option class="dropdown-item" value="obc nc">OBC NC</option>
                          <option class="dropdown-item" value="sc">SC</option>
                          <option class="dropdown-item" value="st">ST</option>
                        </select>
                      </div>
                      <div class="dropdown col mx-2">
                        <label for="minority" aria-labelledby="House_Type">Minority</label>
                        <select name="minority" id="minority" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <option class="dropdown-item" value=''>Please Select</option>
                          <option class="dropdown-item" value="muslim">Muslims</option>
                          <option class="dropdown-item" value="sikh">Sikhs</option>
                          <option class="dropdown-item" value="christian">Christians</option>
                          <option class="dropdown-item" value="buddhist">Buddhists</option>
                          <option class="dropdown-item" value="jain">Jain</option>
                          <option class="dropdown-item" value="zorastrian">Zorastrians</option>
                        </select>
                      </div>
                      <div class="dropdown col mx-2">
                        <label for="parents_or_guardian" aria-labelledby="House_Type">Parents or Guardian</label>
                        <select name="parents_or_guardian" id="parents_or_guardian" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <option class="dropdown-item" value="">Please Select</option>
                          <option class="dropdown-item" value="Father">Father</option>
                          <option class="dropdown-item" value="Month">Mother</option>
                          <option class="dropdown-item" value="Other">Other</option>
                        </select>
                      </div>
                      <div class="dropdown col mx-2">
                        <label for="work_experence" aria-labelledby="House_Type">Work Experience</label>
                        <select name="work_experence" id="work_experence" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <option class="dropdown-item" value=''>Please Select</option>
                          <option class="dropdown-item" value="internship">Internship</option>
                          <option class="dropdown-item" value="full_time">Full Time</option>
                          <option class="dropdown-item" value="part_Time">Part Time</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- 2b -->
            <div class="row mb-4">
              <div class="col-lg-5 col-xl-12 grid-margin grid-margin-xl-0 stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex">
                      <div class="dropdown col mx-2">
                        <label for="profession" aria-labelledby="House_Type">Profession</label>
                        <select name="profession" id="profession" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <option class="dropdown-item" value=''>Please Select</option>
                          <option value="A School student" class="dropdown-item">A School student</option>
                          <option value="Pursuing Bachelors" class="dropdown-item">Pursuing Bachelors</option>
                          <option value="Pursuing masters" class="dropdown-item">Pursuing masters</option>
                          <option value="Pursuing PhD." class="dropdown-item">Pursuing PhD.</option>
                          <option value="Pursuing ITIs/Diploma/Polytechnic/Certificate course" class="dropdown-item">Pursuing ITIs/Diploma/Polytechnic/Certificate course</option>
                          <option value="Preparing for competitive exams" class="dropdown-item">Preparing for competitive exams</option>
                          <option value="Working Professional" class="dropdown-item">Working Professional</option>
                          <option value="Others" class="dropdown-item">Others</option>
                        </select>
                      </div>
                      <div class="dropdown col mx-2">
                        <label for="status" aria-labelledby="House_Type">Status</label>
                        <select name="status" id="status" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <option class="dropdown-item" value=''>Please Select</option>
                          <option class="dropdown-item" value="School Scholarships">School Scholarships</option>
                          <option class="dropdown-item" value="Bachelors Scholarships">Bachelors Scholarships</option>
                          <option class="dropdown-item" value="Master Scholarships">Master Scholarships</option>
                          <option class="dropdown-item" value="PhD. Scholarships">PhD. Scholarships</option>
                          <option class="dropdown-item" value="ITIs/Diploma/Polytechnic/Certificate Scholarships">ITIs/Diploma/Polytechnic/Certificate Scholarships</option>
                          <option class="dropdown-item" value="Competitive Exams Scholarships">Competitive Exams Scholarships</option>
                          <option class="dropdown-item" value="Exchange program scholarships">Exchange program scholarships</option>
                        </select>
                      </div>
                      <div class="dropdown col mx-2">
                        <label for="House_Type" aria-labelledby="House_Type">House Type</label>
                        <select name="House_Type" id="House_Type" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <option class="dropdown-item" value="">Select House Type</option>
                          <option class="dropdown-item" value="self_family_owned_katcha_house"> Self/Family Owned Katcha House<br> (Mud House, Tin Shed)</option>
                          <option class="dropdown-item" value="self_family_owned_pakka_house">Self/Family Owned Pakka House</option>
                          <option class="dropdown-item" value="rented_katcha_house">Rented Katcha (Mud House/Tin Shed)</option>
                          <option class="dropdown-item" value="rented_pakka_house">Rented Pakka House</option>
                        </select>
                      </div>
                      <div class="dropdown col mx-2">
                        <label for="state" aria-labelledby="state">State</label>
                        <select name="state" id="state" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <option class="dropdown-item" value="0">Select State</option>
                          @foreach($state as $s)
                          <option value="{{$s->name}}" class="dropdown-item" data-val="{{$s->id}}">{{$s->name}}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- 2a -->
              <div class="row mb-4">
                <div class="col-lg-5 col-xl-12 grid-margin grid-margin-xl-0 stretch-card">
                  <div class="card">
                    <div class="card-body">
                      <div class="d-flex">
                        <div class="dropdown col mx-2">
                          <label for="districts" aria-labelledby="districts">District</label>
                          <select name="districts" id="districts" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <option class="dropdown-item" value="0">Select District</option>
                            @foreach($district as $s)
                            <option value="{{$s->name}}" class="dropdown-item" data-state="{{$s->state_id}}">{{$s->name}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="dropdown col mx-2">
                          <label for="start_date" aria-labelledby="start_date">Start Date</label>
                          <input type="date" name="start_date" class=" w-100 my-marginblockauto btn btn-secondary filtter" id="start_date"></input>
                        </div>
                        <div class="dropdown col mx-2">
                          <label for="end_date" aria-labelledby="end_date">End Date</label>
                          <input type="date" name="end_date" class=" w-100 my-marginblockauto btn btn-secondary filtter" id="end_date"></input>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- 2 -->

  <!-- 2a -->
  <?php
  $company = \App\Models\User::whereRoleId(3)->pluck('company_name', 'id');
  ?>
  <div class="row mb-4">
    <div class="col-lg-5 col-xl-12 grid-margin grid-margin-xl-0 stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex">
            <div class="dropdown col mx-2">
              <label for="sponsor_name" aria-labelledby="districts">Sponsor Name</label>
              <select name="sponsor_name" id="sponsor_name" required class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                @if(auth()->user()->role_id != 3)
                <option class="dropdown-item" value="0">Select Sponsor Name</option>
                @foreach($company as $k => $v)
                <option value="{{$k}}" class="dropdown-item" data-val="{{$k}}">{{ucwords($v)}}</option>
                @endforeach
                @else
                @foreach($company as $k => $v)
                <option value="{{$k}}" class="dropdown-item" {{($k==auth()->user()->id) ? 'selected' : 'disabled'}} data-val="{{$k}}">{{ucwords($v)}}</option>
                @endforeach
                @endif
              </select>
            </div>
            <div class="dropdown col mx-2">
              <label for="scholarship" aria-labelledby="districts">Scholarship Name</label>
              <select name="scholarship" id="scholarship_by_name" class="col mx-2 form-select select-box-global filtter" required style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <option class="dropdown-item" value="0">Select Sponsor Name</option>
                @if(auth()->user()->role_id == 3)
                @foreach(\App\Models\Scholarship\Scholarship::where('company_id',auth()->user()->id)->pluck('scholarship_name','id') as $k => $v)
                <option value="{{$k}}" class="" data-val="{{$k}}" class="dropdown-item">{{ucwords($v)}}</option>
                @endforeach
                @endif
              </select>
            </div>
            <div class="dropdown col mx-2">
              <label for="gender" aria-labelledby="districts">Gender</label>
              <select name="gender" id="gender" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <option class="dropdown-item" value="0">Select Gender</option>
                <option class="dropdown-item" value='0'>All</option>
                <option class="dropdown-item" value="male">Male</option>
                <option class="dropdown-item" value="female">Female</option>
                <option class="dropdown-item" value="others">Others</option>
              </select>
            </div>

            <div class="dropdown col mx">
              <div class="inutrange row mt-2">
                <label for="slider-range" class="col-md-12">Age Range</label><br>
                <input type="range" class="col-md-10 mx-2 px-2 pt-1 filtter " name="age_range" id="slider-range-input">
                <input class="w-25 filtter" type="text" name="age_range_display" id="slider-range-display" placeholder="50" readonly disabled>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- 2d -->
  <div class="row mb-4">
    <div class="col-lg-5 col-xl-12 grid-margin grid-margin-xl-0 stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex">
            <div class="dropdown col mx-2">
              <label for="dropdownMenuButtonCumulative" aria-labelledby="dropdownMenuButtonCumulative">Cumulative</label>
              <select name="cumulative" id="dropdownMenuButtonCumulative" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <option class="dropdown-item" value="0">Select Cumulative</option>
                <option class="dropdown-item" value='0'>All</option>
              </select>
            </div>
            <div class="dropdown col mx-2">
              <label for="dropdownMenuButtonDate" aria-labelledby="dropdownMenuButtonDate">Date</label>
              <select name="date" id="dropdownMenuButtonDate" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <option class="dropdown-item" value="0">Select Date</option>
                @for($m=1;$m <= 31;$m++)
                  <option class="dropdown-item" value="{{$m}}">{{$m}}</option>
                  @endfor
              </select>
            </div>
            <div class="dropdown col mx-2">
              <label for="dropdownMenuButtonMonth" aria-labelledby="dropdownMenuButtonMonth">Month</label>
              <select name="month" id="dropdownMenuButtonMonth" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <option class="dropdown-item" value="0">Select Month</option>
                <option class="dropdown-item" value="1">January</option>
                <option class="dropdown-item" value="2">February</option>
                <option class="dropdown-item" value="3">March</option>
                <option class="dropdown-item" value="4">April</option>
                <option class="dropdown-item" value="5">May</option>
                <option class="dropdown-item" value="6">June</option>
                <option class="dropdown-item" value="7">July</option>
                <option class="dropdown-item" value="8">August</option>
                <option class="dropdown-item" value="9">September</option>
                <option class="dropdown-item" value="10">October</option>
                <option class="dropdown-item" value="11">November</option>
                <option class="dropdown-item" value="12">December</option>
              </select>
            </div>
            <div class="dropdown col mx">
              <label for="dropdownMenuButtonYear" aria-labelledby="dropdownMenuButtonYear">Year</label>
              <select name="year" id="dropdownMenuButtonYear" class="col mx-2 form-select select-box-global filtter" style="width: 100%; display: flex; justify-content: space-between;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <option class="dropdown-item" value="0">Select Year</option>
                @for($i = 2011; $i <= 2024 ;$i ++) <option class="dropdown-item" value="{{$i}}">{{$i}}</option> @endfor
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row mb-4">
    <button type="submit" class="btn btn-primary w-25">Submit</button>
  </div>
</form>
<!-- css -->
<style>
  #dropdownMenuButton::after {
    margin-block: auto;
  }

  .settings-sidebar-toggler {
    display: none;
  }

  .statedrop.show {
    height: 300px;
    overflow: auto;
  }
</style>


@push('plugin-scripts')
<!-- Plugin js import here -->
<script src="{{ asset('admin/assets/plugins/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('admin/assets/plugins/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('admin/assets/plugins/chartjs/chart.umd.js') }}"></script>
@endpush

<!-- 3 -->
<div id="dashboardfilterdata">

</div>


@push('custom-scripts')

<script>
  'use strict'
  $(function() {
    rendergendergeo();
    rendersocialgroup();
    renderothersocialgroup();
    renderapexScatter();
    renderfamilyincome();
    rendersessionattended();
    renderresourseAvlandDown();
    renderqueriesChart();
    renderaverageageofmeetups();
    renderAnalyticsChart();
    rendercompappprogression();
    rendersessiooutnmatrix();
  });
  var colors = {
    primary: "#6571ff",
    secondary: "#7987a1",
    success: "#05a34a",
    info: "#66d1d1",
    warning: "#fbbc06",
    danger: "#ff3366",
    light: "#e9ecef",
    dark: "#060c17",
    muted: "#7987a1",
    gridBorder: "rgba(77, 138, 240, .15)",
    bodyColor: "#b8c3d9",
    cardBg: "#0c1427"
  }
  var fontFamily = "'Roboto', Helvetica, sans-serif"


  var colors = {
    primary: "#6571ff",
    secondary: "#7987a1",
    success: "#05a34a",
    info: "#66d1d1",
    warning: "#fbbc06",
    danger: "#ff3366",
    light: "#e9ecef",
    dark: "#060c17",
    muted: "#7987a1",
    gridBorder: "rgba(77, 138, 240, .15)",
    bodyColor: "#b8c3d9",
    cardBg: "#0c1427"
  }
  var fontFamily = "'Roboto', Helvetica, sans-serif"


  function rendergendergeo() {
    // gendergeo chart end
    const gender_other = JSON.parse(document.getElementById('gender_geo_value_other').value);
    const gender_female = JSON.parse(document.getElementById('gender_geo_value_female').value);
    const gender_male = JSON.parse(document.getElementById('gender_geo_value_male').value);
    if ($('#gendergeo').length) {
      var options = {
        chart: {
          height: 500,
          type: "pie",
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        colors: [colors.primary, colors.warning, colors.danger, colors.info],
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 8,
            vertical: 0
          },
        },
        stroke: {
          colors: ['rgba(0,0,0,0)']
        },
        dataLabels: {
          enabled: false
        },
        series: [gender_other, gender_male, gender_female],
        labels: ["Others", "Male", "Female"],

      };

      var chart = new ApexCharts(document.querySelector("#gendergeo"), options);
      chart.render();
    }
  }
  // gendergeo chart end




  $(document).ready(function() {
    // Assuming you have jQuery included

    // Event handler for slider range change
    $("#slider-range-input").on("input", function() {
      // Update the value in the display input
      $("#slider-range-display").val($(this).val());
    });
  });



  function rendersocialgroup() {
    // socialgroup chart end
    const social_sc = JSON.parse(document.getElementById('social_sc').value);
    const social_st = JSON.parse(document.getElementById('social_st').value);
    const social_obc = JSON.parse(document.getElementById('social_obc').value);
    const social_gen = JSON.parse(document.getElementById('social_gen').value);
    if ($('#socialgroup').length) {
      var options = {
        chart: {
          height: 300,
          type: "pie",
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        colors: [colors.primary, colors.warning, colors.danger, colors.info],
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 8,
            vertical: 0
          },
        },
        stroke: {
          colors: ['rgba(0,0,0,0)']
        },
        dataLabels: {
          enabled: false
        },
        // series: [20, 20, 40, 80],
        series: [social_sc, social_st, social_obc, social_gen],
        // labels: ["SC " + social_sc + "%", "ST " + social_st + "%", "OBC " + social_obc + "%", "GENERAL " + social_gen + "%"],
        labels: ["SC", "ST", "OBC", "GENERAL"],

      };

      var chart = new ApexCharts(document.querySelector("#socialgroup"), options);
      chart.render();
    }
  }
  // socialgroup chart end

  function renderothersocialgroup() {
    // gendergeo chart end
    const minority_muslim = JSON.parse(document.getElementById('minority_muslim').value);
    const minority_jain = JSON.parse(document.getElementById('minority_jain').value);
    const minority_sikh = JSON.parse(document.getElementById('minority_sikh').value);
    const minority_buddhist = JSON.parse(document.getElementById('minority_buddhist').value);
    const minority_christian = JSON.parse(document.getElementById('minority_christian').value);
    const minority_zoroastrians = JSON.parse(document.getElementById('minority_zoroastrians').value);
    if ($('#othersocialgroup').length) {
      var options = {
        chart: {
          height: 300,
          type: "pie",
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        colors: [colors.primary, colors.warning, colors.bodyColor, colors.info, colors.dark],
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 8,
            vertical: 0
          },
        },
        stroke: {
          colors: ['rgba(0,0,0,0)']
        },
        dataLabels: {
          enabled: false
        },
        // series: [100, 200, 250, 350, 150],
        series: [minority_muslim, minority_sikh, minority_christian, minority_buddhist, minority_jain, minority_zoroastrians],
        // labels: ["Muslims " + minority_muslim + "%", "Sikhs " + minority_sikh + "%", "Christians " + minority_christian + "%", "Buddhists " + minority_buddhist + "%", "Jain " + minority_jain + "%", "Zoroastrians " + minority_zoroastrians + "%"],
        labels: ["Muslims", "Sikhs", "Christians", "Buddhists", "Jain", "Zoroastrians"],
      };

      var chart = new ApexCharts(document.querySelector("#othersocialgroup"), options);
      chart.render();
    }
  }
  // gendergeo chart end

  function renderapexScatter() {
    // Apex Scatter chart start
    const apexScatter_hidden_array = JSON.parse(document.getElementById('apexScatter_hidden_array').value);
    console.log(apexScatter_hidden_array);
    if ($('#apexScatter').length) {
      var options = {
        chart: {
          height: 300,
          type: 'scatter',
          parentHeightOffset: 0,
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        // yaxis: {
        //   title: {
        //     text: 'No of scholars',
        //     style:{
        //       size: 9,
        //       color: colors.muted
        //     }
        //   },
        // },
        colors: [colors.primary, colors.warning, colors.danger],
        grid: {
          borderColor: colors.gridBorder,
          padding: {
            bottom: -4
          },
          xaxis: {
            lines: {
              show: true
            }
          }
        },
        markers: {
          strokeColor: colors.cardBg,
          hover: {
            strokeColor: colors.cardBg

          }
        },
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 20,
            vertical: 0
          },
        },
        series: [
          //   {
          //   name: "Sample A",
          //   data: [
          //   [16.4, 5.4], [21.7, 2], [25.4, 3], [19, 2], [10.9, 1], [13.6, 3.2], [10.9, 7.4], [10.9, 0], [10.9, 8.2], [16.4, 0], [16.4, 1.8], [13.6, 0.3], [13.6, 0], [29.9, 0], [27.1, 2.3], [16.4, 0], [13.6, 3.7], [10.9, 5.2], [16.4, 6.5], [10.9, 0], [24.5, 7.1], [10.9, 0], [8.1, 4.7], [19, 0], [21.7, 1.8], [27.1, 0], [24.5, 0], [27.1, 0], [29.9, 1.5], [27.1, 0.8], [22.1, 2]]
          // },{
          //   name: "Sample B",
          //   data: [
          //   [36.4, 13.4], [1.7, 11], [5.4, 8], [9, 17], [1.9, 4], [3.6, 12.2], [1.9, 14.4], [1.9, 9], [1.9, 13.2], [1.4, 7], [6.4, 8.8], [3.6, 4.3], [1.6, 10], [9.9, 2], [7.1, 15], [1.4, 0], [3.6, 13.7], [1.9, 15.2], [6.4, 16.5], [0.9, 10], [4.5, 17.1], [10.9, 10], [0.1, 14.7], [9, 10], [12.7, 11.8], [2.1, 10], [2.5, 10], [27.1, 10], [2.9, 11.5], [7.1, 10.8], [2.1, 12]]
          // },
          {
            name: "Age",
            data: apexScatter_hidden_array
          }
        ],
        xaxis: {
          axisBorder: {
            color: colors.gridBorder,
          },
          axisTicks: {
            color: colors.gridBorder,
          },
          // tickAmount: 10,
          labels: {
            formatter: function(val) {
              return parseFloat(val).toFixed(1)
            }
          }
        },
        yaxis: {
          tickAmount: 10,
          title: {
            text: 'No of scholars',
          }
        }
      }

      var chart = new ApexCharts(
        document.querySelector("#apexScatter"),
        options
      );
      chart.render();
    }
  }
  // Apex Scatter chart end

  function renderfamilyincome() {
    // Apex Bar chart start
    if ($('#familyincome').length) {
      const family = JSON.parse(document.getElementById('family_income_array').value);
      var options = {
        chart: {
          type: 'bar',
          height: '350',
          parentHeightOffset: 0,
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        colors: [colors.primary],
        grid: {
          padding: {
            bottom: -4
          },
          borderColor: colors.gridBorder,
          xaxis: {
            lines: {
              show: true
            }
          }
        },
        series: [{
          name: 'No of scholars',
          data: family
        }],
        yaxis: {
          title: {
            text: 'No of scholars',
            style: {
              size: 9,
              color: colors.muted
            }
          },
          tickAmount: 4,
          tooltip: {
            enabled: true
          },
          crosshairs: {
            stroke: {
              color: colors.secondary,
            },
          },
        },
        xaxis: {
          type: 'Income',
          categories: ['2L', '2-5L', '5-10L', '10L and Above'],
          axisBorder: {
            color: colors.gridBorder,
          },
          axisTicks: {
            color: colors.gridBorder,
          },
        },
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 8,
            vertical: 0
          },
        },
        stroke: {
          width: 0
        },
        plotOptions: {
          bar: {
            borderRadius: 4
          }
        }
      }

      var familyincomeChart = new ApexCharts(document.querySelector("#familyincome"), options);
      familyincomeChart.render();
    }
  }
  // Apex Bar chart end

  function rendersessionattended() {
    // Queries chart start
    if ($('#sessionattended').length) {
      var options = {
        chart: {
          type: "area",
          height: 300,
          parentHeightOffset: 0,
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
          stacked: true,
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        colors: [colors.danger, colors.info],
        stroke: {
          curve: "smooth",
          width: 3
        },
        dataLabels: {
          enabled: false
        },
        series: [{
          name: 'SCHOLARS ATTENDED',
          data: generateDayWiseTimeSeries(0, 18)
        }, {
          name: 'TOTAL SESSIONS HELD',
          data: generateDayWiseTimeSeries(1, 18)
        }],
        // markers: {
        //   size: 5,
        //   strokeWidth: 3,
        //   hover: {
        //     size: 7
        //   }
        // },
        xaxis: {
          type: "datetime",
          axisBorder: {
            color: colors.gridBorder,
          },
          axisTicks: {
            color: colors.gridBorder,
          },
        },
        yaxis: {
          title: {
            text: 'SESSION',
          },
          tickAmount: 4,
          min: 0,
          labels: {
            // offsetX: -6,
          },
          tooltip: {
            enabled: true
          }
        },
        grid: {
          padding: {
            bottom: -4
          },
          borderColor: colors.gridBorder,
          xaxis: {
            lines: {
              show: true
            }
          }
        },
        tooltip: {
          x: {
            format: "dd MMM yyyy"
          },
        },
        fill: {
          type: 'solid',
          opacity: [0.4, 0.25],
        },
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 8,
            vertical: 0
          },
        },
      };

      var chart = new ApexCharts(document.querySelector("#sessionattended"), options);
      chart.render();

      function generateDayWiseTimeSeries(s, count) {
        var values = [
          [4, 3, 10, 9, 29, 19, 25, 9, 12, 7, 19, 5, 13, 9, 17, 2, 7, 5],
          [2, 3, 8, 7, 22, 16, 23, 7, 11, 5, 12, 5, 10, 4, 15, 2, 6, 2]
        ];
        var i = 0;
        var series = [];
        var x = new Date("11 Nov 2012").getTime();
        while (i < count) {
          series.push([x, values[s][i]]);
          x += 86400000;
          i++;
        }
        return series;
      }
    }
  }
  // Apex Area chart end

  function renderresourseAvlandDown() {
    // Grouped Bar Chart
    if ($('#resourseAvlandDown').length) {
      new Chart($('#resourseAvlandDown'), {
        type: 'bar',
        data: {
          labels: ["2020", "2021", "2022", "2023"],
          datasets: [{
            label: "RESOURSES AVAILABLE",
            backgroundColor: colors.primary,
            data: [408, 547, 675, 734]
          }, {
            label: "RESOURSES DOWNLOADED",
            backgroundColor: colors.danger,
            data: [133, 221, 483, 478]
          }]
        },
        options: {
          plugins: {
            legend: {
              display: true,
              labels: {
                color: colors.bodyColor,
                font: {
                  size: '13px',
                  family: fontFamily
                }
              }
            },
          },
          scales: {
            x: {
              display: true,
              title: {
                display: true,
                text: "Years"
              },
              grid: {
                display: true,
                color: colors.gridBorder,
                borderColor: colors.gridBorder,
              },
              ticks: {
                color: colors.bodyColor,
                font: {
                  size: 12
                }
              }
            },
            y: {
              display: true,
              title: {
                display: true,
                text: "Resources"
              },
              grid: {
                display: true,
                color: colors.gridBorder,
                borderColor: colors.gridBorder,
              },
              ticks: {
                color: colors.bodyColor,
                font: {
                  size: 12
                }
              }
            }
          }
        }
      });
    }
  }

  function renderqueriesChart() {
    // Queries chart start
    if ($('#queriesChart').length) {
      var options = {
        chart: {
          type: "area",
          height: 300,
          parentHeightOffset: 0,
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
          stacked: true,
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        colors: [colors.danger, colors.info],
        stroke: {
          curve: "smooth",
          width: 3
        },
        dataLabels: {
          enabled: false
        },
        series: [{
          name: 'Queries Resolved',
          data: generateDayWiseTimeSeries(0, 18)
        }, {
          name: 'Total Queries Raised',
          data: generateDayWiseTimeSeries(1, 18)
        }],
        // markers: {
        //   size: 5,
        //   strokeWidth: 3,
        //   hover: {
        //     size: 7
        //   }
        // },
        xaxis: {
          type: "datetime",
          axisBorder: {
            color: colors.gridBorder,
          },
          axisTicks: {
            color: colors.gridBorder,
          },
        },
        yaxis: {
          title: {
            text: 'Queries',
          },
          tickAmount: 4,
          min: 0,
          labels: {
            // offsetX: -6,
          },
          tooltip: {
            enabled: true
          }
        },
        grid: {
          padding: {
            bottom: -4
          },
          borderColor: colors.gridBorder,
          xaxis: {
            lines: {
              show: true
            }
          }
        },
        tooltip: {
          x: {
            format: "dd MMM yyyy"
          },
        },
        fill: {
          type: 'solid',
          opacity: [0.4, 0.25],
        },
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 8,
            vertical: 0
          },
        },
      };

      var chart = new ApexCharts(document.querySelector("#queriesChart"), options);
      chart.render();

      function generateDayWiseTimeSeries(s, count) {
        var values = [
          [4, 3, 10, 9, 29, 19, 25, 9, 12, 7, 19, 5, 13, 9, 17, 2, 7, 5],
          [2, 3, 8, 7, 22, 16, 23, 7, 11, 5, 12, 5, 10, 4, 15, 2, 6, 2]
        ];
        var i = 0;
        var series = [];
        var x = new Date("11 Nov 2012").getTime();
        while (i < count) {
          series.push([x, values[s][i]]);
          x += 86400000;
          i++;
        }
        return series;
      }
    }
  }
  // Apex Area chart end

  function renderaverageageofmeetups() {
    // Average Age Line chart start
    if ($('#averageageofmeetups').length) {
      var lineChartOptions = {
        chart: {
          type: "line",
          height: '320',
          parentHeightOffset: 0,
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        colors: [colors.danger, colors.primary, colors.warning],
        grid: {
          padding: {
            bottom: -4
          },
          borderColor: colors.gridBorder,
          xaxis: {
            lines: {
              show: true
            }
          }
        },
        series: [{
          name: "Total No of Meetups",
          data: [45, 52, 38, 45]
        }, {
          name: "Participation Rate",
          data: [40, 48, 28, 40]
        }],
        yaxis: {
          title: {
            text: 'SESSION',
          },
        },
        xaxis: {
          type: "date",
          categories: ["20 Dec", "21 Dec", "22 Dec", "23 Dec"],
          lines: {
            show: true
          },
          axisBorder: {
            color: colors.gridBorder,
          },
          axisTicks: {
            color: colors.gridBorder,
          },
        },
        markers: {
          size: 0,
        },
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 8,
            vertical: 0
          },
        },
        stroke: {
          width: 3,
          curve: "smooth",
          lineCap: "round"
        },
      };
      var averageAgeScholarsChart = new ApexCharts(document.querySelector("#averageageofmeetups"), lineChartOptions);
      averageAgeScholarsChart.render();
    }
  }
  // Average Age Line chart 

  function rendercompappprogression() {
    // COMPARISION OF APPLICANT PROGRESSION

    const compappprogression_hidden_array = JSON.parse(document.getElementById('compappprogression_hidden_array').value);
    const compappprogression_hidden_array_percentage = JSON.parse(document.getElementById('compappprogression_hidden_array_percentage').value);

    if ($('#compappprogression').length) {
      var options = {
        chart: {
          height: 300,
          type: "pie",
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        colors: [colors.primary, colors.warning, colors.danger, colors.info, colors.gridBorder, colors.light, colors.secondary, colors.success],
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 8,
            vertical: 0
          },
        },
        stroke: {
          colors: ['rgba(0,0,0,0)']
        },
        dataLabels: {
          enabled: false
        },
        //   series: [100, 100, 100, 100, 100, 100, 100, 100],
        series: compappprogression_hidden_array_percentage,
        //   labels: ["Applied 20%", "Found eligible 10%", "Appeared 15%", "Registered 15%", "Qualified 10%", "Onboarded 10%", "Onto the platform 10%", "Dropped 10%"],
        labels: compappprogression_hidden_array,

      };

      var chart = new ApexCharts(document.querySelector("#compappprogression"), options);
      chart.render();
    }
  }
  // COMPARISION OF APPLICANT PROGRESSION chart end

  function rendersessiooutnmatrix() {
    // Session Outreach Matrix start
    if ($('#sessiooutnmatrix').length) {
      var options = {
        chart: {
          type: 'bar',
          height: '250',
          parentHeightOffset: 0,
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        colors: [colors.primary],
        grid: {
          padding: {
            bottom: -4
          },
          borderColor: colors.gridBorder,
          xaxis: {
            lines: {
              show: true
            }
          }
        },
        series: [{
          name: 'Sessions Conducted',
          data: [1, 3, 6, 12]
        }],
        yaxis: {
          title: {
            text: 'Sessions Conducted',
            style: {
              size: 9,
              color: colors.muted
            }
          },
          tickAmount: 4,
          tooltip: {
            enabled: true
          },
          crosshairs: {
            stroke: {
              color: colors.secondary,
            },
          },
        },
        xaxis: {
          type: 'Date',
          categories: ['Sep 2023', 'Oct 2023', 'Nov 2023', 'Dec 2023'],
          axisBorder: {
            color: colors.gridBorder,
          },
          axisTicks: {
            color: colors.gridBorder,
          },
        },
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 8,
            vertical: 0
          },
        },
        stroke: {
          width: 0
        },
        plotOptions: {
          bar: {
            borderRadius: 4
          }
        }
      }

      var sessiooutnmatrixChart = new ApexCharts(document.querySelector("#sessiooutnmatrix"), options);
      sessiooutnmatrixChart.render();
    }
  }
  // Apex Bar chart end

  // analyticschart chart start
  function renderAnalyticsChart() {
    if ($('#analyticschart').length) {
      var lineChartOptions = {
        chart: {
          type: "line",
          height: '320',
          parentHeightOffset: 0,
          foreColor: colors.bodyColor,
          background: colors.cardBg,
          toolbar: {
            show: false
          },
        },
        theme: {
          mode: 'dark'
        },
        tooltip: {
          theme: 'dark'
        },
        colors: [colors.danger, colors.primary, colors.warning],
        grid: {
          padding: {
            bottom: -4
          },
          borderColor: colors.gridBorder,
          xaxis: {
            lines: {
              show: true
            }
          }
        },
        series: [{
          name: "Average Time Spent on the Platform",
          data: [45, 52, 38, 45]
        }, {
          name: "Average Footfall on the Platform",
          data: [40, 42, 28, 40]
        }, {
          name: "Average Footfall on the Platform",
          data: [35, 32, 18, 35]
        }, {
          name: "Top-Viewed Resources",
          data: [30, 22, 8, 30]
        }],
        yaxis: {
          title: {
            text: 'Numbers',
          },
        },
        xaxis: {
          type: "date",
          categories: ["Oct 2023", "Nov 2023", "Dec 2023", "Jan 2024"],
          lines: {
            show: true
          },
          axisBorder: {
            color: colors.gridBorder,
          },
          axisTicks: {
            color: colors.gridBorder,
          },
        },
        markers: {
          size: 0,
        },
        legend: {
          show: true,
          position: "top",
          horizontalAlign: 'center',
          fontFamily: fontFamily,
          itemMargin: {
            horizontal: 8,
            vertical: 0
          },
        },
        stroke: {
          width: 3,
          curve: "smooth",
          lineCap: "round"
        },
      };
      var averageAgeScholarsChart = new ApexCharts(document.querySelector("#analyticschart"), lineChartOptions);
      averageAgeScholarsChart.render();
    }
  }

  // analyticschart chart 

  // document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('dashboard_filter_data');

  form.addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form submission

    var sponsorName = form.elements['sponsor_name'].value;
    var scholarship = form.elements['scholarship'].value;

    // Validation logic
    if (!sponsorName || !scholarship) {
      // Display error messages or handle validation failures
      document.getElementById('dashboardfilterdata').innerHTML = 'Please fill in all fields.';
    } else {
      // Form is valid, perform further actions
      applyfilter(); // Call your function when the form is valid
    }
  });
  // });


  /*
  var filterElements = document.getElementsByClassName('filtter');

  for (var i = 0; i < filterElements.length; i++) {
    filterElements[i].addEventListener('change', applyfilter);
    filterElements[i].addEventListener('keyup', applyfilter);
    filterElements[i].addEventListener('select', applyfilter);
    filterElements[i].addEventListener('keydown', applyfilter);
  }
  */
  applyfilter();

  function applyfilter() {
    var formData = new FormData(document.getElementById('dashboard_filter_data'));

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          var response = xhr.responseText;
          var res = JSON.parse(response);
          if (res.msg_type == 'success') {
            document.getElementById('dashboardfilterdata').innerHTML = '';
            document.getElementById('dashboardfilterdata').innerHTML = res.view;
          }
          rendergendergeo();
          rendersocialgroup();
          renderothersocialgroup();
          renderapexScatter();
          renderfamilyincome();
          rendersessionattended();
          renderresourseAvlandDown();
          renderqueriesChart();
          renderaverageageofmeetups();
          renderAnalyticsChart();
          rendercompappprogression();
          rendersessiooutnmatrix();
        } else {
          console.error('Error occurred:', xhr.status);
        }
      }
    };

    xhr.open('POST', '{{ route("admin.dashboard.post") }}', true);
    xhr.send(formData);
  }

  document.getElementById('start_date').addEventListener('change', function() {
    var joiningDate = new Date(this.value);
    document.getElementById('end_date').min = this.value;

    var endDate = new Date(document.getElementById('end_date').value);
    if (endDate < joiningDate) {
      document.getElementById('end_date').value = '';
    }
  });

  document.getElementById('end_date').addEventListener('change', function() {
    var joiningDate = new Date(this.value);
    var endDate = new Date(document.getElementById('start_date').value);

    if (endDate > joiningDate) {
      this.value = ''; // Clear the incorrect value
    }
  });
  /*
  $(document).ready(function(){
       $('#state').on('change', function (e) {
          e.preventDefault();
          var state_id = $(this).val();
          var token = document.head.querySelector('meta[name="_token"]').content;
          
          $.post("{{route('Student.district')}}", { stateId: state_id ,_token:token})
              .done(function (response) {
                  $('#districts').find('option').remove();
                  $('#districts').append('<option value="">Select Districts</option>');
                  $.each(response, function (index, value) {
                      $("#districts").append("<option value='" + value.name + "' data-val='"+value.id+"'>" + value.name + "</option>");
                  });
              })
              .fail(function (xhr, status, error) {
                  // Handle failed AJAX request
                  console.error('Error occurred:', error);
              });
      });
  });
  */
  $(document).on('change', '#state', function() {
    const state = $('#state option:selected').data('val');
    console.log(state);
    var options = $('#districts option');

    options.each(function() {
      if ($(this).data('state') === state) {
        $(this).css('display', 'block');
      } else {
        $(this).css('display', 'none');
      }
    });
  });

  document.getElementById('sponsor_name').addEventListener('change', function() {
    var csrfToken = document.head.querySelector('meta[name="_token"]').content;
    var companyId = this.value;
    var scholarship_by_name = document.getElementById('scholarship_by_name');
    scholarship_by_name.textContent = '';
    if (companyId == 0) {
      var o = document.createElement('option');
      o.value = 0;
      o.textContent = "Select Sponsor Name";
      o.classList.add('dropdown-item');
      o.setAttribute('data-val', 0);
      scholarship_by_name.appendChild(o);
      return false;
    }
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          var response = JSON.parse(xhr.responseText);
          if (Array.isArray(response) && response.length === 0) {
            var l = document.createElement('option');
            l.value = 0;
            l.textContent = "Select Sponsor Name";
            l.classList.add('dropdown-item');
            l.setAttribute('data-val', 0);
            scholarship_by_name.appendChild(l);
            return false;
          } else {
            response.forEach(function(value, i) {
              var option = document.createElement('option');
              option.value = value.id;
              option.textContent = value.scholarship_name.toUpperCase();
              option.classList.add('dropdown-item');
              option.setAttribute('data-val', value.id);
              scholarship_by_name.appendChild(option);
            });
          }
        } else {
          console.error('Error occurred:', xhr.status);
        }
      }
    };

    xhr.open('POST', "{{ route('admin.company_id.scholarship') }}", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    xhr.send('companyId=' + encodeURIComponent(companyId));
  });
</script>
@endpush
<script src="{{ asset('admin/assets/js/blackpage.js') }}"></script>

@endsection
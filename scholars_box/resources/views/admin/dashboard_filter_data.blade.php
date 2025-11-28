<div class="row mb-4">
  <div class="d-flex col-md-6">
    <div class="col me-3 card text-white">
      <div class="card-header" style="text-transform: uppercase;">Total No. Of Scholars Applied</div>
      <div class="card-body">
        <h5 class="card-title fs-1">{{$totalNoOfScholersApplied??0}}</h5>
        <p class="card-text"></p>
      </div>
    </div>
    <div class="col card text-white">
      <div class="card-header" style="text-transform: uppercase;">Total No. Of Students Benefited</div>
      <div class="card-body">
        <h5 class="card-title fs-1">{{$totalNoOfStudentsBenefited??0}}</h5>
        <p class="card-text"></p>
      </div>
    </div>
  </div>
  <div class="d-flex col-md-6">
    <div class="col me-3 card text-white">
      <div class="card-header" style="text-transform: uppercase;">Amount Disbursed</div>
      <div class="card-body">
        <h5 class="card-title fs-1">₹ {{number_format($amountDistribution,2,'.')}}</h5>
        <p class="card-text"></p>
      </div>
    </div>
    <div class="col card text-white">
      <div class="card-header" style="text-transform: uppercase;">% Of Amount Disbursed</div>
      <div class="card-body">
        <h5 class="card-title fs-1 {{$distributionamountpercentage}} {{$totalNoOfScholersApplied}}">{{number_format(calculatePercentage($distributionamountpercentage,$totalNoOfScholersApplied),2,'.')}} %</h5>
        <p class="card-text"></p>
      </div>
    </div>
  </div>
</div>
<div class="row mb-4">

  <div class="col-lg-7 col-xl-6 stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-2">
          <h6 class="card-title mb-0" style="text-transform: uppercase;">Scholarships</h6>
          <div class="dropdown mb-2">
            <button class="btn btn-link p-0" type="button" id="dropdownMenuButton7" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton7">
              <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-feather="download" class="icon-sm me-2"></i> <span class="">Download</span></a>
            </div>
          </div>
        </div>
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;" >
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th class="pt-0">#</th>
                <th class="pt-0" style="text-transform: uppercase;">Name Of Scholarship</th>
                <th class="pt-0">Scholarship Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($scholarshipTable as $key => $value)
              <tr>
                <td>{{$key + 1}}</td>
                <td>{{ucwords($value->scholarship_name)}}</td>
                <td>{{ucwords(($value->status == 1) ? 'Active' : (($value->status == 0) ? 'Inactive' : 'Panding' ) )}}</td>
              </tr>
              @empty
              <tr>
                <td>No</td>
                <td>Scholarship</td>
                <td>Created</td>
                <td>yet !</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex col">
    <div class="col-12 me-3 card text-white">
      <div class="card-header" style="text-transform: uppercase;">Average Rate Of Success</div>
      <div class="card-body">
        <h5 class="card-title fs-1 text-center  {{$totalNoOfStudentsBenefited}} {{$totalNoOfScholersApplied}} ">{{number_format(calculatePercentage($totalNoOfStudentsBenefited??0,$totalNoOfScholersApplied),2,'.')}} %</h5>
        <p class="card-text"></p>
      </div>
    </div>
    <div class="col"></div>
  </div>
</div>

<!-- 4 -->
<div class="row mb-4">
  <div class="col-lg-7 col-xl-6 stretch-card">

    <iframe src="https://scholarsbox.in/admin/example" id="app" width="100%" height="500" frameborder="0" scrolling="no"></iframe>
    <!-- <img class="w-100" src="{{asset('admin/assets/images/others/karnataka3.png')}}" alt="" srcset=""> -->
  </div>
  <div class="col-xl-6 grid-margin stretch-card mb-0">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-4 mb-md-3">
          <h6 class="card-title mb-0">GENDER DEMOGRAPHICS</h6>
          <div class="dropdown"></div>
        </div>
        <div id="gendergeo"></div>
        <div class="text-center">
          <h5 class="text-center">Total: {{$totalgendercount}}</h5>
        </div>
      </div>
    </div>
  </div>
</div><input type="hidden" id="gender_geo_value_female" class="" value="{{$genderFemale}}" />
</div><input type="hidden" id="gender_geo_value_male" class="" value="{{$genderMale}}" />
</div><input type="hidden" id="gender_geo_value_other" class="" value="{{$genderOther}}" />
<!-- 5 -->
<div class="row mb-4">
  <div class="col-xl-6 grid-margin grid-margin-xl-0 stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-4 mb-md-3">
          <h6 class="card-title">AVERAGE AGE OF SCHOLARS</h6>
          <div class="dropdown"></div>
        </div><input id="apexScatter_hidden_array" value="{{$apexScatter_hidden_array}}" type="hidden" />
        <div id="apexScatter"></div>
        <div class="text-center mt-3">

          X axis - Age &nbsp;&nbsp;
          Y axis - Number Student Count's

        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-6 stretch-card">
    <div class="card">
      <div class="card-body">
        <!-- Number of Successful Scholars -->
        <div class="d-flex justify-content-between align-items-baseline mb-4 mb-md-3">
          <h6 class="card-title mb-0">FAMILY INCOME</h6>
          <div class="dropdown"></div>
        </div>
        <div class="" id="familyincome"></div>
      </div>
    </div>
  </div>
</div>
<input type="hidden" id="family_income_array" value="[
<?php foreach ($familyincome as $k => $v) {
  echo ($k == 0 ? '' : ' ,') . $v;
} ?>
]" class="" />
<!-- 6 -->
<div class="row mb-4">
  <div class="col-xl-6 grid-margin stretch-card mb-0">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-4 mb-md-3">
          <h6 class="card-title mb-0">SOCIAL GROUP ANALYSIS</h6>
          <div class="dropdown"></div>
        </div>
        <div id="socialgroup"></div>
        <div class="text-center">
          <h5 class="text-center">Total: {{$totalsocialgroup}}</h5>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" id="social_sc" class="" value="{{$social_sc}}" />
  <input type="hidden" id="social_st" class="" value="{{$social_st}}" />
  <input type="hidden" id="social_obc" class="" value="{{$social_obc}}" />
  <input type="hidden" id="social_gen" class="" value="{{$social_gen}}" />
  <div class="col-xl-6 grid-margin stretch-card mb-0">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-4 mb-md-3">
          <h6 class="card-title mb-0">Other Reservations & Minority</h6>
          <div class="dropdown">
          </div>
        </div>
        <div id="othersocialgroup"></div>
        <div class="text-center">
          <h5 class="text-center">Total: {{$totalminority}}</h5>
        </div>
      </div>
    </div>
  </div>
</div>
<input type="hidden" id="minority_muslim" class="" value="{{$minority_muslim}}" />
<input type="hidden" id="minority_jain" class="" value="{{$minority_jain}}" />
<input type="hidden" id="minority_sikh" class="" value="{{$minority_sikh}}" />
<input type="hidden" id="minority_christian" class="" value="{{$minority_christian}}" />
<input type="hidden" id="minority_buddhist" class="" value="{{$minority_buddhist}}" />
<input type="hidden" id="minority_zoroastrians" class="" value="{{$minority_zoroastrians}}" />
<!-- 6a -->
<div class="row mb-4">
  <div class="col-xl-12 grid-margin stretch-card mb-0">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-2">
          <h6 class="card-title mb-0">NUMBER OF SESSIONS HELD AND SCHOLARS ATTENDED</h6>
          <div class="dropdown mb-2">
            <button class="btn btn-link p-0" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
              <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-feather="eye" class="icon-sm me-2"></i> <span class="">View</span></a>
              <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-feather="download" class="icon-sm me-2"></i> <span class="">Download</span></a>
            </div>
          </div>
        </div>
        <div id="sessionattended"></div>
      </div>
    </div>
  </div>
</div>
<!-- 7 -->
<div class="row mb-4">
  <div class="col-xl-6 grid-margin stretch-card mb-0">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-4 mb-md-3">
          <h6 class="card-title mb-0">COMPARISON OF APPLICANT PROGRESSION</h6>
          <div class="dropdown">
          </div>
        </div>
        <div id="compappprogression"></div>
        <div class="text-center">
          <input id="compappprogression_hidden_array" value="{{$compappprogression}}" type="hidden" />
          <input id="compappprogression_hidden_array_percentage" value="{{$compappprogressionp}}" type="hidden" />
          <h5 class="text-center">Total: {{$compappprogression_count}}</h5>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-6 grid-margin stretch-card mb-0">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-2">
          <h6 class="card-title mb-0">Total and Resolved Queries</h6>
          <div class="dropdown mb-2">
            <button class="btn btn-link p-0" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
              <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-feather="download" class="icon-sm me-2"></i> <span class="">Download</span></a>
            </div>
          </div>
        </div>
        <div id="queriesChart"></div>
      </div>
    </div>
  </div>
</div>
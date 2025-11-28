@if ( count( $investmentDetails[0]['investmentNomiees']) > 0 )
<!--<h3>First Nominee</h3>
<div class="  row">
  <label class=" col-lg-4  ">Full Name : </label>
  <div class="col-lg-8   ">
    {{ $investmentDetails[0]['investmentNomiees'][0]->name }} 
  </div>
</div>
<div class="  row">
  <label class=" col-lg-4  ">Relationship : </label>
  <div class="col-lg-8   ">
    {{ getRelationsName($investmentDetails[0]['investmentNomiees'][0]->relation) }} 
  </div>
</div>
<div class="  row">
  <label class=" col-lg-4  ">Nominee (D.O.B.) : </label>
  <div class="col-lg-8   ">
    {{ date('d-m-Y', strtotime( $investmentDetails[0]['investmentNomiees'][0]->dob ) ) }}
  </div>
</div> 
<div class="  row">
  <label class=" col-lg-4  ">Age : </label>
  <div class="col-lg-8   ">
    {{ $investmentDetails[0]['investmentNomiees'][0]->age }} 
  </div>
</div>
<div class="  row">
  <label class=" col-lg-4  ">Gender : </label>
  <div class="col-lg-8   ">
    @if($investmentDetails[0]['investmentNomiees'][0]->gender==1)
      Male
    @else
      Female
    @endif
  </div>
</div>
<div class="  row">
  <label class=" col-lg-4  ">Percentage : </label>
  <div class="col-lg-8   ">
    {{ $investmentDetails[0]['investmentNomiees'][0]->percentage }}% 
  </div>
</div>
@endif
@if(!empty($investmentDetails[0]['investmentNomiees'][1]))       
<h3>Second Nominee</h3>
<div class="  row">
  <label class=" col-lg-4  ">Full Name : </label>
  <div class="col-lg-8   ">
    {{ $investmentDetails[0]['investmentNomiees'][1]->name }} 
  </div>
</div>
<div class="  row">
  <label class=" col-lg-4  ">Relationship : </label>
  <div class="col-lg-8   ">
    {{ getRelationsName($investmentDetails[0]['investmentNomiees'][1]->relation) }}
  </div>
</div>
<div class="  row">
  <label class=" col-lg-4  ">Nominee (D.O.B.) : </label>
  <div class="col-lg-8   ">
    {{ $investmentDetails[0]['investmentNomiees'][1]->dob }}
  </div>
</div> 
<div class="  row">
  <label class=" col-lg-4  ">Age : </label>
  <div class="col-lg-8   ">
    {{ $investmentDetails[0]['investmentNomiees'][1]->age }}
  </div>
</div>
<div class="  row">
  <label class=" col-lg-4  ">Gender : </label>
  <div class="col-lg-8   ">
    @if($investmentDetails[0]['investmentNomiees'][1]->gender==1)
      Male
    @else
      Female
    @endif
  </div>
</div>
<div class="  row">
  <label class=" col-lg-4  ">Percentage : </label>
  <div class="col-lg-8   ">
    {{ $investmentDetails[0]['investmentNomiees'][1]->percentage }}%
  </div>
</div>-->
@endif
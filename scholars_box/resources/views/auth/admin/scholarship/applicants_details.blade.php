<!-- detail.blade.php -->

@extends('admin.layout.master')

@section('content')
<style>
html {
	line-height: 1;
}
body {
	background-color: #fff;
	color: white;
	font: 16px arial, sans-serif;
}
* {
	margin: 0;
	padding: 0;
}
*,
*:after,
*:before {
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
	        box-sizing: border-box;
}
.container {
	margin: 0 auto;
	max-width: 1024px;
	padding: 0 20px;
	overflow: auto;
}
.container table {
	margin: 15px 0 0;
}
table {
	
	border-collapse: collapse;
	//border-spacing: 4px; /* border-spacing works only if border-collapse is separate */
	color: white;
	font: 15px/1.4 "Helvetica Neue", Helvetica, Arial, Sans-serif;
	width: 100%;
}
thead {
	background: #395870;
	-webkit-background: linear-gradient(#49708f, #293f50);
	   -moz-background: linear-gradient(#49708f, #293f50);
	        background: linear-gradient(#49708f, #293f50);
	color: white;
}
tbody tr:nth-child(even) {
	background: #f0f0f2;
}
/* borders cannot be applied to tr elements or table structure elements. we should follow like below code. */
tfoot tr:last-child td {
	//border-bottom: 0;
}
th,
td {
	//border: 2px solid #666;
	padding: 6px 10px;
	vertical-align: middle;
}
td {
	border-bottom: 1px solid #cecfd5;
	border-right: 1px solid #cecfd5;
}
td:first-child {
	border-left: 1px solid #cecfd5;
}
.book-title {
	color: white;
	//display: block;
}
.item-stock,
.item-qty {
	text-align: center;
}
.item-price {
	text-align: right;
}
.item-multiple {
	display: block;
}
/* task */
.task table {
	margin-bottom: 44px;
}
.task a {
	color: white;
	//text-decoration: none;
}
.task thead {
	background-color: #f5f5f5;
	-webkit-background: transparent;
	   -moz-background: transparent;
	        background: transparent;
	color: white;
}
.task table th, .task table td {
	border-bottom: 0;
	border-right: 0;
}
.task table td {
	border-bottom: 1px solid #ddd;
}
.task table th, .task table td {
	padding-bottom: 22px;
	vertical-align: top;
}
.task tbody tr:nth-child(even) {
	background: transparent;
} 
.task table:last-child {
	//margin-bottom: 0;
}

.value{
    color: gray;
}
    </style>
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Applicant</a></li>
    <li class="breadcrumb-item active" aria-current="page">Applicant Details</li>
  </ol>
</nav>
<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <!-- <h6 class="card-title">Applicant Details</h6> -->
<div class="container">


	<section class="task">
		<table>
			
        
			
			<tbody>
                
				<tr>
                <h4 class="card-title">Applicant Personel Details</h4>    
					<th scope="row">
						
					</th>
					
				</tr>
                <tr>
					<th scope="row">
						
					</th>
					<td>
						
					<td>
										
					</td>

                    <td>
						
						</td>
					<td>
											
					</td>
				</tr>
				<tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>First Name : </h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->first_name}}</h4>						
					</td>

                    <td>
						<h4>Last Name :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->last_name}}</h4>						
					</td>
				</tr>
               
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Email Address :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->email}}</h4>						
					</td>
                    <td>
						<h4>Phone Number :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->phone_number}}</h4>						
					</td>
				</tr>
               
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Date of Birth :</h4> 
						</td>
					<td>
					<h4 class="value">{{ \Carbon\Carbon::parse($applicantsDetail->date_of_birth)->format('d-m-Y') }}</h4>
						
					</td>

                    <td>
						<h4>Gender :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->gender}}</h4>						
					</td>
				</tr>
               
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>State :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->state}}</h4>						
					</td>
                    <td>
						<h4>User type :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->user_type}}</h4>						
					</td>
				</tr>
                
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Looking For :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->looking_for}}</h4>						
					</td>
                    <td>
						<h4>Whatsapp Number :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->whatsapp_number}}</h4>						
					</td>
				</tr>
              
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Aadhar Card Number :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->aadhar_card_number??'Null'}}</h4>						
					</td>
                    <td>
						<h4>User type :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->user_type}}</h4>						
					</td>
				</tr>


                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Minority :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->student->is_minority??'Null'}}</h4>						
					</td>
                    <td>
						<h4>Minority Group :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->student->minority_group}}</h4>						
					</td>
				</tr>
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Category :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->student->category??'Null'}}</h4>						
					</td>
                    <td>
						<h4>Pwd Category :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->student->is_pwd_category}}</h4>						
					</td>
				</tr>
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Pwd Percentage :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->student->pwd_percentage??'Null'}}</h4>						
					</td>
                    <td>
						<h4>Occupation :</h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->student->occupation}}</h4>						
					</td>
				</tr>
                

               
                


				
			</body>
			<tfoot></tfoot>
		</table>
	</section>


	<section class="task">
		<table>
			
        
			
			<tbody>
                
				<tr>
                <h4 class="card-title">Applicant Employment Details</h4>    
					<th scope="row">
						
					</th>
					
				</tr>
                <tr>
					<th scope="row">
						
					</th>
					<td>
						
					<td>
										
					</td>

                    <td>
						
						</td>
					<td>
											
					</td>
				</tr>
				<tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Employment Type : </h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->student->employmentDetails->employment_type}}</h4>						
					</td>

                    <td>
						<h4>Designation :</h4> 
						</td>
					<td>
                    <h4 class="value">{{$applicantsDetail->student->employmentDetails->designation}}</h4>						
				
					</td>
				</tr>
               
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Joining Date :</h4> 
						</td>
					<td>
                    <h4 class="value">{{$applicantsDetail->student->employmentDetails->joining_date}}</h4>						
					
					</td>
                    <td>
						<h4>End Date :</h4> 
						</td>
					<td>
                    <h4 class="value">{{$applicantsDetail->student->employmentDetails->end_date}}</h4>						
					
					</td>
				</tr>
               
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Job Role :</h4> 
						</td>
					<td>
                    <h4 class="value">{{$applicantsDetail->student->employmentDetails->job_role}}</h4>						

						
					</td>
                    <td>
						<h4>Type :</h4> 
						</td>
					<td>
                    <h4 class="value">Null</h4>						

						
					</td>

                    
				</tr>
               
			</body>
			<tfoot></tfoot>
		</table>
	</section>

    <section class="task">
		<table>
			
        
			
			<tbody>
                
				<tr>
                <h4 class="card-title">Applicant Guardian Details</h4>    
					<th scope="row">
						
					</th>
					
				</tr>
                <tr>
					<th scope="row">
						
					</th>
					<td>
						
					<td>
										
					</td>

                    <td>
						
						</td>
					<td>
											
					</td>
				</tr>
				<tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Name : </h4> 
						</td>
					<td>
						<h4 class="value">{{$applicantsDetail->student->guardianDetails->name}}</h4>						
					</td>

                    <td>
						<h4>Relationship :</h4> 
						</td>
					<td>
                    <h4 class="value">{{$applicantsDetail->student->guardianDetails->relationship}}</h4>						
			
				
					</td>
				</tr>
               
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Occupation :</h4> 
						</td>
					<td>
                    <h4 class="value">{{$applicantsDetail->student->guardianDetails->occupation}}</h4>						
				
					
					</td>
                    <td>
						<h4>Phone Number :</h4> 
						</td>
					<td>
                    <h4 class="value">{{$applicantsDetail->student->guardianDetails->phone_number}}</h4>						
					
					
					</td>
				</tr>
               
                <tr>
					<th scope="row">
						
					</th>
					<td>
						<h4>Number of Siblings :</h4> 
						</td>
					<td>
                    <h4 class="value">{{$applicantsDetail->student->guardianDetails->number_of_siblings}}</h4>						
					

						
					</td>

                    <td>
						<h4>annual_income :</h4> 
						</td>
					<td>
                    <h4 class="value">{{$applicantsDetail->student->guardianDetails->annual_income}}</h4>						
					

						
					</td>
				</tr>
              
               
			</body>
			<tfoot></tfoot>
		</table>
	</section>


</div>
</div>
    </div>
  </div>
</div>
@endsection

<?php 

$data=$loanDetails;
$aDetails=[];
if(isset($data['applicant_id']) && $data['applicant_id']>0){
   $aDetails = getMemberData($data['applicant_id']);
   if(!empty($aDetails)){
     $aDetails=$aDetails->toArray();
   }  
}

    // Pen Card
 $aIncome_Tax_PAN_No='';
 if(isset($data['loan_applicants'][0]['address_proof_type']) && $data['loan_applicants'][0]['id_proof_type']==0){
   $aIncome_Tax_PAN_No=$data['loan_applicants'][0]['id_proof_number'];
 }
               

$caDetails=[];
if(isset($data['loan_co_applicants'][0]['member_id']) && $data['loan_co_applicants'][0]['member_id']>0){
   $caDetails = getMemberData($data['loan_co_applicants'][0]['member_id']);
   if(!empty($caDetails)){
     $caDetails=$caDetails->toArray();
   }    
}

     // Pen Card
 $caIncome_Tax_PAN_No='';
 if(isset($data['loan_co_applicants'][0]['address_proof_type']) && $data['loan_co_applicants'][0]['id_proof_type']==0){
   $caIncome_Tax_PAN_No=$data['loan_co_applicants'][0]['id_proof_number'];
 }     


$gDetails=[];
if(isset($data['loan_guarantor'][0]['member_id']) && $data['loan_guarantor'][0]['member_id']>0){
   $gDetails = getMemberData($data['loan_guarantor'][0]['member_id']);
   if(!empty($gDetails)){
     $gDetails=$gDetails->toArray(); 
   } 
}

     // Pen Card
 $gIncome_Tax_PAN_No='';
 if(isset($data['loan_guarantor'][0]['address_proof_type']) && $data['loan_guarantor'][0]['id_proof_type']==0){
   $gIncome_Tax_PAN_No=$data['loan_guarantor'][0]['id_proof_number'];
 } 
$tenure ='';
  if($data['emi_option'] == 1){
        $tenure =  $data['emi_period'].' Months';
    }elseif ($data['emi_option'] == 2) {
        $tenure =  $data['emi_period'].' Weeks';
    }elseif ($data['emi_option'] == 3) {
        $tenure =  $data['emi_period'].' Days';
    }
 ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Loan Form</title>
    
    <link rel="shortcut icon" href="{{url('/')}}/asset/{{ $logo->image_link }}" />
    <link rel="apple-touch-icon" href="{{url('/')}}/asset/{{ $logo->image_link }}" />
    <link rel="apple-touch-icon" sizes="72x72" href="{{url('/')}}/asset/{{ $logo->image_link2 }}" />
    <link rel="apple-touch-icon" sizes="114x114" href="{{url('/')}}/asset/{{ $logo->image_link2 }}" />
    <link rel="stylesheet" href="{{url('/')}}/asset/css/sweetalert.css" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Ubuntu:400,300,100,500,700,900" rel="stylesheet" type="text/css">

     <link rel="stylesheet" href="{{url('/')}}/asset/dashboard/vendor/@fortawesome/fontawesome-free/css/all.min.css" type="text/css">
    <link href="{{url('/')}}/asset/global_assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/global_assets/css/icons/fontawesome/styles.min.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/user/assets/css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/user/assets/css/bootstrap_limitless.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/user/assets/css/layout.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/user/assets/css/components.min.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/user/assets/css/colors.css" rel="stylesheet" type="text/css">

    <link href="{{url('/')}}/asset/dashboard/vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"> 
    <link href="{{url('/')}}/asset/dashboard/vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.standalone.min.css" rel="stylesheet" type="text/css">
    <link href="{{url('/')}}/asset/css/admin_panel.css" rel="stylesheet" type="text/css">
    
    <style type="text/css">
      .thumb_impression{
        width: 300px;
        height:100px;
        border:2px solid black;
        margin:25px 0 0 30px;
      }
      .thumb_impression2{
        width: 300px;
        height:100px;
        border:2px solid black;
      }
	  input[type="text"] {
	  font-weight:bold;
	  }
	   .no_dependent{
			  margin: 80px 0 0px 0;
		  }
		  
		.employee_label{
			margin:32px 0 0 0 ;
		}  
		.total_annual_income{
			margin:0px 0 0 0 ;
		}  
		.employee_name{
			margin:8px 0 0 0 ;
		}
		.designation{
			margin:12px 0 0 0 ;
		}
		.Issue_authority{
			margin:-12px 0 0 0 ;
		}
		.des{
			margin:11px 0 0 0 ;
		}
		.address{
			height:12rem;
		}
	  @media print{
		  @page{
			size: A4 ; /* DIN A4 standard, Europe */
			margin: 250px 0 80px 0;
			
		  }
		 label{
			  margin: 10px 0 0px 0;
		  }
		  .no_dependent{
			  margin: 180px 0 0px 0;
		  }
		  .line_activity,.no_of_yrs{
			  margin: 18px 0 0px 0;
		  }
		  .no_of_yrs{
			  margin: 38px 0 0px 0;
		  }
		 .designation{
			  margin: 48px 0 0px 0;
		 }
		 .security_details{
			 margin: 380px 0 0px 0;
		 }
		 .employee_name,.emp_1{
			 margin: 8px 0 0px 0;
		 }
		 .total_annual_income{
			 margin: 22px 0 0px 0;
		 }
		 .Issue_authority{
			 margin:50px 0 0 0 ;
		 }
		 .address{
			 margin:25px 0 0 0 ; 
			 height:12rem;
		 }
		  
	  }
	 
    </style>
  </head>
<body>

<div class="content " id="body-content" >
  <div class="row"> 
    <div class="card bg-white" >
      <div class="d-flex justify-content-around" >
        <div class="col-lg-3">
          <div class="form-group row">
            <label class="col-form-label col-lg-12" for="name">PHOTO </label>
          </div>
          <div class="form-group row " style="margin-top: 12rem;">
            <label class="col-form-label col-lg-12" for="name">Name </label>
          </div>
          <div class="form-group row" >
            <label class="col-form-label col-lg-12">Father/Husband’s name </label>
          </div>
           <div class="form-group row" >
            <label class="col-form-label col-lg-12">Income Tax PAN No. </label>
          </div>
           <div class="form-group row">
            <label class="col-form-label col-lg-12">Resident Address </label>
          </div>
           <div class="form-group row">
            <label class="col-form-label col-lg-12">Office Address </label>
          </div>
           <div class="form-group row">
            <label class="col-form-label col-lg-12">Telephone No. </label>
          </div>
           <div class="form-group row">
            <label class="col-form-label col-lg-12">Date of Birth </label>
          </div>
           <div class="form-group row">
            <label class="col-form-label col-lg-12">Education Qualification </label>
          </div>
           <div class="form-group row">
            <label class="col-form-label col-lg-12">Marital Status </label>
          </div>
           <div class="form-group row">
            <label class="col-form-label col-lg-12">Do you Belong to </label>
          </div>
          <div class="form-group row "  >
            <label class="col-form-label col-lg-12 no_dependent" >No.of Dependants </label>
          </div>
          <div class="form-group row">
            <label class="col-form-label col-lg-12">Residence ownership </label>
          </div>
          <div class="form-group row"  >
            <label class="col-form-label col-lg-12">(a) If self employed Professional/salaried/other </label>
          </div>
          <div class="d-flex flex-column" style="margin-top:40rem;">
          <div class="form-group row">
            <label class="col-form-label col-lg-12  security_details">Security Detail </label>
          </div>
          <div class="form-group row">
            <label class="col-form-label col-lg-12 " style="margin-top:27rem; ">Loan details </label>
          </div>
        </div> 
      </div>
      <div class="col-lg-3">
        <div class="form-group row">
          <div class="col-lg-12 ">                  
            <span class="text-center rounded-circle w-100">
            
              <?php if(!empty($data['loan_member']['photo'])){?>
              <img  id="photo-preview" alt="applicant profile photo" width="200" height="200" src="{{url('/')}}/asset/profile/member_avatar/<?php echo $data['loan_member']['photo']; ?>">
              <?php }else{ ?>
              <img alt="applicant profile photo" id="photo-preview" src="{{url('/')}}/asset/images/user.png">
              <?php } ?>              
            </span>
          </div>
        </div>
        <div class="form-group">
            <input type="text" name="name" id="aname" class=" form-control" value="<?php echo $data['loan_member']['first_name'].'&nbsp;'.$data['loan_member']['last_name']; ?>" placeholder="applicant Name" redonly>
        </div>
        <div class="form-group">
            <input type="text" name="father_name" id="father_name" class=" form-control mt-3" value="<?php echo $data['loan_member']['father_husband']; ?>" placeholder="applicant_father_name" readonly="">
        </div>
        <div class="form-group">
            <input type="text" name="income_tax" id="income_tax" class=" form-control mt-3" value="<?php echo $aIncome_Tax_PAN_No; ?>" placeholder="applicant_income_tax" readonly="">
        </div>
        <div class="form-group">
            <input type="text" name="residance" id="residance" class=" form-control mt-3" value="<?php echo $data['loan_member']['address']; ?>" placeholder="applicant_residance" readonly="">
        </div> 
        <div class="form-group">
            <input type="text" name="office_address" id="office_address" class=" form-control mt-3" value="<?php echo $data['loan_member']['address']; ?>" placeholder="applicant_office_address">
        </div>
        <div class="form-group">
            <input type="text" name="telephone_number" id="telephone_number" class=" form-control mt-3" value="<?php echo $data['loan_member']['mobile_no']; ?>" placeholder="applicant_telephone_number"  readonly="">
        </div>    
        <div class="input-group">
            <span class="input-group-prepend">
             <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
            </span>
            
		   <?php
           $adob ='';
          if(!empty($data['loan_member']['dob'])){
          $adob = date("d/m/Y", strtotime($data['loan_member']['dob']));
          } 
          ?>
            
            <input type="text" class="form-control " name="dob" id="dob" value="<?php echo $adob; ?>" readonly>
        </div>
        <div class="form-group">
          <input type="text" name="first_name" id="first_name" class=" form-control mt-3"  >
        </div>
        <div class="d-flex" style="margin-top:50px;">
          <div class="custom-control custom-radio mb-3 mr-2">
            <input type="radio" name="lm_marital_status" id="lm_marital_status1" class="custom-control-input m-status" <?php if($data['loan_member']['marital_status']==1){?> checked <?php } ?> value="1">
            <label class="custom-control-label" for="lm_marital_status1">Married</label>
          </div>
          <div class="custom-control custom-radio mb-3  ">
            <input type="radio"  name="lm_marital_status" id="lm_marital_status0" class="custom-control-input m-status" <?php if($data['loan_member']['marital_status']==0){?> checked <?php } ?> value="0">
            <label class="custom-control-label" for="lm_marital_status0">Un Married</label>
          </div>
        </div>
        <div class=" d-flex mt-4 col-lg-12 flex-wrap" id="inline_content">
          <div class="custom-control custom-radio mb-3 mr-2">
            <input type="radio" name="areligion" class="custom-control-input r-status" id="areligion1" value="1">
            <label class="custom-control-label" for="areligion1">SC</label>
          </div>
          <div class="custom-control custom-radio mb-3 mr-2 ">
            <input type="radio"  name="areligion" class="custom-control-input r-status" id="areligion2" value="2">
            <label class="custom-control-label" for="areligion2">ST</label>
          </div>
          <div class="custom-control custom-radio mb-3 mr-2 ">
            <input type="radio"  name="areligion" class="custom-control-input r-status" id="areligion3" value="3">
            <label class="custom-control-label" for="areligion3">OBC</label>
          </div>
          <div class="custom-control custom-radio mb-3 mr-2 ">
            <input type="radio"  name="areligion" class="custom-control-input r-status" id="areligion4" value="4">
            <label class="custom-control-label" for="areligion4">Minority</label>
          </div>
          <div class="custom-control custom-radio mb-3  ">
            <input type="radio"  name="areligion" class="custom-control-input r-status" id="areligion5" value="5">
            <label class="custom-control-label" for="areligion5">General</label>
          </div>
        </div>
        <input type="text" name="first_name" id="afirst_name" class=" form-control mt-2"  >
        <div class="d-flex " style="margin-top:50px;">
        <div class="custom-control custom-radio mb-3 mr-2">
          <input type="radio" name="a_applicant_Residence_ownership" id="a_applicant_Residence_ownership0" class="custom-control-input m-status" <?php if($data['loan_applicants'][0]['temporary_permanent']==0){?> checked <?php } ?>  value="0">
          <label class="custom-control-label" for="a_applicant_Residence_ownership0">Self</label>
        </div>
        <div class="custom-control custom-radio mb-3 mr-2 ">
          <input type="radio"  name="a_applicant_Residence_ownership" id="a_applicant_Residence_ownership1" class="custom-control-input m-status" <?php if($data['loan_applicants'][0]['temporary_permanent']==1){?> checked <?php } ?> value="1">
          <label class="custom-control-label" for="a_applicant_Residence_ownership1">Parental</label> 
        </div>
        <div class="custom-control custom-radio mb-3  ">
          <input type="radio"  name="a_applicant_Residence_ownership" id="a_applicant_Residence_ownership2" class="custom-control-input m-status" <?php if($data['loan_applicants'][0]['temporary_permanent']==2){?> checked <?php } ?> value="2">
          <label class="custom-control-label" for="a_applicant_Residence_ownership2">Rental</label>
        </div>
        
     </div>
        <div class="d-flex flex-column " style="margin-top:50px;">
            <div class="form-group row">
              <label class="col-form-label col-lg-12 details" >Details</label>
            </div>
            <div class="row">
            <div class="col-lg-12">
             <div class="form-group row">
               <div class="col-lg-6">
              <label class="col-form-label col-lg-12">His/her Firm is/Works in/ Occupation</label>
              </div>
              <div class="col-lg-6">
              <label class="col-form-label col-lg-12">Proprietorship Partnership Pvt. Ltd.Public ltd. Co. Other Co<input type="text" name="company_name" class="form-control" placeholder=
                "Proprietorship Partnership Pvt. Ltd" value="" readonly=""></label>
               </div>
            </div>
            <div class="form-group row">
              <div class="col-lg-6">
              <label class="col-form-label col-lg-12 mt-2">Line of activity</label>
            </div>
            <div class="col-lg-6">
               <label class="col-form-label col-lg-12"><input type="text" name="" class="form-control"></label>
            </div>
            </div>
            <div class="form-group row">
              <div class="col-lg-6">
              <label class="col-form-label col-lg-12 mt-4">No. of years in field</label>
            </div>
            <div class="col-lg-6">
                <label class="col-form-label col-lg-12 mt-2"><input type="text" name="years_field" class="form-control"></label>
            </div>
            </div>
            <div class="form-group row">
              <div class="col-lg-6">
              <label class="col-form-label col-lg-12 mt-4">Designation</label>
            </div>
            <div class="col-lg-6">
                <label class="col-form-label col-lg-12 mt-2"><input type="text" name="designation" class="form-control des" value="<?php echo $data['loan_applicants'][0]['designation']; ?>" readonly=""></label>
            </div>
            </div>
            <div class="form-group row">
              <div class="col-lg-6">
              <label class="col-form-label col-lg-12  employee_label">Name of the employer</label>
            </div>
            <div class="col-lg-6">
                 <label class="col-form-label col-lg-12  "><input type="text" name="employer" value="" class="form-control mt-2"></label>
            </div>
            </div>
            <div class="form-group row">
              <div class="col-lg-6">
              <label class="col-form-label col-lg-12 mt-2">Total annual income (Rs.)</label>
            </div>
            <div class="col-lg-6">
               <label class="col-form-label col-lg-12" for="total_annual"><input type="text" name="total_annual" value="<?php echo $data['loan_applicants'][0]['monthly_income']*12; ?>" id="total_annual"  class="form-control"></label>
            </div>
            </div>
          </div>
           
        </div> 
        </div>

        <div class="d-flex flex-row justify-content-between " style="margin-top:3rem;">
           <div class="form-group row">
              <label class="col-form-label col-lg-12">Type security of: 
              
              @if($data['loan_applicants'][0]['id_proof_type']==0)
                <b>Pen Card</b>
            @elseif($data['loan_applicants'][0]['id_proof_type']==1)
                <b>Aadhar Card</b>
            @elseif($data['loan_applicants'][0]['id_proof_type']==2)
                <b>DL</b>
            @elseif($data['loan_applicants'][0]['id_proof_type']==3)
                <b>Voter Id</b>
            @elseif($data['loan_applicants'][0]['id_proof_type']==4)
                <b>Passport</b>
            @elseif($data['loan_applicants'][0]['id_proof_type']==5)
                <b>Identity Card</b>
            @endif
              
              </label>
            </div>
            <div class="form-group row">
              <label class="col-form-label col-lg-12" >Issued By: <b></b></label>
            </div> 
        </div> 
        <div class="d-flex flex-row justify-content-between"  style="margin-top:98px;">
           <div class="form-group row">
              <label class="col-form-label col-lg-12">Document No.: <b>{{$data['loan_applicants'][0]['id_proof_number']}}</b></label>
            </div>
            <div class="form-group row">
              <label class="col-form-label col-lg-12" >Current prematurely value:<b></b></label>
            </div> 
        </div> 
        <div class="d-flex flex-row justify-content-between align-items-center mt-4" >
           <div class="form-group row">
              <label class="col-form-label col-lg-12">Name favor of in :<b></b></label>
            </div>
            <div class="form-group row">
              <label class="col-form-label col-lg-12" ></label>
            </div> 
        </div> 
        <div class="d-flex flex-row justify-content-between align-items-center "  style="margin-top:10rem;">
           <div class="form-group row">
              <label class="col-form-label col-lg-12">Sanction loan amount: <b><?php echo $data['amount'].' <i class="fas fa-rupee-sign"></i>'; ?></b></label>
            </div>
            <div class="form-group row">
              <label class="col-form-label col-lg-12" ></label>
            </div> 
        </div> 
        <div class="d-flex flex-row justify-content-between align-items-center mt-3" >
           <div class="form-group row">
              <label class="col-form-label col-lg-12">Date of sanction: 
			  
		  <?php
          $approve_date ='';
          if(!empty($data['approve_date'])){
          $approve_date = date("d/m/Y", strtotime($data['approve_date']));
          }
		  ?> 
			  
			  <b><?php echo $approve_date; ?></b></label>
            </div>
            <div class="form-group row">
              <label class="col-form-label col-lg-12" ></label>
            </div> 
        </div> 
      </div>
   <!-- Co Applicant -->
   <div class="col-lg-3">
    <div class="form-group row">
          <div class="col-lg-12 ">                  
            <span class="text-center rounded-circle w-100">
              
              <?php if(!empty($caDetails['photo'])){?>
        <img  alt="co applicant profile photo" id="co-photo-preview" width="200" height="200" src="{{url('/')}}/asset/profile/member_avatar/<?php echo $caDetails['photo']; ?>">
              <?php }else{ ?>
              <img alt="co applicant profile photo" id="co-photo-preview" src="{{url('/')}}/asset/images/user.png">
              <?php } ?>  
              
            </span>
          </div>
        </div>
     <div class="form-group">
     
     
     
        <input type="text" name="cname" id="cname" class=" form-control" value="<?php echo $caDetails['first_name'].'&nbsp;'.$caDetails['last_name']; ?>" placeholder="co-applicant Name" redonly>
        </div>
        <div class="form-group">
            <input type="text" name="cfather_name" id="cfather_name" class=" form-control mt-3" placeholder="co-applicant_father_name" value="<?php echo $caDetails['father_husband']; ?>" readonly="">
        </div>
        <div class="form-group">
            <input type="text" name="cincome_tax" id="cincome_tax" class=" form-control mt-3" placeholder="co-applicant_income_tax" value="<?php echo $caIncome_Tax_PAN_No; ?>" readonly="">
        </div>
        <div class="form-group">
            <input type="text" name="cresidance" id="cresidance" class=" form-control mt-3"  placeholder="co-applicant_residance" value="<?php echo $caDetails['address']; ?>" readonly="">
        </div> 
        <div class="form-group">
            <input type="text" name="coffice_address" id="coffice_address" class=" form-control mt-3" placeholder="co-applicant_office_address" value="<?php echo $caDetails['address']; ?>"  >
        </div>
        <div class="form-group">
            <input type="text" name="ctelephone_number" id="ctelephone_number" class=" form-control mt-3" placeholder="co-applicant_telephone_number" value="<?php echo $caDetails['mobile_no']; ?>" readonly="">
        </div>   
        
           <?php
           $cadob ='';
          if(!empty($caDetails['dob'])){
          $cadob = date("d/m/Y", strtotime($caDetails['dob']));
          } 
          ?>
        
         
       <div class="input-group">
            <span class="input-group-prepend">
             <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
            </span>
            <input type="text" class="form-control " name="dob" id="cdob" value="<?php echo $cadob; ?>" readonly>
        </div>
        <div class="form-group">
        <input type="text" name="first_name" id="cfirst_name" class=" form-control mt-3"  >
        </div>
      <div class="d-flex " style="margin-top:50px;">
        <div class="custom-control custom-radio mb-3 mr-2">
          <input type="radio" name="co_applicant_marital_status" id="co_applicant_marital_status1" class="custom-control-input m-status" <?php if($caDetails['marital_status']==1){?> checked <?php } ?> value="1">
          <label class="custom-control-label" for="co_applicant_marital_status1">Married</label>
        </div>
        <div class="custom-control custom-radio mb-3  ">
          <input type="radio"  name="co_applicant_marital_status" id="co_applicant_marital_status0" class="custom-control-input m-status" <?php if($caDetails['marital_status']==0){?> checked <?php } ?> value="0">
          <label class="custom-control-label" for="co_applicant_marital_status0">Un Married</label>
        </div>
     </div>
     <div class=" d-flex mt-4 col-lg-12 flex-wrap">
        <div class="custom-control custom-radio mb-3 mr-2">
          <input type="radio" name="creligion" class="custom-control-input m-status" id="creligion1" value="1">
          <label class="custom-control-label" for="creligion1">SC</label>
        </div>
        <div class="custom-control custom-radio mb-3 mr-2 ">
          <input type="radio"  name="creligion" class="custom-control-input m-status" id="creligion2" value="2">
          <label class="custom-control-label" for="creligion2">ST</label>
        </div>
        <div class="custom-control custom-radio mb-3 mr-2 ">
          <input type="radio"  name="creligion" class="custom-control-input m-status" id="creligion3" value="3">
          <label class="custom-control-label" for="creligion3">OBC</label>
        </div>
        <div class="custom-control custom-radio mb-3 mr-2 ">
          <input type="radio"  name="creligion" class="custom-control-input m-status" id="creligion4" value="4">
          <label class="custom-control-label" for="creligion4">Minority</label>
        </div>
        <div class="custom-control custom-radio mb-3  ">
          <input type="radio"  name="creligion" class="custom-control-input m-status" id="creligion5" value="5">
          <label class="custom-control-label" for="creligion5">General</label>
        </div>
     </div>
      <input type="text" name="first_name" id="ceducation" class=" form-control mt-2"  >
      <div class="d-flex " style="margin-top:50px;">
        <div class="custom-control custom-radio mb-3 mr-2">
          <input type="radio" name="co_applicant_Residence_ownership" id="co_applicant_Residence_ownership0" class="custom-control-input m-status" <?php if($data['loan_co_applicants'][0]['temporary_permanent']==0){?> checked <?php } ?>  value="0">
          <label class="custom-control-label" for="co_applicant_Residence_ownership0">Self</label>
        </div>
        <div class="custom-control custom-radio mb-3 mr-2 ">
          <input type="radio"  name="co_applicant_Residence_ownership" id="co_applicant_Residence_ownership1" class="custom-control-input m-status" <?php if($data['loan_co_applicants'][0]['temporary_permanent']==1){?> checked <?php } ?> value="1">
          <label class="custom-control-label" for="co_applicant_Residence_ownership1">Perental</label>
        </div>
        <div class="custom-control custom-radio mb-3  ">
          <input type="radio"  name="co_applicant_Residence_ownership" id="co_applicant_Residence_ownership2" class="custom-control-input m-status" <?php if($data['loan_co_applicants'][0]['temporary_permanent']==2){?> checked <?php } ?> value="2">
          <label class="custom-control-label" for="co_applicant_Residence_ownership2">Rental</label>
        </div>
        
     </div>
     <div class="d-flex flex-column" style="margin-top:50px;">
        <div class="form-group row">
            <label class="col-form-label col-lg-12 details">Details</label>
          </div>
          <div class="row">
          <div class="col-lg-12">
           <div class="form-group row">
             <div class="col-lg-6">
              <label class="col-form-label col-lg-12">His/her Firm is/Works in/ Occupation</label>
              </div>
              <div class="col-lg-6">
                <label class="col-form-label col-lg-12">Proprietorship Partnership Pvt. Ltd.Public ltd. Co. Other Co<input type="text" name="company_name" value="" class="form-control" readonly=""></label>
              </div>
          </div>
          <div class="form-group row">
           <div class="col-lg-6">&nbsp;</div>
            <div class="col-lg-6">
              <!--<label class="col-form-label col-lg-12"></label>-->
             <label class="col-form-label col-lg-12"><input type="text" name="" class="form-control line_activity"></label>
          </div>
          </div>
          <div class="form-group row">
            <div class="col-lg-6">&nbsp;</div>
             <div class="col-lg-6">
            <!--<label class="col-form-label col-lg-12 mt-4"></label>-->
            <label class="col-form-label col-lg-12 mt-2"><input type="text" name="" class="form-control no_of_yrs"></label>
            </div>
          </div>
           <div class="form-group row">
           <div class="col-lg-6">&nbsp;</div>
            <div class="col-lg-6">  <!--<label class="col-form-label col-lg-12 mt-4"></label>-->
              <label class="col-form-label col-lg-12 mt-2"><input type="text" name="designation" value="<?php echo $data['loan_co_applicants'][0]['designation'];?>" class="form-control designation"></label>
          </div>
          </div>
          <div class="form-group row">
            <div class="col-lg-6">&nbsp;</div>
            <div class="col-lg-6">  <!--<label class="col-form-label col-lg-12 mt-4"></label>-->
              <label class="col-form-label col-lg-12 mt-2"><input type="text" name="" class="form-control employee_name" value="" readonly=""></label>
          </div>
          </div>
           <div class="form-group row">
             <div class="col-lg-6">&nbsp;</div>
            <div class="col-lg-6">  <!--<label class="col-form-label col-lg-12 "></label>-->
              <label class="col-form-label col-lg-12 "><input type="text" name="ctotal_annual_income" value="<?php echo $data['loan_co_applicants'][0]['monthly_income']*12;?>" class="form-control  total_annual_income"></label>
          </div>
          </div>
        </div>
         
      </div>
     </div>
     <div class="" >
        <div class="d-flex flex-row justify-content-between "style="margin-top:3.4rem;" >
         <div class="form-group row">
            <label class="col-form-label col-lg-12 Issue_authority" >Issuing Authority: <b></b></label>
          </div>
          <div class="form-group row">
            <label class="col-form-label col-lg-12" ></label>
          </div> 
        </div> 
        <div class="d-flex flex-row justify-content-between " style="margin-top:100px;" >
          <div class="form-group row">
            <label class="col-form-label col-lg-12">Maturity Value: <b></b></label>
          </div>
          <div class="form-group row">
            <label class="col-form-label col-lg-12" ></label>
          </div> 
        </div> 
        <div class="d-flex  mt-4" >
           <div class="form-group row" >
              <label class="col-form-label col-lg-12 address">Address on document: <b><?php echo $data['loan_member']['address']; ?></b></label> 
            </div>
            
        </div> 
      </div>
       <div class="d-flex flex-row justify-content-between " >
           <div class="form-group row">
              <label class="col-form-label col-lg-12">Loan tenure: <b><?php echo $tenure;?></b></label>
            </div>
            <div class="form-group row">
              <label class="col-form-label col-lg-12" ></label>
            </div> 
        </div> 
        <div class="d-flex flex-row justify-content-between align-items-center mt-3" >
           <div class="form-group row">
              <label class="col-form-label col-lg-12">No. of installment: <b><?php echo $tenure;?></b></label>
            </div>
            <div class="form-group row">
              <label class="col-form-label col-lg-12"></label>
            </div> 
        </div> 
     </div>

<!-- Gurantor -->
<div class="col-lg-3">
    <div class="form-group row">
      <div class="col-lg-12 ">                  
        <span class="text-center rounded-circle w-100">
          
           <?php if(!empty($gDetails['photo'])){?>
              <img alt="guarantor profile photo" width="200" height="200" id="g-photo-preview" src="{{url('/')}}/asset/profile/member_avatar/<?php echo $gDetails['photo']; ?>">
              <?php }else{ ?>
              <img alt="guarantor profile photo" id="g-photo-preview" src="{{url('/')}}/asset/images/user.png">
              <?php } ?>  
        </span>
      </div>
    </div>
    <div class="form-group">
      <input type="text" name="name" id="gname" class=" form-control" value="<?php echo $data['loan_guarantor'][0]['name']; ?>" placeholder="gurantor Name" redonly>
    </div>
    <div class="form-group">
        <input type="text" name="gfather_name" id="gfather_name" class=" form-control mt-3" placeholder="gurantor_father_name" value="<?php echo $data['loan_guarantor'][0]['father_name']; ?>" >
    </div>
    <div class="form-group">
        <input type="text" name="gincome_tax" id="gincome_tax" class=" form-control mt-3" placeholder="gurantor_income_tax" value="<?php echo $gIncome_Tax_PAN_No; ?>" readonly="">
    </div>
    <div class="form-group">
        <input type="text" name="gresidance" id="gresidance" class=" form-control mt-3"  placeholder="gurantor_residance" value="<?php echo $data['loan_guarantor'][0]['local_address']; ?>" readonly="">
    </div> 
    <div class="form-group">
        <input type="text" name="goffice_address" id="goffice_address" class=" form-control mt-3" value="<?php echo $data['loan_guarantor'][0]['local_address']; ?>" placeholder="gurantor_office_address"  >
    </div>
    <div class="form-group">
        <input type="text" name="gtelephone_number" id="gtelephone_number" class=" form-control mt-3" placeholder="gurantor_telephone_number" value="<?php echo $data['loan_guarantor'][0]['mobile_number']; ?>" readonly="">
    </div>  
    
         <?php
           $gdob ='';
          if(!empty($data['loan_guarantor'][0]['dob'])){
          $gdob = date("d/m/Y", strtotime($data['loan_guarantor'][0]['dob']));
          } 
          ?>
      
    <div class="input-group">
      <span class="input-group-prepend">
       <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
      </span>
      <input type="text" class="form-control " name="gdob" id="gdob" value="<?php echo $gdob; ?>" readonly>
    </div>
    <div class="form-group">
      <input type="text" name="geducation" id="geducation" class=" form-control mt-3"  >
    </div>
    <div class="d-flex" style="margin-top:50px;">
      <div class="custom-control custom-radio mb-3 mr-2">
        <input type="radio" name="marital_status" id="marital_status1" class="custom-control-input m-status" <?php if($data['loan_guarantor'][0]['marital_status']==1){?> checked <?php } ?> value="1">
        <label class="custom-control-label" for="marital_status1">Married</label>
      </div>
      <div class="custom-control custom-radio mb-3  ">
        
        <input type="radio" name="marital_status" id="marital_status0" class="custom-control-input m-status" <?php if($data['loan_guarantor'][0]['marital_status']==0){?> checked <?php } ?> value="0">
        <label class="custom-control-label" for="marital_status0">Un Married</label>
      </div>
    </div>
    <div class=" d-flex mt-4 col-lg-12 flex-wrap">
      <div class="custom-control custom-radio mb-3 mr-2">
        <input type="radio" name="gcaste" id="gcaste1" class="custom-control-input m-status" value="1">
        <label class="custom-control-label" for="gcaste1">SC</label>
      </div>
      <div class="custom-control custom-radio mb-3 mr-2 ">
        <input type="radio"  name="gcaste" id="gcaste2" class="custom-control-input m-status" value="2">
        <label class="custom-control-label" for="gcaste2">ST</label>
      </div>
      <div class="custom-control custom-radio mb-3 mr-2 ">
        <input type="radio"  name="gcaste" id="gcaste3"  class="custom-control-input m-status" value="3">
        <label class="custom-control-label" for="gcaste3">OBC</label>
      </div>
      <div class="custom-control custom-radio mb-3 mr-2 ">
        <input type="radio"  name="gcaste" id="gcaste4" class="custom-control-input m-status" value="4">
        <label class="custom-control-label" for="gcaste4">Minority</label>
      </div>
      <div class="custom-control custom-radio mb-3  ">
        <input type="radio"  name="gcaste" id="gcaste5" class="custom-control-input m-status" value="5">
        <label class="custom-control-label" for="gcaste5">General</label>
      </div>
    </div>
    <input type="text" name="first_name" id="gfirst_name"  class=" form-control mt-2"  >
    <div class="d-flex" style="margin-top:50px;">
        <div class="custom-control custom-radio mb-3 mr-2">
          <input type="radio" name="guarantor_Residence_ownership" id="guarantor_Residence_ownership0" class="custom-control-input m-status" <?php if($data['loan_guarantor'][0]['temporary_permanent']==0){?> checked <?php } ?>  value="0">
          <label class="custom-control-label" for="guarantor_Residence_ownership0">Self</label>
        </div>
        <div class="custom-control custom-radio mb-3 mr-2 ">
          <input type="radio"  name="guarantor_Residence_ownership" id="guarantor_Residence_ownership1" class="custom-control-input m-status" <?php if($data['loan_guarantor'][0]['temporary_permanent']==1){?> checked <?php } ?> value="1">
          <label class="custom-control-label" for="guarantor_Residence_ownership1">Perental</label>
        </div>
        <div class="custom-control custom-radio mb-3  ">
          <input type="radio"  name="guarantor_Residence_ownership" id="guarantor_Residence_ownership2" class="custom-control-input m-status" <?php if($data['loan_guarantor'][0]['temporary_permanent']==2){?> checked <?php } ?> value="2">
          <label class="custom-control-label" for="guarantor_Residence_ownership2">Rental</label>
        </div>
        
     </div> 
      <div class="d-flex flex-column" style="margin-top:50px;">
        <div class="form-group row">
            <label class="col-form-label col-lg-12 details">Details</label>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="form-group row">
               <div class="col-lg-6">
                 <label class="col-form-label col-lg-12">His/her Firm is/Works in/ Occupation</label>
               </div>
              <div class="col-lg-6">
                <label class="col-form-label col-lg-12">Proprietorship Partnership Pvt. Ltd.Public ltd. Co. Other Co<input type="text" name="company_name" class="form-control" value="" readonly=""></label>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-lg-6">&nbsp;</div>
              <div class="col-lg-6">
                <!--<label class="col-form-label col-lg-12"></label>-->
                <label class="col-form-label col-lg-12"><input type="text" name="" class="form-control line_activity"></label>
              </div>
            </div>
            <div class="form-group row">
               <div class="col-lg-6">&nbsp;</div>
               <div class="col-lg-6">
                <!--<label class="col-form-label col-lg-12 mt-4"></label>-->
                <label class="col-form-label col-lg-12 mt-2"><input type="text" name="" class="form-control no_of_yrs"></label>
              </div>
            </div>
             <div class="form-group row">
              <div class="col-lg-6">&nbsp;</div>
              <div class="col-lg-6">  <!--<label class="col-form-label col-lg-12 mt-4"></label>-->
                <label class="col-form-label col-lg-12 mt-2"><input type="text" name="designation" class="form-control designation" value="<?php echo $data['loan_co_applicants'][0]['designation'];?>" readonly=""></label>
              </div>
            </div>
         <div class="form-group row">
            <div class="col-lg-6">&nbsp;</div>
            <div class="col-lg-6">  <!--<label class="col-form-label col-lg-12 mt-4"></label>-->
              <label class="col-form-label col-lg-12 mt-2"><input type="text" name="" value="" class="form-control employee_name"></label>
            </div>
          </div>
          <div class="form-group row">
           <div class="col-lg-6">&nbsp;</div>
            <div class="col-lg-6">  <!--<label class="col-form-label col-lg-12 "></label>-->
              <label class="col-form-label col-lg-12 "><input type="text" name="gtotal_annual_income" value="<?php echo $data['loan_co_applicants'][0]['monthly_income']*12;?>" class="form-control total_annual_income"></label>
            </div>
          </div>
        </div>
         
      </div>
       <div class="d-flex flex-column" >
          <div class="row" style="margin-top:3rem;">
            <div class="col-lg-6">
              <div class="d-flex flex-column">
                <div class="form-group row">
                  <div class="d-flex">
                    <label class="col-form-label col-lg-12 Issue_authority" >Issue date</label>
                    <label class="col-form-label col-lg-12"><input type="text" name="line_of_activity" class="form-control" value="" readonly></label>
                  </div>
                </div>
                <div class="form-group row">
                   <div class="d-flex">
                   <label class="col-form-label col-lg-12" >Maturity date</label>
                    <label class="col-form-label col-lg-12"><input type="text" name="line_of_activity" class="form-control" value="" readonly></label>
                  </div>
                </div>
              </div>
            <div class="form-group row">
              <label class="col-form-label col-lg-12" >Value or security</label>
            </div>
        </div>
     </div>
   </div>
 </div>
</div>
  </div>
  <!--  -->

</div>

  <div class="card bg-white" >
    <div class="card-body">
       <h3 class="card-title mb-3">Personal Information </h3>
     <p>We request for sanction of the loan of Rs <b><?php echo $data['amount'].' <i class="fas fa-rupee-sign"></i>'; ?></b>(rupees) <b><?php echo amountINWord($data['amount']) ?></b> Against the security of as describe above and such other securities as may be required by SAMRADDH BESTWIN MICRO FINANCE ASSOCIATIONfor personal use on the basis of information given above. I/We declare that all the particulars and information given in the  application  form  are  true,  correct  and  complete  and  that  they  shall  form  the  basis  of  any  loan SAMRADDH  BESTWIN MICRO FINANCE ASSOCIATIONmay decide to grant me/us. I/We further confirm that I/We have read the agreement/brochure and understood the contents. I/we agree that SAMRADDH BESTWIN MICRO FINANCE ASSOCIATIONmay take up such references and make such enquiries  in  respectof  this  application  as  it  may  deemnecessary.    I/We  further  agree  that  my/our  loan  shall  be  governedby  rules SAMRADDH BESTWIN MICRO FINANCE ASSOCIATION which may be in force from time to time.</p>
     <span class="right">Yoursfaithfully</span>

     <div class="d-flex  mt-4 justify-content-around">
        <div class="d-flex flex-column ">
          <span>....................................................................................</span>
          <span class="display-6 text-center">Applicant</span>
        </div>
        <div class="d-flex flex-column ">
          <span>.....................................................................................</span>
          <span class="display-6 text-center">Co-Applicant</span>
        </div>
        <div class="d-flex flex-column ">
          <span>.....................................................................................</span>
          <span class="display-6 text-center">Guarantor</span>
        </div>
     </div>
     <div class="d-flex  mt-4 justify-content-around " style="margin-top: 52px;">
      <div class="d-flex flex-column">
         <span><b><?php echo $data['loan_member']['first_name'].'&nbsp;'.$data['loan_member']['last_name']; ?></b></span>
        </div>
        <div class="d-flex flex-column">
        <span><b><?php echo $caDetails['first_name'].'&nbsp;'.$caDetails['last_name']; ?></b></span>
        </div>
        <div class="d-flex flex-column">
        <span><b><?php echo $data['loan_guarantor'][0]['name']; ?></b></span>
        </div>
     </div>
       <!-- Office Use Only -->
       <h3 class="card-title mt-3">(For Office Use Only)</h3>
    
     
      <div class="d-flex justify-content-between " style="margin-top: 52px;">
      <div class="d-flex ">
         
          <span class="display-6 ">Signature</span>
         <span>.....................................................................................</span>
        </div>
        <div class="d-flex ">
        
        <span>.....................................................................................</span>
        </div>
        <div class="d-flex ">
          
        <span>.....................................................................................</span>
        </div>
   
    </div>
    <div class="d-flex justify-content-between " style="margin-top: 52px;">
      <div class="d-flex ">
         
          <span class="display-6 ">Name</span>
          <div class="d-flex flex-column">
         <span>.....................................................................................</span>
           <span class="display-6 text-center">Prepared by Office</span>
       </div>
       
        </div>
        <div class="d-flex ">
        
         <div class="d-flex flex-column">
         <span>.....................................................................................</span>
           <span class="display-6 text-center">Branch manager</span>
       </div>
        </div>
        <div class="d-flex ">
          
     <div class="d-flex flex-column">
         <span>.....................................................................................</span>
          <span class="display-6 text-center">Head office</span>
       </div>        
     </div>
   
    </div>
     <div class="d-flex  mt-4 justify-content-around">
        <div class="d-flex flex-column ">
          <span>....................................................................................</span>
          <span class="display-6 text-center">Applicant</span>
        </div>
        <div class="d-flex flex-column ">
          <span>.....................................................................................</span>
          <span class="display-6 text-center">Co-Applicant</span>
        </div>
        <div class="d-flex flex-column ">
          <span>.....................................................................................</span>
          <span class="display-6 text-center">Guarantor</span>
        </div>
     </div>
    </div>
  </div>
  <div class="card bg-white" >
      <div class="card-body">
         <h3 class="card-title mb-3 text-center">LOAN AGGREMENT </h3>
       <p>This Loan Agreement (“agreement") is executed at Jaipuron  the  Date  mentioned    in    Clause    30    of    this    Agreement    Between SAMRADDH  BESTWIN MICRO  FINANCE  ASSOCIATIONincorporated  under  sub-section(8)  of  section  act,  2013  (hereinafter referred as “SAMRADDH  BESTWIN ”) and having its registered office at 114-115,  Pushp  Enclave,  Sector -5 ,  Pratap  Nagar  ,  Tonk Road, Jaipur ,(Rajasthan)(which expression shall unless repugnant  to  the  context  of  the  meaning  thereof  shall  mean  to include its legal representatives, successors in business and assigns) of the ONEPART.<br/>AND <br/>The ..........................................address(es)...................................... and  other  details are set  out in  clause 30,  (Who Is/  are hereinafter referred   to  collectively  as "Borrower" (which  expression unless  repugnant to  the    context   or   the   meaning    thereof   shall   mean    to    include  its' their legal representatives, heirs and permitted assigns) of the OTHERPARTY.</p>
       
       <p>The meaning  of  terms  used  in  this  Agreement  shall  the  equally  applicable  to  both  the  singular  and  plural  forms  of  the  terms.  Unless otherwise specified, reference to a Clause shall mean to that particular clause of this Agreement.</p>

       <p>WHEREAS,the Borrower has approached the SAMRADDH BESTWIN fora loan for his requirements.WHEREAS, on the basis of the Borrower's application and representations the SAMRADDH BESTWIN has agreed to  lend  to  and advance to the Borrower a loan on the terms and conditions, set out here it,below.Now THEREFORE THIS AGREEMENT WITTNESSETH AND IT IS HEREBY MUTUALLY AGREED BY AND BETWEEN THE PARTIESHERE TO ASFOLLOWS</p>
       <div class="line" style="line-height: 200%;" >
         <ol>
          <li>Definitions <br/>
            <span>In this Agreement unless the meaning or context otherwise requires, the following words and expression shall have the meaning assigned to them below:</span>
            <ol>
              <li><strong>Agreement </strong> means and includes this Agreement and the  attachment(s)  annexedhereto.   Agreement  shallinclude application, supplementary  Agreement(s),  modifications,  alterations,  addendum,  attachments  andschedulessubsequently  executed  during  the  tenure of thisAgreement.</li>
              <li><strong>Borrower </strong>means and includes any person(s) to whom the SAMRADDH BESTWIN hasagreed to grant the Loan and who has received the Loan pursuant this Agreement as mentioned herein.</li>
              <li><strong>Delayed Payment Charges </strong>  means fees assessed for a payment delayed beyond due date of the Equated Monthly Installment.</li>
              <li><strong>Effective Date </strong>means unless specified otherwise. the date on which the cheque for the loan amount issue by  the SAMRADDH BESTWIN to the Borrower, as mentioned in Clauses 30 of this Agreement.</li>
              <li>Equated  Monthly installments (EMI’s)" means and includes the amount of monthly  payment  required  to  repay  the  principal  amount  of Loan, interest and any other monies due and payable by the Borrower to the SAMRADDH BESTWIN inaccordance with Clause30 to this Agreement.</li>
              <li><strong>Events of Default</strong>  means and includes the happening of any one or more of theevents ofdefaultas stipulatedin Clause20 of the Agreement.</li>
              <li><strong>Free Look Period</strong>means the interest free period of seven days from the date of issue of cheque by the SAMRADDH BESTWIN towards the Loan where in the Borrower may withhold the cheque from being deposited into the Borrower’s bank account.</li>
              <li><strong>Loan</strong>means the Loan of an amount as set out in Clause 30 of this Agreement,  including any  additional  Top-up  loan availed in future by the Borrower, and includes all interests, costs.-charges or any other expenses related to the Loan.</li>
              <li><strong>Interest</strong> means and includes  rate of interest  chargeable by the SAMRADDH BESTWIN  fromthe Borrower on the  Loan  in accordance with Clause 30 to this Agreement.</li>
              <li><strong>Outstanding Balance</strong> means the Loan outstanding and unpaid interest, costs, charges andexpenses.</li>
              <li><strong>Pre Equated Monthly installment (PEMI)</strong> in case the due date of First EMI of the loan is later than 30 days from the date of disbursement of the loan, a Pre-EMI charge would be payable by the customer and the same would be deducted upfront from the disbursal amount.</li>
              <li><strong>Prepayment</strong>means premature repayment as per the terms and conditions laid down by the SAMRADDH BESTWIN in that behalf and in force at the time of repayment.</li>
              <li><strong>Post  Dated  Cheques (PDCs</strong>means  the  cheques drawn by  the  Borrower  in favorof  the SAMRADDH BESTWIN,for  making  payments as described in Clause 30.</li>
            
             
              <li><strong>Repayment</strong>means and includes repayment of all dues in respect  of  the  Loan,  which  shall  be  construed  to  include the principal amount of loan, interest, all   taxes, levies, charges, legal    fees, expenses and   costs   etc. provided    for  in  this Agreement read with Clause 30.</li>
              <li><strong>Standing Instruction</strong> hereinafter referred to as "SI", means written instructions issued by the borrower to its bank to debit the account of the Borrower maintained with the bank for an amount equal to the EMIs for payment  to the  SAMRADDH BESTWIN  for repayment of the loan facility  as more particularly set out in clause8(a).</li>
              <li><strong>Electronic Clearance Service (Debit Clearing)</strong> hereinafter referred to  us “ECS” means  Debit  Clearing    Service notified by the Reserve Bank of India, participation in Which has been consented to in writing by the borrower for facilitating payment of EMIs as more particularly setout in clause 8(a).</li>
         </ol>
          </li>
          <li>The SAMRADDH  BESTWIN shall  lend to  the Borrower the  sum specified    Clause   30   hereto   for   the   Borrower's  personal  needs the SAMRADDH  BESTWIN agrees to grant to  the Borrower a  Loan for  the   amount as set out in  Clause 30  on the  Terms    and conditions contained  in  this  Agreement.  The SAMRADDH BESTWIN  shallbe  liberty  to  decide  the  actual aggregate amount of the Loan to be granted under this Agreementandhence. for all  purpose the amount actually disbursed shall be the amount of the Loan and the Borrower shall not raise and objections therefore or be discharged from any of  his obligations there  undermerelybecausetheamountappliedfor.Obligationsthereundermerelybecausetheamountdisbursed.</li>
          <li>The Loan shall carry interest at the rate as determined in Clause 30of thisAgreement, which can be varied from time to time  at the solediscretion of  theSAMRADDHBESTWIN.   For purpose of this Agreement theinterest shall be charged by the SAMRADDHBESTWIN on monthly basis, starting from the I of every month (including the month of disbursal of loan by the Agreement or date of issue of cheque by The SAMRADDH BESTWIN.), irrespective of the Effective date of this of agreementor date of issue of cheque by the SAMRADDH BESTWIN.</li>
          <li>A demand promissory  note  has  been executed by the Borrower  in favor  oftheSAMRADDH  BESTWIN for  themaximum amountof Loan, provided  that  the  promissory  note  shall  cover the actual Loan amount  disbursed  as  also  all  interest and other  coststhereon.</li>
          <li>The Borrower confirms having perusedunderstood and agreed to the terms and conditions of this Agreementand shallrepay the Loan and interest thereon in EMIs as per clause30.</li>
          <li>The Borrower is required to pay all taxes arid charges that may be levied in connection with the Loan. The Borrower also agrees also that the EMI will be increased by any incremental taxes, whether by way ofsalestax, exciseduty or any other taxes, hereinafter on this transaction with retrospective orprospective effect.</li>
          <li>All payments to be made by  the SAMRADDH  BESTWIN to  the Borrower   under  or  in  the   terms of  this  Agreement  shall  be  made by Cash or Cheque duly crossed and marked "A/c Payee  only"  and  d  the  collection  charges,  if  any,  in  respect of all  such cheques will  have to  be borne by the Borrower. The Loan amount  shall be disbursed to the Borrower net of all initial payments towards PEMIs, EMIs, AdvanceEMIsTransactioncharges, etc.  As perClause 30 of this Agreement.</li>
          <li>The EMI shall comprise of principal and interest  calculated   on   the    basis   of   monthlyrests   at   the   rate    applicable and rounded off to the next  whole rupee-Interest   and   any    other    charges   due   and   payable   by   the   Borrower  shall be computed  on  the  basis  of  Calendar  year.  Since  the  interest  for  each  EMI would  be  calculated  on  a  reducing balance,the interest component for each EMI may vary,although,EMI will be identical foreach installment.
            <ol>
              <li>(a) The  Borrower(s) will repay the  Loan  as  stated in  Clause  30,  subject however that the  due  date  of  paymentof first EMI shall  in all cases  be  the.................day of  followingmonth  unless  otherwise  decided by the SAMRADDH  BESTWIN . The payment of EMI will be through  PDCs, favoring "SAMRADDH BESTWIN  MICROFINANCE  ASSOCIATION"  Electronic Clearance Scheme    and/ or Standing Instructions.Where    applicable the Borrower shall also    pay    to the SAMRADDHBESTWIN PEMIas perClause 30.  The Borrower is required to payalltaxes and charges  that nm,-be  levied in  connection with the  Loan.EMI shall be computed oil the basis of reducing balance of the principal outstanding amount.</li>
              <li>(b)  The Borrower  also  agreed to  pay such amount as non-refundable processing fee   as   may be   determined   by   the SAMRADDH  BESTWIN from  time to time and the  same shall be  due   from    the Borrower   once   requestfor    Loan  is received by the SAMRADDH  BESTWIN .  It  is  clarified  that  in  the  event the Borrower  chooses  not  to  avail  the  Loan  then  the Borrowers shall remain liable to pay to the SAMRADDH BESTWIN theprocessingfee.</li>
            </ol>
          </li>
          <li>The SAMRADDH BESTWIN on its part is not obligated to provide any statement (s)  of  die  Loan  account  to  the  Borrower during the course of this Agreement unless specifically  requested b}   the   Borrower. The Borrower agrees  topay  such charges for this service as may be determined by the SAMRADDH BESTWIN from wile to time.
            
          </li>
          <li>The delay in the payment of EMI or PEMI shall  render the Borrower liable to delayed  payment charges as Mentioned in Clause 30 of this Agreement without prejudice to the other    rights of the SAMRADDH  BESTWIN. The aforementioned charges would not affectthe Borrower’s obligation ofstrict  compliance  withtherepaymentas mentioned in Clause 30 of this Agreement, which the  Borrower here by acknowledges and agrees to be an essential condition for the grant of the Loan.</li>
          <li>
           (a) In case of dishonor of any cheque/ SI/ ECS or any   other   instrument    issued   by   the   Borrower,   the   Borrower    agrees and undertakes to pay the  same to the SAMRADDH  BESTWIN on demand along with any additional  charges as mentioned in Clause 30  in respect of each of such cheque/ SI/ ECS or any  other instrument dishonored. This amount is in addition to the delayed  payment  charges  specified in Clause  10.   No notice  reminder or intimation     will be given regarding the presentation of these  cheques/ SI/ECS or other instrument. This  would be withoutprejudice to  the right  of  the SAMRADDH  BESTWINunder  the NegotiableInstruments Act. 1881. Andit’sother rights under thisAgreement. The Borrower shall not be entitled to and agrees not to set off, with or deduct any amount from the payment due to the SAMRADDH BESTWIN underthisAgreement.
           <ol>
             <li>(b)  "The  Borrower  shall not   give any instructions to   the SAMRADDH  BESTWIN not to   deposit the PDCs given by him and  in   case he does so it shall presumed that the same has been done to avoid prosecution under section 138 of the Negotiable Instruments Act, 1881. The Borrower further undertakesthat his consent to participate in  the  ECS/ SI, mode for payment of the EMIs shall not be revoked during the tenure of this Agreement  except with the approval of   the  SAMRADDH  BESTWIN . In case the  Borrower  revokes  his  consent to   participate in  the  ECS/SI mode,  it maybe presumed that the same has been done to cheat the SAMRADDH BESTWIN and shall  make  the  Borrower  liable  for  criminal action under the Indian Penal Code and any other law for the time being, inforce.</li> 
           </ol>
         </li>
            <li>Presentment for payment,  noting: and protest is also hereby unconditionally and irrevocably  waived by <strong> the Borrower.  Such PDCs shall </strong> be drawn from a scheduled bank situated in a town or city where office of the SAMRADDH BESTWIN <strong>is located-</strong></li>
            <li>Out <strong>of the upfront EMN  to be paid by </strong> the Borrower to  the SAMRADDH  BESTWIN, one EMI shall be adjusted in  the first month <strong>and  the  remaining  EMI  shall  be  adjusted  towards  the  last  EMI's </strong> in  reverse  order  payable  by  the  Borrower  to  the SAMRADDH BESTWIN.<strong>No interest shall be payable </strong>  by  the <strong>
            SAMRADDH BESTWIN to</strong> the Borrower on amount received towards installments  in advance as specified in clause30-</li>
            <li>The Borrower will be required to pay through deduction against  salary  (DAS)in those cases where his employer is registered with the<strong> SAMRADDH  BESTWIN for  DAS.</strong> If  at  any <strong>time</strong> during the tenor of the Loan,  the Borrower  terminates hiscurrent employment, he will be required to provide PDCs for the Outstanding Balance.</li>
            <li>The Borrower shall pay to the SAMRADDH BESTWIN the sum set out in Clause 30 as non-refundable transactioncharge.Such charges upfront transaction charge will not be refundable under any circumstances.</li>
            <li>Should the  Borrower wish  to  swap;  interchange the post dated cheques from one  bank to another, the  Borrower  may  do so By pay in swap charges as mentioned in Clause 30 to thisAgreement</li>
            <li>The Borrower  agrees and undertakes toutilize the loan only for his personal  requirements  and shallnot use the loan for  any illegal, antisocial, speculative purpose including but not limited to participation in stock markets/IPO's etc.</li>
            <li>The  Borrower  may  after  the  payment  of  minimum  of  Six  EMIs  (excluding  the  advance  EMIs  if  any)  prepay  the loan bygiving the SAMRADDH  BESTWIN at least 7 days prior notice in writing. The SAMRADDH BESTWIN shallbe entitled to levy  aprepaymentcharge at the rate stipulated in Clause 30 on the amount so prepaid. Prepayment amount will bethe principal amount along with all outstanding amounts (s) due and payable at the end of the month in which the prepayment is made, in addition to theprepayment charges. The prepayment shall take effect when cash has been paid      by the Borrower or cheques/ SI/ ECS issued by the Borrower have been cleared. The interest and any other charges, etc. would be livable till the end of the month in which tilt prepayment noticeexpires.
              <ol>
                <li>(a) The Borrower  further  agrees that  in  the  event of foreclosure/  enhancement of Loan or upon expiry of-the   Agreement  the SAMRADDH  BESTWIN may  destroy  or  cancel  the  PDC's  provided  as  security  by  the  Borrower.  Unless  specifically  requested  by  the Borrower in writing, the SAMRADDH BESTWIN willnot be under an obligation to return the said PDC's to the Borrower.</li>
              </ol>
            </li>
            <li>The SAMRADDH BESTWIN mayin it sole discretion and  upon  serving  seven days notice to the Borrower terminate this Agreement  executed  hereto.  Upon termination the  principal  amount  of  the  Loan,  allaccrued interest  thereonand  anyother  charges outstanding will become payable forth with by the Borrower to the SAMRADDH BESTWIN.
             
            </li>
            <li>If one or more of theevents specified in this Clause shall have occurred, the SAMRADDH BESTWIN. may,  by  a seven days  notice  in writing to the Borrower,  declare that  the principal amount of the loan and  all accrued  interest thereon  has   I    become  payable forthwith by the Borrower to the SAMRADDH BESTWIN and the SAMRADDH BESTWIN may at its  sole discretion terminate this Agreement.
              <ol>
                <li>The   Borrower   failing   to   pay   the   Loan   or   any   fee,   changes   or   costs   in   manner   contained   or   any   EMI   or   any other amount(s)duehereunderremainsunpaidforaperiodofthirtydaysfromthedateonwhichitisdueor:</li>
                <li>The  Borrower  committing  a  breach,  if  any,  of  the  terms    and  conditions    and    covenants    herein    contained  or    has  made  any misrepresentation to the SAMRADDH BESTWIN ;or</li>
                <li>The  Borrower  suffers  an  adverse  material  changes in the financial condition  from  the  date  hereof    and    as    a   result thereof  the SAMRADDH BESTWIN deems itself to be insecure;or</li>
                <li>The Borrower has been declared as insolvent;orProceedings for misconduct are taken against the Borrower;or</li>
                <li>TheBorrowerfailstofurnishanyinformationordocumentsthatmayberequiredbytheSAMRADDH BESTWIN ;or</li>
                <li>Borrower enters into any composition with his /her other creditors or the Borrower commits an act of insolvency; or The</li>
                <li>BorrowerdefaultsonanyofthetermsandconditionsofanyotherloanorfacilityprovidedbytheSAMRADDH  BESTWIN or  any  other  lender  to the Borrower; or</li>
                <li>Thereexistsanyothercircumstance,which,.inthesoleopinionoftheSAMRADDH BESTWIN ,jeopardizestheSAMRADDH</li>
                <li>BESTWIN interest.</li>
                <li>The Borrower expressly accepts that if tile Borrower fails to pay any monies when due  or  which  may  be declared due prior to the  date  when  it  would  otherwise  have  become  due  or  commits,  any  other  default  under  any Agreement  (including this Agreement) with the SAMRADDH  BESTWIN under  which the Borrower(s) is/ are enjoying  any financial/ credit/  other  facility;  then  in  such event  the   SAMRADDH  BESTWIN shall,    without    prejudice    to   any   of   its   specific    rights under each of the Agreements, be absolutely entitled to exercise all or any of its rights  under any of the Agreements entered by the Borrower with the SAMRADDH BESTWIN and/  or  withhold  applicable  amount/  documents  of  the Borrower(s)  and or terminate  without  any  notice  and/ or    all    the  Agreements  (including  this  Agreement)  with  the SAMRADDH BESTWIN at the sole discretion 6f the SAMRADDH BESTWIN . Further  it  is  clarified  that  in the  eventofa  defaultinrepaymentof Loan by the Borrower the SAMRADDH BESTWIN  shallbe entitled to create a charge over any other security. Provided by the Borrow in relation to any other loanavailed from the SAMRADDH BESTWIN.</li>
              </ol>
            </li>
            <li>Notwithstanding anythingstated elsewhere in  this Agreement, the Borrower'soutstanding   balance    shall   be payable by the Borrower to the SAMRADDH BESTWIN on  demand-The  SAMRADDH BESTWIN may at any time, at its  sole discretion, and without  assigning  any  reason,  recall the said-Loan  or upon the Borrower to pay the outstanding  Balance  and thereupon the Borrower shall, within seven days of being so called upon, pay to the SAMRADDH BESTWIN the outstanding balance without any delay, demur orprotest.</li>
            <li>The SAMRADDH BESTWIN  shallbe  entitled  to  transfer or assign  any  of  its  obligations herein. The SAMRADDH BESTWIN  mayat any  time  transfers  or  assigns  any  of  its  rights, benefits  orobligations  hereinto  any  party  without the  consentof  theBorrower.    The Borrower shall fulfill and perform all this obligations to such transferee/assignee.</li>
            <li>Any  delay  in  exercising or omission  to  exercise  any  right,  power  or  remedy  accruing  to  the SAMRADDH BESTWIN  underthis Agreement  or  any  other  Agreement  or  document  shall  not  impair  any  such  right,  power  or  remedy  and  shall  not  be  construed  to  be    a waiver thereof or any acquiescence by it in any default and shall not effect or impair any right power     or remedy or the SAMRADDH BESTWIN in respect of any otherdefault.</li>
            <li>The Borrower expressly recognizes and accepts that the SAMRADDH BESTWIN shall  without  prejudice  to  its rights to perform  such activities itself or through its officials or servants, be absolutely entitled and have full powers and authority to appoint one or more third parties  or  the SAMRADDH  BESTWIN choice  and  to  transfer  and  delegate  to  such  third  parties  the  right and  authority  to  collect  on behalf of the SAMRADDH BESTWIN the installments, charges unpaid amount(s) and other sums due to  the SAMRADDH BESTWIN under thisAgreement.</li>
            <li>The Borrower represents that he has understood the terms and conditions of this Agreement. In the event of a default  and/ or breach of the terms and conditions of this Agreement, the SAMRADDH BESTWIN shall have a right of redresses from any Court of Law or any other appropriate forum.
              <ol>
                 <li>(a) I/  We  have read the entire  Agreement  constitutingof  33 clauses including the Loan Details  givenin Clause  30whichhave  been filled in my presence. I/ We shall be bound by all the conditions including the Loan Details. The aforementioned Agreement and other documents have been explained to  me/  us  in  the  language  understood  by  me/us and I/ We have understood the entire meaning of the various clauses.</li>
              </ol>
            </li>
            <li>The Borrower and the SAMRADDH BESTWIN agreeto comply, jointly and severally, with all applicable laws and regulationsfrom time to time in force including any amendments, modification or change there of which may be made to any such laws andregulations.
           
              </li>
          <li>That the Borrower has paid all public demands such income  Tax/All  Other taxes revenue  payable Government  of  India  or to the Govt. of any state or to any local authority and that at  present there are  no  arrears of such taxes and  revenue due andoutstanding</li>   
          <li>The  SAMRADDHBESTWIN shallbe entitled to send  any notice to  the Borrower by prepaid post at  theaddresses last  known to it. Any notice sent will be deemed to have  been  received  three(3) days after the date of posting.The Borrower  shalli mmediately  intimate the SAMRADDH BESTWIN ofany change in his add ress. Any notice to  be sent to the SAMRADDH BESTWIN bythe Borrower shall be sent by prepaid post at the addresses mentioned above.</li> 
         <li>This Agreement shall be construed in accordance with the laws in force in India. If any controversy or dispute should arise between the parties in  performance,    interpretation  orapplication   of  the  Loan  Agreement involving any matter, the same shall be submitted to arbitration of a single arbitrator,  nominated by the SAMRADDH  BESTWIN whose decision  shall be final under the provisions of the   Indian   Arbitration and  Conciliation Act,  1996. The arbitration proceedings shall  beheld atJaipur. Any or all disputes  arising out of thisAgreement shall be subjected to the exclusive jurisdiction of the courts atJaipur(Rajasthan).</li>
         <li>All the loan parameters, includingthe amount of loan, rate of interest, date of loan, number of EMIs and other applicable charges, fees, costs, etc. are specified in the Annexure to the Agreement. A copy of the Annexureis attached with this Agreement and a copy of the Annexure is provided to the Borrower for his reference and record.</li>
         <li>Free  Look Period: The SAMRADDH BESTWIN shalloffer to  the Borrower a "Free  Look Period” ofseven(7)days  withinwhichperiod the Borrower  shall have the option not to avail of the Loan. In such ail event, the Borrower  shall forthwith return to the SAMRADDH BESTWIN, the un-banked Cheque in original as issued by the SAMRADDH BESTWIN towards the loan. For the sake of clarity, in the event the Cheque issued by  the   SAMRADDH  BESTWIN towards the    Loan is deposited   bythe Borrower in  its bank  account  within the "Free  Look Period", or   the  Borrower   fails   to  collect   the   Cheque  issued by the SAMRADDH BESTWIN towards the  loan within the "Free Look Period", the same would amount to theunequivocal  acceptance by the Borrower of   the    Loan along  with the   associated terms  and conditions as   detailed       in this Agreement." However, this Free Look Period shall not be available to the existing customer who has been providedTop Up or additional Loanfacilities.
          <ol>
            <li>(a) itis further clarified that in addition to the provisions of Clause 30 herein above, the Borrower shall be entitled to pay toTheSAMRADDH BESTWINtowards each loan anon-refundable. Processing Fee as may bedetermined by the SAMRADDHBESTWIN fromtime totime-Provide further that in tire event the Borrower Chooses not to avail the Loan, during the Free Look Period, and returns to the SAMRADDH BESTWIN (tie Loan,  during  the  Free  Look  Period,  and  returns  to  the SAMRADDH BESTWIN  theLoan  even  the  Borrower  shall  remain  liable  nay  to  the SAMRADDH BESTWIN theProcessing,fee</li>
          </ol></li>
          <li>Collections: "The Borrower expressly recognizes and  accepts that the SAMRADDH BESTWIN shall, without prejudice to  its right to perform such activities itself or through its officers or employees, be entitled,  and has full  power and authority so  to do, to appoint one or more third parties asthe SAMRADDHBESTWIN. may select and  to  delegate to  such  third party all or any or  its functions, rights and powers under this  Agreement  relating to  the administration ofthe  Loan including the right and authority to collect and receive   on  behalf   ofthe  SAMRADDH BESTWIN. from   the Borrower all due and unpaid PEMIs and other amounts due  by  the  Borrower  under  this  Agreement  and  to  perform and execute all lawful acts, deeds, matters and things connected therewithand    incidental    thereto    including  sending  notices, contacting the Borrower, receiving   case)   cheques/drafts/   Mandates    front   the Borrower and giving  valid and   effectual  receipts and   discharge to   the  Borrower. For  the  purposes  aforesaid orfor any other purpose at the discretion of the SAMRADDH BESTWIN , shall be  entitled   to  shall   be  discuss   such   third  parties all nec essa ry• or relevant in formation  pe rtaining to the Borrower and    the   Loan    and   the   Borrower    hereby consent to such disclosure by the SAMRADDH  BESTWINNotwithstandingthe    above,   the   Borrower    expressly    accepts and authorizes the SAMRADDH BESTWIN (and' or any such third party as  the SAMRADDH BESTWINmay  select)  to  contact third parties (Including the family members of the Borrower)  and  disclosure ail   necessary   or   re levant  in formation  pe rtaining to the Borrower and the   Loan  arid   the  Borrower    hereby  consents   to   such   disclosure  by the SAMRADDH BESTWIN (and/ or any such third party as the SAMRADDH BESTWIN mayselect).</li>
          <li>Disclosure of information to CIBIL : Notwithstanding any of the foregoing, the Borrower understands that as a pre-condition relating to grant of the Loan  to  the  Borrower  the  SAMRADDH BESTWIN requires theBorrower's  consent for the disclosure by the SAMRADDH BESTWIN of information and  data relating to the Borrower,   of   the   credit    facility  availed of to be availed by the Borrower, obligations, assured/to    be  assured  by  the  Borrower  in  relation thereto and default, if any, committed by theBorrower in discharge thereof.
            <span>Accordingly, the Borrower hereby agrees and gives consent for the disclosure by the SAMRADDH BESTWIN of all or any such-y.1</span>
            <ol>
              <li>Information and data relating in the Borrower:</li>
              <li>The Information or data relating to anycredit facility availed oft to be availed by the Borrower-. And</li>
              <li>Default, if any, committed by the Borrower in discharge of such  obligations as the SAMRADDH  BESTWIN may  deem appropriate and necessary to disclose and furnish to Credit  information Bureau  (India) Limited   and    any other agency authorized  in  this  behalf  by  the Reserve Bank ofIndia.</li>
            </ol>
           
          </li>
          <li><span>The Borrower further declares that the information and data furnished by the Borrower to the SAMRADDH BESTWIN are true and correct.</span>
            <ol>
              <span>The Borrower also understands that:</span>
              <li>The Credit  information Bureau (India)  Limited and any other    agency   so   authorized   may   use,    process   the said information and data disclosed by the SAMRADDH BESTWIN in the manner as deemed fit bythem: and</li>
              <li>The  Credit  information  Bureau  India  limited  and  any  other agency so  authorized may furnish  consideration the processed information and data of products thereofprepared  bythem,  tobanks/financial  institutions  andothercredit  grantors  or registered users, as may be specified by the Reserves Bank in-this behalf-</li>
            </ol>
          </li>
          <li>
            <span>Additionally, the Borrower hereby also agrees and gives his unequivocal consent for the disclosure by the SAMRADDH BESTWIN ofall any information/ documents or data as above for protecting its interests to:</span>
            <ol>
              <li>Income Tax authorities,  Credit  Rating  Agencies    (for  the  purpose  of  credit  reference  checks)  or    any  other  Government    or Regulatory Authorities/Bodies/ Departments/ Authorities as and when so demanded-and</li>
              <li>To any court or judicial, statutory or regulatory authority/ tribunal/arbitrator pursuant to any order/ direction to this effect, as and when required.Further, the SAMRADDH BESTWIN shall also be entitled to share all or any information/ documents or data as above with any of its sister concerns, its associates group companies, agents and with third parties as may be deemed appropriate by the.</li>
            </ol>
          </li>
         </ol>
         <p>SAMRADDH  BESTWIN. The  Borrower  further acknowledges that  no  prior  approval  will  be  required  by  the SAMRADDH  BESTWIN \from  the Borrower  in  the  event  Borrower  desires  to  avail  or  the SAMRADDH  BESTWIN offers  any  additional  financial facility  (its) including new products infuture.</p>
         <p>The SAMRADDH BESTWIN shallbe entitled to exercise the above right of disclosure without being required to issue any further notice in this respect to the Borrower. The Borrower and the Guarantor specifically waive the privilege ofprivacy, privacy and defamation.</p>
         <p>Acceptance: I/We hereby confirm to have read the entire Agreement including but not limited to the Lose details, inter aria, providing for interest  rates  in  Clause  30  of  the  Agreement.  The  material  details  in  Clause  30  hale been  filled  in  my/  ourpresence  and  contents  of  the Agreement have been explained in the language understood by me/us.</p>
         <p>In witness whereof the parties hereto have signed this Agreement in acceptance of all terms and conditions on the day, month and yearas mentioned in Clause 30 above.</p>
         <p>For Samraddh Bestwin Micro Finance Association. </p>
         <p>Borrower</p>
          <div class="d-flex  mt-4 justify-content-around mb-2">
                  <div class="d-flex flex-column ">
                    <span>....................................................................................</span>
                    <span class="display-6 text-center">Applicant</span>
                  </div>
                  <div class="d-flex flex-column ">
                    <span>.....................................................................................</span>
                    <span class="display-6 text-center">Co-Applicant</span>
                  </div>
                  <div class="d-flex flex-column ">
                    <span>.....................................................................................</span>
                    <span class="display-6 text-center">Guarantor</span>
                  </div>
                </div>
              <div class="thumb_impression"></div>
              <div class="d-flex justify-content-between " style="margin-top: 52px;">
                <div class="d-flex ">
                  <span class="display-6 ">Name</span>
                 <span>.....................................................................................</span>
                </div>
                <div class="d-flex ">
                
                <span>.....................................................................................</span>
                </div>
                <div class="d-flex ">
                  
                <span>.....................................................................................</span>
                </div>
       
              </div>
                <div class="d-flex flex-column" style="margin-top: 52px;">
                  <div class="d-flex justify-content-around ">
                    <span class="display-6 ">Witness1</span>
                    <span>................................................................................................................................</span>
                    <span>...................................................................................................................................</span>
                 </div>
                  <div class="d-flex justify-content-around mt-4">
                    <span class="display-6 ">Witness2</span>
                    <span>................................................................................................................................</span>             
                    <span>...................................................................................................................................</span>
                  </div>
                  <div class="d-flex justify-content-around ">
                    <span>Name</span>             
                    <span>Signature</span>
                  </div>

                   <div class="d-flex justify-content-between mr-4 mt-4" >
                    <span>Date  <span>......................</span></span>             
                    <span>Place <span>......................</span></span>
                  </div>
                </div>
               

      </div>  
       </div>
  </div>
  <div class="card bg-white col-md-12">
                <div class="card-body">
                  <h3 class="card-title">ANNEXURE</h3>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Type</th>
                        <th>Amount / Percentage</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Amount of Loan</td>
                        <td><b><?php echo $data['amount'].' <i class="fas fa-rupee-sign"></i>'; ?></b></td>
                      </tr>
                      <tr>
                        <td>Rate of Interest</td>
                        <td>
                        <input type="text" name="" class="form-control  col-md-4">
						<?php             
                        /*if($data['emi_option'] == 1){
                            $roi = $data['due_amount']*$data['ROI']/1200;       
                        }elseif($data['emi_option'] == 2){
                            $roi = $data['due_amount']*$data['ROI']/5200; 
                        }elseif($data['emi_option'] == 3){
                            $roi = $data['due_amount']*$data['ROI']/36500;
                        } 
						$principal_amount = $request['deposite_amount']-$roi;  
						 $dueAmount = $mLoan->due_amount-round($principal_amount);
						
						
						echo $roi;*/
                        ?>  
                        </td>
                      </tr>
                      <tr>
                        <td>Interest Amount</td>
                        <td><input type="text" name="" class="form-control  col-md-4"></td>
                      </tr>
                      <tr>
                        <td>Insurance provisions</td>
                        <td><input type="text" name="" class="form-control  col-md-4"></td>
                      </tr>
                      <tr>
                        <td>PenalCharges/Overdue Charges</td>
                        <td><b><?php echo $data['file_charges']; ?></b></td>
                      </tr>
                     
                       <tr>
                        <td>Additional Interest amount payable</td>
                        <td><input type="text" name="" class="form-control  col-md-4"></td>
                      </tr>
                       <tr>
                        <td>Date of Loan disbursed</td>
                        <td><b><?php  
						
						if(!empty($data['approve_date'])){
						      $approve_date = date("d/m/Y", strtotime($data['approve_date']));
						       echo  $approve_date;
							  } 
							  else{
								  
							  }
						
						?></b></td>
                      </tr>
                       <tr>
                        <td>Number of EMIs</td>
                        <td>
                        <b><?php
                        if($data['emi_option'] == 1){
                            $tenure =  $data['emi_period'].' Months';
                        }elseif ($data['emi_option'] == 2) {
                            $tenure =  $data['emi_period'].' Weeks';
                        }elseif ($data['emi_option'] == 3) {
                            $tenure =  $data['emi_period'].' Days';
                        }
						 echo  $tenure;
						?></b>
                        
                        </td>
                      </tr>
                       <tr>
                        <td>EMI Amount</td>
                        <td><b><?php echo $data['emi_amount']; ?></b></td>
                      </tr>
                      <tr>
                        <td>Due date of EMI</td>
                        <td><input type="text" name="" class="form-control  col-md-4"></td>
                      </tr>
                       <tr>
                        <td>Processing charges</td>
                        <td><input type="text" name="" class="form-control  col-md-4"></td>
                      </tr>
                      <tr>
                        <td>Any other charges</td>
                        <td><input type="text" name="" class="form-control  col-md-4"></td>
                      </tr>
                      <tr>
                        <td>Flat Charge for Cheque/ECS/SI dishonor</td>
                        <td><input type="text" name="" class="form-control col-md-4"></td>
                      </tr>
                    </tbody>
                  </table>
                  <hr>
                  <h3 class="card-title">EQUATED MONTLY INSTALLMENT SCHEDULE (EMI):</h3>
				  <div class="d-flex">
                  <table class="table">
                    <thead>
					
                    <tr>
					
                    <th style=""> S.No</th>
                    <th style="">Emi Due Date</th>
                    <!--<th style="width: 10%"> Transaction Date</th>-->
					<th>Amount</th>
					
                  
					
                    <!--<th>Payment Mode</th> 
                    <th>Description</th> 
                    <th>Penalty</th> 
                    <th>Deposit</th>
                    <th>ROI Amount</th>
                    <th>Principal Amount</th>
                    <th>Opening Balance</th>-->
                    <!-- <th>Action</th> -->
                    </tr>
                    </thead>
                    <tbody>
                    <?php  //print_r($EMI); ?>                    
					
					
					
						@for($i=1;$i<=$data['emi_period'];$i++)
						
                      <tr>
					  
						@if($data['emi_option'] == 3)
						
						 <td><b><?php echo $i; ?></b></td>
						<td><b><?php echo date('d/m/Y', strtotime($data['created_at']. ' + '.($i).' days'));  ?></b></td>
						 <td><b><?php echo $data['emi_amount']; ?></b></td>
						
						
						
						
						@endif
						@if($data['emi_option'] == 2)
						<td><b><?php echo $i; ?></b></td>
						<td><b><?php echo date('d/m/Y', strtotime($data['created_at']. ' + '.($i).' weeks'));  ?></b></td>
						<td><b><?php echo $data['emi_amount']; ?></b></td>
						@endif
						@if($data['emi_option'] == 1)
						<td><b><?php echo $i; ?></b></td>
						<td><b><?php echo date('d/m/Y', strtotime($data['created_at']. ' + '.($i).' months'));  ?></b></td>
						<td><b><?php echo $data['emi_amount']; ?></b></td>
						@endif
						
						 
						
                       @endfor
                         
                         
                      </tr>
					
                     
                    </tbody>
					</table>
                  
				  </div>
                </div>
  </div>  

  <div class="card bg-white" >
      <div class="card-body">
         <h3 class="card-title mb-3 text-center">PROMISSORY NOTE</h3>
       <p>ON DEMAND, I/We the undersigned jointly and severally, unconditionally promise to pay to SAMRADDH BESTWIN MICRO FINANCE ASSOCIATION or Order, the sum of Rs <b><?php echo $data['amount'] ?> </b>(Rupees)  <b><?php echo amountINWord($data['amount']) ?></b> With interest there on at the rate of ....................Percent per annum or at a rate which may from time totime be assignedby the SAMRADDH BESTWIN forvalue received.  Presentment for payment noting and protest ofthis note are hereby unconditionally and irrevocably waived.</p>
       <p>Where there are more than on signatory here to the liability of each signatory is joint andseveralwith others in case of the signatory being a partner of the Firm,  all other Partners of  the Firm are also  jointly andseverely</p>
       <div class="d-flex flex-column "> 
       <span class="mb-2">Date: <b><?php echo date('d/m/Y', strtotime($data['created_at']));?></b></span>
       <span>Place: .......................................</span>
        <span class="display-5 text-right mx-4">Applicant</span>
        <div class="d-flex justify-content-end">
         <div class="thumb_impression"></div>
       </div>
       <span class="mb-2 text-right mt-4">Co-Applicant................................................................</span>
     </div>
    </div>

  </div>   
   
  <div class="card bg-white col-md-12" >
      <div class="card-body">
        <h3 class="card-title mb-3 ">(For Office Use Only)</h3>
        <h3 class="card-title mb-3 ">LOAN REPAYMENT CUM DISCHARGE VOUCHER</h3>
        <div class="d-flex flex-column "> 
           <div class="d-flex justify-content-between">
              <span class="mb-2" style="width:50%;">Loan Account No:<b><?php echo $data['account_number']; ?></b></span>
              <span> Date: .......................................</span>

           </div> 
           <div class="d-flex justify-content-between">
              <span class="mb-2">Name of Borrower: <b><?php echo $data['loan_member']['first_name'].'&nbsp;'.$data['loan_member']['last_name']; ?></b></span>
              <span>  Client Id: <b><?php echo $data['loan_member']['member_id']; ?></b></span>
           </div> 
           <div class="d-flex justify-content-between">
              <span class="mb-2">Total Loan amount:  <b><?php echo $data['amount'].' <i class="fas fa-rupee-sign"></i>'; ?></b></span>
              <span>Interest loan amount: .......................................</span>
           </div> 
           <div class="d-flex justify-content-between">
              <span class="mb-2">Deduction, if any:   ..................................</span>
              <span> Net dues: .......................................</span>
           </div> 
           <div class="">
              <span class="mb-2">Details of Amount repaid: ..................(As per Annexure)</span>
            
           </div> 
           <div class="">
              <span class="mb-2">Details of Amount adjusted against:  ..................................</span>
           </div> 
           <div class="d-flex justify-content-between">
              <span class="mb-2">Account No. ..................................</span>
              <span>Balance if any: .......................................</span>
            
           </div> 
           <div class="d-flex justify-content-between">
              <span class="mb-2">Remarks:  ..................................</span>
           </div> 
           <p>Received a sum of Rs  .......................(in words Rs.................................................) towards full and final Settlement of my/our claim as detailed here in above vide no ......... dated ............ drawn on................................</p>
           <div class="d-flex flex-column mr-4 mt-4" >
              <span>Date  <span>......................</span></span>             
              <span>Place <span>......................</span></span>
            </div>
          <div class="d-flex justify-content-between">
            <span class="mb-2">Signature.</span>
            <span>.......................................</span>
          
         </div> 
        </div>
      </div>
  </div>
  <div class="card bg-white col-md-12" >
      <div class="card-body">
        <h3 class="card-title mb-3 text-center">UNDERTAKING</h3>
        <p>I..........................................................Who have signed below,request & recommend you to give loan of Rs. ......................to Mr./Mrs. ...................................................  I he/she fails to pay the amount of interestor principle or both or is irregular in payment, I assure you that I would pay the amount of loan with interest. I have herebygiven you the rights and authority for collecting the amount of loan advanced and thus no sign or consult of mine is required further thus on Date............................. I give you above mentioned assurance and guarantee.</p>
        <div class="d-flex flex-column">
          <span>Place</span>
          <span>Date</span>
          <span>Yours faithfully,</span>
        </div>
      </div>
  </div>  
</div>
  <button id="cmd"  data-id="{{ $data['id'] }}">generate PDF</button>
 
    <button class="btn btn-success float-right" id ="myPrntbtn" onClick="printMyPage()">Print</button>




<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"
    integrity="sha256-c9vxcXyAG4paArQG3xk6DjyW/9aHxai2ef9RpMWO44A=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.min.js"></script>

<script>
$('#cmd').click(function () {
//$(document).ready(function() {

  var pdfButton = document.getElementById('cmd');
   var printButton = document.getElementById("myPrntbtn");
        //Hide the print button 
	printButton.style.visibility = 'hidden';
    pdfButton.style.visibility = 'hidden';	
    domtoimage.toPng(document.getElementById('body-content'))
    .then(function (blob) {
        var pdf = new jsPDF('20', 'pt', [$('#body-content').width(), $('#body-content').height()]);
		
        pdf.addImage(blob, 'PNG', 0, 0, $('#body-content').width(), $('#body-content').height());
		
        pdf.save("Loan Form.pdf");
		location.reload();
       
		 pdfButton.style.visibility = 'visible';
		  printButton.style.visibility = 'visible';
    });
		
        

    
//});
});

	$(document).on('click','#cmd',function(){
		 var memberloan_id = $(this).attr('data-id');
		

        $.ajax({

            type: "POST",  

            url: "{!! route('admin.memberLoans.updatePdfGenerate') !!}",

            dataType: 'JSON',

            data: {'id':memberloan_id,"_token": "{{ csrf_token() }}"},

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },

            success: function(response) {

                $('#cmd').hide();
        
            }
        }); 
		
	})

 function printMyPage() {  
  $( "input" ).removeClass( "custom-control-input m-status" )
  $( "label" ).removeClass( "custom-control-label" )
        //Get the print button
        var printButton = document.getElementById("myPrntbtn");
         var pdfButton = document.getElementById("cmd");
        //Hide the print button 
        printButton.style.visibility = 'hidden';
        pdfButton.style.visibility = 'hidden';
        //Print the page content
        window.print()
        //Show back the print button on web page 
        printButton.style.visibility = 'visible';
         pdfButton.style.visibility = 'visible';
    }
</script>

  </body>
</html>
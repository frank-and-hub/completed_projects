<?php

namespace App\Exports;

use App\Models\ScholarshipApplication\ScholarshipApplication;

use Maatwebsite\Excel\Concerns\FromCollection;


use Maatwebsite\Excel\Concerns\WithHeadings;

class ScholarshipApplicationExport implements FromCollection, WithHeadings
{
    protected $scholarshipId;

    public function __construct(int $scholarshipId)
    {
        $this->scholarshipId = $scholarshipId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    // public function collection()
    // {
    //     return ScholarshipApplication::all();
    // }

//     public function collection()
// {
//     $applicants = ScholarshipApplication::with(['user', 'scholarship'])->where('scholarship_id', $this->scholarshipId)->get();

//     // Modify data before exporting
//     $modifiedApplicants = $applicants->map(function ($applicant) {

       
//         return [
//             'First Name' => $applicant->user->first_name,
//             'Last Name' => $applicant->user->last_name,
//             'Email' => $applicant->user->email,
//             'Phone Number' => $applicant->user->phone_number,
//             'Date Of Birth' => $applicant->user->date_of_birth,
//             'Gender' => $applicant->user->gender,
//             'State' => $applicant->user->state,
//             'District' => $applicant->user->district,
//             'User Type' => $applicant->user->user_type,
//             'Looking For' => $applicant->user->looking_for,
//             'Whatsapp Number' => $applicant->user->whatsapp_number,
//             'Aadhar Card Number' => $applicant->user->aadhar_card_number,
//             'Category' => $applicant->user->student->category,
//             'Pwd Percentage' => $applicant->user->student->pwd_percentage,
//             'Is Army Veteran Category' => $applicant->user->student->is_army_veteran_category,
//             'Occupation' => $applicant->user->student->occupation,
//             'Current Citizenship' => 'Indian',
//             'Is Minority' => $applicant->user->student->is_minority,
//             'Principal/Guardian Name' => $applicant->user->student->guardianDetails->name,
//             'Relationship' => $applicant->user->student->guardianDetails->relationship,
//             'Principal/Guardian Occupation' => $applicant->user->student->guardianDetails->occupation,
//             'Principal/Guardian Phone Number' => $applicant->user->student->guardianDetails->phone_number,
//             'Number of Siblings' => $applicant->user->student->guardianDetails->number_of_siblings,
//             'Family Annual Income' => $applicant->user->student->guardianDetails->annual_income,
//             'House Type' => $applicant->user->student->addressDetails[0]->house_type,
//             'Address' => $applicant->user->student->addressDetails[0]->address,
//             'State' => $applicant->user->student->addressDetails[0]->state,
//             'District' => $applicant->user->student->addressDetails[0]->district,
//             'Pincode' => $applicant->user->student->addressDetails[0]->pincode,
//             'Employment Type' => $applicant->user->student->employmentDetails->employment_type,
//             'designation' => $applicant->user->student->employmentDetails->designation,
//             'Joining Date' => $applicant->user->student->employmentDetails->joining_date,
//             'End Date' => $applicant->user->student->employmentDetails->end_date,
//             'Job Role' => $applicant->user->student->employmentDetails->job_role,
            

//         ];
//     });

//     return $modifiedApplicants;
// } 

public function collection()
{
    $applicants = ScholarshipApplication::with(['user', 'scholarship'])->where('scholarship_id', $this->scholarshipId)->get();

    // Modify data before exporting
    $modifiedApplicants = $applicants->map(function ($applicant) {

        $educationDetails = '';
        if($applicant->user->student){
        foreach ($applicant->user->student->educationDetails as $data) {
            // Concatenate education details into a string
            $educationDetails .= "Institute/University: " . $data->institute_name . ", ";
            $educationDetails .= "Type of Institute: " . $data->institute_type . ", ";
            $educationDetails .= "State: " . $data->state . "\n"; 
            $educationDetails .= "District: " . $data->district . "\n"; 
            $educationDetails .= "Course Name: " . $data->course_name . "\n"; 
            $educationDetails .= "Specialisation: " . $data->specialisation . "\n"; 
            $educationDetails .= "Grading System: " . $data->grade_type . "\n"; 
            $educationDetails .= "Percentage scored/CGPA: " . $data->grade . "\n"; 
            $educationDetails .= "Course Name: " . $data->course_name . "\n"; 
            $educationDetails .= "From: " . $data->start_date . "\n"; 
            $educationDetails .= "To: " . $data->end_date . "\n"; 
        }
    }
        return [
            'First Name' => $applicant->user->first_name,
            'Last Name' => $applicant->user->last_name??'',
            'Email' => $applicant->user->email??'',
            'Phone Number' => $applicant->user->phone_number??'',
            'Date Of Birth' => $applicant->user->date_of_birth??'',
            'Gender' => $applicant->user->gender??'',
            'State' => $applicant->user->state??'',
            'District' => $applicant->user->district??'',
            'User Type' => $applicant->user->user_type??'',
            'Looking For' => $applicant->user->looking_for??'',
            'Whatsapp Number' => $applicant->user->whatsapp_number??'',
            'Aadhar Card Number' => $applicant->user->aadhar_card_number??'',
            'Category' => $applicant->user->student->category??'',
            'Pwd Percentage' => $applicant->user->student->pwd_percentage??'',
            'Is Army Veteran Category' => $applicant->user->student->is_army_veteran_category??'',
            'Occupation' => $applicant->user->student->occupation??'',
            'Current Citizenship' => 'Indian',
            'Is Minority' => $applicant->user->student->is_minority??'',
            'Principal/Guardian Name' => $applicant->user->student->guardianDetails->name??'',
            'Relationship' => $applicant->user->student->guardianDetails->relationship??'',
            'Principal/Guardian Occupation' => $applicant->user->student->guardianDetails->occupation??'',
            'Principal/Guardian Phone Number' => $applicant->user->student->guardianDetails->phone_number??'',
            'Number of Siblings' => $applicant->user->student->guardianDetails->number_of_siblings??'',
            'Family Annual Income' => $applicant->user->student->guardianDetails->annual_income??'',
            'House Type' => $applicant->user->student->addressDetails[0]->house_type??'',
            'Address' => $applicant->user->student->addressDetails[0]->address??'',
            'State' => $applicant->user->student->addressDetails[0]->state??'',
            'District' => $applicant->user->student->addressDetails[0]->district??'',
            'Pincode' => $applicant->user->student->addressDetails[0]->pincode??'',
            'Employment Type' => $applicant->user->student->employmentDetails->employment_type??'',
            'designation' => $applicant->user->student->employmentDetails->designation??'',
            'Joining Date' => $applicant->user->student->employmentDetails->joining_date??'',
            'End Date' => $applicant->user->student->employmentDetails->end_date??'',
            'Job Role' => $applicant->user->student->employmentDetails->job_role??'',
            'Education Details' => $educationDetails, // Add education details
            'Employment Type' => $applicant->user->student->employmentDetails->employment_type??'',
            'Company Name' => $applicant->user->student->employmentDetails->company_name??'',
            'Designation' => $applicant->user->student->employmentDetails->designation??'',
            'Job Profile' => $applicant->user->student->employmentDetails->job_role??'',
            'Joining Date' =>$applicant->user->student->employmentDetails->joining_date??'',
            'Worked Till' => $applicant->user->student->employmentDetails->end_date??'',
        ];
    });

    return $modifiedApplicants;
}




    public function headings(): array
    {
        return [
            'First Name',
'Last Name',
'Email',
'Phone Number',
'Date Of Birth',
'Gender',
'State',
'District',
'User Type',
'Looking For',
'Whatsapp Number',
'Aadhar Card Number',
'Category',
'Pwd Percentage',
'Is Army Veteran Category',
'Occupation',
'Current Citizenship',
'Is Minority',
'Principal/Guardian Name',
'Relationship',
'Principal/Guardian Occupation',
'Principal/Guardian Phone Number',
'Number of Siblings',
'Family Annual Income',
'House Type',
'Address',
'Pincode',
'Employment Type',
'designation',
'Joining Date',
'End Date',
'Job Role',
'Education Details',
'Employment Type',
'Company Name',
'Designation',
'Job Profile',
'Joining Date',
'Worked Till',

          
            // Add more headings as needed
        ];
    }
}

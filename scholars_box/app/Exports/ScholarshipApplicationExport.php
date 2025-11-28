<?php

namespace App\Exports;

use App\Models\ScholarshipApplication\ScholarshipApplication;
use App\Models\User;
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
    public function collection()
    {
        $applicants = ScholarshipApplication::with(['user.student.educationDetails', 'user.student.employmentDetails', 'user.student.guardianDetails', 'user.student.addressDetails', 'user.student.documents', 'scholarship.scholarshipQuestionApplication'])
            ->where('scholarship_id', $this->scholarshipId)
            ->get();

        return $applicants->map(function ($applicant) {
            $user = $applicant->user;
            $student = optional($user)->student;

            // Collect Documents Details
            $educationDetails = $student ? $student->documents->map(function ($doc) {
                $documentLink = asset('storage/' . $doc->document);
                return "Document Type: {$doc->document_type} - " . getUploadedQuestions($doc->other_document_name) . ", Document: {$documentLink}";
            })->implode("\n") : 'N/A';

            // Collect Scholarship Questions and Answers
            $scholarshipQuestions = optional($applicant->scholarship)->scholarshipQuestionApplication;
            $questionsDetails = $scholarshipQuestions ? $scholarshipQuestions->map(function ($question) use ($student) {
                return "{$question->question} - " . getAnswersByQuestions($student->id ?? null, $this->scholarshipId, $question->id);
            })->implode("\n") : 'N/A';

            // Generate the row data
            $row = [
                'First Name' => $user->first_name ?? 'N/A',
                'Last Name' => $user->last_name ?? 'N/A',
                'Email' => $user->email ?? 'N/A',
                'Phone Number' => $user->phone_number ?? 'N/A',
                'Date Of Birth' => $user->date_of_birth ?? 'N/A',
                'Gender' => $user->gender ?? 'N/A',
                'State' => $user->state ?? optional(optional($student->addressDetails)->first())->state,

                'District' => $user->district ?? optional(optional($student->addressDetails)->first())->district,
                'User Type' => $user->user_type ?? 'N/A',
                'Looking For' => $user->looking_for ?? 'N/A',
                'Whatsapp Number' => $user->whatsapp_number ?? 'N/A',
                'Aadhar Card Number' => $user->aadhar_card_number ?? 'N/A',
                'Category' => $student->category ?? 'N/A',
                'Pwd Percentage' => $student->pwd_percentage ?? 'N/A',
                'Is Army Veteran Category' => $student->is_army_veteran_category ?? 'N/A',
                'Occupation' => $student->occupation ?? 'N/A',
                'Current Citizenship' => 'Indian',
                'Is Minority' => $student->is_minority ?? 'N/A',
                'Principal/Guardian Name' => optional($student->guardianDetails)->name ?? 'N/A',
                'Relationship' => optional($student->guardianDetails)->relationship ?? 'N/A',
                'Principal/Guardian Occupation' => optional($student->guardianDetails)->occupation ?? 'N/A',
                'Principal/Guardian Phone Number' => optional($student->guardianDetails)->phone_number ?? 'N/A',
                'Number of Siblings' => optional($student->guardianDetails)->number_of_siblings ?? 'N/A',
                'Family Annual Income' => optional($student->guardianDetails)->annual_income ?? 'N/A',
                'House Type' => optional(optional($student->addressDetails)[0])->house_type ?? 'N/A',
                'Address' => optional(optional($student->addressDetails)[0])->address ?? 'N/A',
                'Employment Type' => optional($student->employmentDetails)->employment_type ?? 'N/A',
                'Designation' => optional($student->employmentDetails)->designation ?? 'N/A',
                'Joining Date' => optional($student->employmentDetails)->joining_date ?? 'N/A',
                'End Date' => optional($student->employmentDetails)->end_date ?? 'N/A',
                'Job Role' => optional($student->employmentDetails)->job_role ?? 'N/A',
            ];

            // Append Education Details Dynamically
            foreach (range(0, 4) as $i) {
                $education = optional($student->educationDetails)[$i];
                $row = array_merge($row, [
                    "Institute/University " . ($i + 1) => $education->institute_name ?? 'N/A',
                    "Institute/University Other " . ($i + 1) => $education->education_institute_other ?? 'N/A',
                    "Profession " . ($i + 1) => $education->level ?? 'N/A',
                    "Type of Institute " . ($i + 1) => $education->institute_type ?? 'N/A',
                    "State " . ($i + 1) => $education->state ?? 'N/A',
                    "District " . ($i + 1) => $education->district ?? 'N/A',
                    "Course Name " . ($i + 1) => $education->course_name ?? 'N/A',
                    "Course Name Other " . ($i + 1) => $education->education_course_other ?? 'N/A',
                    "Specialisation " . ($i + 1) => $education->specialisation ?? 'N/A',
                    "Grading System " . ($i + 1) => $education->grade_type ?? 'N/A',
                    "Percentage scored/CGPA " . ($i + 1) => $education->grade ?? 'N/A',
                    "From " . ($i + 1) => $education->start_date ?? 'N/A',
                    "To " . ($i + 1) => $education->end_date ?? 'N/A',
                ]);
            }

            $row['Documents'] = $educationDetails;
            $row['Questions'] = $questionsDetails;

            return $row;
        });
    }

    public function headings(): array
    {
        return [
            'First Name', 'Last Name', 'Email', 'Phone Number', 'Date Of Birth', 'Gender', 'State', 'District', 'User Type',
            'Looking For', 'Whatsapp Number', 'Aadhar Card Number', 'Category', 'Pwd Percentage', 'Is Army Veteran Category',
            'Occupation', 'Current Citizenship', 'Is Minority', 'Principal/Guardian Name', 'Relationship', 'Principal/Guardian Occupation',
            'Principal/Guardian Phone Number', 'Number of Siblings', 'Family Annual Income', 'House Type', 'Address',
            'Employment Type', 'Designation', 'Joining Date', 'End Date', 'Job Role',
            "Institute/University 1", "Institute/University Other 1", "Profession 1", "Type of Institute 1", "State 1", "District 1",
            "Course Name 1", "Course Name Other 1", "Specialisation 1", "Grading System 1", "Percentage scored/CGPA 1", "From 1", "To 1",
            "Institute/University 2", "Institute/University Other 2", "Profession 2", "Type of Institute 2", "State 2", "District 2",
            "Course Name 2", "Course Name Other 2", "Specialisation 2", "Grading System 2", "Percentage scored/CGPA 2", "From 2", "To 2",
            "Institute/University 3", "Institute/University Other 3", "Profession 3", "Type of Institute 3", "State 3", "District 3",
            "Course Name 3", "Course Name Other 3", "Specialisation 3", "Grading System 3", "Percentage scored/CGPA 3", "From 3", "To 3",
            "Institute/University 4", "Institute/University Other 4", "Profession 4", "Type of Institute 4", "State 4", "District 4",
            "Course Name 4", "Course Name Other 4", "Specialisation 4", "Grading System 4", "Percentage scored/CGPA 4", "From 4", "To 4",
            "Institute/University 5", "Institute/University Other 5", "Profession 5", "Type of Institute 5", "State 5", "District 5",
            "Course Name 5", "Course Name Other 5", "Specialisation 5", "Grading System 5", "Percentage scored/CGPA 5", "From 5", "To 5",
            'Documents', 'Questions'
        ];
    }
}

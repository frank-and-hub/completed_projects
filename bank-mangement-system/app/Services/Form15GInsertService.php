<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\{
    Form15G,
    Member,
};

class Form15GInsertService
{
    public function Form15GInsert($request, $memberId)
    {
        if ($request->hasFile('file')) {
            $mainFolder = storage_path() . '/images/update_15g';
            $file = $request->file;
            $uploadFile = $file->getClientOriginalName();
            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($mainFolder, $fname);
            $fData = [
                'file_name' => $fname,
                'file_path' => $mainFolder,
                'file_extension' => $file->getClientOriginalExtension(),
            ];
            // $form15GUpdate = Form15G::find($id);             
            // $form15GUpdate->file=$fname; 
            // $form15GUpdate->save();

            $formData['year'] = $request->year;
            $formData['member_id'] = $memberId;
            $formData['file'] = $fname;
            return  $form = Form15G::create($formData);
            $id = $form->id;
        }
    }
}

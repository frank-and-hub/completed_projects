<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Member;
use Image;
use App\Services\ImageUpload;

class SignaturePhotoService
{

    public function SignaturePhoto($request , $memberId)
    {
        $signature_filename = '';
        $photo_filename = '';
        if ($request->hasFile('signature')) {
            $signature_image = $request->file('signature');
            $signature_filename = $memberId . '_' . time() . '.' . $signature_image->getClientOriginalExtension();
            $signature_location = 'asset/profile/member_signature/' . $signature_filename;
            $mainFolderSignature= '/profile/member_signature/';
            ImageUpload::upload($signature_image, $mainFolderSignature,$signature_filename);
            // Image::make($signature_image)->resize(300, 300)->save($signature_location);
        }
        if ($request->hasFile('photo')) {
            $photo_image = $request->file('photo');
            $photo_filename = $memberId . '_' . time() . '.' . $photo_image->getClientOriginalExtension();
            $photo_location = 'asset/profile/member_avatar/' . $photo_filename;
            $mainFolderPhoto = '/profile/member_avatar/';
            ImageUpload::upload($photo_image, $mainFolderPhoto,$photo_filename);
            // Image::make($photo_image)->resize(300, 300)->save($photo_location);
        }
        $memberUpdate = Member::find($memberId);
     
        $memberUpdate->signature = $signature_filename;
        $memberUpdate->photo = $photo_filename;
     return   $memberUpdate->save();
    }
}

<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Bestfeatures;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class BestFeaturesController extends Controller
{
    public function index(Request $request)
    {
        $active_page = 'features';
        $title = 'Features';
        if ($request->ajax()) {
            $data = Bestfeatures::latest()->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $btn .= '<a href="' . route('edit_features', $row->id) . '" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-edit"></i></a>';
                    //$btn .= '<a href="javascript:void(0)" data-id ="' . $row->id . '" data-toggle="tooltip" data-placement="top" data-original-title="Delete" data-datatable = "featuresTable" data-url = "' . route('delete_features') . '" class="btn  btn-xs  delete_btn "><i class="fa fa-trash"></i></a>';
                    $btn .= '<a href="javascript:void(0)" data-id ="' . $row->id . '" data-toggle="tooltip" data-placement="top" data-original-title="Delete" data-datatable = "featuresTable" data-url = "' . route('delete_features') . '" class="btn  btn-xs  deletemodel "><i class="fa fa-trash"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->rawColumns(['action', 'created_at'])
                ->make(true);
        }
        return view('features.index', compact('active_page', 'title'));
    }

    public function add_features(Request $request)
    {
        $active_page = 'features';
        $title = 'Add Features';
        $sub_active_page = 'add_features';
        return view('features.add', compact('active_page', 'sub_active_page', 'title'));
    }
    public function edit_features(Request $request, $id)
    {
        $active_page = 'features';
        $sub_active_page = 'edit features';
        $title = 'Edit Features';
        $featureData = Bestfeatures::findOrFail($id);
        return view('features.edit', compact('active_page', 'sub_active_page', 'featureData', 'title'));
    }

    public function insert(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'heading' => 'required|string|max:255',
                'description' => 'required|string',
                'features_img' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:2048',
                    function ($attribute, $value, $fail) use ($request) {
                        $image = $request->file('features_img');
                        $imageSize = getimagesize($image);

                        if ($imageSize === false) {
                            return $fail('Invalid image file.');
                        }

                        $width = $imageSize[0];
                        $height = $imageSize[1];

                        if ($width > 500 || $height > 500) {
                            return $fail('Image dimensions should not exceed 500x500 pixels.');
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'serverside_error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            // Handle the image upload
            $imageFileName = $this->__imageSave($request, 'features_img', 'features-image');
            Log::debug($imageFileName);
            // Create the new record
            Bestfeatures::create([
                'heading' => $request->input('heading'),
                'description' => $request->input('description'),
                'image' => $imageFileName,
            ]);

            return response()->json([
                'status' => 'success',
                'msg' => 'Added Successfully',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'feature_id' => 'required|exists:best_features,id',
                'heading' => 'required|string|max:255',
                'description' => 'required|string',
                // 'features_img' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'features_img' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:2048',
                    function ($attribute, $value, $fail) use ($request) {
                        $image = $request->file('features_img');
                        $imageSize = getimagesize($image);

                        if ($imageSize === false) {
                            return $fail('Invalid image file.');
                        }

                        $width = $imageSize[0];
                        $height = $imageSize[1];

                        if ($width > 500 || $height > 500) {
                            return $fail('Image dimensions should not exceed 500x500 pixels.');
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'serverside_error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $feature_id = $request->input('feature_id');

            // Find the existing record
            $featureData = Bestfeatures::findOrFail($feature_id);

            // Handle the image upload if a new image is provided
            $imageFileName = $featureData->image; // Default to the current image
            if ($request->hasFile('features_img')) {
                $imageFileName = $this->__imageSave($request, 'features_img', 'features-image');

                // Optionally delete the old image if you want to remove it
                if ($featureData->image) {
                    Storage::delete($featureData->image);
                }
            }

            // Prepare data for update
            $data = [
                'heading' => $request->input('heading'),
                'description' => $request->input('description'),
                'image' => $imageFileName,
            ];

            // Update the existing record
            $featureData->update($data);

            return response()->json([
                'status' => 'success',
                'msg' => 'Updated Successfully',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            $user = Auth::user();
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'msg' =>  'Incorrect password',
                ]);
            }
            $dataId = $request->dataId;
            $feature = Bestfeatures::findOrFail($dataId);
            if (!$feature) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Record not found'
                ]);
            }
            $feature->delete();
            return response()->json([
                'status' => 'success',
                'msg' => 'Deleted Successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Deletion process encountered an error: ' . $e->getMessage()
            ]);
        }
    }
}

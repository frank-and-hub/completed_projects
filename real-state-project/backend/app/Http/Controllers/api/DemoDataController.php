<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseBuilder;
use App\Http\Resources\DemoDataResource;
use App\Models\DemoData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DemoDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $demo = DemoData::get();
        $data = DemoDataResource::collection($demo);
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $demo = DemoData::findOrFail($id);
        $data = new DemoDataResource($demo);
        return ResponseBuilder::success($data);
    }
}

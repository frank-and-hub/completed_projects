<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseBuilder;
use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
use App\Http\Resources\InternalPropertyResource;
use App\Http\Resources\ThankYouResource;
use App\Models\Calendar;
use App\Models\InternalProperty;
use Illuminate\Support\Facades\File;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $is_upcoming = $request->is_upcoming ?? 0;
        $user = Auth::user();
        $calendars = $user?->calendar()
            ->when($is_upcoming, function ($query) {
                return $query->where('event_datetime', '>=', Carbon::now());
            }, function ($query) {
                return $query->where('event_datetime', '<', Carbon::now());
            })->paginate(10) ?? null;

        if (!$calendars) {
            return ResponseBuilder::success([], 'No calendar events found.');
        }

        $data = CalendarResource::collection($calendars);
        return ResponseBuilder::successWithPagination($calendars, $data);
    }

    public function accept_event(Request $request, $url_id, $id)
    {
        $calendar = Calendar::find($id);
        if (!$calendar) {
            return redirect()->back()->with('error', 'Calendar not found.');
        }
        $calendar->status = Calendar::STATUS_ACCEPTED;
        $calendar->save();
        $calendar->status_info()->create([
            'calendar_id' => $id,
            'user_id' => $calendar->user_id,
            'status' => Calendar::STATUS_ACCEPTED,
        ]);

        return redirect('/invitation-accepted?id=' . $id, 302);
    }
    public function decline_event(Request $request, $url_id, $id)
    {
        $calendar = Calendar::find($id);
        if (!$calendar) {
            return redirect()->back();
        }
        $calendar->status = Calendar::STATUS_CANCELLED;
        $calendar->save();
        $calendar->status_info()->create([
            'calendar_id' => $id,
            'user_id' => $calendar->user_id,
            'status' => Calendar::STATUS_CANCELLED,
        ]);

        if ($request->method() === 'POST') {
            return ResponseBuilder::success([], 'Event declined successfully.');
        } else {
            return redirect()->back(); // set than you page api url
        }
    }
    public function thank_you(Request $request, $id)
    {
        $calendar = Calendar::with('property')->find($id);
        if (!$calendar) {
            return redirect()->back();
        }
        $data = new ThankYouResource($calendar);
        return ResponseBuilder::success($data, 'Thank you for accepting event.');
    }

    public function reschedule_event(Request $request, $url_id, $id)
    {
        try {

            DB::beginTransaction();
            $calendar = Calendar::find($id);
            if (!$calendar) {
                return ResponseBuilder::error('Calendar not found.', 404);
            }

            if ($calendar->status !== Calendar::STATUS_PENDING) {
                return ResponseBuilder::error('This event cannot be rescheduled.', 422);
            }

            $validator = Validator::make($request->all(), [
                'property_id' => 'required|uuid',
                'date' => 'required|date_format:Y-m-d',
                'time' => 'required|date_format:H:i',
                'message' => 'nullable|string|max:200'
            ]);

            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), 200);
            }
            // dd($request->all());
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $property_id = $request->property_id;
            $property = InternalProperty::find($property_id);

            if (!$property) {
                $property = Property::find($property_id);
                if (!$property) {
                    return ResponseBuilder::error('Property not found.', 404);
                }
            }

            $calendar->status = Calendar::STATUS_RESCHEDULE;
            $calendar->save();
            $calendar->status_info()->create([
                'calendar_id' => $calendar->id,
                'user_id' => $calendar->user_id,
                'status' => Calendar::STATUS_RESCHEDULE,
                'description' => $request->message ?? 'Event rescheduled by user.',
            ]);

            $calendar->event_datetime = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time);
            $calendar->save();
            $data = new InternalPropertyResource($calendar->property);

            $date = dateF($request->date . ' ' . $request->time);
            $time = date('h:i A', strtotime($request->time));

            WhatsappTemplate::eventReschedule(
                $calendar->admin->dial_code,
                $calendar->admin->phone,
                $calendar->user->name,
                $calendar->property->title,
                $calendar->admin->name,
                $calendar->property->propertyAddress(),
                $date,
                $time
            );

            DB::commit();
            return ResponseBuilder::success($data, 'Event rescheduled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error('An error occurred while processing your request.', 500);
        }
    }

    public function calendar_details($id)
    {
        $calendar = Calendar::with(['property', 'admin'])
            ->whereId($id)
            ->first();

        $data = new CalendarResource($calendar);
        return ResponseBuilder::success($data);
    }
}

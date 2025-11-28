<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $phone = null;
        $country_code = null;

        // if user otp is Verified then only show there phone and country-code
        if ($this->otpVerification && $this->otpVerification->otp_verified_at) {
            $phone = '0' . $this->phone;
            $country_code = $this->country_code;
        }

        $tenant_total_requests_count = config('services.property.tenant_request_per_payment');

        return [
            'id' => $this->id,
            'name' => ucwords($this->name),
            'country_code' => $country_code,
            'country' => $this->country,
            'timeZone' => $this->timeZone,
            'phone' => $phone,
            'email' => $this->email,
            'subscription' => $this->subscription,
            'message_alert' => $this->message_alert,
            'schedule_type' => $this->schedule_type,
            'login_type' => $this->social_type ?: 'normal',
            'subscription_type' => $this->subscription_type ?: null,
            'total_request' => $this->total_request ?: 0,
            'image' => $this->image ? Storage::url($this->image) : asset('assets/default_user.png'),
            'location' => [
                'lat' => $this?->countries?->latitude,
                'lng' => $this?->countries?->longitude
            ],
            'status' => $this->status,
            'subscription_expired_date' => $this?->expired_date ?: null,
            'pending_request_count' => ($tenant_total_requests_count - ($this->total_request ?: 0)),

            // credit report data
            'credit_report' => [
                'id' => $this->credit_report?->id,
                'url' => $this->credit_report?->credit_report_pdf ? url('/api/webhook', ['t' => creditReportencodedbase64(), 'id' => $this->credit_report?->id]) : null,
                'status' => $this->credit_report?->id ? true : false,
            ],            
            'user_employment' => $this->user_employment ? new UserEmploymentResource($this->user_employment) : null,
        ];
    }
}

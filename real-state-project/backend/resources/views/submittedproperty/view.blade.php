@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Submitted Property Requests'))

<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            Submitted Property
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ucwords($title)}}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('submitted_property') }}">List</a></li>
                <li class="breadcrumb-item active" aria-current="page">View</li>
            </ol>
        </nav>
    </div>
    @php
        $additionalFeatures = json_decode($data->additional_features, true);
    @endphp

    <div class="card mt-3">
        <div class="card-body">
            <h4 class="card-title"></h4>
            <div class="card_contain_data mt-3">
                <div class="drt_card">
                    <div class="row">
                        <div class="col-md-4 mt-2">
                            <a href="{{ route('user_view', $data->user_id) }}" class="card-text"><i
                                    class="fa fa-calendar fa_dash mr-3" aria-hidden="true"></i> Tenant : <span
                                    class="font-weight-bold">{{ ucwords($data->user->name) ?? 'N/A' }}</span></a>
                        </div>

                        <div class="col-md-4 mt-2">
                            <a href="{{ route('user_view', $data->user_id) }}" class="card-text">Tenant E-mail : <span
                                    class="font-weight-bold">{{ $data->user->email ?? 'N/A' }}</span></a>
                        </div>
                    </div>
                </div>
                <div class="drt_card">
                    <div class="row">
                        <div class="col-md-4 mt-2">
                            <p class="card-text"><i class="fa fa-calendar fa_dash mr-3" aria-hidden="true"></i> Request
                                Date : <span class="font-weight-bold">{{ $data->request_date ?? 'N/A' }}</span></p>
                        </div>

                        <div class="col-md-4 mt-2">
                            <p class="card-text">Price Range : <span
                                    class="font-weight-bold">{{ numberFormat($data->start_price) . ' ' . $data->currency . ' - ' . numberFormat($data->end_price) . ' ' . $data->currency . '' ?? 'N/A' }}</span>
                            </p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text">Property type : <span
                                    class="font-weight-bold">{{ $data->property_type }}</span></p>
                        </div>
                    </div>
                </div>

                <div class="drt_card">
                    <div class="col-md-12 mt-2">
                        <h5 class="custom-heading">Location :-</h5>
                    </div>
                    <div class="row" style="text-transform: capitalize">
                        <div class="col-md-4 mt-2">
                            <p class="card-text">Country : <span class="font-weight-bold">
                                    {{ $data->country ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text">Province : <span class="font-weight-bold">
                                    {{ $data->province_name ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text">City : <span class="font-weight-bold">
                                    {{ $data->city ?? 'N/A' }}</span></p>
                        </div>
                        <div class="col-md-4 mt-2">
                            <p class="card-text">Suburb : <span class="font-weight-bold">
                                    {{ $data->suburb_name ?? 'N/A' }}</span></p>
                        </div>
                    </div>
                </div>

                <div class="drt_card">
                    <div class="col-md-12 mt-2">
                        <h5 class="custom-heading">Bedrooms & Bathrooms :</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mt-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-bath fa-2x mr-2"></i>
                                <p class="card-text mb-0">Number of bathrooms :</p>
                                <h5 class="font-weight-bold ml-2 mb-0">
                                    @if ($data->no_of_bathroom > 4)
                                        {{ $data->no_of_bathroom . '+' }}
                                    @else
                                        {{ $data->no_of_bathroom }}
                                    @endif
                                </h5>
                            </div>
                        </div>
                        <div class="col-md-4 mt-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-bed fa-2x mr-2"></i>
                                <p class="card-text mb-0">Number of bedrooms:</p>
                                <h5 class="font-weight-bold ml-2 mb-0">
                                    @if ($data->no_of_bedroom > 4)
                                        {{ $data->no_of_bedroom . '+' }}
                                    @else
                                        {{ $data->no_of_bedroom }}
                                    @endif
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
                @if (
                    $data->additional_features &&
                        count(array_intersect($additionalFeatures, [
                                'parking',
                                'garden',
                                'pet_friendly',
                                'garage',
                                'fully_furnished' . 'pool',
                            ])))
                    <div class="drt_card">
                        <div class="col-md-12 mt-2">
                            <h5 class="custom-heading">Additional Features :</h5>
                        </div>

                        <div class="row">
                            @if ($additionalFeatures['parking'] ?? null)
                                <div class="col-md-4 mt-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-parking fa-2x mr-2"></i>
                                        <p class="card-text mb-0">Parking:</p>
                                        <h5 class="font-weight-bold ml-2 mb-0">
                                            @if ($additionalFeatures['parking'] == 1)
                                                Yes
                                            @else
                                                No
                                            @endif
                                        </h5>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-4 mt-2">
                                <div class="d-flex align-items-center">
                                    <i class="mr-2"><svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" width="40" height="40"
                                            x="0" y="0" viewBox="0 0 48 48" style="enable-background:new 0 0 512 512"
                                            xml:space="preserve" class="hovered-paths">
                                            <g>
                                                <path
                                                    d="M0 30h30v2H0zM29 42H1c-.6 0-1-.4-1-1v-3c0-.5.2-1 .6-1.4S1.5 36 2 36h26c.5 0 1 .2 1.4.6s.6.9.6 1.4v3c0 .6-.4 1-1 1zM2 40h26v-2H2zM0 26h30v2H0z"
                                                    fill="#f30051" opacity="1" data-original="#000000"
                                                    class="hovered-path"></path>
                                                <path
                                                    d="M6 24h2v13H6zM22 24h2v13h-2zM4 41h2v5H4zM8 41h2v5H8zM20 41h2v5h-2zM24 41h2v5h-2zM32 13h2v33h-2z"
                                                    fill="#f30051" opacity="1" data-original="#000000"
                                                    class="hovered-path"></path>
                                                <path
                                                    d="m32.3 36.278 2.97-2.97 1.414 1.414-2.97 2.97zM28.307 21.728l1.414-1.414 4.03 4.03-1.414 1.414zM32.313 21.27l5.02-5.02 1.413 1.414-5.02 5.02z"
                                                    fill="#f30051" opacity="1" data-original="#000000"
                                                    class="hovered-path"></path>
                                                <path
                                                    d="M39 28h-6c-.6 0-1-.4-1-1s.4-1 1-1h6c3.9 0 7-3.1 7-7 0-3.3-2.3-6.2-5.5-6.8-.3-.1-.5-.2-.6-.5-.1-.2-.2-.5-.1-.8.1-.6.2-1.3.2-1.9 0-3.9-3.1-7-7-7s-7 3.1-7 7c0 .6.1 1.3.3 1.9.1.3 0 .6-.1.8s-.4.4-.6.5c-3.3.6-5.6 3.5-5.6 6.8 0 .9.2 1.8.5 2.6.2.5 0 1.1-.6 1.3s-1.1 0-1.3-.6c-.4-1-.6-2.1-.6-3.3 0-3.9 2.5-7.3 6.1-8.5-.1-.5-.1-1-.1-1.5 0-5 4-9 9-9s9 4 9 9c0 .5 0 1-.1 1.5 3.6 1.2 6.1 4.7 6.1 8.5 0 5-4 9-9 9zM0 45h28v2H0zM30 45h18v2H30zM11 14H9c0-3.9 3.1-7 7-7v2c-2.8 0-5 2.2-5 5z"
                                                    fill="#f30051" opacity="1" data-original="#000000"
                                                    class="hovered-path"></path>
                                                <path d="M11 14H9c0-1.7-1.3-3-3-3V9c2.8 0 5 2.2 5 5z" fill="#f30051"
                                                    opacity="1" data-original="#000000" class="hovered-path">
                                                </path>
                                            </g>
                                        </svg></i>
                                    <p class="card-text mb-0">Garden :</p>
                                    <h5 class="font-weight-bold ml-2 mb-0">
                                        @if ($additionalFeatures['garden'] == 1)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    </h5>
                                </div>
                            </div>

                            <div class="col-md-4 mt-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-paw fa-2x mr-2"></i>
                                    <p class="card-text mb-0">Pet friendly:</p>
                                    <h5 class="font-weight-bold ml-2 mb-0">
                                        @if ($additionalFeatures['pet_friendly'] == 1)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-car fa-2x mr-2"></i>
                                    <p class="card-text mb-0">Garage :</p>
                                    <h5 class="font-weight-bold ml-2 mb-0">
                                        @if ($additionalFeatures['garage'] == 1)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-couch fa-2x mr-2"></i>
                                    <p class="card-text mb-0">Furnished:</p>
                                    <h5 class="font-weight-bold ml-2 mb-0">
                                        @if ($additionalFeatures['fully_furnished'] == 1)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-parking fa-2x mr-2"></i>
                                    <p class="card-text mb-0">Pool: </p>
                                    <h5 class="font-weight-bold ml-2 mb-0">
                                        @if ($additionalFeatures['pool'] == 1)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="drt_card">
                    {{-- <div class="col-md-12 mt-2">
                        <h5 class="text-center">Rental Property Features:-</h5>
                    </div> --}}
                    @foreach ($property_feature_columns as $key => $property_feature_column)
                        <h5 class="custom-heading mt-3">
                            {{ ucwords(checkBoxTextUpadte($key)) }} :-
                        </h5>
                        <div class="row">
                            @php $n_a = 1; @endphp
                            @forelse ($property_feature_column as $key=>$property_feature_)
                                @if ($data->{$key})
                                    @php $n_a = 0; @endphp
                                    <div class="col-md-4 mt-2">
                                        <p class="card-text"><span
                                                class="font-weight-bold">{{ checkBoxTextUpadte([$key]) }} : </span>
                                            {{ checkBoxTextUpadte(implode(', ', (array) $data->{$key})) ?: 'N/A' }}
                                        </p>
                                    </div>
                                @endif
                            @empty
                                N/A
                            @endforelse
                            @if ($n_a)
                                <div class="col-md-12 mt-2 text-center">
                                    <p class="card-text">N/A</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="drt_card">
                    <div class="col-md-12 mt-2">
                        <h5 class="custom-heading">Move-in-date:</h5>
                        <p class="card-text mt-2"><i class="fa fa-calendar fa_dash mr-3" aria-hidden="true"></i>
                            {{ $data->move_in_date ?? 'N/A' }}</p>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="col-lg-12 mt-3">
        <x-admin.matched-property :data="$data" :route="route('admin_user.match_property_list', ['user_search_property_id' => $data->id])" />
    </div>

@endsection

@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')

    {{-- Breadcrumb --}}
    <x-admin.breadcrumb active="Parks" header-button-route="{{ route('admin.park.create') }}"
        header-button="Add New Park" />

    {{-- DataTable --}}
    <div style="position: relative">

        <x-admin.datatable id="category-table" title="Parks" loaderID='dt-loader'>

            <x-slot:other>
                <div class="row pl-4 mt-3">
                    <div class="col-12">
                        <div class="form-inline">
                            @if(!$is_show_filter)
                            <label for="type" class="form-label">Display:</label>
                            <select class="selectpicker ml-2" id="dorpDownFilter" multiple="multiple"title="All Parks"
                                data-style="border border-muted" data-show-subtext='true'>
                                <optgroup label="Images" data-max-options="1">
                                    <option value="with_images">With Images</option>
                                    <option value="without_images">Without Images</option>
                                </optgroup>
                                <optgroup label="Visible-in-app" data-max-options="1">
                                    <option value="active_parks">Active Parks</option>
                                    <option value="inactive_parks">Inactive Parks</option>
                                </optgroup>
                            </select>
                            @else
                            <label for="city" class="form-label"> City: </label>
                            <select class="selectpicker ml-2 form-select-lg" id="city"
                                data-style="border border-muted" data-show-subtext='true' data-live-search="true">
                                  <option value="">Select City</option>
                                @foreach ($cities as $k => $city)
                                <?php
                                $val = "$city->city,$city->state,$city->country";
                                ?>
                                <option value='{{ $val }}'>{{ $val }}</option>
                                @endforeach
                            </select>

                            <label for="seo_feature" class="form-label"> Seo Feature: </label>
                            <select class="selectpicker ml-2 form-select-lg" id="seo_feature"
                                data-style="border border-muted" data-show-subtext='true' data-live-search="true">
                                  <option value="">Select Feature</option>
                                @foreach ($seo_features as $seo_feature)
                                <option value="{{ $seo_feature }}">{{ $seo_feature }}</option>
                                @endforeach
                            </select>
                            @endif
                            <button class="btn btn-primary ml-2" id="ApplyBtn" disabled>Apply</button>
                            <button class="btn btn-danger ml-2" disabled id="ResetAllSelectedBtn">Reset</button>
                        </div>
                         @if($is_show_filter)
                        <br>
                        <div>
                            Total Parks : <span id="total_park_count"></span>
                        </div>
                        @endif
                    </div>
                </div>

            </x-slot:other>
            <x-slot:headings>
                <th>Name</th>
                <th>Location</th>
                <th>Action</th>
            </x-slot:headings>
        </x-admin.datatable>
    </div>

    @push('script')
        <script type="text/javascript">
            var uRL = "{{ route('admin.park.dt_list') }}";
            var role = "{{ $user->getRoleNames()->first() }}";
        </script>
        <script src="{{ asset('assets/js/dt/parks-dt.js') }}"></script>
    @endpush
@endsection

@extends('student.layout.app')
@section('title', 'Scholarship - Scholarsbox')
@section('content')
    <?php
    $degree = \App\Models\EducationDetail::DEGREES;
    $state = \App\Models\CountryData\State::whereStatus('active')->get();
    $district = \App\Models\CountryData\District::whereStatus('active')->get();
    $tag = \DB::table('tags')->select('slug')->groupBy('slug')->pluck('slug');
    $tagName = \DB::table('tags')->pluck('slug', 'name')->toArray();
    ?>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <style>
        .scholarship__detail .scholarship__item-two {
            margin-top: 100px;
            border: none !important;
            padding: 0 !important;
            box-shadow: none !important;
        }

        .scholarship__detail .h2-subtitle-one {
            margin: 25px 0px 25px 0px;
            border-bottom: 2px solid #1a8fc7;
            display: block;
            padding-bottom: 10px;
        }
    </style>
    <section class="all-scholarship-area mt-25 mb-10">
        <div class="container z-indx-1">
            <div class="row">
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="tab">
                        <div id="" class="tab-pane">
                            <div id="list" role="tabpanel" aria-labelledby="list-tab">
                                <div class="row scholarship__detail scholarship__list-wrap row-cols-1">
                                    <div class="col">
                                        <div class="scholarship__item-two shine__animate-item">
                                            <div class="scholarship__item-two-thumb">
                                                <a href="" class="shine__animate-link">
                                                    <img src="{{ $scholarships->avatar ? asset($scholarships->avatar) : asset('images/logo.png') }}"
                                                        alt="img">
                                                </a>
                                            </div>
                                            <div class="scholarship__item-two-content">
                                                @if ($scholarships->comany_link == '')
                                                    <a href="{{ route('subdomain.home', $scholarships->company->company_name ?? '') }}"
                                                        class="scholarship__item-tag">{{ $scholarships->company->company_name ?? '' }}</a>
                                                @endif
                                                <h5 class="scholarship__title"><a
                                                        href="">{{ $scholarships->scholarship_name ?? '' }}</a></h5>
                                                <ul class="scholarship__item-meta list-wrap">

                                                    <li><i class="fa fa-calendar" title="Published Date"></i> Published
                                                        Date:
                                                        {{ \Carbon\Carbon::parse($scholarships->published_date)->format('jS F Y') }}
                                                    </li>

                                                </ul>

                                                <ul class="scholarship__item-meta list-wrap">

                                                    <li><i class="fa fa-calendar" title="End Date"></i> End Date:
                                                        {{ \Carbon\Carbon::parse($scholarships->end_date)->format('jS F Y') }}
                                                    </li>
                                                </ul>
                                                <div class="a2a_kit a2a_kit_size_32 a2a_default_style"
                                                    id="my_centered_buttons_{{ $scholarships->id }}"
                                                    style="display: none; justify-content: center;">
                                                    <a class="a2a_button_twitter"
                                                        href="https://twitter.com/intent/tweet?url={{ urlencode(route('Student.scholarship.details', $scholarships->id)) }}&text={{ urlencode($scholarships->scholarship_name) }}"></a>
                                                    <a class="a2a_button_whatsapp"
                                                        href="whatsapp://send?text={{ urlencode($scholarships->scholarship_name . ' - ' . route('Student.scholarship.details', $scholarships->id)) }}"></a>
                                                    <a class="a2a_button_linkedin"
                                                        href="https://www.linkedin.com/shareArticle?url={{ urlencode(route('Student.scholarship.details', $scholarships->id)) }}&title={{ urlencode($scholarships->scholarship_name) }}"></a>
                                                </div>
                                                @include('student.scholarship.calander')
                                                <div class="bottom-list">
                                                    <a id="sharebutton_{{ $scholarships->id }}"
                                                        class="btn sec-btn-one list-btn"
                                                        onclick="toggleShare({{ $scholarships->id }})"><img
                                                            src="{{ asset('images/share.png') }}"
                                                            style="width:20px; filter:invert(1);"></a>

                                                    <a class="btn sec-btn-one list-btn" id="authorize_button"
                                                        onclick="handleAuthClick()"><img
                                                            src="{{ asset('images/add-calendar-symbol-for-events.png') }}"
                                                            style="width:20px; filter:invert(1);"></a>
                                                    <?php
                                                    $endDateString = $scholarships->end_date;
                                                    $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $endDateString);
                                                    $today = \Carbon\Carbon::now();
                                                    ?>
                                                    @if ($endDate->greaterThanOrEqualTo($today) && $scholarships->comany_link == '')
                                                        @if (isset($scholarships->apply_now))
                                                            @if (isset(auth()->user()->id))
                                                                @if (alreadyApplieadScholarship($scholarships->id))
                                                                    <a class="btn sec-btn-one list-btn alert_condication"
                                                                        data-doc="" data-ec="" data-limit=""
                                                                        data-check="true">Apply Now</a>
                                                                @else
                                                                @if(!studentDocCheck($scholarships->id)['data'])
                                                                    <a class="btn sec-btn-one list-btn alert_condication" data-doc="{{studentDocCheck($scholarships->id)['msg']}}" data-ec="" data-limit="" data-check="">Apply Now</a>
                                                                @elseif (
                                                                        (int) $scholarships->min_age <= auth()->user()->age &&
                                                                            count(array_intersect(education_req_details(), json_decode($scholarships->education_req))) ===
                                                                                count(json_decode($scholarships->education_req)))
                                                                        <a class="btn sec-btn-one list-btn"
                                                                            data-limit="{{ $scholarships->min_age }}"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#applyModal"
                                                                            data-scholarship-id="{{ $scholarships->id }}">Apply
                                                                            Now</a>
                                                                    @else
                                                                        <?php
                                                                        $f = (array) json_decode($scholarships->education_req);
                                                                        $msg = '';
                                                                        foreach ($f as $k => $v) {
                                                                            $msg .= $degree[$v] . (count($f) != $k + 1 ? ', ' : ' ');
                                                                        }
                                                                        $c = count(array_intersect(education_req_details(), json_decode($scholarships->education_req))) !== count(json_decode($scholarships->education_req));
                                                                        ?>
                                                                        <a class="btn sec-btn-one list-btn alert_condication"
                                                                            data-doc="" data-ec="{{ $c == true ? $msg : false }}"
                                                                            data-limit="{{ $scholarships->min_age }}"
                                                                            data-check="">Apply
                                                                            Now</a>
                                                                    @endif
                                                                @endif
                                                            @endif
                                                        @else
                                                            <a href="{{ route('Student.login') }}"
                                                                class="btn sec-btn-one list-btn">Apply Now</a>

                                                        @endif
                                                    @endif

                                                    @if(!isset(auth()->user()->id) && $endDate->greaterThanOrEqualTo($today) && $scholarships->comany_link == '')
                                                    <a href="{{ route('Student.login') }}"
                                                    class="btn sec-btn-one list-btn">Apply Now</a>
                                                    @endif
                                                    <?php
                                                    $endDateString = $scholarships->end_date;
                                                    $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $endDateString);
                                                    $today = \Carbon\Carbon::now();
                                                    ?>
                                                    @if ($endDate->greaterThanOrEqualTo($today))
                                                        @if ($scholarships->comany_link != '')
                                                            <a href="{{ $scholarships->comany_link }}"
                                                                class="btn sec-btn-two list-btn">Explore More</a>
                                                        @else
                                                            <a href="{{ route('subdomain.home', $scholarships->company->company_name ?? '') }}"
                                                                class="readmore btn sec-btn-two list-btn">Explore More</a>
                                                        @endif
                                                    @endif

                                                </div>


                                            </div>
                                        </div>

                                        <div class="medium-12 small-12 columns">
                                            <span class="anchor-sub" id="sub-scholarship"></span>
                                            <h2 class="h2-subtitle-one">About the Scholarship</h2>
                                            <p class="font16 ">{{ $scholarships->scholarship_info ?? '' }}. </p>
                                            <span class="anchor-sub" id="sub-sponsor"></span>

                                            <h2 class="h2-subtitle-one">About the Sponsor</h2>
                                            <p class="font16 ">{{ $scholarships->sponsor_info ?? '' }}. </p>
                                            <span class="anchor-sub" id="sub-who"></span>

                                            <h2 class="h2-subtitle-one">Who can apply?</h2>
                                            <p class="font16 ">{!! $scholarships->who_can_apply_info ?? '' !!}. </p>
                                            <span class="anchor-sub" id="sub-apply"></span>

                                            <h2 class="h2-subtitle-one">How can you apply?</h2>
                                            <p class="font16 ">{!! $scholarships->how_to_apply_info ?? '' !!}. </p>
                                            <span class="anchor-sub" id="sub-faq"></span>

                                            <h2 class="h2-subtitle-one" style="text-transform: capitalize;">FAQs</h2>

                                            <p class="font16 ">{!! $scholarships->faqs ?? '' !!}. </p>
                                            <span class="anchor-sub" id="sub-contact"></span>

                                            <div class="bottom-list text-center d-block">
                                                <?php
                                                $endDateString = $scholarships->end_date;
                                                $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $endDateString);
                                                $today = \Carbon\Carbon::now();
                                                ?>
                                                @if ($endDate->greaterThanOrEqualTo($today) && $scholarships->comany_link == '')
                                                    @if (isset($scholarships->apply_now))
                                                        @if (isset(auth()->user()->id))
                                                            @if (alreadyApplieadScholarship($scholarships->id))
                                                                <a class="btn sec-btn-one list-btn alert_condication"
                                                                    data-doc="" data-ec="" data-limit="" data-check="true">Apply
                                                                    Now</a>
                                                            @else
                                                            @if(!studentDocCheck($scholarships->id)['data'])
                                                                <a class="btn sec-btn-one list-btn alert_condication" data-doc="{{studentDocCheck($scholarships->id)['msg']}}" data-ec="" data-limit="" data-check="">Apply Now</a>
                                                            @elseif (
                                                                    (int) $scholarships->min_age <= auth()->user()->age &&
                                                                        count(array_intersect(education_req_details(), json_decode($scholarships->education_req))) ===
                                                                            count(json_decode($scholarships->education_req)))
                                                                    <a class="btn sec-btn-one list-btn"
                                                                        data-limit="{{ $scholarships->min_age }}"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#applyModal"
                                                                        data-scholarship-id="{{ $scholarships->id }}">Apply
                                                                        Now</a>
                                                                @else
                                                                    <?php
                                                                    $f = (array) json_decode($scholarships->education_req);
                                                                    $msg = '';
                                                                    foreach ($f as $k => $v) {
                                                                        $msg .= $degree[$v] . (count($f) != $k + 1 ? ', ' : ' ');
                                                                    }
                                                                    $c = count(array_intersect(education_req_details(), json_decode($scholarships->education_req))) !== count(json_decode($scholarships->education_req));
                                                                    ?>
                                                                    <a class="btn sec-btn-one list-btn alert_condication"
                                                                        data-doc="" data-ec="{{ $c == true ? $msg : false }}"
                                                                        data-limit="{{ $scholarships->min_age }}"
                                                                        data-check="">Apply
                                                                        Now</a>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    @else
                                                        <a href="{{ route('Student.login') }}"
                                                            class="btn sec-btn-one list-btn">Apply Now</a>

                                                    @endif
                                                @endif
                                                @if(!isset(auth()->user()->id) && $endDate->greaterThanOrEqualTo($today) && $scholarships->comany_link == '')
                                                <a href="{{ route('Student.login') }}"
                                                class="btn sec-btn-one list-btn">Apply Now</a>
                                                @endif
                                                <?php
                                                $endDateString = $scholarships->end_date;
                                                $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $endDateString);
                                                $today = \Carbon\Carbon::now();
                                                ?>
                                                @if ($endDate->greaterThanOrEqualTo($today))
                                                    @if ($scholarships->comany_link != '')
                                                        <a href="{{ $scholarships->comany_link }}"
                                                            class="btn sec-btn-two list-btn">Explore More</a>
                                                    @else
                                                        <a href="{{ route('subdomain.home', $scholarships->company->company_name ?? '') }}"
                                                            class="btn sec-btn-two list-btn">Explore More</a>
                                                    @endif

                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>
    <script async src="https://static.addtoany.com/menu/page.js"></script>
    <script>
        function toggleShare(scholarshipId) {
            const shareDiv = document.getElementById('my_centered_buttons_' + scholarshipId);

            if (shareDiv.style.display === 'block') {
                shareDiv.style.display = 'none';
            } else {
                shareDiv.style.display = 'block';
            }
        }
    </script>
@endsection

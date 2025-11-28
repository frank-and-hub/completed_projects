<?php $degree = \App\Models\EducationDetail::DEGREES; ?>
<div id="myTabContent" @if ($type === 'column') style="display:block" @else style="display:none" @endif
    class="tab-pane fade in show active">
    <div id="list" role="tabpanel" aria-labelledby="list-tab">
        <div class="row scholarship__list-wrap row-cols-1">
            @php
                // Separate featured and non-featured scholarships
                $featuredScholarships = $scholarships->filter(function ($scholarship) {
                    return $scholarship->is_featured == 1;
                })->sortByDesc('created_at');

                $nonFeaturedScholarships = $scholarships->filter(function ($scholarship) {
                    return $scholarship->is_featured == 0;
                })->sortByDesc('created_at');

                // Merge featured scholarships first, followed by non-featured ones
                $combinedScholarships = $featuredScholarships->merge($nonFeaturedScholarships);

                // Separate expired and non-expired scholarships
                $expiredScholarships = $combinedScholarships->filter(function ($scholarship) {
                    $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $scholarship->end_date);
                    $today = \Carbon\Carbon::now();
                    return $endDate->lessThan($today);
                });

                $nonExpiredScholarships = $combinedScholarships->filter(function ($scholarship) {
                    $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $scholarship->end_date);
                    $today = \Carbon\Carbon::now();
                    return $endDate->greaterThanOrEqualTo($today);
                });

                // Combine non-expired scholarships first, followed by expired ones
                $sortedScholarships = $nonExpiredScholarships->merge($expiredScholarships);
            @endphp
            @foreach ($sortedScholarships as $scholarship)
                <div class="col">
                    <div class="scholarship__item-two shine__animate-item">
                        <div class="scholarship__item-two-thumb">
                            <a href="{{ route('Student.scholarship.details', $scholarship->slug) }}" class="shine__animate-link">
                                <img src="{{ $scholarship->avatar ? asset($scholarship->avatar) : asset('images/logo.png') }}" alt="img">
                            </a>
                        </div>
                        <div class="scholarship__item-two-content">
                            @if($scholarship->comany_link == '')
                            <a href="{{ route('subdomain.home', $scholarship->company->company_name ?? '') }}" class="scholarship__item-tag">{{ $scholarship->company ? $scholarship->company->company_name : ' ' }}</a>
                            @endif
                            @if($scholarship->is_featured == 1)
                                <span class="featured-btn-cstm" style="border-radius: 5px; padding: 5px 10px; color: #1a91c9; float:right; font-size: 24px;">
                                    <a href="#"><i class="fa fa-bookmark-o" style="display:none"></i>
                                        <i class="fa fa-bookmark"></i>
                                    </a>
                                </span>                             
                            @endif
                            @if($scholarship->is_scholarsip == 1)
                                <a href="#" class="scholarship__item-tag" style="background-color:gray;">Powered by ScholarsBox</a>
                            @endif
                            @if (isset(auth()->user()->id))
                                <span class="heart-btn heart-btn-schid-{{ $scholarship->id }}" @if ( $scholarship->savescholorship && $scholarship->savescholorship->where('schId', $scholarship->id)->where('userid', auth()->user()->id)->isNotEmpty()) style="color:#A2CC3B;" @endif onClick="test({{ $scholarship->id }})">
                                    <i class="fa fa-heart"></i>
                                </span>
                            @endif
                            <h5 class="scholarship__title"><a href="{{ route('Student.scholarship.details', $scholarship->slug) }}">{{ $scholarship->scholarship_name }}</a>
                            </h5>
                            <ul class="scholarship__item-meta list-wrap">
                                <li>
                                    <i class="fa fa-calendar" title="Published Date"></i>
                                    Published Date:
                                    {{ \Carbon\Carbon::parse($scholarship->published_date)->format('jS F Y') }}
                                </li>
                            </ul>
                            <ul class="scholarship__item-meta list-wrap">
                                <li>
                                    <i class="fa fa-calendar" title="End Date"></i> End Date:
                                    {{ \Carbon\Carbon::parse($scholarship->end_date)->format('jS F Y') }}
                                </li>
                            </ul>
                            <div class="a2a_kit a2a_kit_size_32 a2a_default_style"
                                id="my_centered_buttons_{{ $scholarship->id }}"
                                style="display: none; justify-content: center;">
                                <a class="a2a_button_twitter" href="https://twitter.com/intent/tweet?url={{ urlencode(route('Student.scholarship.details', $scholarship->id)) }}&text={{ urlencode($scholarship->scholarship_name) }}"></a>
                                <a class="a2a_button_whatsapp" href="whatsapp://send?text={{ urlencode($scholarship->scholarship_name . ' - ' . route('Student.scholarship.details', $scholarship->id)) }}"></a>
                                <a class="a2a_button_linkedin" href="https://www.linkedin.com/shareArticle?url={{ urlencode(route('Student.scholarship.details', $scholarship->id)) }}&title={{ urlencode($scholarship->scholarship_name) }}"></a>
                            </div>
                            <?php
                            $endDateString = $scholarship->end_date;
                            $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $endDateString);
                            $today = \Carbon\Carbon::now();
                            ?>
                            @if ($endDate->greaterThanOrEqualTo($today) && $scholarship->comany_link == '')
                                <div class="bottom-list">
                                    <a id="sharebutton_{{ $scholarship->id }}" class="btn sec-btn-one list-btn" onclick="toggleShare({{ $scholarship->id }})">
                                        <img src="{{ asset('images/share.png') }}" style="width:20px; filter:invert(1);">
                                    </a>
                                    @if (isset($scholarship->apply_now))
                                        @if (isset(auth()->user()->id))
                                            @if (alreadyApplieadScholarship($scholarship->id))
                                                <a class="btn sec-btn-one list-btn alert_condication" data-doc="" data-ec="" data-limit="" data-check="true">Apply Now</a>
                                            @else
                                                @if(!studentDocCheck($scholarship->id)['data'])
                                                    <a class="btn sec-btn-one list-btn alert_condication" data-doc="{{studentDocCheck($scholarship->id)['msg']}}" data-ec="" data-limit="" data-check="">Apply Now</a>
                                                @elseif ( (int) $scholarship->min_age <= auth()->user()->age && count(array_intersect(education_req_details(), json_decode($scholarship->education_req))) === count(json_decode($scholarship->education_req)))
                                                    <a class="btn sec-btn-one list-btn" data-limit="{{ $scholarship->min_age }}" data-bs-toggle="modal" data-bs-target="#applyModal" data-scholarship-id="{{ $scholarship->id }}">Apply Now</a>
                                                @else
                                                    <?php
                                                    $f = (array) json_decode($scholarship->education_req);
                                                    $msg = '';
                                                    foreach ($f as $k => $v) {
                                                        $msg .= $degree[$v] . (count($f) != $k + 1 ? ', ' : ' ');
                                                    }
                                                    $c = count(array_intersect(education_req_details(), json_decode($scholarship->education_req))) !== count(json_decode($scholarship->education_req));
                                                    ?>
                                                    <a class="btn sec-btn-one list-btn alert_condication" data-doc="" data-ec="{{ $c == true ? $msg : false }}" data-limit="{{ $scholarship->min_age }}" data-check="">Apply Now</a>
                                                @endif
                                            @endif
                                        @else
                                            <a href="{{ route('Student.login') }}" class="btn sec-btn-one list-btn">Apply Now</a>
                                        @endif
                                    @endif

                                    <a class="btn sec-btn-one list-btn" id="authorize_button"
                                        onclick="handleAuthClick()">
                                        <img src="{{ asset('images/add-calendar-symbol-for-events.png') }}"
                                            style="width:20px; filter:invert(1);" />
                                    </a>
                                    <a href="{{ route('Student.scholarship.details', $scholarship->slug) }}"
                                        class="btn sec-btn-two list-btn">Learn More</a>
                                </div>
                            @elseif($endDate->greaterThanOrEqualTo($today) && $scholarship->comany_link != '')
                                <a id="sharebutton_{{ $scholarship->id }}" class="btn sec-btn-one list-btn"
                                    onclick="toggleShare({{ $scholarship->id }})">
                                    <img src="{{ asset('images/share.png') }}" style="width:20px; filter:invert(1);">
                                </a>
                                {{-- <a href="{{ route('Student.login') }}" class="btn sec-btn-one list-btn">Apply Now</a> --}}
                                
                                <a class="btn sec-btn-one list-btn" id="authorize_button" onclick="handleAuthClick()">
                                    <img src="{{ asset('images/add-calendar-symbol-for-events.png') }}"
                                        style="width:20px; filter:invert(1);" />
                                </a>
                                <a href="{{ route('Student.scholarship.details', $scholarship->slug) }}"
                                    class="btn sec-btn-two list-btn">Learn More</a>
                                
                            @else
                                <a class="btn sec-btn-one list-btn alert_condication" data-ed="end" data-limit=""
                                    data-check="" style="background-color: gray; color: white;">Applications Closed</a>


                            @endif

                            <div class="medium-12 small-12 columns smalldesc"
                                data-scholarship-content="{{ $scholarship->id }}">
                                <span class="anchor-sub" id="sub-scholarship"></span>
                                <h2 class="h2-subtitle-one">Scholarship</h2>
                                <p class="font16 ">{{ $scholarship->scholarship_info }}. </p>
                                <span class="anchor-sub" id="sub-sponsor"></span>
                                <?php
                                $endDateString = $scholarship->end_date;
                                $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $endDateString);
                                $today = \Carbon\Carbon::now();
                                ?>
                                @if ($endDate->greaterThanOrEqualTo($today))
                                    <div class="bottom-list">
                                        @if (isset($scholarship->apply_now))
                                            @if (isset(auth()->user()->id))
                                                @if (alreadyApplieadScholarship($scholarship->id))
                                                    <a class="btn sec-btn-one list-btn alert_condication" data-doc="" data-ec="" data-limit="" data-check="true">Apply Now</a>
                                                @else
                                                    @if(!studentDocCheck($scholarship->id)['data'])
                                                        <a class="btn sec-btn-one list-btn alert_condication" data-doc="{{studentDocCheck($scholarship->id)['msg']}}" data-ec="" data-limit="" data-check="">Apply Now</a>
                                                    @elseif ( (int) $scholarship->min_age <= auth()->user()->age && count(array_intersect(education_req_details(), json_decode($scholarship->education_req))) === count(json_decode($scholarship->education_req)))
                                                        <a class="btn sec-btn-one list-btn" data-limit="{{ $scholarship->min_age }}" data-bs-toggle="modal" data-bs-target="#applyModal" data-scholarship-id="{{ $scholarship->id }}">Apply Now</a>
                                                    @else
                                                        <?php
                                                            $f = (array) json_decode($scholarship->education_req);
                                                            $msg = '';
                                                            foreach ($f as $k => $v) {
                                                                $msg .= $degree[$v] . (count($f) > 1 ? ', ' : ' ');
                                                            }
                                                            $c = count(array_intersect(education_req_details(), json_decode($scholarship->education_req))) !== count(json_decode($scholarship->education_req));
                                                        ?>
                                                        <a class="btn sec-btn-one list-btn alert_condication" data-doc="" data-ec="{{ $c == true ? $msg : false }}" data-limit="{{ $scholarship->min_age }}" data-check="">Apply Now</a>
                                                    @endif
                                                @endif
                                            @else
                                                <a href="{{ route('Student.login') }}" class="btn sec-btn-one list-btn">Apply Now</a>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <a href="#" class="btn sec-btn-one list-btn alert_condication" data-ed="end" data-doc="" data-ec="" data-limit="" data-check="" style="background-color: gray; color: white;">Applications Closed</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div id="gridViewContent" @if ($type !== 'column') style="display:block" @else style="display:none" @endif
    class="tab-pane fade show">
    <div class="inner-content-scholar-grid">
        <div class="row">
            @php
                // Separate featured and non-featured scholarships
                $featuredScholarships = $scholarships->filter(function ($scholarship) {
                    return $scholarship->is_featured == 1;
                })->sortByDesc('created_at');

                $nonFeaturedScholarships = $scholarships->filter(function ($scholarship) {
                    return $scholarship->is_featured == 0;
                })->sortByDesc('created_at');

                // Merge featured scholarships first, followed by non-featured ones
                $combinedScholarships = $featuredScholarships->merge($nonFeaturedScholarships);

                // Separate expired and non-expired scholarships
                $expiredScholarships = $combinedScholarships->filter(function ($scholarship) {
                    $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $scholarship->end_date);
                    $today = \Carbon\Carbon::now();
                    return $endDate->lessThan($today);
                });

                $nonExpiredScholarships = $combinedScholarships->filter(function ($scholarship) {
                    $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $scholarship->end_date);
                    $today = \Carbon\Carbon::now();
                    return $endDate->greaterThanOrEqualTo($today);
                });

                // Combine non-expired scholarships first, followed by expired ones
                $sortedScholarships = $nonExpiredScholarships->merge($expiredScholarships);
            @endphp
            @foreach ($sortedScholarships as $scholarship)
                <div class="col-md-6">
                    <div class="scholar-content-box">
                        <div class="image-scholar-content-grid ">
                            <img src="{{ $scholarship->avatar ? asset($scholarship->avatar) : asset('images/logo.png') }}"
                                alt="img">
                        </div>
                        <div class="meta-scholar-content-grid">
                        @if($scholarship->comany_link == '')
                            <a href="{{ route('subdomain.home', $scholarship->company->company_name ?? '') }}"
                                class="scholarship-tag">{{ $scholarship->company ? $scholarship->company->company_name : ' ' }}</a>
                                @endif
                                @if($scholarship->is_featured == 1)
                                <span class="featured-btn-cstm" style="border-radius: 5px;
                                padding: 5px 10px;
                                color: #1a91c9;
                                float:right;
                                font-size: 24px;"><a href="#"><i class="fa fa-bookmark-o" style="display:none"></i>
                                    <i class="fa fa-bookmark"></i></a></span>
                                @else
                                    <div style="width: 100%; min-height: 35px;"></div>
                                @endif
                                @if($scholarship->is_scholarsip == 1)
                                <a href="#"
                                class="scholarship__item-tag" style="background-color:gray;">Powered by ScholarsBox</a>
                                @endif
                            @if (isset(auth()->user()->id))
                                <span class="heart-btn heart-btn-schid-{{ $scholarship->id }}"
                                    @if (
                                        $scholarship->savescholorship &&
                                            $scholarship->savescholorship->where('schId', $scholarship->id)->where('userid', auth()->user()->id)->isNotEmpty()) style="color:#A2CC3B;" @endif
                                    onClick="test({{ $scholarship->id }})">
                                    <i class="fa fa-heart"></i>
                                </span>
                            @endif
                            <h5 class="scholarship__title"><a
                                    href="{{ route('Student.scholarship.details', $scholarship->slug) }}">{{ $scholarship->scholarship_name }}</a>
                            </h5>
                     
                            <ul class="meta-inner-content">
                                <li>
                                    <i class="fa fa-calendar" title="Published Date"></i>
                                    Published Date:
                                    {{ \Carbon\Carbon::parse($scholarship->published_date)->format('jS F Y') }}
                                </li>
                                <li>
                                    <i class="fa fa-calendar" title="End Date"></i>
                                    End Date: {{ \Carbon\Carbon::parse($scholarship->end_date)->format('jS F Y') }}
                                </li>
                            </ul>
                            <?php
                            $maxLength = 100;
                            $d = $scholarship->scholarship_info;
                            if (strlen($d) > $maxLength) {
                                $truncatedDescription = substr($d, 0, $maxLength) . '...';
                            } else {
                                $truncatedDescription = $d;
                            }
                            ?>
                            <p>{!! $truncatedDescription !!}</p>
                            {{--@if ($endDate->greaterThanOrEqualTo($today) && $scholarship->comany_link == '')
                                <div class="bottom-list">
                                    <a id="sharebutton_{{ $scholarship->id }}" class="btn sec-btn-one list-btn" onclick="toggleShare({{ $scholarship->id }})">
                                        <img src="{{ asset('images/share.png') }}" style="width:20px; filter:invert(1);">
                                    </a>
                                    @if (isset($scholarship->apply_now))
                                        @if (isset(auth()->user()->id))
                                            @if (alreadyApplieadScholarship($scholarship->id))
                                                <a class="btn sec-btn-one list-btn alert_condication" data-doc="" data-ec=""
                                                    data-limit="" data-check="true">Apply Now</a>
                                            @else
                                                @if(!studentDocCheck($scholarship->id)['data'])
                                                    <a class="btn sec-btn-one list-btn alert_condication" data-doc="{{studentDocCheck($scholarship->id)['msg']}}" data-ec="" data-limit="" data-check="">Apply Now</a>
                                                @elseif ((int) $scholarship->min_age <= auth()->user()->age && count(array_intersect(education_req_details(), json_decode($scholarship->education_req))) === count(json_decode($scholarship->education_req)))
                                                    <a class="btn sec-btn-one list-btn" data-limit="{{ $scholarship->min_age }}" data-bs-toggle="modal" data-bs-target="#applyModal" data-scholarship-id="{{ $scholarship->id }}">Apply Now</a>
                                                @else
                                                    <?php
                                                        $f = (array) json_decode($scholarship->education_req);
                                                        $msg = '';
                                                        foreach ($f as $k => $v) {
                                                            $msg .= $degree[$v] . (count($f) != $k + 1 ? ', ' : ' ');
                                                        }
                                                        $c = count(array_intersect(education_req_details(), json_decode($scholarship->education_req))) !== count(json_decode($scholarship->education_req));
                                                    ?>
                                                    <a class="btn sec-btn-one list-btn alert_condication" data-doc="" data-ec="{{ $c == true ? $msg : false }}" data-limit="{{ $scholarship->min_age }}" data-check="">Apply Now</a>
                                                @endif
                                            @endif
                                        @else
                                            <a href="{{ route('Student.login') }}" class="btn sec-btn-one list-btn">Apply Now</a>
                                        @endif
                                    @endif

                                    <a class="btn sec-btn-one list-btn" id="authorize_button" onclick="handleAuthClick()">
                                        <img src="{{ asset('images/add-calendar-symbol-for-events.png') }}" style="width:20px; filter:invert(1);" />
                                    </a>
                                    <a href="{{ route('Student.scholarship.details', $scholarship->id) }}" class="btn sec-btn-two list-btn">Learn More</a>
                                </div>
                            @elseif($endDate->greaterThanOrEqualTo($today) && $scholarship->comany_link != '')
                                <a id="sharebutton_{{ $scholarship->id }}" class="btn sec-btn-one list-btn" onclick="toggleShare({{ $scholarship->id }})">
                                    <img src="{{ asset('images/share.png') }}" style="width:20px; filter:invert(1);">
                                </a>
                                <a href="{{ route('Student.login') }}" class="btn sec-btn-one list-btn">Apply Now</a>                                
                                <a class="btn sec-btn-one list-btn" id="authorize_button" onclick="handleAuthClick()">
                                    <img src="{{ asset('images/add-calendar-symbol-for-events.png') }}" style="width:20px; filter:invert(1);" />
                                </a>
                                <a href="{{ route('Student.scholarship.details', $scholarship->id) }}" class="btn sec-btn-two list-btn">Learn More</a>
                            @else
                                <a class="btn sec-btn-one list-btn alert_condication" data-ed="end" data-limit="" data-check="" style="background-color: gray; color: white;">Apply Now</a>
                            @endif
                            --}}
                            <?php
                            $endDateString = $scholarship->end_date;
                            $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $endDateString);
                            $today = \Carbon\Carbon::now();
                            ?>
                            @if ($endDate->greaterThanOrEqualTo($today) && $scholarship->comany_link == '')
                                <div class="bottom-list">
                                    <a id="sharebutton_{{ $scholarship->id }}" class="btn sec-btn-one list-btn" onclick="toggleShare({{ $scholarship->id }})">
                                        <img src="{{ asset('images/share.png') }}" style="width:20px; filter:invert(1);">
                                    </a>
                                    @if (isset($scholarship->apply_now))
                                        @if (isset(auth()->user()->id))
                                            @if (alreadyApplieadScholarship($scholarship->id))
                                                <a class="btn sec-btn-one list-btn alert_condication" data-doc="" data-ec="" data-limit="" data-check="true">Apply Now</a>
                                            @else
                                                @if(!studentDocCheck($scholarship->id)['data'])
                                                    <a class="btn sec-btn-one list-btn alert_condication" data-doc="{{studentDocCheck($scholarship->id)['msg']}}" data-ec="" data-limit="" data-check="">Apply Now</a>
                                                @elseif ((int) $scholarship->min_age <= auth()->user()->age && count(array_intersect(education_req_details(), json_decode($scholarship->education_req))) === count(json_decode($scholarship->education_req)))
                                                    <a class="btn sec-btn-one list-btn" data-limit="{{ $scholarship->min_age }}" data-bs-toggle="modal" data-bs-target="#applyModal" data-scholarship-id="{{ $scholarship->id }}">Apply Now</a>
                                                @else
                                                    <?php
                                                        $f = (array) json_decode($scholarship->education_req);
                                                        $msg = '';
                                                        foreach ($f as $k => $v) {
                                                            $msg .= $degree[$v] . (count($f) != $k + 1 ? ', ' : ' ');
                                                        }
                                                        $c = count(array_intersect(education_req_details(), json_decode($scholarship->education_req))) !== count(json_decode($scholarship->education_req));
                                                    ?>
                                                    <a class="btn sec-btn-one list-btn alert_condication" data-doc="" data-ec="{{ $c == true ? $msg : false }}" data-limit="{{ $scholarship->min_age }}" data-check="">Apply Now</a>
                                                @endif
                                            @endif
                                        @else
                                            <a href="{{ route('Student.login') }}" class="btn sec-btn-one list-btn">Apply Now</a>
                                        @endif
                                    @endif

                                    <a class="btn sec-btn-one list-btn" id="authorize_button" onclick="handleAuthClick()">
                                        <img src="{{ asset('images/add-calendar-symbol-for-events.png') }}" style="width:20px; filter:invert(1);" />
                                    </a>
                                    <a href="{{ route('Student.scholarship.details', $scholarship->slug) }}" class="btn sec-btn-two list-btn">Learn More</a>
                                </div>
                            @elseif($endDate->greaterThanOrEqualTo($today) && $scholarship->comany_link != '')
                                <a id="sharebutton_{{ $scholarship->id }}" class="btn sec-btn-one list-btn" onclick="toggleShare({{ $scholarship->id }})">
                                    <img src="{{ asset('images/share.png') }}" style="width:20px; filter:invert(1);">
                                </a>                                
                                <a class="btn sec-btn-one list-btn" id="authorize_button" onclick="handleAuthClick()">
                                    <img src="{{ asset('images/add-calendar-symbol-for-events.png') }}" style="width:20px; filter:invert(1);" />
                                </a>
                                <a href="{{ route('Student.scholarship.details', $scholarship->slug) }}" class="btn sec-btn-two list-btn">Learn More</a>
                            @else
                                <a class="btn sec-btn-one list-btn alert_condication" data-ed="end" data-limit="" data-check="" style="background-color: gray; color: white;">Applications Closed</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>




<!-- Include Toastr.js from CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script async src="https://static.addtoany.com/menu/page.js"></script>
<style>
    .toast-success {
        background-color: green !important;
    }
</style>

<script>
    function test(schid) {

        $.ajax({
            type: 'POST',
            url: "{{ route('Student.save.scholorship') }}",
            data: {
                scholarshipId: schid
            },
            success: function(response) {
                // Display toastr popup on success

                toastr.success(response.message);
                if (response.data == 1) {
                    $('.heart-btn-schid-' + schid).css('color', '#A2CC3B');
                } else {
                    $('.heart-btn-schid-' + schid).css('color', 'white');
                }
            },
            error: function(error) {
                console.error('Error saving scholarship', error);
                // Display toastr popup on error
                toastr.error('Error saving scholarship');
            }
        });
    }
</script>

<script>
    function toggleShare(scholarshipId) {
        // Get the share div corresponding to the clicked share button
        const shareDiv = document.getElementById('my_centered_buttons_' + scholarshipId);

        // Toggle the display state of the share div
        if (shareDiv.style.display === 'block') {
            shareDiv.style.display = 'none'; // Hide the share div if it's currently visible
        } else {
            shareDiv.style.display = 'block'; // Show the share div if it's currently hidden
        }
    }
</script>

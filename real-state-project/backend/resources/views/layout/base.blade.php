<!DOCTYPE html>
<html lang="en">


<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/iconfonts/font-awesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/css/vendor.bundle.addons.css') }}">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- SweetAlert2 Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css?v=' . config('constants.asert_version')) }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css?v=' . config('constants.asert_version')) }}">
    <!-- endinject -->
    <!-- endinject -->
    <link rel="shortcut icon" href="{{ asset('assets/admin/images/logo-mini.png') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    @stack('custom-css')
    <style>
        .table-responsive {
            text-transform: capitalize
        }
    </style>
    {{-- custome Api --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/css/property.css?v=' . config('constants.asert_version')) }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tenants.css?v=' . config('constants.asert_version')) }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/setting.css?v=' . config('constants.asert_version')) }}">
</head>

<body>

    <div class="loader-demo-box center-loader" id="loader-web">
        <img src="{{ asset('assets/loader.gif') }}" alt="">
    </div>
    <input type="hidden" name="base_url" id="base_url" value="{{ url('') }}">
    <!-- Delete Modal -->
    <div class="modal fade" id="deletemodel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-confirm delete-success">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <div class="icon-box">
                        <i class="material-icons">&#xE876;</i>
                    </div>
                </div>
                <div class="modal-body text-center">
                    <h4>Deleted!</h4>
                    <p id="delete-message"></p>
                    <button class="btn btn-success" data-dismiss="modal"><span>Ok</span>
                </div>
            </div>
        </div>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="delete-form" id="delete-form">
                    <div class="modal-header plan_name">
                        <h5 class="modal-title text-center">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h6 class="text-danger">Do you really want to delete these records? This process cannot be
                            undone.
                        </h6>
                        <input type="hidden" name="dataId" id="dataId">
                        <div class="form-group position-relative">
                            <label> Enter your Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" id="password"
                                placeholder="***********">
                            <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                        </div>
                        <div class="alert alert-success success_msg" role="alert"></div>
                        <div class="alert alert-danger error_msg" role="alert"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn theme_btn_1">Ok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deletesuccessmodel" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <div class="icon-box">
                        <i class="material-icons">&#xE876;</i>
                    </div>
                </div>
                <div class="modal-body text-center">
                    <h4>Deleted!</h4>
                    <p id="delete-message"></p>
                    <button class="btn btn-success" data-dismiss="modal"><span>Ok</span>
                </div>
            </div>
        </div>
    </div>


    @yield('master')

    <script type="text/javascript">
        const COUNTRIES_SELECT2_URL = "{{ route('common.country') }}";
        const STATES_SELECT2_URL = "{{ route('common.state') }}";
        const CITIES_SELECT2_URL = "{{ route('common.city') }}";
        const SUBURB_SELECT2_URL = "{{ route('common.suburb') }}";
        const CHECK_ADMIN_HAS_PLAN_EXISTS = "{{ route('adminSubUser.check_plan_is_exists') }}"
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- plugins:js -->
    <script src="{{ asset('assets/admin/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/admin/vendors/js/vendor.bundle.addons.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page-->
    <!-- End plugin js for this page-->
    <!-- inject:js -->
    <script src="{{ asset('assets/admin/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/admin/js/misc.js') }}"></script>
    <script src="{{ asset('assets/admin/js/settings.js') }}"></script>
    <script src="{{ asset('assets/admin/js/notify.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/todolist.js') }}"></script>
    <script src="{{ asset('assets/admin/js/morris.js') }}"></script>
    <script src="{{ asset('assets/admin/js/dropify.js') }}"></script>
    <script src="{{ asset('assets/admin/js/owl-carousel.js') }}"></script>

    <!-- endinject -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script src="{{ asset('assets/admin/js/data-table.js?v=' . config('constants.asert_version')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.js"></script>
    <script src="{{ asset('assets/admin/js/custom.js?v=' . config('constants.asert_version')) }}"></script>
    <script src="{{ asset('assets/admin/js/custom2.js?v=' . config('constants.asert_version')) }}"></script>
    {{--
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('constants.GOOGLE_MAP_KEY') }}&loading=async&callback=initMap&libraries=places&geometry&v=weekly"
        defer></script> --}}
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('constants.GOOGLE_MAP_KEY') }}&loading=async&callback=initMap&libraries=places,geometry&v=weekly"
        defer></script>
    {{--
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script> --}}


    {{--
    <script type="text/javascript">(g => { var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__", m = document, b = window; b = b[c] || (b[c] = {}); var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams, u = () => h || (h = new Promise(async (f, n) => { await (a = m.createElement("script")); e.set("libraries", [...r] + ""); for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]); e.set("callback", c + ".maps." + q); a.src = `https://maps.${c}apis.com/maps/api/js?` + e; d[q] = f; a.onerror = () => h = n(Error(p + " could not load.")); a.nonce = m.querySelector("script[nonce]")?.nonce || ""; m.head.append(a) })); d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n)) })
            ({ key: "AIzaSyB5wIXS_zOIQuz24pELj0_MqfyaFgdPvhM", v: "weekly" });</script> --}}
    <script type="text/javascript">
        window.active_page = "{{ $active_page ?? '' }}";
        // window.url = "{{ url('') }}";
        // window.collapse_active_page = "{{ $collapse_active_page ?? '' }}";
        $(`${active_page}`).addClass('active');
        // $('[data-toggle=tooltip]').tooltip();
        function subscriptionAdminModel() {
            $('#adminsubscriptionmodel').modal('show');
        }

        $(document).on('click', '.adminsubuser_plan_join_now', function () {
            $.ajax({
                url: "{{ route('adminSubUser.subscribe') }}",
                type: "POST",
                success: function (response) {
                    if (response.status === 'success') {
                        if (response.is_free && response.is_free == 1) {
                            ToastAlert(msg = response.msg, cls = "success");
                            $('#adminsubscriptionmodel').modal('hide');
                        } else {
                            payFastWebHook(response);
                        }
                    } else {
                        ToastAlert(msg = response.msg, cls = "error");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error: ", status, error);
                }
            });
        });

        $(document).ready(function () {

            @if (session()->get('isSubscriptionPopShow'))
                subscriptionAdminModel()
            @endif


            setInterval(updateTime, 1000);

            function updateTime() {
                const timeZones = ["{{ auth()->user()?->timeZone ?? 'UTC' }}"];
                const now = new Date();

                timeZones.forEach((timeZone) => {
                    const options = {
                        timeZone: timeZone,
                        hour: "2-digit",
                        minute: "2-digit",
                        second: "2-digit",
                    };
                    const formatter = new Intl.DateTimeFormat("en-US", options);
                    const formattedTime = now.toLocaleString(options);
                    const [region, city] = timeZone.split('/');
                    $('#timezone_time').html(`${region + " / " + city}: ${formatter.format(now)}`);
                    $('.timezone_time').html(`${region + " / " + city}: ${formatter.format(now)}`);
                    $('.current_timezone_date').html(`${region + " / " + city}`);
                    $('.current_timezone_time').html(`${formatter.format(now)}`);
                    $('.utl_timezone_time').html(`${formattedTime}`);
                });
            }

            checkWindowSize();

            $(window).resize(function() {
                checkWindowSize();
            });

        });

        function payFastWebHook(response) {
            show_loader();
            const form = $('<form>', {
                action: response.url,
                method: 'POST'
            });

            $.each(response.data, function (key, value) {
                $('<input>', {
                    type: 'hidden',
                    name: key,
                    value: value
                }).appendTo(form);
            });
            form.appendTo('body').submit();
        }

        function checkWindowSize() {
            if ($(window).width() < 375) {
                $('.container-scroller').hide();
                $('body').prepend('<div class="warning-message">Your screen is too small to access the admin panel. Please use a screen wider than 375px.</div>');
                $('.warning-message').css({
                    'position': 'fixed',
                    'top': '0',
                    'left': '0',
                    'width': '100%',
                    'background': '#ffcc00',
                    'color': '#000',
                    'padding': '10px',
                    'text-align': 'center',
                    'z-index': '9999'
                });
            } else {
                $('.container-scroller').show();
                $('.warning-message').remove();
            }
        }


    </script>
    @include('layout.include.toast-alert')
    @stack('custom-script')
</body>


</html>

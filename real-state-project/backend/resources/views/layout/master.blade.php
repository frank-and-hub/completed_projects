@extends('layout.base')
@section('master')
    <div class="container-scroller">
        @php
            $auth = auth()->user();
        @endphp
        @include('layout.include.header')
        <div class="container-fluid page-body-wrapper">
            @include('layout.include.sidebar')
            <!-- partial -->
            <div class="main-panel">
                @yield('content')
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>



    <div class="modal fade" id="adminsubscriptionmodel" style="background:rgba(0,0,0,0.4)" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="adminsubscriptionmodel-form" id="adminsubscriptionmodel-form">
                    <div class="modal-header plan_name">
                        <div class="row">
                            @if (auth()->user()->hasRole('privatelandlord') || auth()->user()->hasRole('agency'))
                                <h5 class="modal-title text-center col-10">Please purchase a plan to add a property.
                                </h5>
                            @else
                                <h6 class="modal-title text-center col-10">It is necessary to purchase a plan
                                    for adding property.
                                </h6>
                            @endif
                            <button type="button" class="close col-2" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="m_96bdd299 mantine-Grid-col __m__-r8o">
                            <div class="m_e615b15f mantine-Card-root m_1b7284a3 mantine-Paper-root" data-with-border="true"
                                style="--paper-radius: var(--mantine-radius-md); --paper-shadow: var(--mantine-shadow-sm); --card-padding: var(--mantine-spacing-lg);">
                                <figure><svg xmlns="http://www.w3.org/2000/svg" width="3.5rem" height="3.5rem"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="tabler-icon tabler-icon-building-estate ">
                                        <path d="M3 21h18"></path>
                                        <path d="M19 21v-4"></path>
                                        <path d="M19 17a2 2 0 0 0 2 -2v-2a2 2 0 1 0 -4 0v2a2 2 0 0 0 2 2z"></path>
                                        <path d="M14 21v-14a3 3 0 0 0 -3 -3h-4a3 3 0 0 0 -3 3v14"></path>
                                        <path d="M9 17v4"></path>
                                        <path d="M8 13h2"></path>
                                        <path d="M8 9h2"></path>
                                    </svg></figure>
                                @if ($authRole = auth()->user()->getRoleNames()->first() != 'agent')
                                    <div class="plans_range">
                                        <h5>Basic</h5>
                                        <h3>{{ number_format(adminSubUserPlanPrice(), 0, 0) }}<span> Rand</span></h3>
                                        <h6>
                                            @if ($authRole == 'agency')
                                                <span class='text-muted'>per month</span>
                                            @else
                                                <span class='text-muted'>per Property</span>
                                            @endif
                                        </h6>
                                    </div>
                                @endif
                                @if (auth()->user()->hasRole('agency'))
                                    <div class="plans_points">
                                        <h5>To continue using the portal, please purchase a subscription. The plan is valid
                                            for 30 days from the payment date. You will be notified before expiry. Proceed
                                            with the payment for uninterrupted access.
                                        </h5>
                                    </div>
                                @elseif (auth()->user()->hasRole('privatelandlord'))
                                    <div class="plans_points">
                                        <h5>
                                            You’re about to add this property to your listings, which operates on a
                                            pay-per-listing basis. Please complete your payment to get started.</h5>
                                    </div>
                                @else
                                    <div class="plans_points">
                                        <h5>You currently don't have an active subscription. Please contact your agency.
                                        </h5>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{-- <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button> --}}
                        @if (auth()->user()->getRoleNames()->first() != 'agent')
                            <button type="button" class="btn adminsubuser_plan_join_now">Join Now</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

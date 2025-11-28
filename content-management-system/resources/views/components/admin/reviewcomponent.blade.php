@foreach ($ratings as $rting)
    @php
        $rating = $rting->rating;
        $rtingStar = View::make('components.admin.ratingcomponent', compact('rating'))->render();
        $image = !empty($rting->user->image) ? $rting->user->image->full_path : asset('images/user.svg');
    @endphp


    <div class='col-md-12'>
        <div class='card-body border border-muted rounded mt-3 mb-3'>
            <div class='d-flex justify-content-between'>
                <div>
                    <div class='flex-shrink-0 me-3'>
                        <div class='avatar'>
                            <img src='{{ $image }}' class='w-px-40 h-auto rounded-circle'>
                        </div>
                    </div>
                    <div class='flex-grow-1'>
                        <a href='{{ route('admin.user.view', $rting->user->id) }}'><span
                                class='fw-semibold d-block'>{{ ucfirst($rting->user->name) }}</span></a>{!!$rtingStar!!}<small
                            class='text-muted'
                            style='vertical-align: -2px;'>{{ \Carbon\Carbon::parse($rting->created_at)->setTimezone(Auth::user()->timezone)->format('M d, Y') }}</small>

                    </div>
                </div>
                @can('users-show')
                <div>
                    <i class='bx bxs-trash-alt text-danger delete-icon' onclick='deleteReview(this)'
                        park-id='{{ $rting->park->id }}' user-id='{{ $rting->user->id }}' rel='tooltip'
                        title='Delete'></i>
                </div>
                @endcan
            </div>
            <div class='text-muted me-3  mt-3 pl-1'>{{ ucfirst($rting->review) }}</div>

        </div>

    </div>
@endforeach

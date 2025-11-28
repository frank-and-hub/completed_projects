<div class="col w-100">
    <div class="mb-4 text-center">
        <h3>{{ __('admin.change_password') }}</h3>
    </div>

    <form method="post" id="reset-password-form" action="{{ route('admin.subadmin.update.password') }}"
        onsubmit="event.preventDefault(); update_password(this)" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="id" name="id" value={{ $user->id }}>
        <div class="row w-100">
            {{-- <div class="col-12">
                <div class="form-group">
                    <label class="form-control-label text-primary"
                        for="input-password">{{ __('admin.current_password') }}</label>
                    <input type="password" id="input-password" class="form-control sm"
                        placeholder="{{ __('admin.current_password') }}" name="current_password">
                </div>
            </div> --}}

            <div class="col-12">
                <div class="form-group">
                    <label class="form-control-label text-primary"
                        for="password">{{ __('admin.new_password') }}</label>

                        <div class="input-group input-group-merge">
                            <input type="password" id="password" class="form-control" name="password" placeholder="{{ __('admin.new_password') }}" aria-describedby="password">
                            <span class="input-group-text cursor-pointer" onclick="PassHideShow(this)"><i class="bx bx-show"></i></span>
                          </div>
                </div>
            </div>

            <div class="col-12">
                <div class="form-group">
                    <label class="form-control-label text-primary"
                        for="confirm-password">{{ __('admin.confirm_password') }}</label>

                        <div class="input-group input-group-merge">
                            <input type="password" id="confirm-password" class="form-control sm" name="confirm_password" placeholder="{{ __('admin.confirm_password') }}" aria-describedby="password">
                            <span class="input-group-text cursor-pointer" onclick="PassHideShow(this)"><i class="bx bx-show"></i></span>
                          </div>
                </div>
            </div>

            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary">
                    {{ __('admin.change_password') }}
                </button>
            </div>
        </div>
    </form>

</div>

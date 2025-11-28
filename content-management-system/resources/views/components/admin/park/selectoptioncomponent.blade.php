<option value="all" selected="" selected-txt="All">
    All
</option>

@if($parkscapeuploadedimage->count()>0)
<option value="parkscape" selected-txt="Parkscape">Parkscape</option>
@endif

@if ($subadminuplodedimage->count() > 0)
    <optgroup label='Sub-Admins' data-icon='bx bxs-user-detail'>
        <option value="all_subadmins" data-subtext="(Sub-Admin)" selected-txt='All'>All</option>
        @foreach ($subadminuplodedimage as $usr)
            <option value={{ $usr->id }} type="subadmin"
                data-subtext="(Sub-Admin)" selected-txt="{{ $usr->name }}">
                {{ $usr->name }}</option>
        @endforeach

    </optgroup>
@endif


@if ($useruploadedimage->count() > 0)
    <optgroup label="Users" data-icon="bx bxs-user">
        <option value="all_users" type="user" data-subtext="(User)">
            All
        </option>
        @foreach ($useruploadedimage as $usr)
            <option value="{{ $usr->id }}" type="user"
                data-subtext="(User)">
                {{ $usr->name }}
            </option>
        @endforeach
    </optgroup>
@endif
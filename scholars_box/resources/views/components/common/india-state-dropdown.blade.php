<div class="form-group">
    <select name="state_id" class="form-input-one" id="state_id" style="width: 100%">
        <option value="">Select State</option>
        @foreach (App\Models\CountryData\State::whereStatus('active')->get() as $state)
            <option value="{{ $state->id }}" @if (isset($selectedValue) && $state->id == $selectedValue) selected @endif>
                {{ ucfirst($state->name) }}
            </option>
        @endforeach
    </select>
</div>

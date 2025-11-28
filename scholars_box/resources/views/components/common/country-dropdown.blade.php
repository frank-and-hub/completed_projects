<div class="form-group">
    <label>Country:</label>
    <select name="country_id" class="form-control" id="country_id" style="width: 100%">
        <option value="">Select Country</option>
        @foreach (App\Models\CountryData\Country::all() as $country)
            <option value="{{ $country->id }}" @if (isset($selectedValue) && $country->id == $selectedValue) selected @endif>
                {{ ucfirst($country->name) }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>State:</label>
    <select name="state_id" id="state_id" class="form-control" data-endpoint="/api/states">
        <option value="">-</option>
    </select>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.js"
    integrity="sha512-n/4gHW3atM3QqRcbCn6ewmpxcLAHGaDjpEBu4xZd47N0W2oQ+6q7oc3PXstrJYXcbNU1OHdQ1T7pAP+gi5Yu8g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    $(document).ready(function() {
        const countrySelect = $('#country_id');

        function updateStateOptions(countryId, selectedState) {
            const dependentField = $('#state_id');
            const endpoint = $('#state_id').data('endpoint');

            if (countryId) {
                $.getJSON(`${endpoint}/${countryId}`, function(data) {
                    dependentField.html('<option value="">-</option>');
                    if (data && data.length > 0) {
                        $.each(data, function(index, item) {
                            dependentField.append($('<option>', {
                                value: item.id,
                                text: capitalizeFirstLetter(item.name),
                                selected: item.id == selectedState
                            }));
                        });
                    }
                    // Trigger the event to let other listeners know that the state options have been updated
                    dependentField.trigger('stateOptionsUpdated');
                });
            } else {
                dependentField.html('<option value="">-</option>');
            }
        }

        countrySelect.on('change', function() {
            updateStateOptions($(this).val());
        });

        // Set the initial selected value on page load
        let selectedValue = @json($selectedValue ?? null);
        updateStateOptions(countrySelect.val(), selectedValue);

    });
</script>

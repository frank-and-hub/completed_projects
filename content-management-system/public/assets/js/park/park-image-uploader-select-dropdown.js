$(document).ready(function () {
    // alert("Working with")
    // $("#customerDropdownGroup").find("input[type=search]").on('input', function () {

    $("#dropdownContainer").find("input[type=search]").on("input", function (e) {
        // $('.selectpicker').selectpicker('destroy').selectpicker('render').html('').selectpicker('refresh')

        //when click the search clear button
        if (!e.target.value.length) {
            getOptionsData($(this).val(), "cleardata");

        } else {
            getOptionsData($(this).val(), "searchdata");
        }

    })



    const getOptionsData = (searchVal, eventType) => {
        $.ajax({
            url: searchOptionsUrl,
            type: "POST",
            data: { search: searchVal, event: eventType },

            success: function (res) {


                    const html=`<option value="all" selected="" selected-txt="All">
                    All
                </option>

                <option value="parkscape" selected-txt="Parkscape">Parkscape</option>

                    <optgroup label="Users" data-icon="bx bxs-user">
                        <option value="all_users" type="user" data-subtext="(User)">
                            All
                        </option>
                                    <option value="3" type="user"
                                data-subtext="(User)">
                                Marie Wehner
                            </option>
                            </optgroup>`;


                // insertOptions(html);

                insertOptions(res.options);

                // $("#select-picker").html(res.options);


            }

        })
    }


    const insertOptions = (options) => {
        // $('.selectpicker').html('');

        $('.selectpicker').html(options);
        $('.selectpicker').selectpicker('destroy').selectpicker();
        // $('.selectpicker').selectpicker();

        // $('.selectpicker').selectpicker('refresh');

        // $('.selectpicker').selectpicker('destroy').selectpicker('render').html(options).selectpicker('refresh')
        // $('.selectpicker').selectpicker('refresh')..selectpicker('render')

    }

});

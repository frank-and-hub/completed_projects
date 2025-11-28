<script type="text/javascript">
    window.addEventListener('pageshow', function(event) {
        if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
            window.location.reload();
        }
    });
    var date = new Date();
    var today = new Date(date.getFullYear() - 18, date.getMonth(), date.getDate());
    var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    $('#rent_generate').validate({
        rules: {
            ledger_month: "required",
            ledger_year: "required",
            rent_lib_id: "required",
            select_date: "required",
        },
        messages: {
            ledger_month: "Please select month.",
            ledger_year: "Please select year.",
            rent_lib_id: "Please select rent labilities.",
            select_date: "Please select Date.",
        },
        errorElement: 'label',
        errorPlacement: function(error, element) {
            error.addClass(' ');
            element.closest('.error-msg').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function() {
            $('button[type="submit"]').prop('disabled', true);
            return true;
        }

    });
    $(document).ready(function() {
        calculateSum();
        jQuery.fn.serializeObject = function() {
            var o = {};
            var a = this.serializeArray();
            jQuery.each(a, function() {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        };
        $.validator.addClassRules({
            transfer_amount: {
                transfer_amountRequried: true,
                decimal: true,
                zero: true
            },
            tds_amount: {
                transfer_amountRequried: true,
                decimal: true,
                zero: true
            },
            amount: {
                transfer_amountRequried: true,
                decimal: true,
                zero: true
            },
            submitHandler: function(form) {
                return false;
            }
        });
        // $.validator.addMethod("transfer_amountRequried", $.validator.methods.required,
        //     "Please enter transfer amount");
        $.validator.addMethod("decimal", function(value, element, p) {
            if (this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {
                $.validator.messages.decimal = "";
                result = true;
            } else {
                $.validator.messages.decimal = "Please enter valid numeric number.";
                result = false;
            }
            return result;
        }, "");
        $.validator.addMethod("zero", function(value, element, p) {
            if (value >= 0 && value != "") {
                $.validator.messages.zero = "";
                result = true;
            } else {
                $.validator.messages.zero = "Amount must be greater than or equal to 0.";
                result = false;
            }
            return result;
        }, "");
        $.validator.addMethod("transfer_amountRequried", function(value, element, p) {
            if (value >= 0) {
                $.validator.messages.transfer_amountRequried = "";
                result = true;
            } else {
                $.validator.messages.transfer_amountRequried = "Amount must be greater than or equal to 0.";
                result = false;
            }
            return result;
        }, "");
        $.validator.addMethod("chk_tra", function(value, element, p) {
            id = $(element).attr('id');
            var res = id.substr(16);
            actual_amount = $('#rent_amount_' + res).val();
            if (value > actual_amount) {
                $.validator.messages.chk_tra = "Rent amount  must be greater than transfer amount";
                result = false;
            } else {
                $.validator.messages.chk_tra = "";
                result = true;
            }
            return result;
        }, "");
        $('.amount').on('change', function() {
            $(this).val($(this).val().replace(/[^0-9.]/g, ""));
            const amount = $(this).val();
            const rowIndex = $(this).attr('data-index');
            const rentAmount = $('#rent_amount_' + rowIndex).val();
            const transfer_amount = $('#transfer_amount_' + rowIndex).val();
            const updatedTds = $('#tds_amount_' + rowIndex).val();
            const updatedTdsAmount = updatedTds;
            if (Number(updatedTdsAmount) >= Number(amount)) {
                swal("Warning!", "Tds cannot be equal or more then amount please correct this first",
                    "warning");
                $("#submit_transfer").prop('disabled', true);
                $('.tds_amount').prop('disabled', true);
                $('.amount').prop('disabled', true);
                $(this).prop('disabled', false);
            } else {
                $("#submit_transfer").prop('disabled', false);
                $('.tds_amount').prop('disabled', false);
                $('.amount').prop('disabled', false);
            }
            $('#transfer_amount_' + rowIndex).val(amount - updatedTdsAmount);
            calculateSum();
        });
        $('.tds_amount').on('change', function() {
            $(this).val($(this).val().replace(/[^0-9.]/g, ""));
            const amount = $(this).val();
            const rowIndex = $(this).attr('data-index');
            const rentAmount = $('#rent_amount_' + rowIndex).val();
            const transfer_amount = $('#amount_' + rowIndex).val();
            console.log(transfer_amount, amount);
            if (Number(amount) >= Number(transfer_amount)) {
                swal("Warning!", "Tds cannot be equal or more then amount please correct this first",
                    "warning");
                $("#submit_transfer").prop('disabled', true);
                $('.tds_amount').prop('disabled', true);
                $('.amount').prop('disabled', true);
                $(this).prop('disabled', false);
                $(this).val(0);
                $('.tds_amount').trigger('keyup');
            } else {
                $("#submit_transfer").prop('disabled', false);
                $('.tds_amount').prop('disabled', false);
                $('.amount').prop('disabled', false);
                $('#transfer_amount_' + rowIndex).val(transfer_amount - amount);
            }
            calculateSum();
        });
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
    });

    function calculateSum() {
        var sum = 0;
        let transferAmount = 0;
        let rentAmount = 0;
        $(".tds_amount").each(function() {
            if (!isNaN(this.value) && this.value.length != 0) {
                sum += parseFloat(this.value);
            }
        });
        $(".transfer_amount").each(function() {
            if (!isNaN(this.value) && this.value.length != 0) {
                transferAmount += parseFloat(this.value);
            }
        });
        $(".amount").each(function() {
            if (!isNaN(this.value) && this.value.length != 0) {
                rentAmount += parseFloat(this.value);
            }
        });
        $("#totalTds").html(sum.toFixed(2));
        $("#sum").html(transferAmount.toFixed(2));
        $("#rentAmount").html(rentAmount.toFixed(2));
    }
</script>

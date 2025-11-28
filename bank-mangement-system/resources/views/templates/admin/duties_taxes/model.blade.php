<div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog custom-modal modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="border-value row">
                    <div class="custom-box col-md-6">
                        <h6 class="font-weight-bold">Old Values</h6>
                        <div class="old-values">
                        </div>
                    </div>
                    <div class="custom-box col-md-6">
                        <h6 class="font-weight-bold">New Values</h6>
                        <div class="new-values">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on('mouseenter', '.logs', function() {
        $(this).addClass('text-primary');
    }).on('mouseleave', '.logs', function() {
        $(this).removeClass('text-primary');
    });
    $(document).on('click', '.logs', function() {
        $('.new-values').html('');
        $('.old-values').html('');
        var LogUrl = $(this).data('url');
        var id = $(this).data('id');
        console.log(id);
        $.ajax({
            type: "POST",
            url: LogUrl,
            data: {
                'id': id
            },
            success: function(response) {
                console.log(response);
                if (response) {
                    var record = response.new_value, oldrecord = response.old_value;
                    if (record) {
                        $('#exampleModal').modal('toggle');
                        var newR = JSON.parse(record),
                            keys = Object.keys(newR),
                            values = Object.values(newR);
                        for (var i = 0; i < keys.length; i++) {
                            if (keys[i] == 'created_at' || keys[i] == 'updated_at') {
                                var d = new Date(values[i]), 
                                    day = ('0' + d.getDate()).slice(-2), 
                                    month = ('0' + (d.getMonth() + 1)).slice(-2), 
                                    year = d.getFullYear(), 
                                    hours = ('0' + d.getHours()).slice(-2), 
                                    minutes = ('0' + d.getMinutes()).slice(-2), 
                                    seconds = ('0' + d.getSeconds()).slice(-2), 
                                    formattedDateTime = day + '/' + month + '/' + year + ' ' + hours + ':' + minutes + ':' + seconds;
                                values[i] = formattedDateTime;
                            }
                            var html = `
                                <div class="border p-2">
                                    <div class="custom-input-group">
                                        <span class="font-weight-bold pb-2">Name : </span>
                                        <span>${keys[i]}</span>
                                    </div>
                                    <div class="custom-input-group">
                                        <span class="font-weight-bold">Value : </span>
                                        <span>${values[i] !== null ? values[i] : 'N/A'}</span>
                                    </div>
                                </div>`;
                            $('.new-values').append(html);
                        }
                    }
                    if (oldrecord) {
                        var oldR = JSON.parse(oldrecord),
                            oldkeys = Object.keys(oldR),
                            oldvalues = Object.values(oldR);
                        for (var i = 0; i < oldkeys.length; i++) {
                            if (oldkeys[i] == 'created_at' || oldkeys[i] == 'updated_at') {
                                var d = new Date(oldvalues[i]), 
                                    day = ('0' + d.getDate()).slice(-2), 
                                    month = ('0' + (d.getMonth() + 1)).slice(-2), 
                                    year = d.getFullYear(), 
                                    hours = ('0' + d.getHours()).slice(-2), 
                                    minutes = ('0' + d.getMinutes()).slice(-2), 
                                    seconds = ('0' + d.getSeconds()).slice(-2), 
                                    formattedDateTime = day + '/' + month + '/' + year + ' ' + hours + ':' + minutes + ':' + seconds;
                                oldvalues[i] = formattedDateTime;
                            }
                            var html = `
                                <div class="border p-2">
                                    <div class="custom-input-group">
                                        <span class="font-weight-bold pb-2">Name : </span>
                                        <span>${oldkeys[i]}</span>
                                    </div>
                                    <div class="custom-input-group">
                                        <span class="font-weight-bold">Value : </span>
                                        <span>${oldvalues[i] !== null ? oldvalues[i] : 'N/A'}</span>
                                    </div>
                                </div>`;

                            $('.old-values').append(html);
                        }
                    }
                    if (!record && !oldrecord) {
                        swal('Warning!', 'No Record Found !', 'warning');
                        return false;
                    }
                }
            }
        });
    });
</script>

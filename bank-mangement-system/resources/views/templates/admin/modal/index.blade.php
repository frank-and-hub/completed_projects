<!-- Need to explain a  reason for reject demand  -->
<div class="modal fade" id="formmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  method="post" id="demandRejectReason" name="demandRejectReason">
                    @csrf

                    <input type="hidden" name="demandId" id="demandId" >
                    <input type="hidden" name="create_application_date" class="create_application_date" id="create_application_date">
                    <div class="form-group">
                        <textarea class="form-control" id="rejectreason" name="rejectreason" rows="3"></textarea>

                    </div>
                    <button type="submit" class="btn btn-primary submit mt-4">Submit</button>
                </form>
            </div>

        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#demandRejectReason').validate({
            rules: {
                rejectreason: {
                    required: true
                }
            },
            messages: {
                rejectreason: {
                    required: "Please enter a reason."
                }
            },
            errorPlacement: function(error, element) {
                // error.appendTo(element.parent().parent()); 
				 error.appendTo(element.parent()); 
            }
        });
    });
</script>
<!-- demandRejectReason End -->


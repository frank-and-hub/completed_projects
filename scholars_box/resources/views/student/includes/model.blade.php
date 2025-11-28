<!-- Modal -->
<div class="modal fade" id="applyModal" tabindex="-1" role="dialog" aria-labelledby="applyModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applyModalLabel">Apply Now</h5>
                <button type="button" id="closeApplyModalButton" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="userApplyForm" name="Applyform" class="row apply-now-form">
                    <div id="applywizard">
                        @foreach(['personal_details','family_details','que'] as $k => $v)
                        <h4></h4>
                        <section>
                            <div class="row" id="{{$v}}">
                            </div>
                        </section>
                        @endforeach
                    </div>
                    <input type="hidden" name="scholarship_id" value="">
                </form>
            </div>

        </div>
    </div>
</div>
<input type="hidden" id="dob_limit_applyNow" value="" />

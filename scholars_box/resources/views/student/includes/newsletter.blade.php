<section class="main-newsletter-one">
    <div class="newsletter-blur1-one">
        <img src="{{asset('images/blur_4.png')}}" alt="Blur">
    </div>
    <div class="newsletter-blur2-one">
        <img src="{{asset('images/blur_4.png')}}" alt="Blur">
    </div>
    <div class="newsletter-one-shape1 animate-this wow fadeIn" data-wow-delay=".6s"></div>
    <div class="newsletter-one-shape2 animate-this wow fadeIn" data-wow-delay=".6s"></div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="newsletter-title-one wow fadeInLeft" data-wow-delay=".4s">
                    <div class="newsletter-icon-one">
                        <img src="{{asset('images/newsletter-icon.png')}}" alt="Icon">
                    </div>
                    <h2 class="h2-title">Get The Latest News & Updates</h2>
                </div>
            </div>
            <div class="col-lg-6">
                <form action="{{route('newsletter.mail')}}" method="post" id="emailForm">
                    @csrf
                <div class="footer-newsletter-form-two">
                    <input type="email" name="email" class="form-input-two subscribe-input" placeholder="Email Address..." required>
                    <button type="submit" class="sec-btn-two">Subscribe</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
function sendEmail() {
    var formData = $('#emailForm').serialize();

    $.ajax({
        type: 'POST',
        url: '{{ route('newsletter.mail') }}',
        data: formData,
        success: function(response) {
            console.log(response);
            if(response.data == 0){
                toastr.error(response.message);
                $('#emailForm')[0].reset(); // Reset the form
            }else{
                toastr.success(response.message);
            $('#emailForm')[0].reset(); // Reset the form
            }
        
        },
        error: function(error) {
            console.log(error.responseJSON);
            toastr.error('Email Not found. !! ');
                $('#emailForm')[0].reset(); // Reset the form
        }
    });
}

$('#emailForm').submit(function(event) {
    event.preventDefault();
    sendEmail();
});
</script>
    <!--Footer Start-->
    <footer class="main-footer-one">
        <!-- <div class="footer-blur1-one">
            <img src="{{asset('images/blur_7.png')}}" alt="Blur">
        </div>
        <div class="footer-blur2-one">
            <img src="{{asset('images/blur_7.png')}}" alt="Blur">
        </div> -->
        <div class="footer-one-shape1 animate-this wow fadeIn" data-wow-delay=".4s"></div>
        <div class="footer-one-shape2 animate-this wow fadeIn" data-wow-delay=".5s"></div>
        <div class="footer-one-shape3 animate-this wow fadeIn" data-wow-delay=".6s"></div>
        <div class="container">
            <div class="row">
                
                <div class="offset-lg-3 text-center col-xl-6 col-lg-5 col-md-6 col-sm-6 footer-links-one">
                    <h3 class="h3-title">Subscribe To Our Newsletter !</h3>
                    {{-- <p>Mauris vel neque ut leo interdum tincidunt et quis ex. Curabitur pellentesque odio eget nisi eleifend rutrum. Nulla ultrices laoreet turpis, eu imperdiet tortor.</p> --}}
                    <form action="{{route('newsletter.mail')}}" method="post" id="emailForm">
                        @csrf
                    <div class="footer-newsletter-form-two">
                        <input type="email" name="email" class="form-input-two subscribe-input" placeholder="Email Address..." required>
                        <input type="hidden" name="micro" value="micro">
                        <button type="submit" id="sendemail" class="sec-btn-two">Subscribe</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
       
        <div class="footer-copyright-one micro">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-7">
                        <div class="copyright-text-one">
                            <span>Powered By <a href="">ScholarsBox.</a> All rights reserved.</span>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
    </footer>
    <!--Footer Start-->
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-8PK2QYQN7R"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-8PK2QYQN7R');
</script>
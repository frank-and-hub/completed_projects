<script src="/js/app.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js"></script>
<script>
    Noty.overrideDefaults({
        type: 'alert',
        layout: 'topRight',
        theme: 'mint',
        timeout: 2000
    });

    // Handle Laravel Flash Messages
    @if (session('success'))
        new Noty({
            type: 'success',
            text: '{{ session('success') }}',
        }).show();
    @endif

    @if (session('error'))
        new Noty({
            type: 'error',
            text: '{{ session('error') }}',
        }).show();
    @endif
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/addons/cleave-phone.ca.js"></script>

<!-- Additional JS -->
@yield('scripts')

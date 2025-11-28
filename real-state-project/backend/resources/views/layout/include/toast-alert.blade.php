@if (session('error'))
    <script type="text/javascript">
        ToastAlert(msg = "{{ session('error') }}", cls="error");
    </script>
@elseif (session('success'))
    <script type="text/javascript">
        ToastAlert(msg = "{{ session('success') }}", cls = "success");
    </script>
@endif

@php
    Session::forget('success');
@endphp


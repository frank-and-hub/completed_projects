@if (session('error'))
    <script>
        ToastAlert(msg = "{{ session('error') }}", type = "Error", className = "bg-danger");
    </script>
@elseif (session('success'))
    <script>
        ToastAlert(msg = "{{ session('success') }}", type = "Success", className = "bg-success");
    </script>
@endif

@php
    Session::forget('success');
@endphp


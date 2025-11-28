<script>
    @if (session('alert'))
        alert("{{ session('alert') }}");
    @elseif(session('success'))
        alert("{{ session('success') }}");
    @elseif(session('error'))
        alert("{{ session('error') }}");
    @elseif(session('warning'))
        alert("{{ session('warning') }}");
    @elseif(session('info'))
        alert("{{ session('info') }}");
    @endif
</script>
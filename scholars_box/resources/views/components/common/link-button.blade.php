<div class="button-container" style="justify-content: {{ $justify ?? 'center' }} !important;">
    <a href="{{ $href ?? '#' }}" class="custom-button {{ $buttonType ?? 'primary-button' }}">
        <span class="sliding-element"></span>
        <span class="button-text">{{ $slot }}</span>
    </a>
</div>

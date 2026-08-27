{{-- Slots are native Livewire here — $slot for default, $slots['name'] for --}}
{{-- named. The addon's {{ slots:header }} exists because Antlers can't --}}
{{-- reach Livewire's own slot proxy. --}}
<div class="card">
    @if ($slots->has('header'))
        <header><strong>{{ $slots['header'] }}</strong></header>
    @endif
    <main>{{ $slot }}</main>
</div>

{{-- The process steps from the flowchart. $steps = list, $current = the step being done now --}}
<ol class="steps">
    @foreach ($steps as $n => $label)
        <li data-step="{{ $n }}" class="{{ $n < $current ? 'done' : ($n == $current ? 'now' : '') }}">
            <span class="num">{{ $n < $current ? '✓' : $n }}</span>
            {{ $label }}
        </li>
    @endforeach
</ol>

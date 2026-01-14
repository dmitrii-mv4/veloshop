@foreach($sections as $section)
    <option value="{{ $section->id }}" {{ $oldValue == $section->id ? 'selected' : '' }}>
        {{ str_repeat('— ', $level) }}{{ $section->name }}
    </option>
    @if($section->children->count() > 0)
        @include('catalog::sections.partials.options', [
            'sections' => $section->children,
            'level' => $level + 1,
            'oldValue' => $oldValue
        ])
    @endif
@endforeach
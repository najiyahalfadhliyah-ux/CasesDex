{{-- Dropdown + label + error + nilai lama. Contoh: <x-select name="status" label="Status" :options="CaseRecord::STATUSES" :value="$case->status" /> --}}
@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'help' => null])

@php($selected = (string) old($name, $value))

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}"
            @if ($help || $errors->has($name)) aria-describedby="{{ $name }}-desc" @endif
            {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <div id="{{ $name }}-desc" class="invalid-feedback">{{ $message }}</div>
    @else
        @if ($help)
            <div id="{{ $name }}-desc" class="form-text">{{ $help }}</div>
        @endif
    @enderror
</div>

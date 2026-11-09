{{-- Teks panjang + label + error + nilai lama. Contoh: <x-textarea name="description" label="Deskripsi" :value="$case->description" rows="4" /> --}}
@props(['name', 'label', 'value' => null, 'help' => null])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <textarea id="{{ $name }}" name="{{ $name }}"
              @if ($help || $errors->has($name)) aria-describedby="{{ $name }}-desc" @endif
              {{ $attributes->merge(['rows' => 4])->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
    @error($name)
        <div id="{{ $name }}-desc" class="invalid-feedback">{{ $message }}</div>
    @else
        @if ($help)
            <div id="{{ $name }}-desc" class="form-text">{{ $help }}</div>
        @endif
    @enderror
</div>

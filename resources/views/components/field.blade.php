{{-- Input form + label + pesan error + nilai lama. Contoh: <x-field name="email" label="Email" type="email" :value="$user->email" required /> --}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           @unless ($type === 'password') value="{{ old($name, $value) }}" @endunless
           @if ($help || $errors->has($name)) aria-describedby="{{ $name }}-desc" @endif
           {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    @error($name)
        <div id="{{ $name }}-desc" class="invalid-feedback">{{ $message }}</div>
    @else
        @if ($help)
            <div id="{{ $name }}-desc" class="form-text">{{ $help }}</div>
        @endif
    @enderror
</div>

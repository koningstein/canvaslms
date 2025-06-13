@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-tcr-green focus:ring-tcr-green rounded-md shadow-sm block w-full px-3 py-3 text-sm']) !!}
       onfocus="this.style.borderColor='#386049'; this.style.boxShadow='0 0 0 3px rgba(56, 96, 73, 0.1)'"
       onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">

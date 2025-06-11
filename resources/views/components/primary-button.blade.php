<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-3 border border-transparent text-sm font-semibold rounded-lg text-white transition ease-in-out duration-150 w-full justify-center', 'style' => 'background-color: #386049;']) }}
        onmouseover="this.style.backgroundColor='#2d4f3b'"
        onmouseout="this.style.backgroundColor='#386049'"
        onfocus="this.style.outline='none'; this.style.boxShadow='0 0 0 3px rgba(56, 96, 73, 0.1)'"
        onblur="this.style.boxShadow='none'">
    {{ $slot }}
</button>

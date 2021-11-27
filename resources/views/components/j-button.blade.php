@props([
    'look' => 'primary',
    'text' => '',
    'icon' => null,
])

<button
    @if ($look == 'dark')
        {{ $attributes->merge(['class' => 'flex items-center gap-2 relative bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-4 rounded']) }}
    @elseif ($look == 'soft')
        {{ $attributes->merge(['class' => 'flex items-center gap-2 relative bg-gray-200 hover:bg-blue-600 text-black hover:text-white font-bold py-1 px-4 rounded']) }}
    @elseif ($look == 'danger')
        {{ $attributes->merge(['class' => 'flex items-center gap-2 relative bg-red-100 hover:bg-red-600 text-black hover:text-white font-bold py-1 px-4 rounded']) }}
    @else
        {{ $attributes->merge(['class' => 'flex items-center gap-2 relative bg-yellow-300 hover:bg-yellow-600 text-black hover:text-white font-bold py-1 px-4 rounded']) }}
    @endif
>
    {{ $text }}
    {{ $slot }}
</button>

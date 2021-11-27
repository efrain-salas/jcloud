@props([
    'model' => '',
    'disabled' => false,
    'okLabel' => 'Aceptar',
    'koLabel' => 'Cancelar',
    'onOk' => '',
    'onKo' => '',
])

<input wire:model.lazy="{{ $model }}" {{ $attributes->merge(['type' => 'text']) }} class="block bg-gray-50 border border-gray-300 text-md text-gray-900 rounded-lg focus:ring-yellow-300 focus:border-yellow-300 block w-full p-2">

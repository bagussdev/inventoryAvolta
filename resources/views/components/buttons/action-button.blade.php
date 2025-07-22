@props([
    'type' => 'submit',
    'color' => 'purple',
    'text' => 'Action',
    'href' => null,
])

@php
    $baseClass =
        'w-fit sm:w-auto min-w-[90px] px-3 py-1 text-sm font-medium rounded-md focus:outline-none focus:ring-2 text-center transition text-[10px] sm:text-xs px-3 py-1.5 sm:px-4 sm:py-2';
    $colorClass = match ($color) {
        'purple' => 'bg-purple-600 text-white hover:bg-purple-700 focus:ring-purple-300',
        'green' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-300',
        'red' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-300',
        'yellow' => 'bg-yellow-500 text-black hover:bg-yellow-600 focus:ring-yellow-300',
        'gray' => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-300',
        'blue' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-300',
        default => 'bg-gray-300 text-black hover:bg-gray-400 focus:ring-gray-200',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$baseClass $colorClass"]) }}>
        {{ $text }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClass $colorClass"]) }}>
        {{ $text }}
    </button>
@endif

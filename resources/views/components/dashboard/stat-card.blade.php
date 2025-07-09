@props(['title', 'value', 'icon', 'color' => 'bg-yellow-300'])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 w-full">
    <div class="flex justify-between items-center gap-4 mb-3">
        <h2 class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $title }}</h2>
        <div class="{{ $color }} rounded-2xl w-10 h-10 flex items-center justify-center shrink-0">
            {!! $icon !!}
        </div>
    </div>
    <p class="text-4xl font-bold text-gray-900 dark:text-white">
        {{ $value }}
    </p>
</div>

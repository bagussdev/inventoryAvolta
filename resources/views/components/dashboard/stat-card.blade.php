@props(['title', 'value', 'icon', 'color' => 'bg-yellow-300', 'href' => null])

@php
    $content = <<<HTML
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-3 sm:p-4 w-full hover:shadow-xl transition duration-300 ease-in-out">
            <div class="flex justify-between items-center gap-3 sm:gap-4 mb-2 sm:mb-3">
                <h2 class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300 truncate">{$title}</h2>
                <div class="{$color} rounded-2xl w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center shrink-0">
                    {$icon}
                </div>
            </div>
            <p class="text-2xl sm:text-4xl font-bold text-gray-900 dark:text-white truncate">{$value}</p>
        </div>
    HTML;
@endphp

@if ($href)
    <a href="{{ $href }}" class="block w-full">
        {!! $content !!}
    </a>
@else
    {!! $content !!}
@endif

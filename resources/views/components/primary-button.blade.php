<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' =>
            'bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm sm:text-base px-4 py-1.5 sm:px-6 sm:py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-150',
    ]) }}>
    {{ $slot }}
</button>

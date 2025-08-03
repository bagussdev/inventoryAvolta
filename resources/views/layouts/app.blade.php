<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <!-- Viewer.js CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    {{-- Memanggil komponen loading overlay di sini --}}
    <x-loading-overlay />

    <div class="min-h-screen bg-gray-100">
        {{-- @include('layouts.navigation') --}}

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let idleTime = 0;
            const maxIdleMinutes = 15;

            function resetIdleTimer() {
                idleTime = 0;
            }

            // Reset setiap aktivitas user
            window.onload = resetIdleTimer;
            document.onmousemove = resetIdleTimer;
            document.onkeypress = resetIdleTimer;
            document.onscroll = resetIdleTimer;
            document.onclick = resetIdleTimer;

            // Periksa setiap 1 menit (60000 ms)
            setInterval(() => {
                idleTime++;
                if (idleTime >= maxIdleMinutes) {
                    // Trigger logout ke backend
                    fetch("{{ route('logout') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]')
                                    .getAttribute("content"),
                                "Content-Type": "application/json",
                                "Accept": "application/json"
                            },
                        })
                        .then(res => res.json())
                        .then(() => {
                            Swal.fire({
                                title: 'Session Berakhir',
                                text: 'Silakan login ulang.',
                                icon: 'warning',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                window.location.href = "{{ route('login') }}";
                            });
                        });
                }
            }, 60000); // tiap 1 menit
        });
    </script>

    <!-- Tom Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js" defer></script>
    <!-- Viewer.js JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>
    <!-- jQuery (wajib sebelum pakai $ atau select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>

    <script>
        function confirmAndLoad(message) {
            const confirmAction = confirm(message);
            if (confirmAction) {
                showFullScreenLoader();
            }
            return confirmAction;
        }
    </script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js" defer></script>
</body>

</html>

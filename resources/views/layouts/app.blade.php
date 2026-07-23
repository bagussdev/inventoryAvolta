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
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>
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
            let isWarningShown = false;
            let isLoggingOut = false;

            const warningAfterMinutes = 14;
            const checkEveryMs = 60000;

            function resetIdleTimer() {
                if (!isWarningShown && !isLoggingOut) {
                    idleTime = 0;
                }
            }

            function updateCsrfToken(token) {
                if (!token) return;

                const metaToken = document.querySelector('meta[name="csrf-token"]');
                if (metaToken) {
                    metaToken.setAttribute("content", token);
                }

                document.querySelectorAll('input[name="_token"]').forEach(input => {
                    input.value = token;
                });
            }

            async function extendSession() {
                const response = await fetch("{{ route('session.keepalive') }}", {
                    method: "POST",
                    credentials: "same-origin",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                            "content"),
                        "Accept": "application/json"
                    }
                });

                if (!response.ok) {
                    throw new Error("Session expired");
                }

                const data = await response.json();
                updateCsrfToken(data.token);

                idleTime = 0;
                isWarningShown = false;
            }

            async function logoutUser() {
                if (isLoggingOut) return;

                isLoggingOut = true;

                try {
                    await fetch("{{ route('force.logout') }}", {
                        method: "POST",
                        credentials: "same-origin",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                            "Accept": "application/json"
                        }
                    });
                } catch (error) {
                    console.warn("Logout failed:", error);
                }

                window.location.replace("{{ route('login') }}");
            }

            function showIdleWarning() {
                if (isWarningShown || isLoggingOut) return;

                isWarningShown = true;

                Swal.fire({
                    title: 'Session Hampir Berakhir',
                    text: 'Kamu sudah idle cukup lama. Mau lanjutkan session?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Extend Session',
                    cancelButtonText: 'Logout',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    timer: 60000,
                    timerProgressBar: true,
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            await extendSession();

                            Swal.fire({
                                title: 'Session Diperpanjang',
                                text: 'Kamu bisa lanjut menggunakan aplikasi.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } catch (error) {
                            await logoutUser();
                        }
                    } else {
                        await logoutUser();
                    }
                });
            }

            window.onload = resetIdleTimer;
            document.onmousemove = resetIdleTimer;
            document.onkeypress = resetIdleTimer;
            document.onscroll = resetIdleTimer;
            document.onclick = resetIdleTimer;

            setInterval(() => {
                if (isLoggingOut) return;

                idleTime++;

                if (idleTime >= warningAfterMinutes) {
                    showIdleWarning();
                }
            }, checkEveryMs);
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

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#6366f1">
        <link rel="apple-touch-icon" href="/logo.png">

        <title inertia>{{ config('app.name', 'Dev Command Center') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="antialiased" style="background-color:#0a0a0f; color:#e2e8f0;">
        @inertia
        
        <script>
            // === ERROR CATCHER - REMOVE AFTER DEBUGGING ===
            window.onerror = function(msg, src, line, col, error) {
                var div = document.getElementById('js-error-box');
                if (!div) {
                    div = document.createElement('div');
                    div.id = 'js-error-box';
                    div.style.cssText = 'position:fixed;top:0;left:0;right:0;background:#ff0000;color:#fff;padding:20px;z-index:99999;font-size:14px;font-family:monospace;word-break:break-all;';
                    document.body.appendChild(div);
                }
                div.innerHTML += '<b>JS ERROR:</b> ' + msg + '<br><b>File:</b> ' + src + '<br><b>Line:</b> ' + line + '<br><br>';
                return false;
            };
            window.addEventListener('unhandledrejection', function(event) {
                var div = document.getElementById('js-error-box');
                if (!div) {
                    div = document.createElement('div');
                    div.id = 'js-error-box';
                    div.style.cssText = 'position:fixed;top:0;left:0;right:0;background:#ff0000;color:#fff;padding:20px;z-index:99999;font-size:14px;font-family:monospace;word-break:break-all;';
                    document.body.appendChild(div);
                }
                div.innerHTML += '<b>PROMISE ERROR:</b> ' + event.reason + '<br><br>';
            });
        </script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').then(registration => {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    }).catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
                });
            }
        </script>
    </body>
</html>

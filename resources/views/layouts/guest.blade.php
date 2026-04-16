<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic OS</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Smooth fade-in for the whole page */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="bg-[#E0E9FF] font-sans antialiased text-slate-900 overflow-x-hidden">
    
    <div class="min-h-screen fade-in">
        @yield('content')
    </div>

    <footer class="fixed bottom-4 w-full text-center text-slate-400 text-[10px] uppercase tracking-widest pointer-events-none">
        &copy; {{ date('Y') }} Clinic Operating System
    </footer>

</body>
</html>
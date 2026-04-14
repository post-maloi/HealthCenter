<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <aside class="w-64 bg-slate-900 text-white">
            <div class="p-6 text-xl font-bold border-b border-slate-800">CLINIC RECORDS</div>
            <nav class="mt-6">
                <a href="{{ route('dashboard') }}" class="block py-3 px-6 bg-blue-600">Dashboard</a>
                <a href="{{ route('record.index') }}" class="block py-3 px-6 hover:bg-slate-800">Clinic Records</a>
            </nav>
        </aside>

        <main class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800">Welcome to CLinic Records Dashboard</h1>
            <p class="text-gray-600 mt-2">Manage your patient records and daily consultations.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-lg text-slate-700">Patient Management</h3>
                    <p class="text-sm text-gray-500 mt-1">Access all clinical history and medicines given.</p>
                    
                    <a href="{{ route('record.index') }}" class="inline-block mt-4 text-sm font-semibold text-blue-600 hover:underline">
                        View all records →
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
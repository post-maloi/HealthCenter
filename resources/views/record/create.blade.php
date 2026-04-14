<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Clinic Record</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <aside class="w-64 bg-slate-900 text-white">
            <div class="p-6 text-xl font-bold border-b border-slate-800">CLINIC OS</div>
            <nav class="mt-6">
                <a href="{{ route('dashboard') }}" class="block py-3 px-6 hover:bg-slate-800">Dashboard</a>
                <a href="{{ route('record.index') }}" class="block py-3 px-6 hover:bg-slate-800 {{ request()->routeIs('record.*') ? 'bg-blue-600' : '' }}">Clinic Records</a>
            </nav>
        </aside>

        <main class="flex-1 p-10 overflow-y-auto">
            <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-6 bg-slate-50 border-b">
                    <h2 class="text-2xl font-bold text-slate-800">Add New Patient Record</h2>
                </div>

                <form action="{{ route('record.store') }}" method="POST" class="p-8 grid grid-cols-2 gap-6">
                    @csrf
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Patient Name</label>
                        <input type="text" name="patient_name" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Enter full name" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date of Consultation</label>
                        <input type="date" name="consultation_date" value="{{ now()->format('Y-m-d') }}" class="w-full p-3 border rounded-lg outline-none" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Birthday</label>
                        <input type="date" id="birthday" name="birthday" class="w-full p-3 border rounded-lg outline-none" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Gender</label>
                        <select name="gender" class="w-full p-3 border rounded-lg outline-none">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Age</label>
                        <input type="text" id="age_display" name="age_display" class="w-full p-3 border rounded-lg bg-gray-50 outline-none" placeholder="0" readonly>
                        <input type="hidden" id="age" name="age"> 
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Diagnosis</label>
                        <textarea name="diagnosis" rows="3" class="w-full p-3 border rounded-lg outline-none" placeholder="Describe symptoms/results"></textarea>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Medicines Given</label>
                        <textarea name="medicines_given" rows="2" class="w-full p-3 border rounded-lg outline-none" placeholder="List medications"></textarea>
                    </div>

                    <div class="col-span-2 flex justify-end gap-4 mt-4">
                        <a href="{{ route('record.index') }}" class="px-6 py-2 text-gray-600 font-semibold hover:text-gray-800 transition">Cancel</a>
                        <button type="submit" class="px-8 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-md">
                            Save Record
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('birthday').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            
            if (isNaN(birthDate.getTime())) return;

            // Calculate total months difference
            let totalMonths = (today.getFullYear() - birthDate.getFullYear()) * 12;
            totalMonths += today.getMonth() - birthDate.getMonth();

            // Adjust if the current day is before the birth day
            if (today.getDate() < birthDate.getDate()) {
                totalMonths--;
            }

            const ageDisplay = document.getElementById('age_display');
            const ageHidden = document.getElementById('age');

            if (totalMonths < 0) {
                ageDisplay.value = "0 Months";
                ageHidden.value = 0;
            } else if (totalMonths < 12) {
                // Show months for infants
                ageDisplay.value = totalMonths + " Month" + (totalMonths === 1 ? "" : "s");
                ageHidden.value = 0; // Storing 0 in age column for database consistency
            } else {
                // Show years for everyone else
                const years = Math.floor(totalMonths / 12);
                ageDisplay.value = years + " Year" + (years === 1 ? "" : "s");
                ageHidden.value = years;
            }
        });
    </script>
</body>
</html>
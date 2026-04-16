@extends('layouts.guest')

@section('content')
{{-- 1. Outer Container (Light blue background) --}}
<div class="min-h-screen flex items-center justify-center bg-[#E0E9FF] p-4 md:p-8">
    
    {{-- 2. Main Card: Split Flexbox Layout --}}
    <div class="flex flex-col md:flex-row w-full max-w-6xl bg-white rounded-3xl shadow-2xl overflow-hidden min-h-[600px] border border-gray-100">
        
        {{-- ========================================== --}}
        {{-- LEFT COLUMN: Text Block & Character        --}}
        {{-- ========================================== --}}
        {{-- Sets bg color, handles responsive padding, and overflow --}}
        <div class="w-full md:w-1/2 p-10 sm:p-14 lg:p-20 flex flex-col bg-[#E6F7F5] overflow-hidden relative">
            
            {{-- Hello Text Block (Z-index keeps it above background elements) --}}
            <div class="relative z-10 space-y-3">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-[#2D8A80] uppercase tracking-tighter leading-none">
                    HELLO !
                </h1>
                {{-- Updated to use the professional off-black color discussed previously --}}
                <p class="text-base sm:text-lg text-gray-900 font-medium max-w-xs leading-relaxed">
                    Welcome to Barangay Banilad Health Center!
                </p>
            </div>

            {{-- 3. Character Image Container (Positioned absolutely) --}}
            {{-- The responsive positioning ensures it peeks in correctly on desktop but stays managed on mobile --}}
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-[72%] md:h-[60%] z-20 pointer-events-none">
    <img 
        src="https://img.freepik.com/free-psd/3d-illustration-cartoon-character-doctor-medical-assistant_1150-62164.jpg" 
        alt="3D Doctor Character peeking"
        class="w-full h-full object-contain object-bottom transform scale-x-[-1]" 
    />
</div>
            
            {{-- Decorative teal circle --}}
            <div class="absolute -bottom-10 -left-10 w-40 h-40 border-8 border-[#2D8A80]/10 rounded-full"></div>
        </div>

        {{-- ========================================== --}}
        {{-- RIGHT COLUMN: Logo, Form, and Logic        --}}
        {{-- ========================================== --}}
        <div class="w-full md:w-1/2 p-10 sm:p-14 lg:p-20 flex flex-col justify-center bg-white">
            
            {{-- Logo area: generic style --}}
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-[#2D8A80] tracking-tight">
                    <span class="font-light text-gray-400"></span> Clinic OS
                </h2>
            </div>

            {{-- **Error Display Logic: Kept exactly as is** --}}
            @if ($errors->any())
                <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm w-full rounded-r-xl">
                    <ul class="space-y-1 font-medium list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- **Form Start Logic: Kept exactly as is** --}}
            <form action="{{ route('login') }}" method="POST" class="space-y-6 w-full">
                @csrf
                
                {{-- 4. Email Input: Styled to match image 1 and 2 --}}
                <div class="space-y-2">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest">Username or E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@clinic.com"
                        class="w-full px-6 py-4 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition bg-gray-50/50">
                </div>

                {{-- 5. Password Input: With generic "Forgot?" link styling --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest">Password</label>
                        {{-- Generic forgot password link placeholder --}}
                        <a href="#" class="text-xs font-medium text-gray-400 hover:text-blue-600">Forgot Password?</a>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full px-6 py-4 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none text-base font-medium transition bg-gray-50/50">
                </div>

                {{-- 6. Submit Button: Re-styled as full-width and rounded-xl (Image 2 style) --}}
                <div class="pt-6">
                    <button type="submit" class="w-full py-4 bg-blue-600 text-white text-sm font-black rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-100 transition transform hover:-translate-y-0.5 active:scale-95 uppercase tracking-wider">
                        Sign In
                    </button>
                </div>
                
                {{-- **Register Link Logic: Kept exactly as is** --}}
                <div class="text-center mt-10">
                    <p class="text-sm text-gray-500 font-medium">Don't have an account? 
                        <a href="{{ route('register') }}" class="text-blue-600 font-bold hover:underline">Register here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@php
    $line1 = trim((string) ($printHeader['line_1'] ?? 'Republic of the Philippines'));
    $line2 = trim((string) ($printHeader['line_2'] ?? 'Office of the City Health'));
    $line3 = trim((string) ($printHeader['line_3'] ?? 'Dumaguete City'));
    $title = trim((string) ($printHeader['title'] ?? 'Individual Treatment Record'));

    $rawLogoPath = trim((string) ($printHeader['logo_path'] ?? ''));
    $logoUrl = $rawLogoPath !== '' ? asset('storage/' . ltrim($rawLogoPath, '/')) : null;
@endphp

<div class="print-template-header border-b-2 border-slate-700 pb-3">
    <div class="grid grid-cols-[80px_1fr_80px] items-center gap-3">
        <div class="w-[72px] h-[72px] border border-slate-600 rounded-full overflow-hidden flex items-center justify-center bg-white">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Clinic seal" class="w-full h-full object-cover">
            @else
                <div class="w-[64px] h-[64px] rounded-full border border-slate-500 flex items-center justify-center text-[8px] font-bold text-slate-700 text-center leading-tight px-1">
                    OFFICIAL<br>SEAL
                </div>
            @endif
        </div>

        <div class="text-center leading-tight text-slate-900">
            <p class="text-[12px] tracking-wide">{{ $line1 }}</p>
            <p class="text-[31px] font-serif uppercase">{{ $line2 }}</p>
            <p class="text-[15px]">{{ $line3 }}</p>
        </div>

        <div aria-hidden="true"></div>
    </div>

    <p class="mt-2 text-center text-[31px] font-serif uppercase tracking-wide text-slate-900">
        {{ $title }}
    </p>
</div>

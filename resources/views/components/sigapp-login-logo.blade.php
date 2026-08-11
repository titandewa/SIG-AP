@props(['variant' => 'wordmark'])

@if ($variant === 'mark')
<svg viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <defs>
        <linearGradient id="sigappMarkAccent" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#3b82f6" />
            <stop offset="1" stop-color="#2563eb" />
        </linearGradient>
    </defs>
    <rect x="14" y="18" width="118" height="130" rx="18" fill="none" stroke="currentColor" stroke-width="12" />
    <path d="M87 18v30c0 6.1 5 11 11 11h29" fill="none" stroke="currentColor" stroke-width="12" stroke-linejoin="round" />
    <path d="M91 43h25l-25-25v25Z" fill="url(#sigappMarkAccent)" />
    <path d="M71 83c0-12.7 10.4-22.6 25.8-22.6 10.8 0 19.6 4 26 11.2l-8.9 7.9c-4.2-4.8-9.6-7.6-16.5-7.6-7.6 0-12.8 3.7-12.8 8.9 0 5.4 4.4 7.4 13.9 9.6 12.6 2.9 26.9 6.5 26.9 22.6 0 14.6-12.6 23.4-30 23.4-12 0-21.8-4.6-28.2-12.2l8.9-8c4.5 5.4 11.1 8.8 19.4 8.8 8.4 0 13.7-3.4 13.7-8.7 0-6.6-6.5-8.6-15.5-10.7-10.2-2.4-21.8-5.4-21.8-17.6Z" fill="currentColor" />
    <path d="M94 104l20 20" fill="none" stroke="url(#sigappMarkAccent)" stroke-width="8" stroke-linecap="round" />
    <path d="M106 93l21 21-14 14-21-21 14-14Z" fill="url(#sigappMarkAccent)" />
    <path d="M77 120c10 8 20 10 28 8 5-1 9-3 13-7" fill="none" stroke="url(#sigappMarkAccent)" stroke-width="6" stroke-linecap="round" />
    <path d="M50 137c14 12 29 16 44 16 10 0 21-3 31-8" fill="none" stroke="url(#sigappMarkAccent)" stroke-width="6" stroke-linecap="round" opacity="0.96" />
</svg>
@else
<svg viewBox="0 0 1120 280" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <defs>
        <linearGradient id="sigappLogoTextBlue" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#3b82f6" />
            <stop offset="1" stop-color="#2f6feb" />
        </linearGradient>
    </defs>

    <g transform="translate(16 12)">
        <g transform="translate(0 0)">
            <rect x="8" y="24" width="168" height="180" rx="22" fill="none" stroke="currentColor" stroke-width="14" />
            <path d="M120 24v42c0 7.2 5.8 13 13 13h39" fill="none" stroke="currentColor" stroke-width="14" stroke-linejoin="round" />
            <path d="M126 58h34l-34-34v34Z" fill="url(#sigappLogoTextBlue)" opacity="0.95" />
            <path d="M64 116c0-18.7 15.4-33.4 39-33.4 16 0 29.1 6.2 38.2 17.4l-11.9 10.7c-6.5-7.6-14.6-11.9-24.8-11.9-11.5 0-19.3 6.1-19.3 14.4 0 9 7.7 11.9 22 15 18.5 3.9 39.3 9.8 39.3 33.4 0 21.7-18 34.8-43.1 34.8-17.9 0-32.8-6.9-42-18.5l12.1-10.7c6.9 8.2 16.8 13.5 29 13.5 12.6 0 20.7-5.3 20.7-13.9 0-10.8-10.8-14-25.3-17.4-16.2-3.8-34.1-8.2-34.1-26.6Z" fill="currentColor" />
            <path d="M140 150l30 30" fill="none" stroke="url(#sigappLogoTextBlue)" stroke-width="11" stroke-linecap="round" />
            <path d="M157 135l30 30-22 22-30-30 22-22Z" fill="url(#sigappLogoTextBlue)" />
            <path d="M112 188c15 10 30 13 43 10 8-2 15-6 21-12" fill="none" stroke="url(#sigappLogoTextBlue)" stroke-width="8" stroke-linecap="round" />
            <path d="M56 209c20 18 42 24 65 24 16 0 32-4 47-12" fill="none" stroke="url(#sigappLogoTextBlue)" stroke-width="8" stroke-linecap="round" opacity="0.96" />
        </g>

        <g transform="translate(250 46)">
            <text x="0" y="92" fill="currentColor" font-family="Inter, Segoe UI, Arial, Helvetica, sans-serif" font-size="96" font-weight="900" letter-spacing="-3">SIG</text>
            <text x="304" y="92" fill="url(#sigappLogoTextBlue)" font-family="Inter, Segoe UI, Arial, Helvetica, sans-serif" font-size="96" font-weight="900" letter-spacing="-3">APP</text>
            <g transform="translate(166 130)">
                <line x1="0" y1="24" x2="40" y2="24" stroke="#3b82f6" stroke-width="4" stroke-linecap="round" />
                <line x1="300" y1="24" x2="340" y2="24" stroke="#3b82f6" stroke-width="4" stroke-linecap="round" />
                <text x="50" y="30" fill="currentColor" font-family="Inter, Segoe UI, Arial, Helvetica, sans-serif" font-size="28" font-weight="800" letter-spacing="0.4">SIGN FASTER, WORK BETTER</text>
            </g>
        </g>
    </g>
</svg>
@endif

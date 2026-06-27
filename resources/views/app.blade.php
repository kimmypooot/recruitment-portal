<!-- resources/views/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <link rel="preload" href="/images/cscbg_facade.jpeg" as="image" fetchpriority="high">
    <meta name="app-url" content="{{ config('app.url') }}">
    <meta property="og:site_name" content="CSC RO VIII - Recruitment Portal">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ url('/images/csc-logo.png') }}">
    <meta property="og:image:width" content="256">
    <meta property="og:image:height" content="256">
    <meta name="twitter:card" content="summary">
    <link rel="canonical" href="{{ url()->current() }}">
    <title inertia>{{ config('app.name') }}</title>
    @php
    $ldJson = [
        '@context' => 'https://schema.org',
        '@type'    => 'GovernmentOrganization',
        'name'     => 'Civil Service Commission Regional Office VIII',
        'alternateName' => 'CSC RO VIII',
        'url'      => config('app.url'),
        'logo'     => url('/images/csc-logo.png'),
        'address'  => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Palo',
            'addressRegion'   => 'Leyte',
            'addressCountry'  => 'PH',
        ],
        'contactPoint' => [
            '@type'     => 'ContactPoint',
            'email'     => 'ro08.hrd@csc.gov.ph',
            'telephone' => '(053) 888-1811',
        ],
    ];
    @endphp
    <script type="application/ld+json">{{ json_encode($ldJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
    <script>
      (function () {
        if (!sessionStorage.getItem('csc_page_transition')) return
        sessionStorage.removeItem('csc_page_transition')

        var veil = document.createElement('div')
        veil.id  = 'csc-page-veil'
        veil.style.cssText = [
          'position:fixed', 'inset:0', 'z-index:999999', 'pointer-events:all',
          'background:linear-gradient(135deg,#f0eef9 0%,#e8eafa 50%,#fdeef0 100%)',
          'opacity:1', 'transition:opacity 0.4s ease',
        ].join(';')
        document.documentElement.appendChild(veil)

        window.addEventListener('load', function () {
          requestAnimationFrame(function () {
            veil.style.opacity = '0'
            setTimeout(function () { veil.remove() }, 420)
          })
        })
      })()
    </script>
</head>
<body class="antialiased bg-gray-50">
    @inertia
</body>
</html>

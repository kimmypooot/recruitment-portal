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
    <script src="/js/page-transition.js"></script>
</head>
<body class="antialiased bg-gray-50">
    @include('partials.imis-boot', ['system' => 'Recruitment Portal', 'office' => 'Regional Office VIII', 'opening' => ($page['component'] ?? null) === 'Auth/ImisSso'])
    @inertia
</body>
</html>

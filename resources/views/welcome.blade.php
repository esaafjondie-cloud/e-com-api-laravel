<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ecommerce API - Welcome</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS (via CDN for simplicity in welcome view) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 3rem;
            max-width: 28rem;
            width: 100%;
            text-align: center;
        }

        .btn-admin {
            background-color: #4f46e5;
            color: white;
            transition: background-color 0.2s;
        }

        .btn-admin:hover {
            background-color: #4338ca;
        }

        .btn-vendor {
            background-color: #059669;
            color: white;
            transition: background-color 0.2s;
        }

        .btn-vendor:hover {
            background-color: #047857;
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 0.75rem 1.5rem;
            font-size: 1.125rem;
            font-weight: 500;
            text-align: center;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-decoration: none;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome!</h1>
        <p class="text-gray-600 mb-8">Choose your dashboard to log in.</p>

        <a href="{{ url('/admin') }}" class="btn btn-admin">
            Admin Dashboard
        </a>

        <a href="{{ url('/vendor-panel') }}" class="btn btn-vendor">
            Vendor Dashboard
        </a>

        <div class="mt-8 pt-6 border-t border-gray-100 hidden">
            <p class="text-sm text-gray-500">Ecommerce API System</p>
        </div>
    </div>
</body>
</html>

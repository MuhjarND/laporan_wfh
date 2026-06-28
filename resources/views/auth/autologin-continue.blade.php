<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Menghubungkan - SIAP WFH</title>
    <meta name="theme-color" content="#0f4c3a">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('logo3.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('pwa/icon-192.png') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }
        .box {
            width: 100%;
            max-width: 460px;
            padding: 28px;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .08);
            text-align: center;
        }
        h1 { margin: 0 0 10px; font-size: 1.25rem; color: #0f4c3a; }
        .logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
            margin-bottom: 14px;
        }
        p { margin: 0 0 18px; color: #4b5563; line-height: 1.5; }
        button {
            border: 0;
            display: inline-block;
            padding: 10px 16px;
            border-radius: 6px;
            background: #0f4c3a;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="box">
        <img src="{{ asset('logo3.png') }}" alt="Logo Aplikasi" class="logo">
        <h1>Menghubungkan Akun</h1>
        <p>Mohon tunggu, Anda sedang diarahkan ke dashboard.</p>
        <form id="autologinForm" method="POST" action="{{ route('autologin.login') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <button type="submit">Masuk ke Dashboard</button>
        </form>
    </div>

    <script>
        document.getElementById('autologinForm').submit();
    </script>
</body>
</html>

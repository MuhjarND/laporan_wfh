<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - SIAP WFH PTA Papua Barat</title>
    <meta name="theme-color" content="#0f4c3a">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('logo5.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('pwa/icon-192.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh; display: flex;
            background: #0f4c3a;
        }
        .login-left {
            flex: 0 0 57.5%; display: flex; flex-direction: column; justify-content: center; align-items: center;
            background: linear-gradient(135deg, #0a3d2e 0%, #0f4c3a 50%, #1a6b50 100%);
            padding: 40px; color: #fff; position: relative; overflow: hidden;
        }
        .login-left::before {
            content: ''; position: absolute; top: -50%; right: -50%;
            width: 100%; height: 200%; background: radial-gradient(circle, rgba(232,184,40,0.08) 0%, transparent 70%);
        }
        .login-left .logo-area { text-align: center; z-index: 1; }
        .login-left .logo-icon {
            width: 126px; height: 126px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            filter: drop-shadow(0 10px 24px rgba(0,0,0,.18));
        }
        .login-left .logo-icon img,
        .mobile-header .m-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .login-left h1 { font-size: 2rem; font-weight: 800; margin-bottom: 8px; }
        .login-left h2 {
            font-size: 1rem;
            font-weight: 700;
            color: rgba(255,255,255,.92);
            margin: 0 auto 8px;
            max-width: 460px;
            line-height: 1.45;
        }
        .login-left .divider {
            width: 60px; height: 3px; background: #e8b828; border-radius: 2px;
            margin: 16px auto;
        }
        .login-left p {
            font-size: .88rem;
            color: rgba(255,255,255,.68);
            max-width: 430px;
            text-align: center;
            line-height: 1.6;
        }

        .login-right {
            flex: 0 0 42.5%; display: flex; align-items: center; justify-content: center;
            background: #fff; padding: 50px clamp(54px, 5.625vw, 108px);
        }
        .login-form { width: 100%; max-width: 600px; }
        .login-form h3 { color: #0f4c3a; font-weight: 700; font-size: 1.7rem; margin-bottom: 4px; }
        .login-form p.subtitle { color: #6b7280; font-size: .95rem; margin-bottom: 12px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #374151; font-weight: 600; font-size: .82rem; margin-bottom: 6px; }
        .input-wrap {
            display: flex; align-items: center; border: 2px solid #e5e7eb;
            border-radius: 8px; overflow: hidden; transition: all .2s;
        }
        .input-wrap:focus-within { border-color: #0f4c3a; box-shadow: 0 0 0 3px rgba(15,76,58,.1); }
        .input-wrap .icon { padding: 12px 14px; color: #9ca3af; background: #f9fafb; border-right: 1px solid #e5e7eb; }
        .input-wrap input {
            flex: 1; border: none; outline: none; padding: 12px 14px;
            font-size: .9rem; color: #1a202c; font-family: 'Inter', sans-serif;
        }
        .input-wrap input::placeholder { color: #9ca3af; }
        .check-row { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; }
        .check-row input[type="checkbox"] { accent-color: #0f4c3a; width: 16px; height: 16px; }
        .check-row label { color: #6b7280; font-size: .82rem; margin: 0; cursor: pointer; }
        .btn-login {
            width: 100%; padding: 13px; border: none; border-radius: 8px;
            background: linear-gradient(135deg, #0f4c3a, #1a6b50);
            color: #fff; font-weight: 700; font-size: .95rem; cursor: pointer;
            transition: all .2s;
        }
        .btn-login:hover { box-shadow: 0 6px 20px rgba(15,76,58,.35); transform: translateY(-1px); }
        .login-separator {
            display: flex; align-items: center; gap: 12px; color: #9ca3af;
            font-size: .78rem; margin: 22px 0;
        }
        .login-separator::before,
        .login-separator::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
        .btn-sso {
            display: block; width: 100%; padding: 12px; border-radius: 8px;
            border: 2px solid #0f4c3a; color: #0f4c3a; background: #fff;
            font-weight: 700; font-size: .95rem; text-align: center; text-decoration: none;
            transition: all .2s;
        }
        .btn-sso:hover { background: #ecfdf5; color: #0a3d2e; text-decoration: none; }
        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            border-radius: 8px; padding: 10px 14px; font-size: .85rem; margin-bottom: 20px;
        }
        .alert-success {
            background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;
            border-radius: 8px; padding: 10px 14px; font-size: .85rem; margin-bottom: 20px;
        }
        .invalid-feedback { color: #dc2626; font-size: .78rem; margin-top: 4px; display: block; }

        @media(max-width:900px) {
            .login-left { display: none; }
            .login-right { flex: 1 1 auto; width: 100%; }
            body { background: #fff; }
        }
        @media(max-width:480px) {
            .login-right { padding: 30px 24px; }
        }

        /* Mobile header - shown only when left panel is hidden */
        .mobile-header { display: none; text-align: center; margin-bottom: 24px; }
        .mobile-header .m-icon {
            width: 74px; height: 74px;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 12px;
        }
        .mobile-header h3 { color: #0f4c3a; font-weight: 700; font-size: 1.1rem; }
        .mobile-header p { color: #6b7280; font-size: .78rem; line-height: 1.4; }
        @media(max-width:900px) { .mobile-header { display: block; } }
    </style>
</head>
<body>
    <div class="login-left">
        <div class="logo-area">
            <div class="logo-icon"><img src="{{ asset('logo5.png') }}" alt="Logo Aplikasi"></div>
            <h1>SIAP WFH</h1>
            <h2>Sistem Aplikasi Pelaporan Work From Home PTA Papua Barat</h2>
            <div class="divider"></div>
            <p>Aplikasi internal untuk pendaftaran WFH, pengisian kegiatan, pelaporan eviden, monitoring, dan proses persetujuan secara elektronik.</p>
        </div>
    </div>
    <div class="login-right">
        <div class="login-form">
            <div class="mobile-header">
                <div class="m-icon"><img src="{{ asset('logo5.png') }}" alt="Logo Aplikasi"></div>
                <h3>SIAP WFH</h3>
                <p>Sistem Aplikasi Pelaporan Work From Home PTA Papua Barat</p>
            </div>

            <h3>Selamat Datang 👋</h3>
            <p class="subtitle">Masuk ke akun Anda untuk melanjutkan</p>

            @if(session('error'))
                <div class="alert-error"><i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert-success"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="nip">NIP</label>
                    <div class="input-wrap">
                        <span class="icon"><i class="fas fa-id-card"></i></span>
                        <input type="text" name="nip" id="nip" value="{{ old('nip') }}" placeholder="Masukkan NIP Anda" required autofocus>
                    </div>
                    @error('nip')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <span class="icon"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" placeholder="Masukkan Password" required>
                    </div>
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="check-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat Saya</label>
                </div>
                <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt mr-2"></i>Masuk</button>
            </form>

        </div>
    </div>
</body>
</html>

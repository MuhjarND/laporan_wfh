<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Laporan WFH PTA Papua Barat</title>
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
            flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;
            background: linear-gradient(135deg, #0a3d2e 0%, #0f4c3a 50%, #1a6b50 100%);
            padding: 40px; color: #fff; position: relative; overflow: hidden;
        }
        .login-left::before {
            content: ''; position: absolute; top: -50%; right: -50%;
            width: 100%; height: 200%; background: radial-gradient(circle, rgba(232,184,40,0.08) 0%, transparent 70%);
        }
        .login-left .logo-area { text-align: center; z-index: 1; }
        .login-left .logo-icon {
            width: 80px; height: 80px; background: rgba(232,184,40,0.15);
            border: 2px solid rgba(232,184,40,0.3); border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 2rem; color: #e8b828;
        }
        .login-left h1 { font-size: 1.6rem; font-weight: 800; margin-bottom: 8px; }
        .login-left h2 { font-size: .95rem; font-weight: 400; color: rgba(255,255,255,.7); margin-bottom: 6px; }
        .login-left .divider {
            width: 60px; height: 3px; background: #e8b828; border-radius: 2px;
            margin: 16px auto;
        }
        .login-left p { font-size: .82rem; color: rgba(255,255,255,.5); max-width: 300px; text-align: center; line-height: 1.5; }

        .login-right {
            width: 480px; display: flex; align-items: center; justify-content: center;
            background: #fff; padding: 50px 40px;
        }
        .login-form { width: 100%; max-width: 380px; }
        .login-form h3 { color: #0f4c3a; font-weight: 700; font-size: 1.3rem; margin-bottom: 4px; }
        .login-form p.subtitle { color: #6b7280; font-size: .85rem; margin-bottom: 28px; }
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
        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            border-radius: 8px; padding: 10px 14px; font-size: .85rem; margin-bottom: 20px;
        }
        .invalid-feedback { color: #dc2626; font-size: .78rem; margin-top: 4px; display: block; }

        @media(max-width:900px) {
            .login-left { display: none; }
            .login-right { width: 100%; }
            body { background: #fff; }
        }
        @media(max-width:480px) {
            .login-right { padding: 30px 24px; }
        }

        /* Mobile header - shown only when left panel is hidden */
        .mobile-header { display: none; text-align: center; margin-bottom: 24px; }
        .mobile-header .m-icon {
            width: 56px; height: 56px; background: #ecfdf5; border-radius: 14px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: #0f4c3a; margin-bottom: 10px;
        }
        .mobile-header h3 { color: #0f4c3a; font-weight: 700; font-size: 1.1rem; }
        .mobile-header p { color: #6b7280; font-size: .78rem; }
        @media(max-width:900px) { .mobile-header { display: block; } }
    </style>
</head>
<body>
    <div class="login-left">
        <div class="logo-area">
            <div class="logo-icon"><i class="fas fa-file-alt"></i></div>
            <h1>Laporan WFH</h1>
            <h2>Pengadilan Tinggi Agama Papua Barat</h2>
            <div class="divider"></div>
            <p>Sistem pelaporan kegiatan Work From Home untuk pegawai PTA Papua Barat</p>
        </div>
    </div>
    <div class="login-right">
        <div class="login-form">
            <div class="mobile-header">
                <div class="m-icon"><i class="fas fa-file-alt"></i></div>
                <h3>Laporan WFH</h3>
                <p>PTA Papua Barat</p>
            </div>

            <h3>Selamat Datang 👋</h3>
            <p class="subtitle">Masuk ke akun Anda untuk melanjutkan</p>

            @if(session('error'))
                <div class="alert-error"><i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}</div>
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

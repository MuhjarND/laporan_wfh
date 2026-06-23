<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laporan WFH') - PTA Papua Barat</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        :root {
            --primary: #0f4c3a;
            --primary-hover: #1a6b50;
            --sidebar-bg: #0f4c3a;
            --sidebar-active: #e8b828;
            --accent: #e8b828;
            --accent-hover: #d4a520;
            --bg-body: #f0f2f5;
            --bg-card: #ffffff;
            --text-dark: #1a202c;
            --text-body: #374151;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.05), 0 2px 4px rgba(0,0,0,0.04);
        }
        * { box-sizing: border-box; }
        body { font-family:'Inter',sans-serif; background:var(--bg-body); color:var(--text-body); font-size:14px; }

        .mobile-bottom-nav { display: none; }

        /* ===== SIDEBAR ===== */
        .main-sidebar {
            background: linear-gradient(180deg, #0a3d2e 0%, #0f4c3a 40%, #145740 100%) !important;
            box-shadow: 2px 0 10px rgba(0,0,0,0.15);
        }
        @media (min-width: 769px) {
            body.sidebar-collapse .main-sidebar {
                margin-left: -250px !important;
            }
            body.sidebar-collapse .content-wrapper,
            body.sidebar-collapse .main-header,
            body.sidebar-collapse .main-footer {
                margin-left: 0 !important;
            }
        }
        .main-sidebar .brand-link {
            background: rgba(0,0,0,0.15) !important;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 14px 15px;
        }
        .main-sidebar .brand-link .brand-text {
            color: #fff !important; font-weight: 700; font-size: 1rem;
        }
        .main-sidebar .brand-link small { color: var(--accent) !important; }
        .sidebar .user-panel {
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 15px 12px;
        }
        .sidebar .user-panel .info a { color: #fff !important; font-weight: 600; font-size: .85rem; }
        .sidebar .user-panel .info small { color: var(--accent); font-size: .7rem; }
        .nav-sidebar .nav-header {
            color: rgba(232,184,40,0.7) !important;
            font-size: .65rem !important; letter-spacing: 1.5px; padding: 12px 18px 4px;
        }
        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link,
        .nav-sidebar .nav-link {
            color: rgba(255,255,255,0.7) !important;
            border-radius: 6px; margin: 1px 10px; padding: 10px 14px; transition: all .2s;
        }
        .nav-sidebar .nav-link:hover {
            background: rgba(255,255,255,0.08) !important;
            color: #fff !important;
        }
        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active,
        .nav-sidebar .nav-link.active {
            background: var(--accent) !important;
            color: #0f4c3a !important; font-weight: 600;
            box-shadow: 0 2px 8px rgba(232,184,40,0.35);
        }
        .nav-sidebar .nav-link.active .nav-icon { color: #0f4c3a !important; }
        .nav-sidebar .nav-link .nav-icon { color: rgba(255,255,255,0.5); width: 22px; text-align: center; }
        .nav-sidebar .nav-link:hover .nav-icon { color: var(--accent); }

        /* ===== NAVBAR ===== */
        .main-header {
            background: #fff !important; border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
        .main-header .nav-link { color: #4b5563 !important; font-size: .9rem; }
        .main-header .nav-link:hover { color: var(--primary) !important; }

        /* ===== CONTENT ===== */
        .content-wrapper { background: var(--bg-body) !important; }
        .content-header h1 { color: var(--text-dark); font-weight: 700; font-size: 1.35rem; }

        /* ===== CARDS ===== */
        .card {
            background: #fff; border: 1px solid var(--border); border-radius: 10px;
            box-shadow: var(--shadow); transition: box-shadow .2s;
        }
        .card:hover { box-shadow: var(--shadow-md); }
        .card-header {
            background: #f9fafb; border-bottom: 1px solid var(--border);
            font-weight: 600; border-radius: 10px 10px 0 0 !important; padding: 12px 16px;
        }
        .card-header .card-title { font-size: .9rem; color: var(--text-dark); }
        .card-header.d-flex { gap: 12px; }
        .card-header.d-flex .card-title { margin-bottom: 0; }
        .card-header .card-tools {
            float: none;
            margin-left: auto;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }
        .card-footer { background: #f9fafb; border-top: 1px solid var(--border); }

        /* ===== SMALL BOX ===== */
        .small-box { border-radius: 10px; border: none; box-shadow: var(--shadow); overflow: hidden; }
        .small-box .inner h3 { font-weight: 800; font-size: 1.8rem; }
        .small-box .inner p { font-size: .8rem; font-weight: 500; }
        .small-box .icon { top: 8px; font-size: 55px; }
        .small-box .small-box-footer { font-size: .78rem; }
        .bg-gradient-success { background: linear-gradient(135deg, #0f4c3a, #1a7a56) !important; }
        .bg-gradient-info { background: linear-gradient(135deg, #1e40af, #3b82f6) !important; }
        .bg-gradient-warning { background: linear-gradient(135deg, #b45309, #f59e0b) !important; }
        .bg-gradient-danger { background: linear-gradient(135deg, #991b1b, #ef4444) !important; }

        /* ===== TABLE ===== */
        .table { color: var(--text-body); font-size: .85rem; }
        .table thead th {
            background: #f1f5f9; border-bottom: 2px solid var(--primary) !important;
            color: var(--primary); font-weight: 700; font-size: .75rem;
            text-transform: uppercase; letter-spacing: .5px; padding: 10px 12px;
        }
        .table td { border-top: 1px solid #f1f5f9; padding: 10px 12px; vertical-align: middle; }
        .table th:last-child,
        .table td:last-child { text-align: right; }
        .table td:last-child .btn-group,
        .table td:last-child .users-actions {
            justify-content: flex-end;
        }
        .table-hover tbody tr:hover { background: #f0fdf4; }
        .table-striped tbody tr:nth-of-type(odd) { background: #fafbfc; }

        /* ===== BUTTONS ===== */
        .btn { border-radius: 6px; font-weight: 500; font-size: .85rem; transition: all .15s; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); box-shadow: 0 3px 8px rgba(15,76,58,.25); }
        .btn-success { background: #059669; border-color: #059669; }
        .btn-success:hover { background: #047857; box-shadow: 0 3px 8px rgba(5,150,105,.25); }
        .btn-warning { background: var(--accent); border-color: var(--accent); color: #1a202c; }
        .btn-warning:hover { background: var(--accent-hover); color: #1a202c; }
        .btn-danger { background: #dc2626; border-color: #dc2626; }
        .btn-outline-secondary { border-color: #d1d5db; color: #6b7280; }
        .btn-outline-secondary:hover { background: #f3f4f6; border-color: #9ca3af; color: #374151; }

        /* ===== FORMS ===== */
        .form-control, .custom-select {
            border: 1px solid #d1d5db; border-radius: 6px; color: var(--text-dark);
            padding: 8px 12px; font-size: .875rem; transition: all .15s;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15,76,58,.1); }
        label { color: var(--text-dark); font-weight: 600; font-size: .8rem; margin-bottom: 4px; }

        /* ===== BADGES ===== */
        .badge { font-weight: 600; padding: 4px 10px; border-radius: 4px; font-size: .72rem; letter-spacing: .3px; }
        .badge-success { background: #059669; }
        .badge-info { background: #2563eb; }
        .badge-warning { background: #d97706; color: #fff; }
        .badge-danger { background: #dc2626; }
        .badge-secondary { background: #6b7280; }

        /* ===== ALERTS ===== */
        .alert { border-radius: 8px; font-size: .875rem; border: none; }
        .alert-success { background: #ecfdf5; color: #065f46; border-left: 4px solid #059669; }
        .alert-danger { background: #fef2f2; color: #991b1b; border-left: 4px solid #dc2626; }
        .alert-info { background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6; }

        /* ===== DROPDOWN ===== */
        .dropdown-menu { border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,.1); }
        .dropdown-item { font-size: .85rem; padding: 8px 16px; color: var(--text-body); }
        .dropdown-item:hover { background: #f0fdf4; color: var(--primary); }
        .dropdown-header { color: var(--primary) !important; font-weight: 700; }

        /* ===== BREADCRUMB ===== */
        .breadcrumb { background: transparent; margin-bottom: 0; font-size: .8rem; }
        .breadcrumb-item a { color: var(--primary); }
        .breadcrumb-item.active { color: var(--text-muted); }

        /* ===== PAGINATION ===== */
        .page-item .page-link { border-color: var(--border); color: var(--text-muted); font-size: .85rem; }
        .page-item.active .page-link { background: var(--primary); border-color: var(--primary); }

        /* ===== FOOTER ===== */
        .main-footer { background: #fff; border-top: 1px solid var(--border); color: var(--text-muted); font-size: .8rem; }

        /* ===== ANIMATION ===== */
        @keyframes fadeIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
        .animate-in { animation: fadeIn .3s ease-out; }

        /* ===== MOBILE ===== */
        @media(max-width:768px) {
            body { padding-bottom: calc(70px + env(safe-area-inset-bottom)); }
            .main-sidebar { display: none !important; }
            .main-header { margin-left: 0 !important; }
            .main-header .navbar-nav:first-child { display: none; }
            .content-wrapper, .main-footer { margin-left: 0 !important; }
            .content-header h1 { font-size: 1.1rem; }
            .small-box .inner h3 { font-size: 1.4rem; }
            .small-box .inner p { font-size: .72rem; }
            .small-box .icon { font-size: 40px; }
            .content-wrapper>.content { padding: .5rem; }
            .content-wrapper .container-fluid { padding: 0 8px; }
            .card-body { padding: 12px; }
            .card-header { padding: 10px 12px; }
            .table { font-size: .78rem; }
            .table thead th { font-size: .68rem; padding: 8px; }
            .table td { padding: 8px; }
            .btn-group-sm>.btn { padding: 3px 7px; font-size: .72rem; }
            .main-footer { padding: 8px 12px; font-size: .72rem; }
            .breadcrumb { font-size: .7rem; }

            .mobile-bottom-nav {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 1040;
                display: grid;
                grid-auto-flow: column;
                grid-auto-columns: 1fr;
                gap: 4px;
                padding: 8px 10px calc(8px + env(safe-area-inset-bottom));
                background: #fff;
                border-top: 1px solid var(--border);
                box-shadow: 0 -8px 24px rgba(15, 23, 42, .12);
            }
            .mobile-bottom-nav a,
            .mobile-bottom-nav button {
                position: relative;
                min-width: 0;
                min-height: 52px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 4px;
                color: #6b7280;
                text-decoration: none;
                border-radius: 8px;
                border: 0;
                background: transparent;
                font-size: .68rem;
                font-weight: 700;
                line-height: 1.1;
                font-family: inherit;
                cursor: pointer;
            }
            .mobile-bottom-nav a i,
            .mobile-bottom-nav button i {
                font-size: 1.05rem;
                line-height: 1;
            }
            .mobile-bottom-nav .bottom-nav-badge {
                position: absolute;
                top: 4px;
                right: 18%;
                min-width: 18px;
                height: 18px;
                padding: 0 5px;
                border-radius: 999px;
                background: #dc2626;
                color: #fff;
                border: 2px solid #fff;
                font-size: .62rem;
                line-height: 14px;
                text-align: center;
            }
            .mobile-bottom-nav a.active {
                background: #ecfdf5;
                color: var(--primary);
            }
            .mobile-bottom-nav button.bottom-nav-logout {
                color: #dc2626;
            }
            .mobile-bottom-nav a.bottom-nav-action {
                width: 58px;
                min-width: 58px;
                height: 58px;
                min-height: 58px;
                justify-self: center;
                margin-top: -24px;
                border-radius: 999px;
                background: var(--accent);
                color: #0f4c3a;
                border: 4px solid #fff;
                box-shadow: 0 8px 20px rgba(15, 76, 58, .28);
            }
            .mobile-bottom-nav a.bottom-nav-action i {
                font-size: 1.35rem;
            }
            .mobile-bottom-nav a.bottom-nav-action.active,
            .mobile-bottom-nav a.bottom-nav-action:hover {
                background: #f5c842;
                color: #0f4c3a;
            }
        }
        @media(max-width:576px) {
            .small-box { margin-bottom: 8px; }
            .btn { font-size: .8rem; padding: 6px 12px; }
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c4c4c4; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }
    </style>
    @yield('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    @php
        $approvalPendingCount = 0;
        if (auth()->check() && auth()->user()->isAtasan()) {
            $approvalPendingCount = \App\LaporanWfh::whereIn(
                'user_id',
                \App\User::where('atasan_id', auth()->id())->pluck('id')
            )->where('status', 'submitted')->count();
        }
        $wfhLetterApproverId = auth()->check() ? (int) \App\AppSetting::value('wfh_letter_approver_user_id') : 0;
        $isWfhLetterApprover = auth()->check() && $wfhLetterApproverId === (int) auth()->id();
        $wfhLetterPendingCount = $isWfhLetterApprover
            ? \App\WfhDate::where('letter_status', 'pending_approval')->whereNotNull('letter_number')->count()
            : 0;
    @endphp
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user-circle mr-1"></i>
                    <span class="d-none d-sm-inline" style="font-weight:500;">{{ auth()->user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">
                        {{ auth()->user()->name }}<br><small class="text-muted">{{ ucfirst(str_replace('_',' ',auth()->user()->role)) }}</small>
                    </span>
                    <div class="dropdown-divider"></div>
                    <a href="{{ session('sso_access_token') ? route('logout.sso') : route('logout') }}" class="dropdown-item" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-2 text-danger"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ session('sso_access_token') ? route('logout.sso') : route('logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-0">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <i class="fas fa-file-alt ml-1 mr-2" style="color:var(--accent);font-size:1.2rem;"></i>
            <span class="brand-text">Laporan WFH</span>
            <br><small style="margin-left:38px;">PTA Papua Barat</small>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image"><i class="fas fa-user-circle fa-2x" style="color:rgba(255,255,255,.5);"></i></div>
                <div class="info">
                    <a href="#">{{ Str::limit(auth()->user()->name, 20) }}</a>
                    <br><small>{{ ucfirst(str_replace('_',' ',auth()->user()->role)) }}</small>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    @if(auth()->user()->isSuperAdmin())
                        <li class="nav-header">ADMIN</li>
                        <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="nav-icon fas fa-users-cog"></i><p>Kelola User</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.laporan.index') }}" class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-alt"></i><p>Seluruh Laporan</p></a></li>
                        <li class="nav-item"><a href="{{ route('admin.wfh-dates.index') }}" class="nav-link {{ request()->routeIs('admin.wfh-dates.*') ? 'active' : '' }}"><i class="nav-icon fas fa-calendar-alt"></i><p>Tanggal WFH</p></a></li>
                    @endif
                    @if(auth()->user()->isPegawai() || auth()->user()->isAtasan())
                        <li class="nav-header">LAPORAN</li>
                        <li class="nav-item"><a href="{{ route('pegawai.wfh-registrations.index') }}" class="nav-link {{ request()->routeIs('pegawai.wfh-registrations.*') ? 'active' : '' }}"><i class="nav-icon fas fa-calendar-check"></i><p>Daftar WFH</p></a></li>
                        <li class="nav-item"><a href="{{ route('pegawai.laporan.index') }}" class="nav-link {{ request()->routeIs('pegawai.laporan.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-alt"></i><p>Laporan WFH</p></a></li>
                    @endif
                    @if(auth()->user()->isAtasan())
                        <li class="nav-header">MONITORING</li>
                        <li class="nav-item"><a href="{{ route('atasan.monitoring.index') }}" class="nav-link {{ request()->routeIs('atasan.monitoring.index') ? 'active' : '' }}"><i class="nav-icon fas fa-users"></i><p>Daftar Pegawai</p></a></li>
                        <li class="nav-item">
                            <a href="{{ route('atasan.monitoring.pending') }}" class="nav-link {{ request()->routeIs('atasan.monitoring.pending') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clock"></i>
                                <p>
                                    Laporan Pending
                                    @if($approvalPendingCount > 0)
                                        <span class="badge badge-warning right">{{ $approvalPendingCount }}</span>
                                    @endif
                                </p>
                            </a>
                        </li>
                    @endif
                    @if($isWfhLetterApprover)
                        <li class="nav-header">APPROVAL</li>
                        <li class="nav-item">
                            <a href="{{ route('wfh-letter-approvals.index') }}" class="nav-link {{ request()->routeIs('wfh-letter-approvals.index', 'wfh-letter-approvals.show', 'wfh-letter-approvals.pdf', 'wfh-letter-approvals.sign') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-signature"></i>
                                <p>
                                    Approval Surat
                                    @if($wfhLetterPendingCount > 0)
                                        <span class="badge badge-warning right">{{ $wfhLetterPendingCount }}</span>
                                    @endif
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('wfh-letter-approvals.monitoring') }}" class="nav-link {{ request()->routeIs('wfh-letter-approvals.monitoring', 'wfh-letter-approvals.report') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>Monitoring Laporan</p>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </aside>

    <nav class="mobile-bottom-nav" aria-label="Navigasi utama mobile">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i>
                <span>User</span>
            </a>
            @if($isWfhLetterApprover)
                <a href="{{ route('wfh-letter-approvals.index') }}" class="{{ request()->routeIs('wfh-letter-approvals.index', 'wfh-letter-approvals.show', 'wfh-letter-approvals.pdf', 'wfh-letter-approvals.sign') ? 'active' : '' }}">
                    <i class="fas fa-file-signature"></i>
                    @if($wfhLetterPendingCount > 0)
                        <span class="bottom-nav-badge">{{ $wfhLetterPendingCount }}</span>
                    @endif
                    <span>Approval</span>
                </a>
            @endif
            <a href="{{ route('admin.laporan.index') }}" class="{{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <a href="{{ route('admin.wfh-dates.index') }}" class="{{ request()->routeIs('admin.wfh-dates.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span>WFH</span>
            </a>
            <button type="submit" form="logout-form" class="bottom-nav-logout" aria-label="Logout" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        @endif

        @if(auth()->user()->isPegawai())
            @if($isWfhLetterApprover)
                <a href="{{ route('wfh-letter-approvals.index') }}" class="{{ request()->routeIs('wfh-letter-approvals.index', 'wfh-letter-approvals.show', 'wfh-letter-approvals.pdf', 'wfh-letter-approvals.sign') ? 'active' : '' }}">
                    <i class="fas fa-file-signature"></i>
                    @if($wfhLetterPendingCount > 0)
                        <span class="bottom-nav-badge">{{ $wfhLetterPendingCount }}</span>
                    @endif
                    <span>Approval</span>
                </a>
            @endif
            @php
                $mobileCurrentLaporan = \App\LaporanWfh::where('user_id', auth()->id())
                    ->where('bulan', now()->month)
                    ->where('tahun', now()->year)
                    ->first();
                if ($mobileCurrentLaporan && $mobileCurrentLaporan->status !== 'approved') {
                    $mobileTambahKegiatanUrl = route('pegawai.laporan.edit', $mobileCurrentLaporan);
                } elseif ($mobileCurrentLaporan) {
                    $mobileTambahKegiatanUrl = route('pegawai.laporan.show', $mobileCurrentLaporan);
                } else {
                    $mobileTambahKegiatanUrl = route('pegawai.laporan.create');
                }
            @endphp
            <a href="{{ $mobileTambahKegiatanUrl }}" class="bottom-nav-action {{ (request()->routeIs('pegawai.laporan.create') || request()->routeIs('pegawai.laporan.edit')) ? 'active' : '' }}" aria-label="Tambah Kegiatan" title="Tambah Kegiatan">
                <i class="fas fa-plus-circle"></i>
            </a>
            <a href="{{ route('pegawai.laporan.index') }}" class="{{ (request()->routeIs('pegawai.laporan.index') || request()->routeIs('pegawai.laporan.show')) ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <a href="{{ route('pegawai.wfh-registrations.index') }}" class="{{ request()->routeIs('pegawai.wfh-registrations.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i>
                <span>Daftar</span>
            </a>
            <button type="submit" form="logout-form" class="bottom-nav-logout" aria-label="Logout" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        @endif

        @if(auth()->user()->isAtasan())
            @if($isWfhLetterApprover)
                <a href="{{ route('wfh-letter-approvals.index') }}" class="{{ request()->routeIs('wfh-letter-approvals.index', 'wfh-letter-approvals.show', 'wfh-letter-approvals.pdf', 'wfh-letter-approvals.sign') ? 'active' : '' }}">
                    <i class="fas fa-file-signature"></i>
                    @if($wfhLetterPendingCount > 0)
                        <span class="bottom-nav-badge">{{ $wfhLetterPendingCount }}</span>
                    @endif
                    <span>Approval</span>
                </a>
            @endif
            <a href="{{ route('atasan.monitoring.index') }}" class="{{ request()->routeIs('atasan.monitoring.index') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Pegawai</span>
            </a>
            @php
                $mobileCurrentLaporanAtasan = \App\LaporanWfh::where('user_id', auth()->id())
                    ->where('bulan', now()->month)
                    ->where('tahun', now()->year)
                    ->first();
                if ($mobileCurrentLaporanAtasan && $mobileCurrentLaporanAtasan->status !== 'approved') {
                    $mobileTambahKegiatanAtasanUrl = route('pegawai.laporan.edit', $mobileCurrentLaporanAtasan);
                } elseif ($mobileCurrentLaporanAtasan) {
                    $mobileTambahKegiatanAtasanUrl = route('pegawai.laporan.show', $mobileCurrentLaporanAtasan);
                } else {
                    $mobileTambahKegiatanAtasanUrl = route('pegawai.laporan.create');
                }
            @endphp
            <a href="{{ $mobileTambahKegiatanAtasanUrl }}" class="bottom-nav-action {{ (request()->routeIs('pegawai.laporan.create') || request()->routeIs('pegawai.laporan.edit')) ? 'active' : '' }}" aria-label="Tambah Kegiatan" title="Tambah Kegiatan">
                <i class="fas fa-plus-circle"></i>
            </a>
            <a href="{{ route('pegawai.laporan.index') }}" class="{{ (request()->routeIs('pegawai.laporan.index') || request()->routeIs('pegawai.laporan.show')) ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <a href="{{ route('pegawai.wfh-registrations.index') }}" class="{{ request()->routeIs('pegawai.wfh-registrations.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i>
                <span>Daftar</span>
            </a>
            <a href="{{ route('atasan.monitoring.pending') }}" class="{{ request()->routeIs('atasan.monitoring.pending') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                @if($approvalPendingCount > 0)
                    <span class="bottom-nav-badge">{{ $approvalPendingCount }}</span>
                @endif
                <span>Pending</span>
            </a>
            <button type="submit" form="logout-form" class="bottom-nav-logout" aria-label="Logout" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        @endif
    </nav>

    <!-- Content -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0">@yield('page-title', 'Dashboard')</h1></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-right">@yield('breadcrumb')</ol></div>
                </div>
            </div>
        </div>
        <section class="content animate-in">
            <div class="container-fluid">
                @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>@endif
                @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>@endif
                @if(session('info'))<div class="alert alert-info alert-dismissible fade show"><i class="fas fa-info-circle mr-2"></i>{{ session('info') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>@endif
                @if($errors->any())<div class="alert alert-danger alert-dismissible fade show"><ul class="mb-0 pl-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="close" data-dismiss="alert">&times;</button></div>@endif
                @yield('content')
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block"><b>v</b>1.0</div>
        <strong>&copy; {{ date('Y') }} PTA Papua Barat.</strong> Sistem Laporan Work From Home
    </footer>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>$(function(){ if($(window).width()<=768) $('body').addClass('sidebar-collapse'); });</script>
@yield('scripts')
</body>
</html>

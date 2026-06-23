<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Tugas WFH</title>
    <style>
        @page {
            margin: 18mm 20mm 16mm 20mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
            line-height: 1.15;
        }

        .kop {
            width: 100%;
            margin-bottom: 10px;
        }

        .title {
            text-align: center;
            margin-bottom: 12px;
        }

        .title h1 {
            display: inline-block;
            margin: 0;
            padding-bottom: 1px;
            border-bottom: 1px solid #000;
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0;
        }

        .title .number {
            margin-top: 2px;
            font-size: 12pt;
        }

        p {
            margin: 0 0 8px 0;
            text-align: justify;
        }

        .basis-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .basis-table td {
            vertical-align: top;
            padding: 0 0 2px 0;
        }

        .basis-label {
            width: 112px;
            white-space: nowrap;
        }

        .basis-colon {
            width: 18px;
            text-align: center;
        }

        .inner-list {
            width: 100%;
            border-collapse: collapse;
        }

        .inner-list td {
            padding: 0 0 1px 0;
            vertical-align: top;
            text-align: justify;
        }

        .inner-list .marker {
            width: 24px;
            white-space: nowrap;
        }

        .label-row {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }

        .label-row td {
            vertical-align: top;
            padding: 0 0 3px 0;
        }

        .label {
            width: 78px;
        }

        .colon {
            width: 12px;
        }

        .assign-title {
            text-align: center;
            font-weight: bold;
            margin: 8px 0 4px 0;
        }

        .kepada-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .kepada-row td {
            padding: 0;
            vertical-align: top;
            font-weight: bold;
        }

        .kepada-row .kepada-label {
            width: 112px;
            padding-left: 30px;
        }

        .kepada-row .kepada-colon {
            width: 18px;
            text-align: center;
        }

        .pegawai-table {
            width: 96%;
            border-collapse: collapse;
            margin: 0 auto 10px auto;
            page-break-inside: auto;
        }

        .pegawai-table th,
        .pegawai-table td {
            border: 1px solid #000;
            padding: 3px 7px;
            vertical-align: top;
        }

        .pegawai-table th {
            text-align: center;
            font-weight: bold;
            background: #d9d9d9;
        }

        .pegawai-table .no {
            width: 32px;
            text-align: left;
        }

        .pegawai-table .nip {
            width: 160px;
            white-space: nowrap;
        }

        .pegawai-table .jabatan {
            width: 190px;
        }

        .purpose-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .purpose-table td {
            vertical-align: top;
            padding: 0 0 3px 0;
        }

        .purpose-label {
            width: 112px;
        }

        .purpose-colon {
            width: 18px;
            text-align: center;
        }

        .purpose-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .purpose-list td {
            vertical-align: top;
            padding: 0 0 2px 0;
            text-align: justify;
        }

        .purpose-list .marker {
            width: 22px;
        }

        .signature {
            width: 300px;
            margin-left: auto;
            margin-top: 16px;
            text-align: left;
            page-break-inside: avoid;
            position: relative;
            min-height: 132px;
        }

        .signature .space {
            height: 70px;
            position: relative;
        }

        .signature-img {
            position: absolute;
            top: -22px;
            left: -82px;
            width: 360px;
            max-height: 132px;
            z-index: 3;
            opacity: .98;
        }

        .signature .name {
            font-weight: bold;
            text-decoration: underline;
            position: relative;
            z-index: 1;
        }
        .signature-date,
        .signature-title,
        .signature-nip {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
@php
    $hariNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $bulanNames = [1 => 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $tanggalWfh = $wfhDate->tanggal;
    $tanggalTerbit = $wfhDate->letter_approved_at ?: ($wfhDate->letter_published_at ?: now());
    $tanggalWfhText = $hariNames[$tanggalWfh->dayOfWeek] . ', ' . $tanggalWfh->format('d') . ' ' . $bulanNames[(int) $tanggalWfh->format('n')] . ' ' . $tanggalWfh->format('Y');
    $tanggalTerbitText = $tanggalTerbit->format('d') . ' ' . $bulanNames[(int) $tanggalTerbit->format('n')] . ' ' . $tanggalTerbit->format('Y');
    $tanggalWfhUntuk = $hariNames[$tanggalWfh->dayOfWeek] . ' ' . $tanggalWfh->format('d') . ' ' . $bulanNames[(int) $tanggalWfh->format('n')] . ' ' . $tanggalWfh->format('Y');
@endphp

@php
    $kopPath = file_exists(public_path('KOP.png'))
        ? public_path('KOP.png')
        : public_path('images/kop.png');
@endphp

@if(file_exists($kopPath))
    <img class="kop" src="{{ $kopPath }}" alt="Kop Surat">
@else
    <div style="text-align:center; font-weight:bold; font-size:14pt;">
        PENGADILAN TINGGI AGAMA PAPUA BARAT
    </div>
@endif

<div class="title">
    <h1>SURAT TUGAS</h1>
    <div class="number">Nomor : {{ $wfhDate->letter_number }}</div>
</div>

<table class="basis-table">
    <tr>
        <td class="basis-label">Menimbang</td>
        <td class="basis-colon">:</td>
        <td>
            <table class="inner-list">
                <tr>
                    <td class="marker">a.</td>
                    <td>Bahwa berdasarkan Surat Edaran Sekretaris Mahkamah Agung RI, Nomor 4 Tahun 2026 tanggal 8 April 2026 tentang Pelaksanaan Tugas Kedinasan Bagi Hakim dan Aparatur Mahkamah Agung dan Badan Peradilan yang Berada Dibawahnya Dalam Rangka Mendukung Transformasi Budaya Kerja Nasional.</td>
                </tr>
                <tr>
                    <td class="marker">b.</td>
                    <td>Bahwa untuk melaksanakan tugas sebagaimana tersebut pada huruf (a) perlu dibuatkan surat tugas.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="basis-label">Dasar</td>
        <td class="basis-colon">:</td>
        <td>
            <table class="inner-list">
                <tr>
                    <td class="marker">1.</td>
                    <td>Undang-Undang Nomor 3 Tahun 2009 tentang Mahkamah Agung Republik Indonesia.</td>
                </tr>
                <tr>
                    <td class="marker">2.</td>
                    <td>Undang-Undang Nomor 17 Tahun 2003 tentang keuangan Negara.</td>
                </tr>
                <tr>
                    <td class="marker">3.</td>
                    <td>Undang-Undang Nomor 25 Tahun 2004 tentang Sistem Perencanaan Kerja dan Anggaran Kementerian Negara/Lembaga.</td>
                </tr>
                <tr>
                    <td class="marker">4.</td>
                    <td>Undang-Undang Nomor 5 Tahun 2014 Tentang Aparatur Sipil Negara.</td>
                </tr>
                <tr>
                    <td class="marker">5.</td>
                    <td>PP Nomor 17 Tahun 2020 Tentang Manajemen PNS.</td>
                </tr>
                <tr>
                    <td class="marker">6.</td>
                    <td>PP Nomor 49 Tahun 2018 Tentang Manajemen PPPK.</td>
                </tr>
                <tr>
                    <td class="marker">7.</td>
                    <td>Surat Edaran Menteri Pendayagunaan Aparatur Negara dan Reformasi Birokrasi Nomor 2 Tahun 2026 tentang Pelaksanaan Tugas Kedinasan secara Fleksibel.</td>
                </tr>
                <tr>
                    <td class="marker">8.</td>
                    <td>Surat Edaran Sekretaris Mahkamah Agung RI, Nomor 4 Tahun 2026 tanggal 8 April 2026 tentang Pelaksanaan Tugas Kedinasan Bagi Hakim dan Aparatur Mahkamah Agung dan Badan Peradilan yang Berada Dibawahnya Dalam Rangka Mendukung Transformasi Budaya Kerja Nasional.</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="assign-title">Memberi Tugas</div>
<table class="kepada-row">
    <tr>
        <td class="kepada-label">Kepada</td>
        <td class="kepada-colon">:</td>
        <td></td>
    </tr>
</table>

<table class="pegawai-table">
    <thead>
        <tr>
            <th class="no">No</th>
            <th>Nama</th>
            <th class="nip">NIP</th>
            <th class="jabatan">Jabatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $index => $user)
            <tr>
                <td class="no">{{ $index + 1 }}.</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->nip ?: '-' }}</td>
                <td>{{ $user->jabatan ?: '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="purpose-table">
    <tr>
        <td class="purpose-label">Untuk</td>
        <td class="purpose-colon">:</td>
        <td>
            Melaksanakan Tugas Kedinasan Bagi Hakim dan Aparatur Mahkamah Agung dan Badan Peradilan yang Berada Dibawahnya pada hari {{ $tanggalWfhUntuk }} dengan ketentuan ;
            <table class="purpose-list">
                <tr>
                    <td class="marker">a.</td>
                    <td>Mengisi daftar hadir atau presensi 2 (dua) kali sehari melalui SIKEP sesuai dengan ketentuan;</td>
                </tr>
                <tr>
                    <td class="marker">b.</td>
                    <td>Memastikan ketersediaan sarana dan prasarana pendukung kerja;</td>
                </tr>
                <tr>
                    <td class="marker">c.</td>
                    <td>Mematuhi ketentuan kode etik dan kode perilaku serta disiplin sesuai dengan ketentuan yang berlaku di lingkungan Mahkamah Agung dan Badan Peradilan yang berada di Bawahnya;</td>
                </tr>
                <tr>
                    <td class="marker">d.</td>
                    <td>Menggunakan jam kerja secara efektif untuk pelaksanaan tugas kedinasan dengan baik, berintegritas, dan penuh tanggung jawab;</td>
                </tr>
                <tr>
                    <td class="marker">e.</td>
                    <td>Responsif dan dapat dihubungi;</td>
                </tr>
                <tr>
                    <td class="marker">f.</td>
                    <td>Melaporkan hasil pelaksanaan pekerjaan kepada atasan langsung.</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="signature">
    <div class="signature-date">Manokwari, {{ $tanggalTerbitText }}</div>
    <div class="signature-title">Ketua,</div>
    <div class="space">
        @if($wfhDate->letter_signature)
            <img src="{{ $wfhDate->letter_signature }}" class="signature-img" alt="Tanda Tangan Ketua">
        @endif
    </div>
    <div class="name">{{ $approver->name ?? '........................................' }}</div>
    @if(!empty($approver) && $approver->nip)
        <div class="signature-nip">NIP. {{ $approver->nip }}</div>
    @endif
</div>
</body>
</html>

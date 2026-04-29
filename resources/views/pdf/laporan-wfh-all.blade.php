<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Semua Laporan WFH</title>
    <style>
        @page { margin: 1.5cm 2cm 2cm 2cm; }
        body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.3; color: #000; }

        .laporan-page { page-break-after: always; }
        .laporan-page:last-child { page-break-after: auto; }

        .kop-surat { text-align: center; margin-bottom: 15px; }
        .kop-surat img { width: 100%; max-width: 100%; }

        .judul { text-align: center; margin: 20px 0 15px; }
        .judul h3, .judul h4 { font-size: 13pt; font-weight: bold; margin: 0; }
        .periode { text-align: center; font-weight: bold; margin-top: 4px; }

        .info-pegawai { margin-bottom: 12px; }
        .info-pegawai table { width: 100%; }
        .info-pegawai td { padding: 2px 0; vertical-align: top; }
        .info-pegawai .label { font-weight: bold; width: 210px; white-space: nowrap; }
        .info-pegawai .separator { width: 15px; text-align: center; }

        .tabel-kegiatan { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .tabel-kegiatan th, .tabel-kegiatan td { border: 1px solid #000; padding: 5px 7px; vertical-align: top; font-size: 11pt; }
        .tabel-kegiatan th { background: #f0f0f0; text-align: center; font-weight: bold; }
        .tabel-kegiatan .no { text-align: center; width: 5%; }
        .tabel-kegiatan .tanggal { width: 12%; text-align: center; }
        .tabel-kegiatan .kegiatan { width: 33%; }
        .tabel-kegiatan .capaian { width: 30%; }
        .tabel-kegiatan .tempat { width: 20%; text-align: center; }
        .rich-content p, .rich-content ul, .rich-content ol, .rich-content blockquote { margin: 0 0 4px 0; padding: 0; }
        .rich-content ul, .rich-content ol { padding-left: 18px; }
        .rich-content :last-child { margin-bottom: 0; }

        .ttd-section { margin-top: 20px; width: 100%; }
        .ttd-section table { width: 100%; }
        .ttd-left { width: 40%; vertical-align: top; text-align: left; }
        .ttd-right { width: 60%; vertical-align: top; text-align: right; }
        .ttd-right-inner { text-align: left; display: inline-block; }
        .ttd-section p { margin: 0; padding: 0; line-height: 1.4; }
        .ttd-name { font-weight: bold; text-decoration: underline; }
        .ttd-spacer { height: 60px; }
        .ttd-img { height: 58px; max-width: 180px; object-fit: contain; }
        .ttd-stack { position: relative; height: 68px; width: 190px; overflow: visible; }
        .ttd-stack .ttd-img { position: absolute; left: -38px; top: -18px; height: 118px; max-width: 285px; z-index: 2; }
        .cap-img { position: absolute; left: -24px; top: -34px; height: 172px; max-width: 185px; z-index: 1; opacity: .78; }
    </style>
</head>
<body>
@php
    $bulanNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
@endphp

@foreach($laporans as $laporan)
    <div class="laporan-page">
        <div class="kop-surat">
            @if(isset($isPdf) && $isPdf)
                <img src="{{ public_path('images/kop.png') }}" alt="Kop Surat">
            @else
                <img src="{{ asset('images/kop.png') }}" alt="Kop Surat">
            @endif
        </div>

        <div class="judul">
            <h3>DAFTAR LAPORAN KERJA</h3>
            <h4>WORK FROM HOME (WFH)</h4>
            <div class="periode">{{ strtoupper($laporan->periode) }}</div>
        </div>

        <div class="info-pegawai">
            <table>
                <tr><td class="label">NAMA/NIP PEGAWAI</td><td class="separator">:</td><td>{{ $laporan->user->name }} / {{ $laporan->user->nip }}</td></tr>
                <tr><td class="label">PANGKAT/GOL. RUANG</td><td class="separator">:</td><td>{{ $laporan->user->pangkat ?? '-' }}</td></tr>
                <tr><td class="label">JABATAN</td><td class="separator">:</td><td>{{ $laporan->user->jabatan ?? '-' }}</td></tr>
                <tr><td class="label">SATUAN KERJA</td><td class="separator">:</td><td>{{ $laporan->user->satuan_kerja ?? 'Pengadilan Tinggi Agama Papua Barat' }}</td></tr>
            </table>
        </div>

        <table class="tabel-kegiatan">
            <thead>
                <tr>
                    <th class="no">NO</th>
                    <th class="tanggal">TANGGAL</th>
                    <th class="kegiatan">KEGIATAN</th>
                    <th class="capaian">CAPAIAN</th>
                    <th class="tempat">TEMPAT WFH</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporan->kegiatan as $i => $keg)
                <tr>
                    <td class="no">{{ $i + 1 }}.</td>
                    <td class="tanggal">{{ $keg->tanggal->format('d-m-Y') }}</td>
                    <td class="kegiatan"><div class="rich-content">{!! $keg->kegiatan !!}</div></td>
                    <td class="capaian">
                        <div class="rich-content">{!! $keg->capaian !!}</div>
                        @if($keg->all_evidens->isNotEmpty())
                            @foreach($keg->all_evidens as $evidenIndex => $eviden)
                                <br><a href="{{ $eviden->preview_link }}">Lihat Eviden {{ $evidenIndex + 1 }}</a>
                            @endforeach
                        @endif
                    </td>
                    <td class="tempat">{{ $keg->tempat_wfh }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:20px;">Tidak ada kegiatan</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="ttd-section">
            <table>
                <tr>
                    <td class="ttd-left">
                        <p>Mengetahui,</p>
                        <p><strong>{{ $laporan->user->atasan->jabatan ?? 'Atasan Langsung' }}</strong></p>
                        @if($laporan->signature_atasan)
                            <div class="ttd-stack">
                                <img src="{{ $laporan->signature_atasan }}" class="ttd-img" alt="Tanda Tangan Atasan">
                                @if(isset($isPdf) && $isPdf)
                                    <img src="{{ public_path('images/cap.png') }}" class="cap-img" alt="Cap">
                                @else
                                    <img src="{{ asset('images/cap.png') }}" class="cap-img" alt="Cap">
                                @endif
                            </div>
                        @else
                            <div class="ttd-spacer"></div>
                        @endif
                        <p class="ttd-name">{{ $laporan->user->atasan->name ?? '.............................' }}</p>
                        <p>NIP. {{ $laporan->user->atasan->nip ?? '.............................' }}</p>
                    </td>
                    <td class="ttd-right">
                        <div class="ttd-right-inner">
                            <p>Manokwari, {{ date('d') }} {{ $bulanNames[(int)date('m')] }} {{ date('Y') }}</p>
                            <p><strong>Pegawai Yang WFH</strong></p>
                            @if($laporan->signature_pegawai)
                                <img src="{{ $laporan->signature_pegawai }}" class="ttd-img" alt="Tanda Tangan Pegawai">
                            @else
                                <div class="ttd-spacer"></div>
                            @endif
                            <p class="ttd-name">{{ $laporan->user->name }}</p>
                            <p>NIP. {{ $laporan->user->nip }}</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endforeach
</body>
</html>

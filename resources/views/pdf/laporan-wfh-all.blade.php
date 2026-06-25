<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Semua Laporan WFH</title>
    <style>
        @page { margin: 1.2cm 1.7cm 1.35cm 1.7cm; }
        body { font-family: 'Times New Roman', serif; font-size: 11.5pt; line-height: 1.22; color: #000; }

        .laporan-page { page-break-after: always; }
        .laporan-page:last-child { page-break-after: auto; }

        .kop-surat { text-align: center; margin-bottom: 8px; }
        .kop-surat img { width: 100%; max-width: 100%; }

        .judul { text-align: center; margin: 10px 0 8px; }
        .judul h3, .judul h4 { font-size: 12.5pt; font-weight: bold; margin: 0; }
        .periode { text-align: center; font-weight: bold; margin-top: 2px; }

        .info-pegawai { margin-bottom: 6px; }
        .info-pegawai table { width: 100%; }
        .info-pegawai td { padding: 1px 0; vertical-align: top; }
        .info-pegawai .label { font-weight: bold; width: 210px; white-space: nowrap; }
        .info-pegawai .separator { width: 15px; text-align: center; }

        .tabel-kegiatan { width: 100%; border-collapse: collapse; margin: 7px 0 0; }
        .tabel-kegiatan th, .tabel-kegiatan td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; font-size: 10.5pt; line-height: 1.2; }
        .tabel-kegiatan th { background: #f0f0f0; text-align: center; font-weight: bold; }
        .tabel-kegiatan .no { text-align: center; width: 5%; }
        .tabel-kegiatan .tanggal { width: 12%; text-align: center; }
        .tabel-kegiatan .kegiatan { width: 33%; }
        .tabel-kegiatan .capaian { width: 30%; }
        .tabel-kegiatan .tempat { width: 20%; text-align: center; }
        .rich-content p, .rich-content ul, .rich-content ol, .rich-content blockquote { margin: 0 0 2px 0; padding: 0; }
        .rich-content ul, .rich-content ol { padding-left: 15px; }
        .rich-content :last-child { margin-bottom: 0; }

        .ttd-section { margin-top: 8px; width: 100%; page-break-inside: avoid; break-inside: avoid; }
        .ttd-section table { width: 100%; }
        .ttd-left { width: 40%; vertical-align: top; text-align: left; }
        .ttd-right { width: 60%; vertical-align: top; text-align: right; }
        .ttd-right-inner { text-align: left; display: inline-block; }
        .ttd-section p { margin: 0; padding: 0; line-height: 1.22; }
        .ttd-name { font-weight: bold; text-decoration: underline; }
        .ttd-spacer { height: 54px; }
        .ttd-img { height: 58px; max-width: 190px; object-fit: contain; }
        .ttd-stack { position: relative; height: 50px; width: 210px; overflow: visible; }
        .ttd-stack .ttd-img { position: absolute; left: -42px; top: -38px; height: 132px; max-width: 300px; z-index: 3; opacity: .98; }
        .ttd-stack-pegawai { margin-top: 0; }
        .ttd-stack-pegawai .ttd-img { left: -48px; top: -42px; height: 136px; max-width: 310px; }
        .ttd-name { position: relative; z-index: 1; }
        .cap-img { position: absolute; left: -28px; top: -48px; height: 176px; max-width: 190px; z-index: 2; opacity: .78; }
    </style>
</head>
<body>
@php
    $bulanNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
@endphp

@foreach($laporans as $laporan)
    @php
        $signaturePegawai = $laporan->signature_pegawai ?: optional($laporan->user)->signature;
    @endphp
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
                            @if($signaturePegawai)
                                <div class="ttd-stack ttd-stack-pegawai">
                                    <img src="{{ $signaturePegawai }}" class="ttd-img" alt="Tanda Tangan Pegawai">
                                </div>
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

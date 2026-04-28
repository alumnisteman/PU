<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Aset Jalan - {{ $jalan->jalan_nama }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-bold: true; margin-bottom: 5px; }
        .subtitle { font-size: 14px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { bg-color: #f2f2f2; font-weight: bold; }
        .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 10px 0; border-left: 4px solid #4f46e5; padding-left: 10px; }
        .photo-container { text-align: center; margin-top: 20px; }
        .photo { max-width: 100%; height: auto; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN DATA TEKNIS JALAN</div>
        <div class="subtitle">Sistem Informasi Manajemen Aset Jalan (SISMAP)</div>
    </div>

    <div class="section-title">Informasi Umum</div>
    <table>
        <tr>
            <th width="30%">Nama Ruas</th>
            <td>{{ $jalan->jalan_nama }}</td>
        </tr>
        <tr>
            <th>Kode Ruas</th>
            <td>{{ $jalan->jalan_kode }}</td>
        </tr>
        <tr>
            <th>Panjang Ruas</th>
            <td>{{ number_format($jalan->jalan_panjang, 2) }} KM</td>
        </tr>
        <tr>
            <th>Lebar Rata-rata</th>
            <td>{{ $jalan->jalan_lebar }} M</td>
        </tr>
        <tr>
            <th>Koordinat (LLH)</th>
            <td>{{ $jalan->jalan_llh }}</td>
        </tr>
    </table>

    <div class="section-title">Kondisi Jalan Terakhir</div>
    <table>
        <thead>
            <tr>
                <th>Tipe Kondisi</th>
                <th>Awal (KM)</th>
                <th>Akhir (KM)</th>
                <th>Tahun</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jalan->kondisis as $k)
            <tr>
                <td>{{ $k->kondisi_tipe }}</td>
                <td>{{ $k->kondisi_km_awal }}</td>
                <td>{{ $k->kondisi_km_akhir }}</td>
                <td>{{ \Carbon\Carbon::parse($k->kondisi_dibuat_pada)->format('Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Dokumentasi Visual</div>
    <div class="photo-container">
        @php
            $latestDetailWithPhoto = $jalan->details->whereNotNull('detail_foto_alias')->where('detail_foto_alias', '!=', '')->last();
        @endphp
        @if($latestDetailWithPhoto)
            <img src="{{ public_path('uploads/others/pu/' . $latestDetailWithPhoto->detail_tahun . '/' . $latestDetailWithPhoto->detail_foto_alias) }}" style="width: 100%; height: auto; max-height: 250px; object-fit: cover; border-radius: 4px;">
        @else
            <div style="width: 100%; height: 250px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; font-style: italic;">
                Foto Tidak Tersedia
            </div>
        @endif
    </div>

    <div style="margin-top: 50px; text-align: right;">
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>

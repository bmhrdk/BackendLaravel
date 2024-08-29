@php
    \Carbon\Carbon::setLocale('id');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Service</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            position: relative;
            margin-bottom: 20px;
        }

        .logo {
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: auto;
        }

        .status-container {
            position: absolute;
            top: 15px;
            right: 0;
        }

        .status-label {
            font-weight: bold;
            color: black;
            margin-top: 50px;
        }

        .status-value {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 5px;
        }

        .status-pending {
            color: red;
        }

        .status-complete {
            color: green;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        .total-label {
            text-align: right;
            font-weight: bold;
        }

        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 10px;
            border-top: 5px solid black;
        }
    </style>
</head>

<body>

    <div class="header">
        <img src="data:image/jpg;base64,{{ base64_encode(file_get_contents(public_path('images/Delcomp.jpg'))) }}" alt="Delcomp Logo" class="logo">
        <div class="status-container">
            <p class="status-label">Status Service:</p>
            <p class="status-value 
               @if ($service['status'] == 'Menunggu Pembayaran') status-pending 
               @elseif ($service['status'] == 'Selesai') status-complete 
               @endif">
               {{ $service['status'] }}
            </p>
        </div>
    </div>

    <h1>Nota Service #{{ $service['id'] }}</h1>
    <p>Nama Customer: {{ ucwords($service['nama_customer']) }}</p>
    <p>Estimasi: {{ isset($service['detail_service']['estimasi']) ? \Carbon\Carbon::parse($service['detail_service']['estimasi'])->translatedFormat('l, d F Y') : 'N/A' }}</p>
    <p>Tanggal Mulai Service: {{ isset($service['tanggalMasuk']) ? \Carbon\Carbon::parse($service['tanggalMasuk'])->translatedFormat('l, d F Y') : 'N/A'  }}</p>
    @if ($service['tanggalKeluar'])
        <p>Tanggal Selesai Service:{{ isset($service['tanggalKeluar']) ? \Carbon\Carbon::parse($service['tanggalKeluar'])->translatedFormat('l, d F Y') : 'N/A'  }}</p>
    @endif

    <!-- Displaying createdBy, processedBy, finishedBy -->
    <p>Teknisi: {{ $service['technician'] ?? 'N/A' }}</p>
    {{-- <p>Pelayanan Awal: {{ $service['createdBy']['name'] }} ({{ $service['createdBy']['role'] }})</p>
    <p>Mengubah Status: {{ $service['processedBy'] ?? 'N/A' }}</p>
    <p>Pelayanan Akhir: {{ $service['finishedBy'] ?? 'N/A' }}</p> --}}

    <h2>Detail Service</h2>
    <table>
        <thead>
            <tr>
                <th>Kerusakan</th>
                <th>Biaya Kerusakan</th>
            </tr>
        </thead>
        <tbody>
            @if (is_array($service['detail_service']['kerusakan']) && is_array($service['detail_service']['biayaKerusakan']))
                @foreach ($service['detail_service']['kerusakan'] as $index => $kerusakanItem)
                    <tr>
                        <td>{{ ucwords($kerusakanItem) }}</td>
                        <td>{{ number_format((float) $service['detail_service']['biayaKerusakan'][$index], 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>{{ is_array($service['detail_service']['kerusakan']) ? implode(', ', $service['detail_service']['kerusakan']) : $service['detail_service']['kerusakan'] }}
                    </td>
                    <td>{{ number_format((float) $service['detail_service']['biayaKerusakan'], 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <h2>Tambahan Sparepart</h2>
    <table>
        <thead>
            <tr>
                <th>Merek Sparepart</th>
                <th>Tipe Sparepart</th>
                <th>Jumlah Sparepart</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($service['inventory'] as $item)
                <tr>
                    <td>{{ strtoupper($item['merek_sparepart']) }}</td>
                    <td>{{ $item['tipe_sparepart'] }}</td>
                    <td>{{ $item['jumlah_sparepart'] }}</td>
                    <td>{{ number_format((float) $item['harga_satuan'], 2) }}</td>
                    <td>{{ number_format((float) $item['total_harga'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data sparepart</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Total Biaya Kerusakan -->
    <p class="total-label">Total Biaya Kerusakan:
        {{ number_format(
            is_array($service['detail_service']['biayaKerusakan'])
                ? array_sum(array_map('floatval', $service['detail_service']['biayaKerusakan']))
                : (float) $service['detail_service']['biayaKerusakan'], 2)
        }}
    </p>

    <!-- Total Biaya Sparepart -->
    <p class="total-label">Total Biaya Sparepart:
        {{ number_format(
            $service['inventory']->sum(function($item) {
                return (float)$item['total_harga'];
            }), 2)
        }}
    </p>

    <p class="total">Total Harga Keseluruhan:
        {{ number_format(
            $service['totalHargaKeseluruhan'] +
                (is_array($service['detail_service']['biayaKerusakan'])
                    ? array_sum(array_map('floatval', $service['detail_service']['biayaKerusakan']))
                    : (float) $service['detail_service']['biayaKerusakan']),
            2,
        ) }}
    </p>
</body>

</html>

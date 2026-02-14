<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
        }
        .sheet {
            max-width: 1100px;
            margin: 24px auto;
            background: #fff;
            border: 1px solid #d1d5db;
            padding: 24px;
        }
        .heading { text-align: center; margin-bottom: 14px; }
        .branding {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .brand-logo {
            width: 58px;
            height: 58px;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            object-fit: cover;
            background: #fff;
        }
        .brand-text {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            line-height: 1.15;
        }
        .brand-text .cbt {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.06em;
        }
        .heading h1 { margin: 0; font-size: 22px; letter-spacing: 0.04em; }
        .heading p { margin: 6px 0 0; font-size: 12px; color: #6b7280; }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #111827;
            padding: 6px;
            font-size: 11px;
            word-break: break-word;
            text-align: center;
        }
        th {
            background: #f9fafb;
            text-align: center;
            text-transform: uppercase;
            font-weight: 700;
        }
        .center { text-align: center; }
        th.name-col {
            text-align: center !important;
        }
        td.name-col {
            white-space: normal;
            text-align: left !important;
            padding-left: 10px;
        }
        .badge {
            display: inline-block;
            border-radius: 999px;
            border: 1px solid #9ca3af;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: normal;
            line-height: 1.2;
            max-width: 130px;
            text-align: center;
        }
        td.status-col {
            text-align: center;
            width: 145px;
        }
        .print-page-number { display: none; }
        .toolbar {
            position: fixed;
            right: 20px;
            bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            border: none;
            border-radius: 999px;
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .btn.primary { background: #2563eb; color: #fff; }
        .btn.secondary { background: #fff; color: #111827; border: 1px solid #d1d5db; }
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            body { background: #fff; }
            .sheet { margin: 0; border: none; max-width: none; padding: 0; }
            .no-print { display: none !important; }
            .print-page-number {
                display: block;
                position: fixed;
                right: 0;
                bottom: 0;
                font-size: 10px;
                color: #4b5563;
            }
            .print-page-number::after {
                content: "Halaman " counter(page);
            }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="heading">
            <div class="branding">
                <img class="brand-logo" src="{{ $schoolLogoUrl ?: $adminProfileUrl }}" alt="Logo Sekolah">
                <div class="brand-text">
                    <span class="cbt">CBT {{ strtoupper($schoolName) }}</span>
                </div>
            </div>
            <h1>REKAPITULASI NILAI SISWA</h1>
            <p>Dicetak: {{ $printedAt->format('d F Y H:i') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:48px;">No</th>
                    <th style="width:95px;">NIS</th>
                    <th class="name-col" style="width:235px;">Nama Siswa</th>
                    <th style="width:95px;">Kelas</th>
                    <th style="width:80px;">Nilai</th>
                    <th style="width:145px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $idx => $row)
                <tr>
                    <td class="center">{{ $idx + 1 }}</td>
                    <td class="center">{{ $row['nis'] }}</td>
                    <td class="name-col">{{ $row['name'] }}</td>
                    <td>{{ $row['class'] }}</td>
                    <td class="center">{{ $row['score'] !== null ? number_format((float) $row['score'], 1) : '-' }}</td>
                    <td class="status-col"><span class="badge">{{ $row['status'] }}</span></td>
                </tr>
                @empty
                <tr>
                    <td class="center" colspan="6">Belum ada data siswa untuk dicetak.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <div class="toolbar no-print">
        <a class="btn secondary" href="{{ $backRoute }}">Kembali</a>
        <button type="button" class="btn primary" onclick="window.print()">Cetak</button>
    </div>
    <div class="print-page-number"></div>
</body>
</html>

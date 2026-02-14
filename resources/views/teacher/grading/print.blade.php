<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Nilai - {{ $exam->name }}</title>
    <style>
        :root {
            --border: #111827;
            --text: #111827;
            --muted: #6b7280;
            --bg: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background: #f3f4f6;
        }

        .page {
            max-width: 1200px;
            margin: 24px auto;
            background: var(--bg);
            border: 1px solid #d1d5db;
            padding: 24px;
        }

        .title {
            text-align: center;
            margin-bottom: 16px;
        }

        .title h1 {
            margin: 0 0 6px;
            font-size: 22px;
            letter-spacing: 0.03em;
        }

        .title p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid var(--border);
            padding: 8px;
            font-size: 12px;
            vertical-align: top;
        }

        th {
            text-transform: uppercase;
            font-weight: 700;
            background: #f9fafb;
            text-align: center;
        }

        td.num {
            width: 48px;
            text-align: center;
        }

        td.center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            border: 1px solid #9ca3af;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .badge.graded {
            border-color: #16a34a;
            color: #166534;
            background: #dcfce7;
        }

        .badge.pending {
            border-color: #d97706;
            color: #92400e;
            background: #fef3c7;
        }

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
            cursor: pointer;
            text-decoration: none;
        }

        .btn.primary {
            background: #2563eb;
            color: #fff;
        }

        .btn.secondary {
            background: #fff;
            color: #111827;
            border: 1px solid #d1d5db;
        }

        @media print {
            body {
                background: #fff;
            }

            .page {
                margin: 0;
                border: none;
                max-width: none;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="title">
            <h1>REKAP NILAI UJIAN</h1>
            <p>{{ $exam->name }} | Dicetak: {{ $printedAt->format('d F Y H:i') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 48px;">No</th>
                    <th style="width: 140px;">NIS</th>
                    <th>Siswa</th>
                    <th style="width: 190px;">Kelas</th>
                    <th style="width: 110px;">Nilai</th>
                    <th style="width: 150px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $index => $attempt)
                    @php
                        $attemptStatus = $attempt->status instanceof \App\Enums\ExamAttemptStatus ? $attempt->status->value : $attempt->status;
                        $isGraded = $attemptStatus === \App\Enums\ExamAttemptStatus::Graded->value;
                    @endphp
                    <tr>
                        <td class="num">{{ $index + 1 }}</td>
                        <td class="center">{{ $attempt->student->nis ?? '-' }}</td>
                        <td>{{ $attempt->student->user->name ?? '-' }}</td>
                        <td>{{ $attempt->student->classroom->name ?? '-' }}</td>
                        <td class="center">
                            {{ $attempt->total_score !== null ? number_format((float) $attempt->total_score, 1) : '-' }}
                        </td>
                        <td class="center">
                            <span class="badge {{ $isGraded ? 'graded' : 'pending' }}">
                                {{ $isGraded ? 'Selesai Dinilai' : 'Butuh Koreksi' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="center">Belum ada data siswa pada ujian ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="toolbar no-print">
        <a href="{{ $backRoute }}" class="btn secondary">Kembali</a>
        <button type="button" class="btn primary" onclick="window.print()">Cetak</button>
    </div>
</body>
</html>

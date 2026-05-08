<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel ZipStream Dashboard</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            padding: 40px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .title {
            text-align: center;
            margin-bottom: 40px;
        }

        .title h1 {
            font-size: 38px;
            color: #333;
        }

        .title p {
            color: #777;
            margin-top: 10px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .card h2 {
            font-size: 30px;
            margin-bottom: 10px;
            color: #4f46e5;
        }

        .card p {
            color: #666;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .panel {
            background: white;
            border-radius: 14px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .panel h3 {
            margin-bottom: 20px;
            color: #333;
        }

        .file-item {
            margin-bottom: 15px;
        }

        .download-btn {
            margin-top: 25px;
        }

        .download-btn button {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        .download-btn button:hover {
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        table th {
            background: #f8fafc;
        }

        .empty {
            color: #999;
            text-align: center;
            padding: 20px;
        }

        @media(max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="title">
        <h1>Laravel ZipStream Dashboard</h1>
        <p>Select files and generate ZIP downloads instantly</p>
    </div>

    <div class="stats">

        <div class="card">
            <h2>{{ count($availableFiles) }}</h2>
            <p>Total Available Files</p>
        </div>

        <div class="card">
            <h2>{{ $downloads->count() }}</h2>
            <p>Recent Downloads</p>
        </div>

    </div>

    <div class="main-grid">

        <div class="panel">

            <h3>Select Files for ZIP Download</h3>

            <form action="{{ route('zip.download') }}" method="GET">

                @foreach($availableFiles as $file)

                    <div class="file-item">
                        <label>
                            <input type="checkbox" name="files[]" value="{{ $file }}">
                            {{ $file }}
                        </label>
                    </div>

                @endforeach

                <div class="download-btn">
                    <button type="submit">
                        Download Selected ZIP
                    </button>
                </div>

            </form>

        </div>

        <div class="panel">

            <h3>Recent ZIP Downloads</h3>

            @if($downloads->count() > 0)

                <table>

                    <thead>
                        <tr>
                            <th>ZIP Name</th>
                            <th>Files</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($downloads as $download)

                            <tr>
                                <td>{{ $download->zip_name }}</td>
                                <td>{{ $download->total_files }}</td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

            @else

                <div class="empty">
                    No downloads yet
                </div>

            @endif

        </div>

    </div>

</div>

</body>
</html>
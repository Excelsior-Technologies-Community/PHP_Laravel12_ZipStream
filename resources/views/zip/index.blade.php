<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laravel ZipStream</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .container {
            background: white;
            padding: 50px 60px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 420px;
        }

        .container h1 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #333;
        }

        .container p {
            color: #666;
            margin-bottom: 30px;
        }

        .download-btn {
            display: inline-block;
            text-decoration: none;
        }

        .download-btn button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 14px 28px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all .3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .download-btn button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25);
        }

        .footer {
            margin-top: 25px;
            font-size: 13px;
            color: #999;
        }
    </style>

</head>

<body>

    <div class="container">

        <h1>Laravel ZipStream</h1>

        <p>
            Download multiple files instantly using ZipStream streaming technology.
        </p>

        <a href="{{ route('zip.download') }}" class="download-btn">
            <button>Download ZIP File</button>
        </a>

        <div class="footer">
            Powered by Laravel 12
        </div>

    </div>

</body>

</html>
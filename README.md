# PHP_Laravel12_ZipStream

## Project Description

PHP_Laravel12_ZipStream is a simple Laravel 12 web application that demonstrates how to generate and download ZIP files dynamically using streaming technology.

The application allows users to download multiple files as a single ZIP archive directly from the browser. Instead of first creating a ZIP file on the server and storing it temporarily, the files are compressed and streamed in real time during the download process.

This approach improves performance and reduces server storage usage because no temporary ZIP file is created. It is especially useful when downloading multiple documents, images, or reports from a web application.

The project is useful for developers who want to learn how to implement efficient file downloads in Laravel applications.


## Features

- Download multiple files as a single ZIP archive

- Stream ZIP files directly to the browser

- No temporary ZIP file stored on the server

- Simple and clean user interface

- Efficient memory usage for file downloads

- Easy integration with Laravel storage files

- Beginner-friendly Laravel example project

- Clean MVC architecture implementation


## Technologies Used

- PHP 8+

- Laravel 12

- Blade Template Engine

- HTML5

- CSS3

- Composer (Dependency Manager)

- Laravel Artisan CLI

- Laravel Storage System

- Zip Streaming Library


## How It Works

1. The user opens the ZipStream download page.
2. A button allows the user to request a ZIP download.
3. When the button is clicked, a request is sent to the Laravel controller.
4. The controller collects files from the Laravel storage folder.
5. These files are compressed and streamed as a ZIP archive.
6. The browser downloads the ZIP file instantly.

This approach avoids creating temporary files and improves performance.



---



## Installation Steps


---


## STEP 1: Create Laravel 12 Project

### Open terminal / CMD and run:

```
composer create-project laravel/laravel PHP_Laravel12_ZipStream "12.*"

```

### Go inside project:

```
cd PHP_Laravel12_ZipStream

```

#### Explanation:

This command installs a fresh Laravel 12 application and creates the project folder.

The cd command moves into the project directory so we can run Laravel commands.




## STEP 2: Database Setup (Optional)

### Update database details:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_ZipStream
DB_USERNAME=root
DB_PASSWORD=

```

### Create database in MySQL / phpMyAdmin:

```
Database name: laravel12_ZipStream

```

### Then Run:

```
php artisan migrate

```


#### Explanation:

This step connects Laravel with a MySQL database.

The php artisan migrate command creates Laravel’s default tables like users, cache, and jobs.





## STEP 3: Install ZipStream Package 

### Install the package:

```
composer require stechstudio/laravel-zipstream

```

#### Explanation:

This package allows Laravel to generate and stream ZIP files directly to the browser.

Instead of creating a ZIP file on the server first, the files are compressed and streamed in real time during download.





## STEP 4: Publish Vendor Config (optional)

### Run:

```
php artisan vendor:publish --provider="Stechstudio\Laravel\ZipStream\ZipStreamServiceProvider"

```

### Explanation:

This command publishes the package configuration file into the Laravel project so you can customize ZipStream settings if needed.





## STEP 5: Create Controller

### We need a controller to handle the logic:

```
php artisan make:controller ZipController

```

### Open app/Http/Controllers/ZipController.php and replace with:

```
<?php

namespace App\Http\Controllers;

use STS\ZipStream\Facades\Zip;

class ZipController extends Controller
{
    public function index()
    {
        return view('zip.index');
    }

    public function downloadZip()
    {
        $files = [
            'sample1.txt' => storage_path('app/public/sample1.txt'),
            'sample2.txt' => storage_path('app/public/sample2.txt'),
        ];

        return Zip::create('myfiles.zip', $files);
    }
}

```

#### Explanation:

The index() method loads the download page.

The downloadZip() method creates a ZIP archive containing multiple files and streams it directly to the browser.





## STEP 6: Create View

### resources/views/zip/index.blade.php

```
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

```

#### Explanation:

This Blade view creates a simple UI page with a button that allows users to download the ZIP file.





## STEP 7: Create Sample Files

### Then open this folder:

```
storage/app/public

```

### Create a new file:

```
sample1.txt

```

### Put this inside:

```
This is sample file 1 from Laravel ZipStream

Laravel ZipStream allows streaming ZIP downloads without creating temporary files.

This file is included in the ZIP archive generated by the application.

```

### Create another file:

```
sample2.txt

```

### Put this inside:

```
This is sample file 2 from Laravel ZipStream

ZipStream is useful when downloading multiple files as a single ZIP archive.

It streams files directly to the browser for efficient downloads.

```


### And make them accessible:

```
php artisan storage:link

```

#### Explanation:

This command creates a symbolic link between storage and public folders so files stored in Laravel storage can be accessed publicly.




## STEP 8: Define Routes

### Open routes/web.php and add:

```
use App\Http\Controllers\ZipController;

Route::get('/zip', [ZipController::class, 'index']);
Route::get('/zip/download', [ZipController::class, 'downloadZip'])->name('zip.download');

```

#### Explanation:

Routes define the URL endpoints of the application.

/zip → shows the download page

/zip/download → generates and downloads the ZIP file





## STEP 9: Test It

### Start Laravel dev server:

```
php artisan serve

```

### Open in browser:

```
http://127.0.0.1:8000/zip

```

#### Explanation:

Laravel starts a local development server, and visiting /zip displays the download page where users can generate the ZIP file.




## Expected Output:

### ZipStream Download Page:


<img width="1919" height="956" alt="Screenshot 2026-03-10 111009" src="https://github.com/user-attachments/assets/577cbcfe-9af2-4bd7-813b-79b3f7b3119e" />


### Downloaded ZIP File:


<img width="465" height="140" alt="Screenshot 2026-03-10 131518" src="https://github.com/user-attachments/assets/db2c9c62-2d97-4622-b6ba-4a16db7d6790" />



---

# Project Folder Structure:

```
PHP_Laravel12_ZipStream
│
├── app
│   └── Http
│       └── Controllers
│           └── ZipController.php
│
├── bootstrap
│
├── config
│
├── database
│   └── migrations
│
├── public
│
├── resources
│   └── views
│       └── zip
│           └── index.blade.php
│
├── routes
│   └── web.php
│
├── storage
│   └── app
│       └── public
│           ├── sample1.txt
│           └── sample2.txt
│
├── vendor
│
├── .env
├── artisan
├── composer.json
├── package.json
└── README.md

```

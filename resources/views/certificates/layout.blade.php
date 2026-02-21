<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate</title>
    <style>
        @page {
            margin: 0;
            size: landscape;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
            width: 100%;
            height: 100%;
        }
        .certificate-container {
            width: 100%;
            height: 100vh;
            position: relative;
            box-sizing: border-box;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
        }
        /* Common Typography */
        h1, h2, h3, h4, p {
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-uppercase { text-transform: uppercase; }
        .font-bold { font-weight: bold; }
        .absolute { position: absolute; }
        .w-full { width: 100%; }
        
        /* Utility for centering content generally */
        .content-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 80%;
        }
    </style>
    @yield('styles')
</head>
<body>
    @yield('content')
</body>
</html>

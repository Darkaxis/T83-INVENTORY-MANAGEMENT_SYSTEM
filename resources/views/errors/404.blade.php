<!-- filepath: d:\WST\inventory-management-system\resources\views\errors\404.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .error-container {
            text-align: center;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        .icon {
            font-size: 64px;
            color: #4e73df;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 16px;
        }
        .error-code {
            font-size: 42px;
            font-weight: 700;
            color: #4e73df;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            background-color: #4e73df;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
            margin: 0 5px;
        }
        .btn:hover {
            background-color: #2e59d9;
        }
        .btn-light {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-light:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">🔍</div>
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        

        <div>
         
                <a href="{{ route('login') }}" class="btn">Login</a>
           
           
            
            <a href="javascript:history.back()" class="btn btn-light">Go Back</a>
        </div>
    </div>
</body>
</html>
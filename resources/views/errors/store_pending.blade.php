<!-- filepath: d:\WST\inventory-management-system\resources\views\errors\store_pending.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store Pending Approval</title>
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
            color: #ffc107;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 16px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            background-color: #1976d2;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">⏳</div>
        <h1>Store Pending Approval</h1>
        <p>This store is currently pending administrator approval and is not yet accessible. Please check back later.</p>
        <a href="http://{{ config('app.domain', 'localhost') }}" class="btn">Return to Main Site</a>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Library System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-card {
            max-width: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .error-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .error-content {
            padding: 30px 25px;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-header">
            <h1><i class="fas fa-exclamation-triangle"></i> Error</h1>
        </div>
        <div class="error-content">
            <div class="alert alert-danger">
                <h5 class="alert-heading">Cannot Request Loan</h5>
                <hr>
                <p class="mb-0">{{ $message }}</p>
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>


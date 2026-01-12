<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Holidays.io - Operator Management'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 1200px;
        }

        .auth-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            min-height: 600px;
        }

        .auth-row {
            display: flex;
            min-height: 600px;
        }

        .auth-form-side {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-branding-side {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .auth-logo {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .auth-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .auth-branding-side h2 {
            font-size: 2rem;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .auth-branding-side p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .benefits-list {
            list-style: none;
            text-align: left;
            margin-top: 30px;
        }

        .benefits-list li {
            padding: 10px 0;
            font-size: 0.95rem;
        }

        .benefits-list i {
            margin-right: 10px;
            color: #ffc107;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control, .form-check-input {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px 12px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-check {
            margin-bottom: 12px;
        }

        .form-check-label {
            margin-bottom: 0;
            cursor: pointer;
        }

        .btn-auth {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: bold;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 1rem;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s;
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-auth:active {
            transform: translateY(0);
        }

        .form-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .form-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .form-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .password-strength {
            margin-top: 10px;
            font-size: 0.85rem;
        }

        .strength-item {
            padding: 5px 0;
            display: flex;
            align-items: center;
        }

        .strength-item i {
            margin-right: 8px;
            width: 16px;
        }

        .strength-item.valid {
            color: #28a745;
        }

        .strength-item.invalid {
            color: #dc3545;
        }

        .required {
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .auth-row {
                flex-direction: column;
                min-height: auto;
            }

            .auth-form-side, .auth-branding-side {
                padding: 40px 20px;
            }

            .auth-title {
                font-size: 1.5rem;
            }

            .auth-branding-side {
                min-height: 300px;
            }
        }
    </style>
</head>
<body>

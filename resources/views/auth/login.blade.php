<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Designerflix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 400px;
            width: 100%;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            color: #007bff;
            font-size: 32px;
            font-weight: bold;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 10px;
            padding: 12px 15px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #007bff;
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            width: 100%;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
        }

        .text-light {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <i class="fas fa-play-circle"></i> Designerflix
        </div>
        
        <h4 class="text-center text-light mb-4">Área de Membros</h4>
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label text-light">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="douglas@test.com" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label text-light">Senha</label>
                <input type="password" class="form-control" id="password" name="password" value="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>
        
        <div class="text-center mt-4">
            <small class="text-light">
                <strong>Credenciais de teste:</strong><br>
                Email: douglas@test.com<br>
                Senha: password
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
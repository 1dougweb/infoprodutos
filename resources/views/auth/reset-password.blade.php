<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Redefinir Senha - {{ $siteName }}</title>
    @if($faviconPath)
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($faviconPath) }}">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --secondary-color: {{ $secondaryColor }};
            --background-color: {{ $backgroundColor }};
            --card-background: {{ $cardBackground }};
        }

        body {
            background: linear-gradient(135deg, var(--background-color), {{ $backgroundGradient }});
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .reset-card {
            background: var(--card-background);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 420px;
            width: 100%;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-image {
            max-height: 60px;
            max-width: 200px;
            margin-bottom: 15px;
        }

        .logo-text {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 16px;
            margin-bottom: 0;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 0.2rem {{ $primaryColorRgba }};
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .btn-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .text-light {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #d4edda;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #f8d7da;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        @media (max-width: 480px) {
            .reset-card {
                margin: 20px;
                padding: 30px 25px;
            }

            .logo-text {
                font-size: 24px;
            }
        }

        .password-requirements {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            font-size: 14px;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }

        .password-requirements li {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="logo-container">
            @if($logoPath)
                <img src="{{ Storage::url($logoPath) }}" alt="{{ $siteName }}" class="logo-image">
            @endif
            <h1 class="logo-text">{{ $siteName }}</h1>
            <p class="subtitle">Redefinir Senha</p>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" id="resetForm">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            
            <div class="mb-3">
                <label for="email_display" class="form-label text-light">
                    <i class="bi bi-envelope me-2"></i>Email
                </label>
                <input type="email" class="form-control" id="email_display" value="{{ $email }}" disabled>
                <small class="text-light">Este é o email que receberá a nova senha</small>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label text-light">
                    <i class="bi bi-lock me-2"></i>Nova Senha
                </label>
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Digite sua nova senha" required minlength="8" autocomplete="new-password">
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label text-light">
                    <i class="bi bi-lock-fill me-2"></i>Confirmar Nova Senha
                </label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" 
                       placeholder="Confirme sua nova senha" required minlength="8" autocomplete="new-password">
            </div>

            <div class="password-requirements">
                <strong class="text-light">Requisitos da senha:</strong>
                <ul>
                    <li>Mínimo de 8 caracteres</li>
                    <li>As senhas devem ser idênticas</li>
                </ul>
            </div>
            
            <button type="submit" class="btn btn-primary mt-3" id="resetBtn">
                <i class="bi bi-key me-2"></i>
                <span class="btn-text">Redefinir Senha</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </form>

        <div class="back-to-login">
            <a href="{{ route('login') }}" class="btn-link">
                <i class="bi bi-arrow-left me-1"></i>Voltar ao Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    
    <script>
        // Reset form handling
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('resetBtn');
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            
            // Validar se as senhas coincidem
            if (password !== passwordConfirmation) {
                e.preventDefault();
                alert('As senhas não coincidem!');
                return;
            }
            
            btn.disabled = true;
            btnText.textContent = 'Redefinindo...';
            spinner.classList.remove('d-none');
        });

        // Validação em tempo real
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmation = this.value;
            
            if (confirmation && password !== confirmation) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '';
            }
        });
    </script>
</body>
</html>

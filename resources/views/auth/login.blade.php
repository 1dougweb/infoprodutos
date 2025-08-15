<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ $siteName }}</title>
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

        .login-card {
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

        .input-with-icon {
            position: relative;
            margin-bottom: 1rem;
        }

        .input-with-icon .main-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            z-index: 2;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 10px;
            padding: 12px 15px 12px 45px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 0.2rem {{ $primaryColorRgba }};
        }

        .input-with-icon:focus-within .main-icon {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.1);
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

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
        }

        .divider span {
            background: var(--card-background);
            padding: 0 15px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .login-card {
                margin: 20px;
                padding: 30px 25px;
            }

            .logo-text {
                font-size: 24px;
            }

            .remember-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .forgot-link {
                align-self: flex-end;
            }
        }

        .alert-info {
            background-color: rgba(13, 202, 240, 0.2);
            color: #b6effb;
            border: 1px solid rgba(13, 202, 240, 0.3);
        }

        .alert-warning {
            background-color: rgba(255, 193, 7, 0.2);
            color: #fff3cd;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        #reset_code {
            font-family: 'Courier New', monospace;
            text-align: center;
            font-size: 18px;
            letter-spacing: 3px;
        }

        .code-input {
            padding-left: 45px !important;
            padding-right: 15px !important;
        }

        .modal-content {
            min-height: 400px;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .step.active {
            background: var(--primary-color);
            color: white;
        }

        .step.completed {
            background: #28a745;
            color: white;
        }

        /* Custom checkbox styling */
        .remember-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            margin-top: 0.5rem;
        }

        .custom-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            user-select: none;
        }

        .custom-checkbox input[type="checkbox"] {
            appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 3px;
            background: rgba(255, 255, 255, 0.1);
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .custom-checkbox input[type="checkbox"]:hover {
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.15);
        }

        .custom-checkbox input[type="checkbox"]:checked {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .custom-checkbox input[type="checkbox"]:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 11px;
            font-weight: bold;
            animation: checkmark 0.3s ease-in-out;
        }

        .custom-checkbox input[type="checkbox"]:active {
            transform: scale(0.95);
        }

        .custom-checkbox input[type="checkbox"]:focus {
            outline: none;
            box-shadow: 0 0 0 3px {{ $primaryColorRgba }};
        }

        @keyframes checkmark {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.5);
            }
            50% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1.2);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            opacity: 0.9;
        }

        .forgot-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
            opacity: 1;
        }

        .forgot-link i {
            font-size: 12px;
        }

        /* Back button styling to look like a link */
        .back-link {
            background: none;
            border: none;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            opacity: 0.9;
            cursor: pointer;
            padding: 0;
            font-family: inherit;
        }

        .back-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
            opacity: 1;
        }

        .back-link:focus {
            outline: none;
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .back-link i {
            font-size: 12px;
            margin-right: 4px;
        }

        /* Validation states */
        .input-with-icon.is-valid .form-control {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }

        .input-with-icon.is-valid .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .input-with-icon.is-invalid .form-control {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }

        .input-with-icon.is-invalid .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .validation-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            z-index: 3;
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .input-with-icon.is-valid .validation-icon.valid {
            opacity: 1;
            color: #28a745;
            transform: translateY(-50%) scale(1);
            animation: validationPop 0.3s ease-out;
        }

        .input-with-icon.is-invalid .validation-icon.invalid {
            opacity: 1;
            color: #dc3545;
            transform: translateY(-50%) scale(1);
            animation: validationPop 0.3s ease-out;
        }

        .input-with-icon.is-validating .validation-icon.loading {
            opacity: 1;
            color: var(--primary-color);
            animation: spin 1s linear infinite;
            transform: translateY(-50%) scale(1);
        }

        @keyframes validationPop {
            0% {
                opacity: 0;
                transform: translateY(-50%) scale(0.5);
            }
            50% {
                opacity: 1;
                transform: translateY(-50%) scale(1.2);
            }
            100% {
                opacity: 1;
                transform: translateY(-50%) scale(1);
            }
        }

        /* Ajustar padding do input para dar espaço aos ícones de validação */
        .input-with-icon.is-valid .form-control,
        .input-with-icon.is-invalid .form-control,
        .input-with-icon.is-validating .form-control {
            padding-right: 45px;
        }

        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }

        .form-control.is-validating {
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-container">
            @if($logoPath)
                <img src="{{ Storage::url($logoPath) }}" alt="{{ $siteName }}" class="logo-image">
            @endif
            <h1 class="logo-text">{{ $siteName }}</h1>
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
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" id="loginForm">
            @csrf
            <div class="input-with-icon" id="email-container">
                <input type="email" class="form-control" id="email" name="email" 
                       value="{{ old('email') }}" 
                       placeholder="Digite seu email" required autocomplete="email">
                <i class="bi bi-envelope main-icon"></i>
                <i class="validation-icon valid bi bi-check-circle-fill"></i>
                <i class="validation-icon invalid bi bi-x-circle-fill"></i>
                <i class="validation-icon loading bi bi-arrow-clockwise"></i>
            </div>
            
            <div class="input-with-icon" id="password-container">
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Digite sua senha" required autocomplete="current-password">
                <i class="bi bi-lock main-icon"></i>
                <i class="validation-icon valid bi bi-check-circle-fill"></i>
                <i class="validation-icon invalid bi bi-x-circle-fill"></i>
                <i class="validation-icon loading bi bi-arrow-clockwise"></i>
            </div>

            <div class="remember-container">
                <label class="custom-checkbox" for="remember">
                    <input type="checkbox" id="remember" name="remember" 
                           {{ old('remember') ? 'checked' : '' }}>
                    <span>Lembrar de mim</span>
                </label>
                
                <a href="#" class="forgot-link" onclick="showForgotPassword()">
                    <i class="bi bi-question-circle me-1"></i>Esqueceu sua senha?
                </a>
            </div>
            
            <button type="submit" class="btn btn-primary" id="loginBtn">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                <span class="btn-text">Entrar</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </form>
    </div>

    <!-- Modal de Recuperação de Senha -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: var(--card-background); border: 1px solid rgba(255, 255, 255, 0.1);">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                    <h5 class="modal-title text-light" id="forgotPasswordModalLabel">
                        <i class="bi bi-key me-2"></i>Recuperar Senha
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="step-indicator">
                        <div class="step active" id="step1">1</div>
                        <div class="step" id="step2">2</div>
                        <div class="step" id="step3">3</div>
                    </div>
                    <p class="text-light mb-3" id="stepDescription">
                        Digite seu email abaixo e enviaremos um código de 6 dígitos para redefinir sua senha.
                    </p>
                    <div id="emailStep">
                        <form id="forgotPasswordForm">
                            @csrf
                            <div class="input-with-icon">
                                <input type="email" class="form-control" id="forgot_email" name="email" 
                                       placeholder="Digite seu email" required>
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="forgotBtn">
                                    <i class="bi bi-send me-2"></i>
                                    <span class="btn-text">Enviar Código</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="codeStep" class="d-none">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Digite o código de 6 dígitos enviado para seu email.
                        </div>
                        <form id="validateCodeForm">
                            @csrf
                            <input type="hidden" id="reset_email" name="email">
                            <div class="input-with-icon">
                                <input type="text" class="form-control code-input" id="reset_code" name="code" 
                                       placeholder="000000" maxlength="6" pattern="[0-9]{6}" required>
                                <i class="bi bi-key"></i>
                            </div>
                            <small class="text-light d-block text-center mb-3">Código válido por 15 minutos</small>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="validateBtn">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <span class="btn-text">Validar Código</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="back-link" onclick="backToEmailStep()">
                                    <i class="bi bi-arrow-left"></i>Voltar
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="passwordStep" class="d-none">
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Código validado! Agora defina sua nova senha.
                        </div>
                        <form id="resetPasswordForm">
                            @csrf
                            <input type="hidden" id="final_email" name="email">
                            <input type="hidden" id="final_code" name="code">
                            <div class="input-with-icon">
                                <input type="password" class="form-control" id="new_password" name="password" 
                                       placeholder="Digite sua nova senha" required minlength="8">
                                <i class="bi bi-lock"></i>
                            </div>
                            <div class="input-with-icon">
                                <input type="password" class="form-control" id="new_password_confirmation" name="password_confirmation" 
                                       placeholder="Confirme sua nova senha" required minlength="8">
                                <i class="bi bi-lock-fill"></i>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="resetPasswordBtn">
                                    <i class="bi bi-key me-2"></i>
                                    <span class="btn-text">Redefinir Senha</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    
    <script>
        function showForgotPassword() {
            const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            modal.show();
        }

        // Login form handling with data protection
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevenir submit padrão
            
            const btn = document.getElementById('loginBtn');
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            // Limpar campos imediatamente para evitar exposição
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            
            btn.disabled = true;
            btnText.textContent = 'Entrando...';
            spinner.classList.remove('d-none');
            
            // Enviar dados via fetch para proteger contra exposição
            fetch('{{ route("login.post") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    email: btoa(email), // Codificar email
                    password: btoa(password), // Codificar senha
                    remember: remember
                })
            })
            .then(response => {
                if (response.redirected) {
                    // Redirect manual para dashboard
                    window.location.href = response.url;
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success === false) {
                    // Restaurar apenas o email em caso de erro
                    document.getElementById('email').value = email;
                    
                    // Mostrar erro
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger';
                    errorAlert.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + (data.message || 'Erro ao fazer login');
                    
                    const loginCard = document.querySelector('.login-card');
                    const existingAlert = loginCard.querySelector('.alert');
                    if (existingAlert) {
                        existingAlert.remove();
                    }
                    loginCard.insertBefore(errorAlert, loginCard.firstChild);
                    
                    // Remover alert após 5 segundos
                    setTimeout(() => errorAlert.remove(), 5000);
                }
            })
            .catch(error => {
                // Restaurar campos em caso de erro crítico
                document.getElementById('email').value = email;
                document.getElementById('password').value = password;
                
                // Em caso de erro, fazer submit tradicional como fallback
                this.removeEventListener('submit', arguments.callee);
                this.submit();
            })
            .finally(() => {
                btn.disabled = false;
                btnText.textContent = 'Entrar';
                spinner.classList.add('d-none');
            });
        });

        function updateStepIndicator(activeStep) {
            // Reset all steps
            document.getElementById('step1').className = 'step';
            document.getElementById('step2').className = 'step';
            document.getElementById('step3').className = 'step';
            
            // Set completed steps
            for (let i = 1; i < activeStep; i++) {
                document.getElementById('step' + i).classList.add('completed');
            }
            
            // Set active step
            document.getElementById('step' + activeStep).classList.add('active');
        }

        function backToEmailStep() {
            document.getElementById('emailStep').classList.remove('d-none');
            document.getElementById('codeStep').classList.add('d-none');
            document.getElementById('passwordStep').classList.add('d-none');
            
            // Update modal title and description
            document.getElementById('forgotPasswordModalLabel').innerHTML = '<i class="bi bi-key me-2"></i>Recuperar Senha';
            document.getElementById('stepDescription').textContent = 'Digite seu email abaixo e enviaremos um código de 6 dígitos para redefinir sua senha.';
            
            // Update step indicator
            updateStepIndicator(1);
            
            // Clear forms
            document.getElementById('reset_code').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('new_password_confirmation').value = '';
        }

        function showCodeStep(email) {
            document.getElementById('emailStep').classList.add('d-none');
            document.getElementById('codeStep').classList.remove('d-none');
            document.getElementById('passwordStep').classList.add('d-none');
            
            // Update modal title and description
            document.getElementById('forgotPasswordModalLabel').innerHTML = '<i class="bi bi-shield-check me-2"></i>Validar Código';
            document.getElementById('stepDescription').textContent = 'Digite o código de 6 dígitos que foi enviado para seu email.';
            
            // Update step indicator
            updateStepIndicator(2);
            
            // Set email
            document.getElementById('reset_email').value = email;
        }

        function showPasswordStep(email, code) {
            document.getElementById('emailStep').classList.add('d-none');
            document.getElementById('codeStep').classList.add('d-none');
            document.getElementById('passwordStep').classList.remove('d-none');
            
            // Update modal title and description
            document.getElementById('forgotPasswordModalLabel').innerHTML = '<i class="bi bi-lock-fill me-2"></i>Nova Senha';
            document.getElementById('stepDescription').textContent = 'Código validado com sucesso! Agora defina sua nova senha.';
            
            // Update step indicator
            updateStepIndicator(3);
            
            // Set email and code
            document.getElementById('final_email').value = email;
            document.getElementById('final_code').value = code;
        }

        // Step 1: Send code to email
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('forgotBtn');
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');
            const email = document.getElementById('forgot_email').value;
            
            btn.disabled = true;
            btnText.textContent = 'Enviando...';
            spinner.classList.remove('d-none');
            
            fetch('{{ route("password.email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showCodeStep(email);
                } else {
                    throw new Error(data.message || 'Erro ao enviar código');
                }
            })
            .catch(error => {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + error.message;
                
                document.getElementById('emailStep').appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 5000);
            })
            .finally(() => {
                btn.disabled = false;
                btnText.textContent = 'Enviar Código';
                spinner.classList.add('d-none');
            });
        });

        // Step 2: Validate code
        document.getElementById('validateCodeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('validateBtn');
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');
            const email = document.getElementById('reset_email').value;
            const code = document.getElementById('reset_code').value;
            
            btn.disabled = true;
            btnText.textContent = 'Validando...';
            spinner.classList.remove('d-none');
            
            fetch('{{ route("password.validate-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email, code: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showPasswordStep(email, code);
                } else {
                    throw new Error(data.message || 'Código inválido');
                }
            })
            .catch(error => {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + error.message;
                
                document.getElementById('codeStep').appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 5000);
            })
            .finally(() => {
                btn.disabled = false;
                btnText.textContent = 'Validar Código';
                spinner.classList.add('d-none');
            });
        });

        // Step 3: Reset password
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('resetPasswordBtn');
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');
            const email = document.getElementById('final_email').value;
            const code = document.getElementById('final_code').value;
            const password = document.getElementById('new_password').value;
            const passwordConfirmation = document.getElementById('new_password_confirmation').value;
            
            if (password !== passwordConfirmation) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>As senhas não coincidem!';
                
                document.getElementById('passwordStep').appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 5000);
                return;
            }
            
            btn.disabled = true;
            btnText.textContent = 'Redefinindo...';
            spinner.classList.remove('d-none');
            
            fetch('{{ route("password.reset-with-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    email: btoa(email), // Encode email
                    code: code, 
                    password: btoa(password), // Encode password
                    password_confirmation: btoa(passwordConfirmation) // Encode password confirmation
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                    modal.hide();
                    
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success';
                    alert.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
                    
                    const loginCard = document.querySelector('.login-card');
                    loginCard.insertBefore(alert, loginCard.firstChild);
                    
                    // Reset modal to initial state
                    setTimeout(() => {
                        backToEmailStep();
                        document.getElementById('forgot_email').value = '';
                        alert.remove();
                    }, 3000);
                } else {
                    throw new Error(data.message || 'Erro ao redefinir senha');
                }
            })
            .catch(error => {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + error.message;
                
                document.getElementById('passwordStep').appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 5000);
            })
            .finally(() => {
                btn.disabled = false;
                btnText.textContent = 'Redefinir Senha';
                spinner.classList.add('d-none');
            });
        });

        // Auto-format code input (only numbers)
        document.getElementById('reset_code').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });

        // Função para limpar dados sensíveis do localStorage/sessionStorage
        function clearSensitiveData() {
            // Limpar qualquer dado sensível que possa ter sido armazenado
            const sensitiveKeys = ['email', 'password', 'code', 'token', 'credentials'];
            sensitiveKeys.forEach(key => {
                localStorage.removeItem(key);
                sessionStorage.removeItem(key);
            });
        }

        // Limpar dados ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            clearSensitiveData();
        });

        // Limpar dados antes de sair da página
        window.addEventListener('beforeunload', function() {
            clearSensitiveData();
        });

        // Sobrescrever console em produção para evitar logs acidentais
        if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            const originalConsole = {
                log: console.log,
                error: console.error,
                warn: console.warn,
                info: console.info
            };
            
            console.log = function() { /* Silenciado em produção */ };
            console.info = function() { /* Silenciado em produção */ };
            console.warn = function(...args) { 
                // Manter warnings importantes
                if (args[0] && typeof args[0] === 'string' && args[0].includes('security')) {
                    originalConsole.warn.apply(console, args);
                }
            };
            console.error = function(...args) { 
                // Manter erros críticos
                originalConsole.error.apply(console, args);
            };
        }

        // Proteção contra debugging
        setInterval(function() {
            if (window.devtools && window.devtools.open) {
                clearSensitiveData();
            }
        }, 1000);

        // Real-time validation
        let emailValidationTimeout;
        let passwordValidationTimeout;

        function setValidationState(container, state) {
            const containerEl = document.getElementById(container);
            containerEl.classList.remove('is-valid', 'is-invalid', 'is-validating');
            
            if (state) {
                containerEl.classList.add('is-' + state);
            }
        }

        function validateEmail(email) {
            if (!email || email.length < 3) {
                setValidationState('email-container', null);
                return;
            }

            // Basic email format check
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                setValidationState('email-container', 'invalid');
                return;
            }

            setValidationState('email-container', 'validating');

            fetch('{{ route("validate.email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    setValidationState('email-container', 'valid');
                } else {
                    setValidationState('email-container', 'invalid');
                }
            })
            .catch(error => {
                setValidationState('email-container', 'invalid');
            });
        }

        function validatePassword(email, password) {
            if (!password || password.length < 1) {
                setValidationState('password-container', null);
                return;
            }

            // Validação básica local apenas
            if (password.length < 6) {
                setValidationState('password-container', 'invalid');
                return;
            }

            // Por segurança, não validamos credenciais em tempo real
            // Apenas validação básica de formato/comprimento
            setValidationState('password-container', 'valid');
        }

        // Email validation
        document.getElementById('email').addEventListener('input', function(e) {
            clearTimeout(emailValidationTimeout);
            emailValidationTimeout = setTimeout(() => {
                validateEmail(e.target.value);
            }, 500); // Wait 500ms after user stops typing
        });

        // Password validation (local apenas)
        document.getElementById('password').addEventListener('input', function(e) {
            clearTimeout(passwordValidationTimeout);
            passwordValidationTimeout = setTimeout(() => {
                validatePassword('', e.target.value);
            }, 300); // Validação local mais rápida
        });
    </script>
</body>
</html> 
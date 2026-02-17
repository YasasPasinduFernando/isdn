<?php
$isRegisterMode = ($authMode ?? 'login') === 'register';
$swapFlash = get_flash_message();
?>

<style>
    .auth-swap-page {
        min-height: calc(100vh - 8.5rem);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }
    .auth-swap-wrapper {
        width: 100%;
        max-width: 900px;
    }
    .auth-swap-container {
        position: relative;
        width: 100%;
        max-width: 860px;
        min-height: 560px;
        margin: 0 auto;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 20px 60px rgba(19, 38, 77, 0.22);
        overflow: hidden;
    }
    .auth-swap-container.active {
        min-height: 620px;
    }
    .auth-form-box {
        position: absolute;
        top: 0;
        width: 50%;
        height: 100%;
        padding: 2.2rem 1.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #333;
        background: #ffffff;
        overflow: hidden;
        transition: all 0.6s ease-in-out;
        z-index: 2;
    }
    .auth-form-box.login {
        right: 0;
        left: auto;
    }
    .auth-form-box.register {
        left: 0;
        right: auto;
        opacity: 0;
        transform: translateX(-100%);
        pointer-events: none;
    }
    .auth-swap-container.active .auth-form-box.login {
        opacity: 0;
        transform: translateX(100%);
        pointer-events: none;
    }
    .auth-swap-container.active .auth-form-box.register {
        opacity: 1;
        transform: translateX(0);
        pointer-events: auto;
    }
    .auth-form-content {
        width: 100%;
        max-width: 360px;
        margin: auto 0;
    }
    .auth-form-box.register .auth-form-content {
        margin: 0;
    }
    .auth-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 74px;
        height: 74px;
        border-radius: 999px;
        margin-bottom: 0.65rem;
        background: linear-gradient(135deg, #14b8a6 0%, #059669 100%);
        color: #ffffff;
        box-shadow: 0 12px 26px rgba(16, 185, 129, 0.32);
    }
    .auth-title {
        margin: 0;
        font-size: 2.1rem;
        font-weight: 800;
        color: #1f2937;
        letter-spacing: -0.02em;
    }
    .auth-sub {
        margin: 0.25rem 0 0.95rem;
        font-size: 0.92rem;
        color: #6b7280;
    }
    .auth-field {
        position: relative;
        margin-top: 0.72rem;
    }
    .auth-input {
        width: 100%;
        border: 1px solid #e5e7eb;
        background: #f3f4f8;
        border-radius: 11px;
        padding: 0.76rem 2.8rem 0.76rem 2.55rem;
        font-size: 0.9rem;
        color: #1f2937;
        outline: none;
        transition: box-shadow 0.2s, background 0.2s, border-color 0.2s;
    }
    .auth-input:focus {
        background: #ffffff;
        border-color: #14b8a6;
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.16);
    }
    .auth-left-icon {
        position: absolute;
        left: 0.78rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        pointer-events: none;
    }
    .auth-eye-btn {
        position: absolute;
        right: 0.72rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
    }
    .auth-eye-btn:hover {
        color: #0d9488;
    }
    .auth-row {
        margin-top: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.84rem;
    }
    .auth-row a {
        color: #0d9488;
        font-weight: 600;
    }
    .auth-row a:hover {
        color: #0f766e;
        text-decoration: underline;
    }
    .auth-main-btn {
        width: 100%;
        margin-top: 0.85rem;
        border: none;
        border-radius: 11px;
        padding: 0.77rem 0.8rem;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.92rem;
        background: linear-gradient(135deg, #14b8a6 0%, #059669 100%);
        box-shadow: 0 10px 22px rgba(16, 185, 129, 0.25);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .auth-main-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(16, 185, 129, 0.3);
    }
    .auth-divider {
        position: relative;
        margin: 1.35rem 0 1.1rem;
    }
    .auth-divider-line {
        height: 1px;
        background: #e5e7eb;
    }
    .auth-divider-text {
        position: absolute;
        left: 50%;
        top: 0;
        transform: translate(-50%, -50%);
        background: #ffffff;
        padding: 0 0.55rem;
        font-size: 0.72rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .auth-google-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        border-radius: 11px;
        padding: 0.74rem 0.8rem;
        font-size: 0.88rem;
        font-weight: 600;
        color: #374151;
        margin-top: 0.2rem;
        transition: background 0.2s, box-shadow 0.2s;
    }
    .auth-google-btn:hover {
        background: #f8fafc;
        box-shadow: 0 4px 14px rgba(2, 6, 23, 0.08);
    }
    .auth-bottom {
        margin-top: 0.95rem;
        font-size: 0.88rem;
        color: #6b7280;
    }
    .auth-bottom button {
        border: none;
        background: transparent;
        color: #0d9488;
        font-weight: 700;
        padding: 0 0.2rem;
    }
    .auth-bottom button:hover {
        text-decoration: underline;
    }
    .auth-mobile-nav {
        display: none;
    }
    .auth-side-layer {
        position: absolute;
        inset: 0;
        z-index: 4;
        pointer-events: none;
    }
    .auth-side-layer::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        width: 50%;
        height: 100%;
        background: linear-gradient(145deg, #14b8a6 0%, #0d9488 45%, #059669 100%);
        border-radius: 24px 120px 120px 24px;
        transition: all 0.7s ease-in-out;
        box-shadow: inset -8px 0 20px rgba(255, 255, 255, 0.12);
    }
    .auth-swap-container.active .auth-side-layer::before {
        transform: translateX(100%);
        border-radius: 120px 24px 24px 120px;
    }
    .auth-side-panel {
        position: absolute;
        top: 0;
        width: 50%;
        height: 100%;
        padding: 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #ffffff;
        pointer-events: none;
        transition: all 0.6s ease-in-out;
        z-index: 5;
    }
    .auth-side-panel.left {
        left: 0;
        pointer-events: auto;
    }
    .auth-side-panel.right {
        right: 0;
        opacity: 0;
        pointer-events: none;
    }
    .auth-swap-container.active .auth-side-panel.left {
        opacity: 0;
        pointer-events: none;
    }
    .auth-swap-container.active .auth-side-panel.right {
        opacity: 1;
        pointer-events: auto;
    }
    .auth-side-title {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
    }
    .auth-side-sub {
        margin: 0.5rem 0 1rem;
        font-size: 0.92rem;
        color: #d9fff8;
    }
    .auth-side-btn {
        border: 2px solid rgba(255, 255, 255, 0.9);
        background: transparent;
        color: #ffffff;
        border-radius: 10px;
        min-width: 125px;
        padding: 0.45rem 0.9rem;
        font-size: 0.85rem;
        font-weight: 700;
        transition: all 0.2s;
    }
    .auth-side-btn:hover {
        background: #ffffff;
        color: #0f766e;
    }
    .auth-flash {
        border-radius: 10px;
        color: #ffffff;
        padding: 0.62rem 0.75rem;
        font-size: 0.82rem;
        text-align: left;
        margin-bottom: 0.65rem;
    }
    .auth-flash-success { background: #16a34a; }
    .auth-flash-error { background: #dc2626; }
    @media (max-width: 960px) {
        .auth-swap-page {
            min-height: 100vh;
            padding: 2.2rem 1rem;
        }
        .auth-swap-wrapper {
            max-width: 460px;
        }
        .auth-swap-container {
            min-height: 0;
            background: transparent;
            box-shadow: none;
            border-radius: 0;
            overflow: visible;
        }
        .auth-swap-container.active {
            min-height: 0;
        }
        .auth-side-layer,
        .auth-side-panel {
            display: none;
        }
        .auth-form-box {
            position: relative;
            width: 100%;
            height: auto;
            right: auto;
            transform: none !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            display: none;
            background: rgba(255, 255, 255, 0.88);
            border-radius: 24px;
            box-shadow: 0 18px 44px rgba(19, 38, 77, 0.2);
        }
        .auth-form-box.login {
            display: <?php echo $isRegisterMode ? 'none' : 'flex'; ?>;
        }
        .auth-form-box.register {
            display: <?php echo $isRegisterMode ? 'flex' : 'none'; ?>;
        }
        .auth-swap-container.active .auth-form-box.login {
            display: none;
        }
        .auth-swap-container.active .auth-form-box.register {
            display: flex;
        }
        .auth-mobile-nav {
            display: flex;
            justify-content: center;
            margin-top: 0.95rem;
            font-size: 0.88rem;
            color: #6b7280;
        }
        .auth-mobile-nav a {
            color: #0d9488;
            font-weight: 700;
            margin-left: 0.35rem;
        }
        .auth-mobile-nav a:hover {
            text-decoration: underline;
        }
    }
    @media (max-width: 640px) {
        .auth-swap-page {
            min-height: auto;
            padding: 0.75rem 0.55rem 1rem;
        }
        .auth-swap-wrapper {
            max-width: 100%;
        }
        .auth-swap-container {
            width: 100%;
            border-radius: 14px;
            box-shadow: 0 10px 26px rgba(19, 38, 77, 0.14);
        }
        .auth-form-box {
            padding: 1.1rem 0.9rem 1.2rem;
        }
        .auth-form-content {
            max-width: 100%;
        }
        .auth-avatar {
            width: 60px;
            height: 60px;
            margin-bottom: 0.5rem;
        }
        .auth-sub {
            font-size: 0.87rem;
            margin: 0.2rem 0 0.75rem;
        }
        .auth-input {
            font-size: 0.86rem;
            padding: 0.7rem 2.6rem 0.7rem 2.4rem;
        }
        .auth-row {
            font-size: 0.8rem;
        }
        .auth-main-btn {
            font-size: 0.88rem;
            padding: 0.72rem 0.7rem;
        }
        .auth-divider {
            margin: 1rem 0 0.8rem;
        }
        .auth-google-btn {
            font-size: 0.84rem;
            padding: 0.68rem 0.7rem;
        }
    }
</style>

<div class="auth-swap-page">
    <div class="auth-swap-wrapper">
        <div class="auth-swap-container <?php echo $isRegisterMode ? 'active' : ''; ?>" id="authSwapContainer">
            <div class="auth-form-box login">
                <div class="auth-form-content">
                    <div class="auth-avatar">
                        <span class="material-symbols-rounded text-5xl">account_circle</span>
                    </div>
                    <p class="auth-sub">Login to your ISDN account</p>

                    <?php if ($swapFlash): ?>
                        <div class="auth-flash <?php echo $swapFlash['type'] === 'success' ? 'auth-flash-success' : 'auth-flash-error'; ?>">
                            <?php echo htmlspecialchars((string) $swapFlash['message']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php">
                        <input type="hidden" name="action" value="login">
                        <div class="auth-field">
                            <span class="material-symbols-rounded auth-left-icon">mail</span>
                            <input type="email" name="email" required class="auth-input" placeholder="Enter your email">
                        </div>
                        <div class="auth-field">
                            <span class="material-symbols-rounded auth-left-icon">lock</span>
                            <input type="password" name="password" required id="swap_login_password" class="auth-input" placeholder="Enter your password">
                            <button type="button" class="auth-eye-btn" onclick="authSwapTogglePassword('swap_login_password','swap_login_eye')">
                                <span class="material-symbols-rounded" id="swap_login_eye">visibility</span>
                            </button>
                        </div>
                        <div class="auth-row">
                            <label class="flex items-center text-gray-600">
                                <input type="checkbox" class="rounded text-teal-600 focus:ring-teal-500 border-gray-300">
                                <span class="ml-2">Remember me</span>
                            </label>
                            <a href="<?php echo BASE_PATH; ?>/index.php?page=forgot-password">Forgot Password?</a>
                        </div>
                        <button type="submit" class="auth-main-btn">
                            <span class="material-symbols-rounded align-middle mr-1">login</span>
                            Login Now
                        </button>
                    </form>

                    <div class="auth-divider">
                        <div class="auth-divider-line"></div>
                        <div class="auth-divider-text">Or continue with</div>
                    </div>

                    <a href="<?php echo BASE_PATH; ?>/controllers/AuthController.php?action=google_login" class="auth-google-btn">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Continue with Google</span>
                    </a>
                    <p class="auth-mobile-nav">
                        Don't have an account?
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=register">Register here</a>
                    </p>

                </div>
            </div>

            <div class="auth-form-box register">
                <div class="auth-form-content">
                    <div class="auth-avatar">
                        <span class="material-symbols-rounded text-5xl">person_add</span>
                    </div>
                    <p class="auth-sub">Register as a Customer on ISDN</p>

                    <?php if ($swapFlash): ?>
                        <div class="auth-flash <?php echo $swapFlash['type'] === 'success' ? 'auth-flash-success' : 'auth-flash-error'; ?>">
                            <?php echo htmlspecialchars((string) $swapFlash['message']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php">
                        <input type="hidden" name="action" value="register">
                        <div class="auth-field">
                            <span class="material-symbols-rounded auth-left-icon">person</span>
                            <input type="text" name="username" required class="auth-input" placeholder="Full Name">
                        </div>
                        <div class="auth-field">
                            <span class="material-symbols-rounded auth-left-icon">mail</span>
                            <input type="email" name="email" required class="auth-input" placeholder="Email Address">
                        </div>
                        <div class="auth-field">
                            <span class="material-symbols-rounded auth-left-icon">lock</span>
                            <input type="password" name="password" required minlength="6" id="swap_register_password" class="auth-input" placeholder="Create Password">
                            <button type="button" class="auth-eye-btn" onclick="authSwapTogglePassword('swap_register_password','swap_register_eye')">
                                <span class="material-symbols-rounded" id="swap_register_eye">visibility</span>
                            </button>
                        </div>
                        <div class="auth-field">
                            <span class="material-symbols-rounded auth-left-icon">lock</span>
                            <input type="password" name="confirm_password" required minlength="6" id="swap_confirm_password" class="auth-input" placeholder="Confirm Password">
                            <button type="button" class="auth-eye-btn" onclick="authSwapTogglePassword('swap_confirm_password','swap_confirm_eye')">
                                <span class="material-symbols-rounded" id="swap_confirm_eye">visibility</span>
                            </button>
                        </div>
                        <button type="submit" class="auth-main-btn">
                            <span class="material-symbols-rounded align-middle mr-1">rocket_launch</span>
                            Create Customer Account
                        </button>
                    </form>

                    <div class="auth-divider">
                        <div class="auth-divider-line"></div>
                        <div class="auth-divider-text">Or register with</div>
                    </div>

                    <a href="<?php echo BASE_PATH; ?>/controllers/AuthController.php?action=google_login" class="auth-google-btn">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Continue with Google</span>
                    </a>
                    <p class="auth-mobile-nav">
                        Already have an account?
                        <a href="<?php echo BASE_PATH; ?>/index.php?page=login">Login here</a>
                    </p>

                </div>
            </div>

            <div class="auth-side-layer"></div>
            <div class="auth-side-panel left">
                <h3 class="auth-side-title">Hello, Welcome!</h3>
                <p class="auth-side-sub">Don't have an account?</p>
                <button type="button" class="auth-side-btn" data-auth-swap="register">Register</button>
            </div>
            <div class="auth-side-panel right">
                <h3 class="auth-side-title">Welcome Back!</h3>
                <button type="button" class="auth-side-btn" data-auth-swap="login">Login</button>
            </div>
        </div>

    </div>
</div>

<script>
    (function () {
        const container = document.getElementById('authSwapContainer');
        if (!container) return;

        function setMode(mode) {
            const isRegister = mode === 'register';
            container.classList.toggle('active', isRegister);
        }

        document.querySelectorAll('[data-auth-swap]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                setMode(btn.getAttribute('data-auth-swap'));
            });
        });
    })();

    function authSwapTogglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input || !icon) return;
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        icon.textContent = reveal ? 'visibility_off' : 'visibility';
    }
</script>

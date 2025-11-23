<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

if (current_user()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success_message = '';
$signup_email = '';

// Check for signup success message
if (isset($_GET['signup']) && $_GET['signup'] === 'success' && isset($_SESSION['signup_success'])) {
    $signup_email = $_SESSION['signup_email'] ?? '';
    $success_message = 'Registration successful! Your account has been created. Please sign in below.';
    unset($_SESSION['signup_success'], $_SESSION['signup_email']);
}

// Handle Google login (mock)
if (isset($_POST['google_login']) || isset($_GET['google_login'])) {
    // Simulate Google login - create a mock user or use existing
    try {
        $conn = get_db_connection();
        // Check if username column exists
        $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'username'");
        $hasUsername = $columnCheck->num_rows > 0;
        
        // Check if a Google user exists, if not create one
        if ($hasUsername) {
            $stmt = $conn->prepare('SELECT id, name, email, username FROM users WHERE email = ? LIMIT 1');
        } else {
            $stmt = $conn->prepare('SELECT id, name, email FROM users WHERE email = ? LIMIT 1');
        }
        $googleEmail = 'google.user@example.com';
        $stmt->bind_param('s', $googleEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            // Create a mock Google user
            $hash = password_hash('google123', PASSWORD_BCRYPT);
            $username = 'googleuser';
            $name = 'Google User';
            
            if ($hasUsername) {
                $stmt = $conn->prepare('INSERT INTO users (username, name, email, password_hash) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssss', $username, $name, $googleEmail, $hash);
            } else {
                $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $name, $googleEmail, $hash);
            }
            
            $stmt->execute();
            $userId = $stmt->insert_id;
            $user = [
                'id' => $userId,
                'name' => $name,
                'email' => $googleEmail,
            ];
            if ($hasUsername) {
                $user['username'] = $username;
            }
            $stmt->close();
        }

        login_user($conn, $user, false);
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $errors[] = 'Google login failed. Please try again.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['google_login'])) {
    $login = trim($_POST['login'] ?? ''); // Can be username or email
    $password = trim($_POST['password'] ?? '');

    // Validation: No field should be empty
    if ($login === '') {
        $errors[] = 'Email or username is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $conn = get_db_connection();
        
        // Check if username column exists
        $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'username'");
        $hasUsername = $columnCheck->num_rows > 0;
        
        // Try to find user by email or username (if column exists)
        if ($hasUsername) {
            $stmt = $conn->prepare('SELECT id, name, email, password_hash, username FROM users WHERE email = ? OR username = ? LIMIT 1');
            $stmt->bind_param('ss', $login, $login);
        } else {
            // Fallback: only search by email if username column doesn't exist
            $stmt = $conn->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $login);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $errors[] = 'The email or username you entered isn\'t connected to an account.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $errors[] = 'The password you entered is incorrect.';
        } else {
            login_user($conn, $user, false);
            $target = $_SESSION['redirect_to'] ?? 'index.php';
            unset($_SESSION['redirect_to']);
            header('Location: ' . $target);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | Facebook</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            background: #f0f2f5;
            color: #1c1e21;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 980px;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 60px;
        }
        .left-section {
            flex: 1;
            padding-right: 32px;
        }
        .logo {
            color: #1877f2;
            font-size: 56px;
            font-weight: bold;
            margin-bottom: 16px;
            letter-spacing: -1px;
        }
        .tagline {
            font-size: 28px;
            line-height: 32px;
            color: #1c1e21;
        }
        .right-section {
            flex: 0 0 432px;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 28px;
        }
        .form-group {
            margin-bottom: 12px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            font-size: 17px;
            border: 1px solid #dddfe2;
            border-radius: 6px;
            background: #fff;
            color: #1c1e21;
            transition: border-color 0.2s;
        }
        input:focus {
            outline: none;
            border-color: #1877f2;
        }
        input::placeholder {
            color: #90949c;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            font-size: 20px;
            font-weight: 600;
            color: #fff;
            background: #1877f2;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 6px;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: #166fe5;
        }
        .btn-google {
            width: 100%;
            padding: 11px;
            font-size: 17px;
            font-weight: 600;
            color: #1c1e21;
            background: #fff;
            border: 1px solid #dddfe2;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .btn-google:hover {
            background: #f5f6f7;
        }
        .errors {
            background: #fce8e6;
            border: 1px solid #f28b82;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            color: #c5221f;
        }
        .errors ul {
            margin: 0;
            padding-left: 20px;
        }
        .errors li {
            margin-bottom: 4px;
        }
        .success {
            background: #e7f3ff;
            border: 1px solid #1877f2;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            color: #1877f2;
        }
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
            color: #90949c;
            font-size: 12px;
        }
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            right: 0;
            height: 1px;
            background: #dadde1;
        }
        .divider span {
            background: #fff;
            padding: 0 16px;
            position: relative;
        }
        .forgot-link {
            text-align: center;
            margin-top: 16px;
        }
        .forgot-link a {
            color: #1877f2;
            text-decoration: none;
            font-size: 14px;
        }
        .forgot-link a:hover {
            text-decoration: underline;
        }
        .signup-link {
            text-align: center;
            font-size: 14px;
            margin-top: 28px;
        }
        .signup-link a {
            color: #1877f2;
            text-decoration: none;
            font-weight: 600;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                gap: 40px;
            }
            .left-section {
                text-align: center;
                padding-right: 0;
            }
            .logo {
                font-size: 48px;
            }
            .tagline {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <div class="logo">facebook</div>
            <div class="tagline">Connect with friends and the world around you on Facebook.</div>
        </div>
        <div class="right-section">
            <div class="card">
                <?php if (!empty($success_message)) : ?>
                    <div class="success">
                        <?php echo e($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)) : ?>
                    <div class="errors">
                        <ul>
                            <?php foreach ($errors as $error) : ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="form-group">
                        <input type="text" name="login" placeholder="Email address or username" value="<?php echo e($_POST['login'] ?? $signup_email); ?>" required autofocus>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn-primary">Log In</button>
                    <div class="forgot-link">
                        <a href="#">Forgotten password?</a>
                    </div>
                </form>

                <div class="divider"><span>or</span></div>

                <form method="post">
                    <input type="hidden" name="google_login" value="1">
                    <button type="submit" class="btn-google">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Log in with Google
                    </button>
                </form>
            </div>
            <div class="signup-link">
                <a href="signup.php">Create new account</a>
            </div>
        </div>
    </div>
</body>
</html>

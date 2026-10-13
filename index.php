<?php
declare(strict_types=1);
session_start();

/*
 * index.php — login page (frontend only, static demo accounts).
 * Accounts come from users.php, not a database. The PHP logic is:
 *   - escaping output
 *   - keeping the username filled in after a POST
 *   - checking the login against users.php and starting a session
 * Swap the users.php lookup for a real database query where marked "BACKEND HOOK".
 * Styles live in auth.css (keep it in the same folder).
 */

$appName   = 'Your App';
$username  = '';
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    // BACKEND HOOK: replace this users.php lookup with a database query
    // (and password_verify() against a hashed password). Also check a CSRF token.
    if ($username !== '' && $password !== '') {
        $users = require __DIR__ . '/users.php';
        foreach ($users as $user) {
            $nameOk = strtolower($user['username']) === strtolower($username)
                   || strtolower($user['email']) === strtolower($username);
            if ($nameOk && hash_equals($user['password'], $password)) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['username'];
                $_SESSION['user_name'] = $user['name'];
                header('Location: get-started.php');
                exit;
            }
        }
        $formError = 'Username or password is incorrect.';
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in – <?= e($appName) ?></title>
  <meta name="color-scheme" content="light dark">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="auth.css">
</head>
<body>
  <div class="shell">
    <div class="brand">
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false">
        <defs>
          <linearGradient id="mark" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
            <stop stop-color="#f43f8e"/>
            <stop offset="1" stop-color="#7c3aed"/>
          </linearGradient>
        </defs>
        <rect width="40" height="40" rx="12" fill="url(#mark)"/>
        <path d="M12 21l6 6 11-13" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span><?= e($appName) ?></span>
    </div>

    <main class="card">
      <h1>Welcome back</h1>
      <p class="lede">Sign in to continue to <?= e($appName) ?>.</p>

      <?php if ($formError !== ''): ?>
        <p class="form-error" role="alert"><?= e($formError) ?></p>
      <?php endif; ?>

      <form id="login-form" method="post" action="" novalidate>
        <div class="field" id="username-field">
          <label for="username">Username or email</label>
          <div class="input-wrap">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/></svg>
            <input
              type="text"
              id="username"
              name="username"
              value="<?= e($username) ?>"
              autocomplete="username"
              autocapitalize="none"
              autocorrect="off"
              spellcheck="false"
              inputmode="email"
              required
            >
          </div>
          <p class="error" id="username-error">Enter your username or email.</p>
        </div>

        <div class="field" id="password-field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="5" y="11" width="14" height="9" rx="2.5"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
            <input
              type="password"
              id="password"
              name="password"
              class="has-toggle"
              autocomplete="current-password"
              required
            >
            <!-- Revealed by JS; without JS the password simply stays masked -->
            <button type="button" class="toggle" id="toggle-password"
                    aria-controls="password" aria-label="Show password" hidden>Show</button>
          </div>
          <p class="error" id="password-error">Enter your password.</p>
        </div>

        <div class="row">
          <div class="check">
            <input type="checkbox" id="remember" name="remember" value="1">
            <label for="remember">Keep me signed in</label>
          </div>
          <a href="forgot-password.php">Forgot password?</a>
        </div>

        <button type="submit" class="btn">Sign in</button>
      </form>

      <p class="alt">New here? <a href="register.php">Create new account</a></p>
    </main>
  </div>

  <script>
    (function () {
      var form = document.getElementById('login-form');
      var fields = [
        { input: document.getElementById('username'), wrap: document.getElementById('username-field') },
        { input: document.getElementById('password'), wrap: document.getElementById('password-field') }
      ];

      /* Password show/hide */
      var pw = document.getElementById('password');
      var toggle = document.getElementById('toggle-password');
      toggle.hidden = false;
      toggle.addEventListener('click', function () {
        var showing = pw.type === 'text';
        pw.type = showing ? 'password' : 'text';
        toggle.textContent = showing ? 'Show' : 'Hide';
        toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
      });

      /* Validation: per-field messages, focus moves to the first problem */
      function setError(f, hasError) {
        f.wrap.classList.toggle('has-error', hasError);
        if (hasError) {
          f.input.setAttribute('aria-invalid', 'true');
          f.input.setAttribute('aria-describedby', f.input.id + '-error');
        } else {
          f.input.removeAttribute('aria-invalid');
          f.input.removeAttribute('aria-describedby');
        }
      }

      form.addEventListener('submit', function (event) {
        var firstInvalid = null;
        fields.forEach(function (f) {
          var empty = f.input.value.trim() === '';
          setError(f, empty);
          if (empty && !firstInvalid) { firstInvalid = f.input; }
        });
        if (firstInvalid) {
          event.preventDefault();
          firstInvalid.focus();
        }
      });

      fields.forEach(function (f) {
        f.input.addEventListener('input', function () {
          if (f.input.value.trim() !== '') { setError(f, false); }
        });
      });
    })();
  </script>
</body>
</html>

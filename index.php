<?php
declare(strict_types=1);

/*
 * login.php — frontend only.
 * No authentication happens here. The only PHP logic is:
 *   - escaping output
 *   - keeping the username filled in after a POST (so a failed sign-in
 *     doesn't make people retype it)
 * Wire up real auth where marked "BACKEND HOOK" below.
 */

$appName   = 'Your App';
$username  = '';
$formError = ''; // BACKEND HOOK: set to e.g. "Username or password is incorrect." after a failed check

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    // BACKEND HOOK: verify credentials and CSRF token here.
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

  <style>
    :root {
      --bg: #dce5ec;
      --surface: #ffffff;
      --ink: #0f1b2d;
      --muted: #44546a;
      --line: #6b7b90;
      --accent: #0b5fa5;
      --accent-hover: #084c85;
      --on-accent: #ffffff;
      --focus: #0b5fa5;
      --error: #b3261e;
      --error-bg: #fdecea;
      --radius: 10px;
    }

    @media (prefers-color-scheme: dark) {
      :root {
        --bg: #0c1522;
        --surface: #15233a;
        --ink: #eef3f9;
        --muted: #b4c1d3;
        --line: #8a9bb2;
        --accent: #7ab8f5;
        --accent-hover: #a2cdf8;
        --on-accent: #0c1522;
        --focus: #ffd166;
        --error: #ffb4ab;
        --error-bg: #3a1a1a;
      }
    }

    *, *::before, *::after { box-sizing: border-box; }

    html { -webkit-text-size-adjust: 100%; }

    body {
      margin: 0;
      min-height: 100vh;
      min-height: 100dvh;
      display: grid;
      place-items: center;
      padding: 1.5rem;
      background: var(--bg);
      color: var(--ink);
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      font-size: 1rem;
      line-height: 1.5;
    }

    .card {
      width: 100%;
      max-width: 26rem;
      padding: 2.25rem 2rem 2rem;
      background: var(--surface);
      border-radius: var(--radius);
      border-top: 6px solid var(--accent);
      box-shadow: 0 1px 2px rgba(15, 27, 45, .12), 0 12px 32px rgba(15, 27, 45, .12);
    }

    .brand {
      margin: 0 0 .25rem;
      font-size: 1rem;
      font-weight: 600;
      color: var(--muted);
    }

    h1 {
      margin: 0 0 1.75rem;
      font-family: "Iowan Old Style", "Palatino Linotype", Palatino, Georgia, serif;
      font-size: 2.25rem;
      line-height: 1.1;
      font-weight: 700;
      letter-spacing: -.01em;
    }

    .field { margin-bottom: 1.25rem; }

    label,
    .label {
      display: block;
      margin-bottom: .375rem;
      font-weight: 600;
    }

    /* 16px minimum keeps iOS Safari from zooming on focus */
    input[type="text"],
    input[type="password"] {
      display: block;
      width: 100%;
      min-height: 3rem;
      padding: .625rem .875rem;
      font: inherit;
      font-size: 1rem;
      color: var(--ink);
      background: var(--surface);
      border: 2px solid var(--line);
      border-radius: 8px;
    }

    input[aria-invalid="true"] { border-color: var(--error); }

    .password-wrap {
      display: flex;
      gap: .5rem;
      align-items: stretch;
    }
    .password-wrap input { flex: 1 1 auto; min-width: 0; }

    .toggle {
      flex: 0 0 auto;
      min-width: 4.5rem;
      min-height: 3rem;
      padding: 0 .875rem;
      font: inherit;
      font-weight: 600;
      color: var(--accent);
      background: transparent;
      border: 2px solid var(--line);
      border-radius: 8px;
      cursor: pointer;
    }
    .toggle:hover { border-color: var(--accent); }

    .error {
      display: none;
      margin: .375rem 0 0;
      color: var(--error);
      font-size: .9375rem;
      font-weight: 500;
    }
    .field.has-error .error { display: block; }

    .form-error {
      margin: 0 0 1.25rem;
      padding: .75rem .875rem;
      color: var(--error);
      background: var(--error-bg);
      border-left: 4px solid var(--error);
      border-radius: 4px;
      font-weight: 500;
    }

    .row {
      display: flex;
      flex-wrap: wrap;
      gap: .5rem 1rem;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
    }

    .check {
      display: inline-flex;
      align-items: center;
      gap: .625rem;
      min-height: 2.75rem;
      cursor: pointer;
    }
    .check label { margin: 0; font-weight: 400; cursor: pointer; }
    .check input {
      width: 1.375rem;
      height: 1.375rem;
      margin: 0;
      accent-color: var(--accent);
      cursor: pointer;
    }

    a {
      color: var(--accent);
      font-weight: 600;
      text-underline-offset: .2em;
    }
    a:hover { color: var(--accent-hover); }

    .row a,
    .alt a {
      display: inline-flex;
      align-items: center;
      min-height: 2.75rem;
    }

    .btn {
      display: block;
      width: 100%;
      min-height: 3.25rem;
      padding: .75rem 1rem;
      font: inherit;
      font-size: 1.0625rem;
      font-weight: 700;
      color: var(--on-accent);
      background: var(--accent);
      border: 2px solid var(--accent);
      border-radius: 8px;
      cursor: pointer;
    }
    .btn:hover { background: var(--accent-hover); border-color: var(--accent-hover); }
    .btn:active { transform: translateY(1px); }

    .alt {
      margin: 1.25rem 0 0;
      text-align: center;
      color: var(--muted);
    }

    /* Visible keyboard focus on every interactive element */
    :focus { outline: none; }
    :focus-visible {
      outline: 3px solid var(--focus);
      outline-offset: 2px;
    }
    input:focus-visible {
      border-color: var(--focus);
    }

    .visually-hidden {
      position: absolute;
      width: 1px; height: 1px;
      margin: -1px; padding: 0;
      overflow: hidden;
      clip: rect(0 0 0 0);
      white-space: nowrap;
      border: 0;
    }

    /* Small phones: use the full width, drop the floating-card chrome */
    @media (max-width: 480px) {
      body {
        place-items: stretch;
        padding: 0;
        background: var(--surface);
      }
      .card {
        max-width: none;
        padding: 1.75rem 1.25rem 2rem;
        border-radius: 0;
        box-shadow: none;
      }
      h1 { font-size: 2rem; }
    }

    @media (prefers-reduced-motion: reduce) {
      * { transition: none !important; animation: none !important; }
      .btn:active { transform: none; }
    }

    @media (forced-colors: active) {
      .card { border: 2px solid CanvasText; }
      .btn, .toggle { border: 2px solid ButtonText; }
    }
  </style>
</head>
<body>
  <main class="card">
    <p class="brand"><?= e($appName) ?></p>
    <h1>Sign in</h1>

    <?php if ($formError !== ''): ?>
      <p class="form-error" role="alert"><?= e($formError) ?></p>
    <?php endif; ?>

    <form id="login-form" method="post" action="" novalidate>
      <div class="field" id="username-field">
        <label for="username">Username or email</label>
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
        <p class="error" id="username-error">Enter your username or email.</p>
      </div>

      <div class="field" id="password-field">
        <label for="password">Password</label>
        <div class="password-wrap">
          <input
            type="password"
            id="password"
            name="password"
            autocomplete="current-password"
            required
          >
          <!-- Revealed by JS; without JS the password stays masked and nothing is broken -->
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
<?php
declare(strict_types=1);

/*
 * register.php — frontend only.
 * No account is created here. The only PHP logic is:
 *   - escaping output
 *   - keeping email and phone filled in after a POST
 * Passwords are never echoed back into the page.
 * Wire up real sign-up where marked "BACKEND HOOK" below.
 * Styles live in auth.css (keep it in the same folder).
 */

$appName   = 'Your App';
$email     = '';
$phone     = '';
$formError = ''; // BACKEND HOOK: e.g. "An account with this email already exists."

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    // BACKEND HOOK: validate again on the server, check duplicates,
    // hash the password (password_hash), verify CSRF token, create the user.
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
  <title>Create account – <?= e($appName) ?></title>
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
        <rect x="1" y="1" width="38" height="38" rx="12" fill="#fff" fill-opacity=".12" stroke="#fff" stroke-opacity=".35" stroke-width="2"/>
        <path d="M12 21l6 6 11-13" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span><?= e($appName) ?></span>
    </div>

    <main class="card">
      <h1>Create your account</h1>
      <p class="lede">It takes less than a minute.</p>

      <?php if ($formError !== ''): ?>
        <p class="form-error" role="alert"><?= e($formError) ?></p>
      <?php endif; ?>

      <form id="register-form" method="post" action="" novalidate>
        <div class="field" id="email-field">
          <label for="email">Email</label>
          <div class="input-wrap">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 6.5A2.5 2.5 0 0 1 5.5 4h13A2.5 2.5 0 0 1 21 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 17.5z"/><path d="m4 7 8 6 8-6"/></svg>
            <input
              type="email"
              id="email"
              name="email"
              value="<?= e($email) ?>"
              autocomplete="email"
              autocapitalize="none"
              autocorrect="off"
              spellcheck="false"
              required
            >
          </div>
          <p class="error" id="email-error"></p>
        </div>

        <div class="field" id="phone-field">
          <label for="phone">Phone number</label>
          <div class="input-wrap">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6.6 3h3l1.5 4.2-2 1.3a11 11 0 0 0 5.4 5.4l1.3-2 4.2 1.5v3A2.6 2.6 0 0 1 17.4 19C10.6 18.6 5.4 13.4 5 6.6A2.6 2.6 0 0 1 6.6 3z"/></svg>
            <input
              type="tel"
              id="phone"
              name="phone"
              value="<?= e($phone) ?>"
              autocomplete="tel"
              inputmode="tel"
              required
              aria-describedby="phone-hint"
            >
          </div>
          <p class="hint" id="phone-hint">Digits only. Add your country code if you have one, like +91.</p>
          <p class="error" id="phone-error"></p>
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
              autocomplete="new-password"
              minlength="8"
              required
              aria-describedby="password-hint"
            >
            <button type="button" class="toggle" data-target="password"
                    aria-controls="password" aria-label="Show password" hidden>Show</button>
          </div>
          <p class="hint" id="password-hint">Use at least 8 characters.</p>
          <p class="error" id="password-error"></p>
        </div>

        <div class="field" id="confirm-field">
          <label for="confirm">Confirm password</label>
          <div class="input-wrap">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="5" y="11" width="14" height="9" rx="2.5"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
            <input
              type="password"
              id="confirm"
              name="confirm"
              class="has-toggle"
              autocomplete="new-password"
              required
            >
            <button type="button" class="toggle" data-target="confirm"
                    aria-controls="confirm" aria-label="Show confirm password" hidden>Show</button>
          </div>
          <p class="error" id="confirm-error"></p>
        </div>

        <button type="submit" class="btn spaced">Create account</button>
      </form>

      <p class="alt">Already have an account? <a href="index.php">Sign in</a></p>
    </main>
  </div>

  <script>
    (function () {
      var form = document.getElementById('register-form');

      /* Show/hide buttons (revealed only when JS is running) */
      Array.prototype.forEach.call(document.querySelectorAll('.toggle'), function (btn) {
        var input = document.getElementById(btn.getAttribute('data-target'));
        var name = btn.getAttribute('aria-controls') === 'confirm' ? 'confirm password' : 'password';
        btn.hidden = false;
        btn.addEventListener('click', function () {
          var showing = input.type === 'text';
          input.type = showing ? 'password' : 'text';
          btn.textContent = showing ? 'Show' : 'Hide';
          btn.setAttribute('aria-label', (showing ? 'Show ' : 'Hide ') + name);
        });
      });

      /* Fields and their rules. Each check returns an error message or ''. */
      var fields = [
        {
          id: 'email',
          hint: '',
          check: function (v) {
            if (v === '') { return 'Enter your email address.'; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) { return 'Enter a valid email, like name@example.com.'; }
            return '';
          }
        },
        {
          id: 'phone',
          hint: 'phone-hint',
          check: function (v) {
            if (v === '') { return 'Enter your phone number.'; }
            var digits = v.replace(/[\s\-().]/g, '');
            if (!/^\+?\d{7,15}$/.test(digits)) { return 'Enter a valid phone number, 7 to 15 digits.'; }
            return '';
          }
        },
        {
          id: 'password',
          hint: 'password-hint',
          check: function (v) {
            if (v === '') { return 'Create a password.'; }
            if (v.length < 8) { return 'Your password needs at least 8 characters.'; }
            return '';
          }
        },
        {
          id: 'confirm',
          hint: '',
          check: function (v) {
            if (v === '') { return 'Re-enter your password.'; }
            if (v !== document.getElementById('password').value) { return 'Passwords do not match.'; }
            return '';
          }
        }
      ];

      fields.forEach(function (f) {
        f.input = document.getElementById(f.id);
        f.wrap = document.getElementById(f.id + '-field');
        f.errorEl = document.getElementById(f.id + '-error');
      });

      function validate(f) {
        var value = f.input.value;
        // Passwords keep their spaces; other fields are trimmed
        var message = f.check(f.id === 'password' || f.id === 'confirm' ? value : value.trim());
        var describedBy = (f.hint ? f.hint + ' ' : '') + (message ? f.id + '-error' : '');
        f.errorEl.textContent = message;
        f.wrap.classList.toggle('has-error', message !== '');
        if (message) { f.input.setAttribute('aria-invalid', 'true'); }
        else { f.input.removeAttribute('aria-invalid'); }
        if (describedBy.trim()) { f.input.setAttribute('aria-describedby', describedBy.trim()); }
        else { f.input.removeAttribute('aria-describedby'); }
        return message === '';
      }

      form.addEventListener('submit', function (event) {
        var firstInvalid = null;
        fields.forEach(function (f) {
          if (!validate(f) && !firstInvalid) { firstInvalid = f.input; }
        });
        if (firstInvalid) {
          event.preventDefault();
          firstInvalid.focus();
        }
      });

      /* Once a field has been flagged, re-check it as the person fixes it */
      fields.forEach(function (f) {
        f.input.addEventListener('input', function () {
          if (f.wrap.classList.contains('has-error')) { validate(f); }
        });
      });
      // Changing the password can fix or break the confirmation
      fields[2].input.addEventListener('input', function () {
        if (fields[3].wrap.classList.contains('has-error')) { validate(fields[3]); }
      });
    })();
  </script>
</body>
</html>

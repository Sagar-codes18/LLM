<?php
declare(strict_types=1);
session_start();

/*
 * get-started.php — frontend only.
 * Shown after sign-up or sign-in. The person picks what they are here to do
 * ("Looking for an employee" or "Looking for a job") and the matching tab
 * panel opens.
 *
 * Nothing is saved here. The only PHP logic is:
 *   - escaping output
 *   - whitelisting the ?tab= value
 *   - repopulating submitted values and showing a placeholder success message
 * Wire up real logic where marked "BACKEND HOOK" below.
 * Styles: auth.css (shared) then app.css (this page). Keep both in this folder.
 */

// Session check: people who are not signed in go back to the login page.
// (To open this page directly while developing, comment these lines out.)
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$appName  = 'Your App';
$userName = (string) ($_SESSION['user_name'] ?? '');
$greeting = $userName !== '' ? 'Welcome, ' . $userName : 'Welcome';

$tabs     = ['employer', 'seeker'];
$jobTypes = ['Full-time', 'Part-time', 'Contract', 'Internship'];

// Initially open tab: whitelisted, default none (shows the chooser only)
$requested = $_GET['tab'] ?? '';
$tab = (is_string($requested) && in_array($requested, $tabs, true)) ? $requested : '';

$employer = ['company' => '', 'title' => '', 'type' => '', 'location' => '', 'openings' => '', 'description' => ''];
$seeker   = ['name' => '', 'title' => '', 'type' => '', 'location' => '', 'experience' => '', 'bio' => ''];
$notice   = ['employer' => '', 'seeker' => ''];  // shown as an error box
$success  = ['employer' => '', 'seeker' => ''];  // shown as a success box

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post_value(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function all_filled(array $values): bool
{
    foreach ($values as $value) {
        if ($value === '') {
            return false;
        }
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = post_value('form');

    if ($submitted === 'employer') {
        $tab = 'employer';
        foreach (array_keys($employer) as $key) {
            $employer[$key] = post_value('e_' . $key);
        }
        // Light check for people without JavaScript. Real validation belongs on the server.
        if (
            !all_filled($employer)
            || !in_array($employer['type'], $jobTypes, true)
            || !ctype_digit($employer['openings'])
            || (int) $employer['openings'] < 1
        ) {
            $notice['employer'] = 'Please fill in every field, with a job type and at least 1 opening.';
        } else {
            // BACKEND HOOK: validate, sanitize, save (and check the CSRF token).
            $success['employer'] = 'Thanks! Your job post looks good. This is a preview, so nothing has been saved yet.';
        }
    } elseif ($submitted === 'seeker') {
        $tab = 'seeker';
        foreach (array_keys($seeker) as $key) {
            $seeker[$key] = post_value('s_' . $key);
        }
        if (
            !all_filled($seeker)
            || !in_array($seeker['type'], $jobTypes, true)
            || !ctype_digit($seeker['experience'])
            || (int) $seeker['experience'] > 60
        ) {
            $notice['seeker'] = 'Please fill in every field, with a job type and your years of experience.';
        } else {
            // BACKEND HOOK: validate, sanitize, save. Resume upload ($_FILES['s_resume'])
            // is ignored here: check type and size, then store it outside the web root.
            $success['seeker'] = 'Thanks! Your profile looks good. This is a preview, so nothing has been saved yet.';
        }
    }
}

/* ---------- Small rendering helpers ---------- */

function icon(string $name, string $class = 'icon'): string
{
    static $paths = [
        'user-plus' => '<circle cx="9" cy="8" r="4"/><path d="M2 20c0-3.6 3.1-6 7-6s7 2.4 7 6"/><path d="M19 8v6M16 11h6"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7"/><path d="M3 13h18"/>',
        'building'  => '<rect x="4" y="3" width="11" height="18" rx="2"/><path d="M15 9h3a2 2 0 0 1 2 2v10"/><path d="M8 7.5h3M8 11.5h3M8 15.5h3"/>',
        'clock'     => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'pin'       => '<path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 19.5c0-3.3 2.9-5.5 6.5-5.5s6.5 2.2 6.5 5.5"/><path d="M16 4.8a3.5 3.5 0 0 1 0 6.4M18 14.4c2 .7 3.5 2.3 3.5 5.1"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/>',
        'trending'  => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
        'chevron'   => '<path d="m6 9 6 6 6-6"/>',
        'check'     => '<circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.8 2.8L16 9.5"/>',
    ];

    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"'
        . ' aria-hidden="true" focusable="false">' . ($paths[$name] ?? '') . '</svg>';
}

function attrs(array $attrs): string
{
    $out = '';
    foreach ($attrs as $name => $value) {
        $out .= ' ' . $name . '="' . e((string) $value) . '"';
    }
    return $out;
}

/*
 * Prints one .field block. Keys:
 *   id, label, kind (text|number|select|textarea|file), icon, value, hint,
 *   placeholder + options (select), attrs (extra HTML attributes),
 *   required (default true), wide (spans both columns)
 */
function render_field(array $f): void
{
    $id       = (string) $f['id'];
    $kind     = (string) ($f['kind'] ?? 'text');
    $value    = (string) ($f['value'] ?? '');
    $hint     = (string) ($f['hint'] ?? '');
    $required = $f['required'] ?? true;
    $req      = $required ? ' required' : '';
    $extra    = attrs($f['attrs'] ?? []);
    $describe = $hint !== '' ? ' aria-describedby="' . e($id) . '-hint"' : '';
    $class    = 'field' . (!empty($f['wide']) ? ' field--full' : '');
    ?>
    <div class="<?= $class ?>" id="<?= e($id) ?>-field">
      <label for="<?= e($id) ?>"><?= e((string) $f['label']) ?><?php if (!$required): ?> <span class="optional">(optional)</span><?php endif; ?></label>
      <?php if ($kind === 'select'): ?>
        <div class="input-wrap select-wrap">
          <?= icon((string) $f['icon']) ?>
          <select id="<?= e($id) ?>" name="<?= e($id) ?>"<?= $req . $describe . $extra ?>>
            <option value=""><?= e((string) ($f['placeholder'] ?? 'Select')) ?></option>
            <?php foreach ($f['options'] as $option): ?>
              <option value="<?= e($option) ?>"<?= $value === $option ? ' selected' : '' ?>><?= e($option) ?></option>
            <?php endforeach; ?>
          </select>
          <?= icon('chevron', 'chev') ?>
        </div>
      <?php elseif ($kind === 'textarea'): ?>
        <textarea id="<?= e($id) ?>" name="<?= e($id) ?>" rows="5"<?= $req . $describe . $extra ?>><?= e($value) ?></textarea>
      <?php elseif ($kind === 'file'): ?>
        <input type="file" class="file-input" id="<?= e($id) ?>" name="<?= e($id) ?>"<?= $req . $describe . $extra ?>>
      <?php else: ?>
        <div class="input-wrap">
          <?= icon((string) $f['icon']) ?>
          <input type="<?= $kind === 'number' ? 'number' : 'text' ?>" id="<?= e($id) ?>" name="<?= e($id) ?>" value="<?= e($value) ?>"<?= $req . $describe . $extra ?>>
        </div>
      <?php endif; ?>
      <?php if ($hint !== ''): ?>
        <p class="hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></p>
      <?php endif; ?>
      <p class="error" id="<?= e($id) ?>-error"></p>
    </div>
    <?php
}

$employerFields = [
    ['id' => 'e_company', 'label' => 'Company name', 'icon' => 'building', 'value' => $employer['company'], 'wide' => true,
     'attrs' => ['autocomplete' => 'organization']],
    ['id' => 'e_title', 'label' => 'Job title you are hiring for', 'icon' => 'briefcase', 'value' => $employer['title'],
     'attrs' => ['autocomplete' => 'off']],
    ['id' => 'e_type', 'label' => 'Job type', 'kind' => 'select', 'icon' => 'clock', 'value' => $employer['type'],
     'placeholder' => 'Select a job type', 'options' => $jobTypes],
    ['id' => 'e_location', 'label' => 'Location', 'icon' => 'pin', 'value' => $employer['location'],
     'hint' => 'A city, or "Remote".', 'attrs' => ['autocomplete' => 'off']],
    ['id' => 'e_openings', 'label' => 'Number of openings', 'kind' => 'number', 'icon' => 'users', 'value' => $employer['openings'],
     'hint' => 'A whole number from 1 to 999.',
     'attrs' => ['min' => '1', 'max' => '999', 'step' => '1', 'inputmode' => 'numeric']],
    ['id' => 'e_description', 'label' => 'Short job description', 'kind' => 'textarea', 'value' => $employer['description'], 'wide' => true,
     'hint' => 'Up to 600 characters.', 'attrs' => ['maxlength' => '600']],
];

$seekerFields = [
    ['id' => 's_name', 'label' => 'Full name', 'icon' => 'user', 'value' => $seeker['name'], 'wide' => true,
     'attrs' => ['autocomplete' => 'name']],
    ['id' => 's_title', 'label' => 'Desired job title', 'icon' => 'briefcase', 'value' => $seeker['title'],
     'attrs' => ['autocomplete' => 'off']],
    ['id' => 's_type', 'label' => 'Preferred job type', 'kind' => 'select', 'icon' => 'clock', 'value' => $seeker['type'],
     'placeholder' => 'Select a job type', 'options' => $jobTypes],
    ['id' => 's_location', 'label' => 'Preferred location', 'icon' => 'pin', 'value' => $seeker['location'],
     'hint' => 'A city, or "Remote".', 'attrs' => ['autocomplete' => 'off']],
    ['id' => 's_experience', 'label' => 'Years of experience', 'kind' => 'number', 'icon' => 'trending', 'value' => $seeker['experience'],
     'hint' => 'Enter 0 if you are just starting out.',
     'attrs' => ['min' => '0', 'max' => '60', 'step' => '1', 'inputmode' => 'numeric']],
    ['id' => 's_bio', 'label' => 'Short bio or skills', 'kind' => 'textarea', 'value' => $seeker['bio'], 'wide' => true,
     'hint' => 'Up to 600 characters.', 'attrs' => ['maxlength' => '600']],
    ['id' => 's_resume', 'label' => 'Resume', 'kind' => 'file', 'required' => false, 'wide' => true,
     'hint' => 'PDF, DOC or DOCX, up to 5 MB.', 'attrs' => ['accept' => '.pdf,.doc,.docx']],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Get started – <?= e($appName) ?></title>
  <meta name="color-scheme" content="light dark">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="auth.css">
  <link rel="stylesheet" href="app.css">
</head>
<body>
  <div class="shell shell--wide">
    <div class="topbar">
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
      <a class="signout" href="logout.php">Sign out</a>
    </div>

    <main class="card" id="main">
      <p class="eyebrow"><?= e($greeting) ?></p>
      <h1>What brings you here?</h1>
      <p class="lede">Tell us what you are here to do and we will open the right form. You can change your choice any time.</p>

      <!-- Without JavaScript these are plain links and the server opens the right panel.
           With JavaScript they become tabs. -->
      <div class="chooser<?= $tab !== '' ? ' chooser--compact' : '' ?>" id="chooser" data-selected="<?= e($tab) ?>">
        <a class="choice<?= $tab === 'employer' ? ' is-selected' : '' ?>" id="tab-employer" href="get-started.php?tab=employer"
           data-tab="employer" aria-labelledby="tab-employer-title" aria-describedby="tab-employer-desc"<?= $tab === 'employer' ? ' aria-current="true"' : '' ?>>
          <span class="choice__icon"><?= icon('user-plus', '') ?></span>
          <span class="choice__text">
            <span class="choice__title" id="tab-employer-title">Looking for an employee</span>
            <span class="choice__desc" id="tab-employer-desc">Post an opening and reach people who are ready to work.</span>
          </span>
          <?= icon('check', 'choice__check') ?>
        </a>

        <a class="choice<?= $tab === 'seeker' ? ' is-selected' : '' ?>" id="tab-seeker" href="get-started.php?tab=seeker"
           data-tab="seeker" aria-labelledby="tab-seeker-title" aria-describedby="tab-seeker-desc"<?= $tab === 'seeker' ? ' aria-current="true"' : '' ?>>
          <span class="choice__icon"><?= icon('briefcase', '') ?></span>
          <span class="choice__text">
            <span class="choice__title" id="tab-seeker-title">Looking for a job</span>
            <span class="choice__desc" id="tab-seeker-desc">Create a profile and tell employers what you are after.</span>
          </span>
          <?= icon('check', 'choice__check') ?>
        </a>
      </div>

      <!-- Tab 1: employer -->
      <section class="panel" id="panel-employer" aria-labelledby="tab-employer-title"<?= $tab === 'employer' ? '' : ' hidden' ?>>
        <div class="panel__head">
          <h2 class="panel__title">Post a job</h2>
          <a class="change" href="get-started.php">Change my choice</a>
        </div>
        <p class="panel__lede">Tell us about the role. This is a preview, so nothing is saved yet.</p>

        <?php if ($success['employer'] !== ''): ?>
          <p class="form-success" role="status" tabindex="-1"><?= e($success['employer']) ?></p>
        <?php endif; ?>
        <?php if ($notice['employer'] !== ''): ?>
          <p class="form-error" role="alert"><?= e($notice['employer']) ?></p>
        <?php endif; ?>

        <form id="employer-form" method="post" action="get-started.php?tab=employer" novalidate>
          <input type="hidden" name="form" value="employer">
          <!-- BACKEND HOOK: add a CSRF token field here. -->
          <div class="form-grid">
            <?php foreach ($employerFields as $field) { render_field($field); } ?>
          </div>
          <button type="submit" class="btn spaced">Post this job</button>
        </form>
      </section>

      <!-- Tab 2: job seeker -->
      <section class="panel" id="panel-seeker" aria-labelledby="tab-seeker-title"<?= $tab === 'seeker' ? '' : ' hidden' ?>>
        <div class="panel__head">
          <h2 class="panel__title">Create your profile</h2>
          <a class="change" href="get-started.php">Change my choice</a>
        </div>
        <p class="panel__lede">Tell us what you are looking for. This is a preview, so nothing is saved yet.</p>

        <?php if ($success['seeker'] !== ''): ?>
          <p class="form-success" role="status" tabindex="-1"><?= e($success['seeker']) ?></p>
        <?php endif; ?>
        <?php if ($notice['seeker'] !== ''): ?>
          <p class="form-error" role="alert"><?= e($notice['seeker']) ?></p>
        <?php endif; ?>

        <form id="seeker-form" method="post" action="get-started.php?tab=seeker" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="form" value="seeker">
          <!-- BACKEND HOOK: add a CSRF token field here. -->
          <div class="form-grid">
            <?php foreach ($seekerFields as $field) { render_field($field); } ?>
          </div>
          <button type="submit" class="btn spaced">Create my profile</button>
        </form>
      </section>
    </main>
  </div>

  <script>
    (function () {
      /* ---------- Tabs ---------- */
      var chooser = document.getElementById('chooser');
      var tabs = Array.prototype.slice.call(chooser.querySelectorAll('[data-tab]'));
      var panels = {};
      var current = chooser.getAttribute('data-selected') || '';

      chooser.setAttribute('role', 'tablist');
      chooser.setAttribute('aria-label', 'What you are here to do');

      tabs.forEach(function (tab) {
        var key = tab.getAttribute('data-tab');
        var panel = document.getElementById('panel-' + key);
        panels[key] = panel;
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-controls', 'panel-' + key);
        tab.removeAttribute('aria-current');
        panel.setAttribute('role', 'tabpanel');
      });

      function updateUrl(key) {
        if (window.history && window.history.replaceState) {
          window.history.replaceState(null, '', key ? '?tab=' + key : window.location.pathname);
        }
      }

      function select(key, changeUrl) {
        current = key;
        tabs.forEach(function (tab) {
          var on = tab.getAttribute('data-tab') === key;
          tab.setAttribute('aria-selected', on ? 'true' : 'false');
          tab.classList.toggle('is-selected', on);
          // Roving tabindex: one tab stop. With nothing chosen, that is the first tab.
          tab.tabIndex = (on || (key === '' && tab === tabs[0])) ? 0 : -1;
        });
        Object.keys(panels).forEach(function (k) {
          panels[k].hidden = (k !== key);
        });
        chooser.classList.toggle('chooser--compact', key !== '');
        if (changeUrl) { updateUrl(key); }
      }

      tabs.forEach(function (tab) {
        tab.addEventListener('click', function (event) {
          event.preventDefault();
          select(tab.getAttribute('data-tab'), true);
        });
      });

      chooser.addEventListener('keydown', function (event) {
        var index = tabs.indexOf(document.activeElement);
        var next;
        if (index < 0) { return; }
        switch (event.key) {
          case 'ArrowRight': case 'Right': next = (index + 1) % tabs.length; break;
          case 'ArrowLeft': case 'Left': next = (index - 1 + tabs.length) % tabs.length; break;
          case 'Home': next = 0; break;
          case 'End': next = tabs.length - 1; break;
          case ' ': case 'Spacebar': next = index; break;
          default: return;
        }
        event.preventDefault();
        select(tabs[next].getAttribute('data-tab'), true);
        tabs[next].focus();
      });

      /* "Change my choice" returns to the chooser */
      Array.prototype.forEach.call(document.querySelectorAll('.change'), function (link) {
        link.addEventListener('click', function (event) {
          event.preventDefault();
          select('', true);
          tabs[0].focus();
        });
      });

      select(current, false);

      /* After a successful submit the server re-renders the page: move focus to the message */
      var ok = document.querySelector('.form-success');
      if (ok) { ok.focus(); }

      /* ---------- Form validation (same approach as register.php) ---------- */
      function required(message) {
        return function (v) { return v === '' ? message : ''; };
      }

      function wholeNumber(emptyMsg, badMsg, min, max) {
        return function (v) {
          if (v === '') { return emptyMsg; }
          if (!/^\d+$/.test(v)) { return badMsg; }
          var n = parseInt(v, 10);
          return (n < min || n > max) ? badMsg : '';
        };
      }

      function resumeCheck(v, input) {
        var file = input.files && input.files[0];
        if (!file) { return ''; }
        if (!/\.(pdf|docx?)$/i.test(file.name)) { return 'Choose a PDF, DOC or DOCX file.'; }
        if (file.size > 5 * 1024 * 1024) { return 'Your resume must be 5 MB or smaller.'; }
        return '';
      }

      function initForm(formId, defs) {
        var form = document.getElementById(formId);
        var fields = defs.map(function (def) {
          return {
            input: document.getElementById(def.id),
            wrap: document.getElementById(def.id + '-field'),
            errorEl: document.getElementById(def.id + '-error'),
            hintId: document.getElementById(def.id + '-hint') ? def.id + '-hint' : '',
            id: def.id,
            check: def.check
          };
        });

        function validate(f) {
          var message = f.check(f.input.value.trim(), f.input);
          var describedBy = ((f.hintId ? f.hintId + ' ' : '') + (message ? f.id + '-error' : '')).trim();
          f.errorEl.textContent = message;
          f.wrap.classList.toggle('has-error', message !== '');
          if (message) { f.input.setAttribute('aria-invalid', 'true'); }
          else { f.input.removeAttribute('aria-invalid'); }
          if (describedBy) { f.input.setAttribute('aria-describedby', describedBy); }
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
          function recheck() {
            if (f.wrap.classList.contains('has-error')) { validate(f); }
          }
          f.input.addEventListener('input', recheck);
          f.input.addEventListener('change', recheck);
        });
      }

      initForm('employer-form', [
        { id: 'e_company', check: required('Enter your company name.') },
        { id: 'e_title', check: required('Enter the job title you are hiring for.') },
        { id: 'e_type', check: required('Choose a job type.') },
        { id: 'e_location', check: required('Enter a location, or Remote.') },
        { id: 'e_openings', check: wholeNumber('Enter the number of openings.', 'Enter a whole number from 1 to 999.', 1, 999) },
        { id: 'e_description', check: required('Add a short job description.') }
      ]);

      initForm('seeker-form', [
        { id: 's_name', check: required('Enter your full name.') },
        { id: 's_title', check: required('Enter the job title you want.') },
        { id: 's_type', check: required('Choose a job type.') },
        { id: 's_location', check: required('Enter your preferred location, or Remote.') },
        { id: 's_experience', check: wholeNumber('Enter your years of experience.', 'Enter a whole number from 0 to 60.', 0, 60) },
        { id: 's_bio', check: required('Tell us a little about yourself or your skills.') },
        { id: 's_resume', check: resumeCheck }
      ]);
    })();
  </script>
</body>
</html>

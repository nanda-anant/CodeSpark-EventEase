/*!
 * CampusPass form validation
 * Add  data-validate  to a <form> and it gets:
 *  - inline, field-level error messages (live on blur / input)
 *  - password show/hide + optional strength meter (data-strength)
 *  - character counters for textareas with maxlength
 *  - double-submit protection
 * Extra field attributes:
 *  data-rule="name|mobile|strongpass|category|code"   built-in rules
 *  data-min-length / minlength / maxlength   length limits
 *  data-required-if="#otherField"          required only when another field has a value
 *  data-match="#otherField"                  must equal another field
 *  data-after="#otherField"                  must be later than another date/time field
 *  data-after-or-equal="#otherField"         same, but equal is allowed
 *  data-not-after-date="#dateField"          date part must not be after that date
 *  data-not-past  [data-original="value"]    date must not be in the past (unless unchanged)
 *  data-msg-required / -type / -short / -long / -min / -max / -pattern   custom messages
 * The server re-validates everything — this is only for fast feedback.
 */
(function () {
  'use strict';

  var RULES = {
    name: {
      test: function (v) { try { return new RegExp("^\\p{L}[\\p{L} .'-]*$", 'u').test(v); } catch (e) { return /^[A-Za-z][A-Za-z .'-]*$/.test(v); } },
      msg: 'Use letters, spaces, dots, apostrophes or hyphens only.'
    },
    mobile: {
      test: function (v) { return /^(\+91|91|0)?[6-9][0-9]{9}$/.test(v.replace(/[\s-]/g, '')); },
      msg: 'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.'
    },
    strongpass: {
      test: function (v) { return /[a-z]/.test(v) && /[A-Z]/.test(v) && /[0-9]/.test(v) && /[^A-Za-z0-9]/.test(v); },
      msg: 'Include an uppercase letter, a lowercase letter, a number and a special character.'
    },
    category: {
      test: function (v) { try { return new RegExp("^[\\p{L}\\p{N} &/,.-]+$", 'u').test(v); } catch (e) { return /^[A-Za-z0-9 &\/,.-]+$/.test(v); } },
      msg: 'Category can contain letters, numbers, spaces and & / , . - only.'
    },
    code: {
      test: function (v) { return /^[A-Za-z0-9-]{6,64}$/.test(v); },
      msg: 'Enter a valid entry code (letters, numbers and hyphens, 6–64 characters).'
    }
  };

  function $(sel, root) { return (root || document).querySelector(sel); }
  function labelText(el) {
    if (el.dataset.label) return el.dataset.label;
    var box = el.closest('.mb-3, .mb-4, [class*="col-"], .form-check') || el.parentNode;
    var lab = (el.id && $('label[for="' + el.id + '"]')) || (box && $('label', box));
    var t = lab ? lab.textContent.replace(/\*/g, '').replace(/\(.*?\)/g, '').trim() : (el.name || 'This field');
    return t || 'This field';
  }
  function isCheckable(el) { return el.type === 'checkbox' || el.type === 'radio'; }
  function skip(el) {
    return !el.name && !el.id || el.disabled || el.type === 'hidden' || el.type === 'submit' || el.type === 'button' || el.type === 'reset' || el.type === 'file';
  }

  /* ---------- message builder ---------- */
  function messageFor(el) {
    var d = el.dataset, label = labelText(el), v = el.value, tv = v.trim(), len = v.length;

    // required (also catches whitespace-only text)
    if (el.required) {
      if (isCheckable(el) ? !el.checked : tv === '') {
        return d.msgRequired || (isCheckable(el) ? 'Please tick this box to continue.' : (el.tagName === 'SELECT' ? 'Please select ' + label.toLowerCase() + '.' : label + ' is required.'));
      }
    }
    if (d.requiredIf && tv === '') {
      var ro = $(d.requiredIf);
      if (ro && ro.value !== '') return d.msgRequired || label + ' is required.';
    }
    if (isCheckable(el) || tv === '' && !el.required) {
      // optional + empty: nothing else to check (except match)
      if (!(el.dataset.match && tv !== '')) return '';
    }
    var minL = parseInt(el.getAttribute('minlength') || d.minLength || 0, 10);
    var maxL = parseInt(el.getAttribute('maxlength') || 0, 10);
    var checkVal = el.type === 'password' ? v : tv;
    if (minL && checkVal.length < minL) return d.msgShort || label + ' must be at least ' + minL + ' characters (now ' + checkVal.length + ').';
    if (maxL && len > maxL) return d.msgLong || label + ' must be at most ' + maxL + ' characters.';

    if (el.validity) {
      if (el.validity.typeMismatch) return d.msgType || (el.type === 'email' ? 'Enter a valid email address, e.g. name@college.edu.' : el.type === 'url' ? 'Enter a valid link starting with https://' : 'Enter a valid value.');
      if (el.validity.badInput) return 'Enter a valid value.';
      if (el.validity.rangeUnderflow) return d.msgMin || label + ' must be at least ' + el.min + '.';
      if (el.validity.rangeOverflow) return d.msgMax || label + ' must be at most ' + el.max + '.';
      if (el.validity.stepMismatch) return label + ' must be a whole number.';
      if (el.validity.patternMismatch) return d.msgPattern || el.title || 'Please match the requested format.';
    }
    if (el.type === 'email' && tv.length > 120) return 'Email must be at most 120 characters.';
    if (el.type === 'url' && tv !== '' && !/^https?:\/\//i.test(tv)) return d.msgType || 'Link must start with http:// or https://';

    var rule = RULES[d.rule];
    if (rule && !rule.test(checkVal)) return d.msgRule || rule.msg;

    if (d.match) {
      var other = $(d.match);
      if (other && other.value !== el.value) return d.msgMatch || 'Passwords do not match.';
    }
    if (d.after || d.afterOrEqual) {
      var o = $(d.after || d.afterOrEqual);
      if (o && o.value && el.value) {
        var bad = d.after ? el.value <= o.value : el.value < o.value;
        if (bad) return d.msgAfter || label + ' must be ' + (d.after ? 'after ' : 'on or after ') + labelText(o).toLowerCase() + '.';
      }
    }
    if (d.notAfterDate) {
      var dd = $(d.notAfterDate);
      if (dd && dd.value && el.value && el.value.slice(0, 10) > dd.value) return d.msgAfter || label + ' cannot be after the event date.';
    }
    if (d.notPast !== undefined && el.value && el.value !== d.original) {
      var t = new Date(), today = t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0') + '-' + String(t.getDate()).padStart(2, '0');
      if (el.value < today) return d.msgPast || label + ' cannot be in the past.';
    }
    return '';
  }

  /* ---------- UI helpers ---------- */
  function feedbackEl(el) {
    var anchor = el.closest('.password-wrap') || el.closest('.input-group') || el;
    var holder = isCheckable(el) ? el.closest('.form-check') : anchor.parentNode;
    var fb = isCheckable(el) ? $('.invalid-feedback', holder) : (anchor.nextElementSibling && anchor.nextElementSibling.classList.contains('invalid-feedback') ? anchor.nextElementSibling : null);
    if (!fb) {
      fb = document.createElement('div');
      fb.className = 'invalid-feedback';
      fb.id = (el.id || el.name) + '-error';
      if (isCheckable(el)) holder.appendChild(fb); else anchor.insertAdjacentElement('afterend', fb);
    }
    return fb;
  }
  function setState(el, msg) {
    var fb = feedbackEl(el), wrap = el.closest('.password-wrap');
    if (msg) {
      el.classList.add('is-invalid'); el.classList.remove('is-valid');
      fb.textContent = msg; fb.style.display = 'block';
      el.setAttribute('aria-invalid', 'true'); el.setAttribute('aria-describedby', fb.id);
      if (wrap) wrap.classList.add('has-error');
    } else {
      el.classList.remove('is-invalid');
      var hasVal = !isCheckable(el) && el.value.trim() !== '' && el.tagName !== 'SELECT';
      if (hasVal && el.type !== 'password' || (el.type === 'password' && el.value !== '' && el.dataset.match)) el.classList.add('is-valid'); else el.classList.remove('is-valid');
      fb.textContent = ''; fb.style.display = 'none';
      el.removeAttribute('aria-invalid');
      if (wrap) wrap.classList.remove('has-error');
    }
  }
  function checkField(el) { if (skip(el)) return true; var m = messageFor(el); setState(el, m); return !m; }
  function fieldsOf(form) { return Array.prototype.slice.call(form.querySelectorAll('input, select, textarea')); }
  function validateForm(form) {
    var first = null, ok = true;
    fieldsOf(form).forEach(function (el) { if (!checkField(el)) { ok = false; if (!first) first = el; } });
    if (first) {
      try { first.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
      first.focus({ preventScroll: true });
      form.classList.remove('shake'); void form.offsetWidth; form.classList.add('shake');
    }
    return ok;
  }

  /* ---------- enhancements ---------- */
  function addPasswordToggle(el) {
    if (el.closest('.password-wrap') || el.dataset.noToggle !== undefined) return;
    var wrap = document.createElement('div'); wrap.className = 'password-wrap';
    el.parentNode.insertBefore(wrap, el); wrap.appendChild(el);
    var b = document.createElement('button'); b.type = 'button'; b.className = 'password-toggle';
    b.setAttribute('aria-label', 'Show password'); b.innerHTML = '<i class="bi bi-eye"></i>';
    b.addEventListener('click', function () {
      var show = el.type === 'password'; el.type = show ? 'text' : 'password';
      b.innerHTML = '<i class="bi bi-eye' + (show ? '-slash' : '') + '"></i>';
      b.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
    wrap.appendChild(b);
  }
  function addStrength(el) {
    var meter = document.createElement('div'); meter.className = 'strength'; meter.innerHTML = '<span></span>';
    var lab = document.createElement('div'); lab.className = 'strength-label'; lab.textContent = 'Password strength';
    var anchor = el.closest('.password-wrap') || el; anchor.insertAdjacentElement('afterend', lab); anchor.insertAdjacentElement('afterend', meter);
    var bar = meter.firstChild, names = ['Too weak', 'Weak', 'Fair', 'Good', 'Strong'], cols = ['#e5484d', '#e5484d', '#ffb020', '#12b886', '#0b7d57'];
    function score(v) {
      var s = 0; if (v.length >= 8) s++; if (v.length >= 12) s++;
      if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++; if (/[0-9]/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
      return Math.min(4, Math.max(v ? 1 : 0, s - (v.length < 8 ? 1 : 0)));
    }
    el.addEventListener('input', function () {
      var s = el.value ? score(el.value) : -1;
      bar.style.width = s < 0 ? '0' : ((s + 1) * 20) + '%'; bar.style.background = s < 0 ? 'transparent' : cols[s];
      lab.textContent = s < 0 ? 'Password strength' : 'Strength: ' + names[s];
    });
  }
  function addCounter(el) {
    var max = parseInt(el.getAttribute('maxlength'), 10); if (!max) return;
    var c = document.createElement('div'); c.className = 'char-count'; el.insertAdjacentElement('afterend', c);
    function up() { c.textContent = el.value.length + ' / ' + max; }
    el.addEventListener('input', up); up();
  }

  function init(form) {
    if (form.dataset.vInit) return; form.dataset.vInit = '1';
    form.setAttribute('novalidate', 'novalidate');
    fieldsOf(form).forEach(function (el) {
      if (el.type === 'password') addPasswordToggle(el);
      if (el.dataset.strength !== undefined) addStrength(el);
      if (el.tagName === 'TEXTAREA') addCounter(el);
      if (el.dataset.rule === 'mobile') el.addEventListener('input', function () { var p = el.selectionStart; el.value = el.value.replace(/[^0-9+\s-]/g, ''); });
      if (el.dataset.rule === 'code') el.addEventListener('input', function () { el.value = el.value.toUpperCase().replace(/\s/g, ''); });
      el.addEventListener('blur', function () { if (!skip(el)) { el.dataset.touched = '1'; checkField(el); } });
      el.addEventListener(isCheckable(el) || el.tagName === 'SELECT' ? 'change' : 'input', function () {
        if (el.dataset.touched || el.classList.contains('is-invalid')) checkField(el);
        // re-check dependants
        fieldsOf(form).forEach(function (o) {
          var d = o.dataset;
          if ((d.requiredIf === '#' + el.id || d.match === '#' + el.id || d.after === '#' + el.id || d.afterOrEqual === '#' + el.id || d.notAfterDate === '#' + el.id) && (o.value !== '' || o.classList.contains('is-invalid'))) checkField(o);
        });
      });
    });
    form.addEventListener('submit', function (ev) {
      if (!validateForm(form)) { ev.preventDefault(); ev.stopImmediatePropagation(); return; }
      var btn = form.querySelector('button:not([type]), button[type="submit"]');
      if (btn && !form.hasAttribute('data-no-lock') && form.method && form.method.toLowerCase() === 'post' && !form.hasAttribute('onsubmit')) {
        setTimeout(function () { btn.disabled = true; btn.dataset.label = btn.innerHTML; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Please wait…'; }, 0);
      }
    }, true);
    form.addEventListener('reset', function () { setTimeout(function () { fieldsOf(form).forEach(function (el) { el.classList.remove('is-invalid', 'is-valid'); }); }, 0); });
  }

  window.addEventListener('pageshow', function () {
    document.querySelectorAll('form[data-validate] button[disabled]').forEach(function (b) { if (b.dataset.label) { b.disabled = false; b.innerHTML = b.dataset.label; } });
  });
  window.CPValidate = { validateForm: validateForm, checkField: checkField };
  document.addEventListener('DOMContentLoaded', function () { document.querySelectorAll('form[data-validate]').forEach(init); });
})();

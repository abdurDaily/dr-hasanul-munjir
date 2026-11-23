/* ==========================================================================
   admin.js — sidebar, toasts, confirmations, image previews, rich-text editor,
   staff slot picker. Requires Bootstrap bundle + SweetAlert2 (Quill only on editor pages).
   ========================================================================== */
(() => {
  'use strict';
  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  /* ---------- Sidebar (mobile) ---------- */
  document.querySelectorAll('[data-sidebar-toggle]').forEach((b) => b.addEventListener('click', () => document.body.classList.toggle('sidebar-open')));
  document.querySelector('.a-backdrop')?.addEventListener('click', () => document.body.classList.remove('sidebar-open'));

  /* ---------- Toasts (session flash → SweetAlert2 toast) ---------- */
  const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3500, timerProgressBar: true });
  const flash = document.body.dataset;
  if (flash.toastSuccess) Toast.fire({ icon: 'success', title: flash.toastSuccess });
  if (flash.toastError) Toast.fire({ icon: 'error', title: flash.toastError });
  window.adminToast = Toast;

  /* ---------- Confirm before destructive / important actions ---------- */
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.dataset.confirm && !form.dataset.confirmed) {
      e.preventDefault();
      const danger = form.dataset.confirmDanger !== undefined;
      const { isConfirmed } = await Swal.fire({
        title: form.dataset.confirm,
        text: form.dataset.confirmText || '',
        icon: danger ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: form.dataset.confirmButton || 'Yes, continue',
        confirmButtonColor: danger ? '#DC2626' : '#0E7C86',
        reverseButtons: true,
        focusCancel: danger,
      });
      if (isConfirmed) { form.dataset.confirmed = '1'; form.requestSubmit(e.submitter || undefined); }
      return;
    }
    // Loading state + double-submit protection
    const btn = e.submitter || form.querySelector('[type="submit"]');
    if (btn && !e.defaultPrevented) {
      btn.classList.add('is-loading');
      setTimeout(() => { btn.disabled = true; }, 0);
    }
  });

  /* ---------- Image preview + client-side size/type check ---------- */
  document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files[0];
      const img = document.querySelector(input.dataset.preview);
      if (!file) return;
      const maxKb = +input.dataset.maxKb || 5120;
      if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size > maxKb * 1024) {
        input.value = '';
        Toast.fire({ icon: 'error', title: `Please choose a JPG, PNG or WebP image under ${Math.round(maxKb / 1024)} MB.` });
        return;
      }
      if (img) img.src = URL.createObjectURL(file);
    });
  });

  /* ---------- Multi-image upload preview (e.g. treatment gallery) ---------- */
  document.querySelectorAll('input[type="file"][data-preview-list]').forEach((input) => {
    const list = document.querySelector(input.dataset.previewList);
    input.addEventListener('change', () => {
      const maxKb = +input.dataset.maxKb || 5120;
      const maxFiles = +input.dataset.maxFiles || 12;
      const files = [...input.files];
      const bad = files.find((f) => !/^image\/(jpeg|png|webp)$/.test(f.type) || f.size > maxKb * 1024);
      if (bad || files.length > maxFiles) {
        input.value = '';
        list.replaceChildren();
        Toast.fire({ icon: 'error', title: bad ? `“${bad.name}” is not a JPG/PNG/WebP under ${Math.round(maxKb / 1024)} MB.` : `Please choose at most ${maxFiles} images at a time.` });
        return;
      }
      list.replaceChildren(...files.map((f) => Object.assign(document.createElement('img'), {
        src: URL.createObjectURL(f), alt: '', className: 'thumb thumb--lg', title: f.name,
      })));
    });
  });

  // Fade gallery items marked for removal
  document.querySelectorAll('.js-gallery-delete').forEach((box) => box.addEventListener('change', () => {
    box.closest('.gallery-item').style.opacity = box.checked ? '.4' : '';
  }));

  /* ---------- Slug from title ---------- */
  document.querySelectorAll('[data-slug-from]').forEach((slug) => {
    const src = document.querySelector(slug.dataset.slugFrom);
    if (!src) return;
    let touched = slug.value !== '';
    slug.addEventListener('input', () => { touched = slug.value !== ''; });
    src.addEventListener('input', () => {
      if (!touched) slug.value = src.value.toLowerCase().normalize('NFKD').replace(/[^\w\s-]/g, '').trim().replace(/[\s_-]+/g, '-');
    });
  });

  /* ---------- Rich text editor (Quill) ---------- */
  document.querySelectorAll('textarea.js-editor').forEach((textarea) => {
    if (!window.Quill) return;
    const holder = document.createElement('div');
    holder.className = 'editor';
    textarea.after(holder);
    textarea.hidden = true;
    const quill = new Quill(holder, {
      theme: 'snow',
      placeholder: textarea.placeholder || 'Write here…',
      modules: {
        toolbar: {
          container: [[{ header: [2, 3, false] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'link', ...(textarea.dataset.uploadUrl ? ['image'] : [])], ['clean']],
          handlers: {
            image: () => {
              const input = document.createElement('input');
              input.type = 'file';
              input.accept = 'image/jpeg,image/png,image/webp';
              input.onchange = async () => {
                const body = new FormData();
                body.append('image', input.files[0]);
                const res = await fetch(textarea.dataset.uploadUrl, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) { Toast.fire({ icon: 'error', title: (data.errors && Object.values(data.errors)[0][0]) || 'Upload failed.' }); return; }
                const range = quill.getSelection(true);
                quill.insertEmbed(range.index, 'image', data.url, 'user');
              };
              input.click();
            },
          },
        },
      },
    });
    quill.clipboard.dangerouslyPasteHTML(textarea.value || ''); // server-sanitised content
    textarea.form.addEventListener('submit', () => {
      textarea.value = quill.getText().trim() === '' && !holder.querySelector('img') ? '' : quill.root.innerHTML;
    });
  });

  /* ---------- Staff slot picker (new appointment / reschedule) ---------- */
  document.querySelectorAll('[data-slot-picker]').forEach((wrap) => {
    const date = wrap.querySelector('input[type="date"]');
    const out = wrap.querySelector('.slot-pick');
    const name = wrap.dataset.name || 'time';
    const load = async () => {
      out.innerHTML = '<span class="text-muted">Loading…</span>';
      const url = new URL(wrap.dataset.slotPicker, location.origin);
      url.searchParams.set('date', date.value);
      if (wrap.dataset.ignore) url.searchParams.set('ignore', wrap.dataset.ignore);
      const res = await fetch(url, { headers: { Accept: 'application/json' } });
      const sessions = res.ok ? await res.json() : [];
      out.innerHTML = '';
      let any = false;
      sessions.forEach((s) => s.slots.forEach((slot) => {
        any = true;
        const id = `${name}-${slot.time.replace(':', '')}`;
        out.insertAdjacentHTML('beforeend', `<span><input type="radio" name="${name}" id="${id}" value="${slot.time}" ${slot.available ? '' : 'disabled'} required><label for="${id}" title="${s.name}">${slot.label}</label></span>`);
      }));
      if (!any) out.innerHTML = '<span class="text-muted">No chamber hours on this date.</span>';
    };
    date.addEventListener('change', load);
    if (date.value) load();
  });

  /* ---------- Bootstrap tooltips ---------- */
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
})();

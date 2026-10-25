document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.main-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('nav-open');
      toggle.setAttribute('aria-expanded', String(open));
      document.body.classList.toggle('menu-is-open', open);
    });
    nav.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
      nav.classList.remove('nav-open');
      toggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('menu-is-open');
    }));
  }

  const input = document.getElementById('imageInput');
  const preview = document.getElementById('imagePreview');
  if (input && preview) {
    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (!file) { preview.hidden = true; preview.removeAttribute('src'); return; }
      if (!file.type.startsWith('image/')) { alert('Pilih file gambar yang valid.'); input.value = ''; return; }
      if (file.size > 5 * 1024 * 1024) { alert('Ukuran gambar maksimal 5 MB.'); input.value = ''; return; }
      preview.src = URL.createObjectURL(file);
      preview.hidden = false;
    });
  }
});
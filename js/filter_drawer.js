document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.getElementById('openFilter');
  const closeBtn = document.getElementById('closeFilter');
  const drawer = document.getElementById('filterDrawer');
  const backdrop = document.getElementById('filterBackdrop');

  if (!openBtn) return;

  const open = () => {
    drawer.classList.remove('translate-x-full');
    backdrop.classList.remove('hidden');
  };

  const close = () => {
    drawer.classList.add('translate-x-full');
    backdrop.classList.add('hidden');
  };

  openBtn.addEventListener('click', open);
  closeBtn.addEventListener('click', close);
  backdrop.addEventListener('click', close);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') close();
  });

  drawer.addEventListener('submit', close);

  // Stepper
  document.querySelectorAll('.step-up').forEach(btn => {
    btn.onclick = () => {
      const input = btn.previousElementSibling;
      input.value = Number(input.value || 0) + 1;
    };
  });

  document.querySelectorAll('.step-down').forEach(btn => {
    btn.onclick = () => {
      const input = btn.nextElementSibling;
      input.value = Math.max(0, Number(input.value || 0) - 1);
    };
  });
});
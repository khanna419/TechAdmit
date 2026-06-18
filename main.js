// main.js — TechAdmit Engineering Portal

// Auto-hide alerts after 4 seconds
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => { el.style.transition = 'opacity 0.5s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 500); }, 4000);
});

// Multi-step form logic (apply.php)
const panels = document.querySelectorAll('.form-panel');
const stepTabs = document.querySelectorAll('.step-tab');

function showPanel(n) {
  panels.forEach((p, i) => p.style.display = (i === n ? 'block' : 'none'));
  stepTabs.forEach((t, i) => {
    t.classList.remove('active', 'done');
    if (i === n) t.classList.add('active');
    else if (i < n) t.classList.add('done');
  });
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

const nextBtns = document.querySelectorAll('.btn-next');
const prevBtns = document.querySelectorAll('.btn-prev');
let currentPanel = 0;

nextBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    if (currentPanel < panels.length - 1) {
      currentPanel++;
      showPanel(currentPanel);
    }
  });
});

prevBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    if (currentPanel > 0) {
      currentPanel--;
      showPanel(currentPanel);
    }
  });
});

if (panels.length) showPanel(0);

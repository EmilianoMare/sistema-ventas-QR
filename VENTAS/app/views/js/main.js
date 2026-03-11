/*Mostrar ocultar menu principal*/
let btn_menu=document.getElementById('btn-menu');
if(btn_menu){
  btn_menu.addEventListener("click", function(e){
    e.preventDefault();

    let navLateral=document.getElementById('navLateral');
    let pageContent=document.getElementById('pageContent');

    // On small screens (mobile), only toggle sidebar as an overlay
    const isMobile = window.innerWidth <= 768;

    if(navLateral && navLateral.classList.contains('navLateral-change')){
      navLateral.classList.remove('navLateral-change');
      if(!isMobile && pageContent) pageContent.classList.remove('pageContent-change');
    }else{
      if(navLateral) navLateral.classList.add('navLateral-change');
      if(!isMobile && pageContent) pageContent.classList.add('pageContent-change');
    }
  });
}

/*Mostrar y ocultar submenus*/
let btn_subMenu=document.querySelectorAll(".btn-subMenu");
if(btn_subMenu && btn_subMenu.length){
  btn_subMenu.forEach(subMenu => {
    subMenu.addEventListener("click", function(e){
      e.preventDefault();
      if(this.classList.contains('btn-subMenu-show')){
        this.classList.remove('btn-subMenu-show');
      }else{
        this.classList.add('btn-subMenu-show');
      }
    });
  });
}


document.addEventListener('DOMContentLoaded', () => {
  // Functions to open and close a modal
  function openModal($el) {
    $el.classList.add('is-active');
  }

  function closeModal($el) {
    $el.classList.remove('is-active');
  }

  function closeAllModals() {
    (document.querySelectorAll('.modal') || []).forEach(($modal) => {
      closeModal($modal);
    });
  }

  // Add a click event on buttons to open a specific modal
  (document.querySelectorAll('.js-modal-trigger') || []).forEach(($trigger) => {
    const modal = $trigger.dataset.target;
    const $target = document.getElementById(modal);

    $trigger.addEventListener('click', () => {
      openModal($target);
    });
  });

  // Add a click event on various child elements to close the parent modal
  (document.querySelectorAll('.modal-background, .modal-close, .modal-card-head .delete, .modal-card-foot .button') || []).forEach(($close) => {
    const $target = $close.closest('.modal');

    $close.addEventListener('click', () => {
      closeModal($target);
    });
  });

  // Add a keyboard event to close all modals
  document.addEventListener('keydown', (event) => {
    if (event.code === 'Escape') {
      closeAllModals();
    }
  });

  /* ---------- Sidebar compact/expand behaviour ---------- */
  const COLLAPSE_KEY = 'navLateralCollapsed';
  const btnCollapse = document.getElementById('btn-collapse-menu');
  const navLateralEl = document.getElementById('navLateral');
  const pageContentEl = document.getElementById('pageContent');
  const navOverlayEl = document.getElementById('navOverlay');

  // Ensure menu anchors have a data-title for tooltip in compact mode
  document.querySelectorAll('#navLateral .menu-principal li a').forEach(a => {
    if (!a.hasAttribute('data-title')){
      const label = a.querySelector('.navLateral-body-cr');
      if(label) a.setAttribute('data-title', label.textContent.trim());
    }
  });

  function setCollapsedState(collapsed){
    if(!navLateralEl || !pageContentEl || !btnCollapse) return;

    if(collapsed){
      navLateralEl.classList.add('navLateral-compact');
      pageContentEl.classList.add('pageContent-compact');
      btnCollapse.setAttribute('aria-pressed','true');
      btnCollapse.title = 'Expandir menú';
      const i = btnCollapse.querySelector('i');
      if(i){ i.classList.remove('fa-angle-double-left'); i.classList.add('fa-angle-double-right'); }
    }else{
      navLateralEl.classList.remove('navLateral-compact');
      pageContentEl.classList.remove('pageContent-compact');
      btnCollapse.setAttribute('aria-pressed','false');
      btnCollapse.title = 'Colapsar menú';
      const i = btnCollapse.querySelector('i');
      if(i){ i.classList.remove('fa-angle-double-right'); i.classList.add('fa-angle-double-left'); }
    }

    try{ localStorage.setItem(COLLAPSE_KEY, collapsed ? '1' : '0'); }catch(e){}
  }

  if(btnCollapse){
    btnCollapse.addEventListener('click', (e) => {
      e.preventDefault();
      const isCollapsed = navLateralEl.classList.contains('navLateral-compact');
      setCollapsedState(!isCollapsed);
    });
  }

  // Close overlay on click (mobile)
  if(navOverlayEl){
    navOverlayEl.addEventListener('click', () => {
      if(!navLateralEl || !pageContentEl) return;
      navLateralEl.classList.remove('navLateral-change');
      pageContentEl.classList.remove('pageContent-change');
    });
  }

  // Initialize state from localStorage
  try{
    const stored = localStorage.getItem(COLLAPSE_KEY);
    setCollapsedState(stored === '1');
  }catch(e){ /* ignore */ }

});

// Accessibility helpers: ensure form fields have id/name and labels are associated
document.addEventListener('DOMContentLoaded', () => {
  try{
    const usedIds = new Set();
    document.querySelectorAll('[id]').forEach(el=> usedIds.add(el.id));

    let autoCounter = 1;
    document.querySelectorAll('input, select, textarea').forEach(el => {
      // Skip inputs that are decorative
      if (el.type === 'hidden' || el.hasAttribute('data-no-autofill')) return;

      const name = el.getAttribute('name');
      let id = el.getAttribute('id');

      if (!id && name) {
        // prefer using name as id if free
        const candidate = name.replace(/[^a-zA-Z0-9_\-]/g, '_');
        if (!usedIds.has(candidate)) {
          id = candidate;
          el.id = id;
          usedIds.add(id);
        }
      }

      if (!id && !name) {
        // generate id and name
        do { id = 'autofield_' + autoCounter++; } while (usedIds.has(id));
        el.id = id;
        el.name = id;
        usedIds.add(id);
      }

      if (!name && id) {
        // set name to id to help form submission/autofill
        if (!el.hasAttribute('name')) el.setAttribute('name', id);
      }
    });

    // Fix labels: ensure label[for] points to existing id, or associate child inputs
    document.querySelectorAll('label').forEach(label => {
      const forId = label.getAttribute('for');
      if (forId) {
        if (!document.getElementById(forId)) {
          // try to find input by name matching forId
          const candidate = document.querySelector('[name="' + CSS.escape(forId) + '"]');
          if (candidate) {
            candidate.id = forId;
          } else {
            // remove invalid for to avoid confusion
            label.removeAttribute('for');
          }
        }
      } else {
        // if label wraps an input, ensure for/id
        const childInput = label.querySelector('input, select, textarea');
        if (childInput) {
          if (!childInput.id) {
            let newId;
            do { newId = 'autofield_' + autoCounter++; } while (usedIds.has(newId));
            childInput.id = newId; usedIds.add(newId);
          }
          label.setAttribute('for', childInput.id);
        }
      }
    });
  }catch(e){ console.warn('Accessibility helper failed', e); }
});
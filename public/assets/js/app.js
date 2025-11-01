(function(){
  const THEME_KEY = 'shaikhoology-theme';

  window.themeDetector = function(){
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const stored = localStorage.getItem(THEME_KEY);
    const initial = stored || (prefersDark ? 'dark' : 'light');
    const apply = (mode) => {
      document.documentElement.classList.toggle('dark', mode === 'dark');
      localStorage.setItem(THEME_KEY, mode);
    };
    apply(initial);
    return {
      theme: initial,
      toggleTheme(){
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        apply(this.theme);
      }
    };
  };

  window.appLayout = function(){
    return {
      sidebarCollapsed: false,
      toggleCommandPalette(open) {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: open ? 'command-palette' : null }));
      },
      toggleTheme(){
        window.dispatchEvent(new Event('toggle-theme'));
      }
    };
  };

  window.drawer = function(){
    return {
      open: false,
      init(){
        window.addEventListener('toggle-drawer', () => { this.open = !this.open; });
      },
      toggle(){ this.open = !this.open; }
    };
  };

  window.addEventListener('toggle-theme', () => {
    const root = document.documentElement;
    const isDark = root.classList.contains('dark');
    root.classList.toggle('dark', !isDark);
    localStorage.setItem(THEME_KEY, !isDark ? 'dark' : 'light');
  });

  window.tableEnhance = function(id){
    return {
      page: 0,
      pageSize: 10,
      summary: '',
      init(){ this.paginate(); },
      next(){ this.page++; this.paginate(); },
      prev(){ if (this.page > 0) { this.page--; this.paginate(); } },
      paginate(){
        const table = document.querySelector(`[data-table="${id}"] tbody`);
        if (!table) return;
        const rows = Array.from(table.querySelectorAll('tr'));
        const start = this.page * this.pageSize;
        const end = start + this.pageSize;
        rows.forEach((row, index) => {
          row.style.display = index >= start && index < end ? '' : 'none';
        });
        const total = rows.length;
        const shown = Math.min(end, total);
        this.summary = total ? `Showing ${start + 1} - ${shown} of ${total}` : 'No rows';
      }
    };
  };

  window.AppUI = {
    toast(tone, message){
      const container = document.getElementById('app-toasts');
      if (!container) return;
      const toast = document.createElement('div');
      toast.className = `toast-${tone} toast-enter rounded-2xl px-4 py-3 text-sm font-medium shadow-lg`;
      toast.textContent = message;
      container.appendChild(toast);
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-8px)';
        setTimeout(() => toast.remove(), 250);
      }, 3800);
    },

    debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }
  };

  function initCharts() {
    // Lazy load Chart.js only when needed
    if (!window.Chart && document.querySelector('[data-chart]')) {
      const script = document.createElement('script');
      script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js';
      script.onload = () => {
        document.querySelectorAll('[data-chart]').forEach((wrapper) => {
          if (wrapper.__chart) return;
          try {
            const config = JSON.parse(wrapper.dataset.chart || '{}');
            const canvas = wrapper.querySelector('canvas');
            if (canvas && config.type) {
              wrapper.__chart = new Chart(canvas.getContext('2d'), config);
            }
          } catch (err) {
            console.warn('Chart config error', err);
          }
        });
      };
      document.head.appendChild(script);
    } else if (window.Chart) {
      document.querySelectorAll('[data-chart]').forEach((wrapper) => {
        if (wrapper.__chart) return;
        try {
          const config = JSON.parse(wrapper.dataset.chart || '{}');
          const canvas = wrapper.querySelector('canvas');
          if (canvas && config.type) {
            wrapper.__chart = new Chart(canvas.getContext('2d'), config);
          }
        } catch (err) {
          console.warn('Chart config error', err);
        }
      });
    }
  }

  function initCsvButtons(){
    document.querySelectorAll('[data-csv-target]').forEach((button) => {
      button.addEventListener('click', () => {
        const target = button.getAttribute('data-csv-target');
        const table = document.querySelector(target);
        if (!table) return;
        const rows = Array.from(table.querySelectorAll('tr'));
        const csv = rows.map((row) => Array.from(row.children).map((cell) => '"' + cell.textContent.trim().replace(/"/g, '""') + '"').join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = button.getAttribute('data-csv-name') || 'export.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
      });
    });
  }

  function initSearchInputs(){
    document.querySelectorAll('input[type="search"]').forEach((input) => {
      const debouncedSearch = window.AppUI.debounce((value) => {
        // Trigger search - could dispatch event or submit form
        const form = input.closest('form');
        if (form) {
          const event = new Event('submit', { cancelable: true });
          form.dispatchEvent(event);
        }
      }, 300);

      input.addEventListener('input', (e) => {
        debouncedSearch(e.target.value);
      });
    });
  }

  function initLazyImages() {
    const images = document.querySelectorAll('img[loading="lazy"]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src || img.src;
          img.classList.remove('lazy');
          observer.unobserve(img);
        }
      });
    });
    images.forEach(img => imageObserver.observe(img));
  }

  function initPrefetch() {
    document.querySelectorAll('a[href]').forEach(link => {
      link.addEventListener('mouseenter', () => {
        const href = link.getAttribute('href');
        if (href && href.startsWith('/') && !href.includes('#')) {
          const linkElement = document.createElement('link');
          linkElement.rel = 'prefetch';
          linkElement.href = href;
          document.head.appendChild(linkElement);
        }
      }, { once: true });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    initCsvButtons();
    initSearchInputs();
    initLazyImages();
    initPrefetch();
    if (window.lucide && window.lucide.createIcons) {
      window.lucide.createIcons();
    }
  });
})();

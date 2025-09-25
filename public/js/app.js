document.addEventListener('DOMContentLoaded', () => {
  const meta = document.getElementById('dash-meta');
  if (!meta) return;

  const DATA_URL = meta.dataset.url;
  let currentMonth = meta.dataset.month;
  let currentDay   = meta.dataset.day;

  const wrapCal  = document.getElementById('calendar-wrap');
  const wrapList = document.getElementById('daylist-wrap');

  const fadeSwap = (el, html) => {
    el.style.opacity = 0.4;
    setTimeout(() => {
      el.innerHTML = html;
      el.style.opacity = 1;
      attachHandlers();
    }, 120);
  };

  const loadData = async (month, day) => {
    try {
      const url = new URL(DATA_URL, window.location.origin);
      if (month) url.searchParams.set('month', month);
      if (day)   url.searchParams.set('day', day);
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      if (!res.ok) throw new Error('Error cargando dashboard');
      const json = await res.json();
      fadeSwap(wrapCal,  json.calendar);
      fadeSwap(wrapList, json.day_list);
      currentMonth = month || currentMonth;
      currentDay   = day   || currentDay;
      history.replaceState({}, '', `?month=${currentMonth}&day=${currentDay}`);
    } catch (e) {
      console.error(e);
      alert('No se pudo actualizar el calendario.');
    }
  };

  const attachHandlers = () => {
    document.querySelectorAll('[data-nav]').forEach(btn => {
      btn.onclick = () => {
        const action = btn.dataset.nav;
        let m = new Date(currentMonth + '-01T00:00:00');
        if (action === 'prev') m.setMonth(m.getMonth() - 1);
        if (action === 'next') m.setMonth(m.getMonth() + 1);
        if (action === 'today') m = new Date();
        const month = `${m.getFullYear()}-${String(m.getMonth()+1).padStart(2,'0')}`;
        const day   = action === 'today'
          ? `${m.getFullYear()}-${String(m.getMonth()+1).padStart(2,'0')}-${String(m.getDate()).padStart(2,'0')}`
          : currentDay;
        loadData(month, day);
      };
    });

    document.querySelectorAll('#calendar-grid [data-day]').forEach(cell => {
      cell.onclick = () => {
        const day = cell.dataset.day;
        loadData(currentMonth, day);
      };
    });
  };

  attachHandlers();
});

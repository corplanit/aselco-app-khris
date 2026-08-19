

  <style>
    :root{
      --bg:#0b1220;
      --panel:#0f172a;
      --border: rgba(148,163,184,.20);
      --text:#e5e7eb;
      --muted:#94a3b8;
    }

    body { background: #f1f5f9; }
    .app-shell { max-width: 1100px; margin: 0 auto; }

    /* =========================
      Desktop Table Scroller
    ========================= */
    .table-scroller {
      min-height: 600px;
      max-height: 70vh;
      overflow: auto;
      position: relative;
      -webkit-overflow-scrolling: touch;
      overscroll-behavior: contain;
    }

    .table-scroller .lw-table {
      border-collapse: separate;
      border-spacing: 0;
      table-layout: fixed;
      width: 100%;
      min-width: 980px; /* horizontal swipe if needed */
    }

    .table-scroller .lw-table th:not(:last-child),
    .table-scroller .lw-table td:not(:last-child) {
      border-right: 1px solid rgb(229 231 235);
    }

    .table-scroller .lw-table thead th,
    .table-scroller .lw-table tbody td,
    .table-scroller .lw-table tfoot th,
    .table-scroller .lw-table tfoot td {
      border-bottom: 1px solid rgb(229 231 235);
    }

    .table-scroller .lw-table tbody tr.month-header>td,
    .table-scroller .lw-table tbody tr.month-row.bg-slate-50>td {
      border-top: 1px solid rgb(209 213 219);
      border-bottom: 1px solid rgb(209 213 219);
    }

    .table-scroller thead th {
      position: sticky;
      top: 0;
      z-index: 2;
      background: #f9fafb;
    }

    .table-scroller tfoot th,
    .table-scroller tfoot td {
      position: sticky;
      bottom: 0;
      z-index: 1;
      background: #f9fafb;
    }

    .table-scroller.scrolled-top thead th {
      box-shadow: 0 2px 6px rgba(15, 23, 42, .06);
    }

    .table-scroller.scrolled-bottom tfoot th,
    .table-scroller.scrolled-bottom tfoot td {
      box-shadow: 0 -2px 6px rgba(15, 23, 42, .06);
    }

    /* Mobile adjustments */
    @media (max-width: 640px) {
      .table-scroller {
        min-height: auto;
        max-height: 65vh;
        border-radius: 12px;
      }
      .table-scroller .lw-table {
        min-width: 920px;
      }
      .table-scroller .lw-table th,
      .table-scroller .lw-table td {
        padding: 8px 10px !important;
        font-size: 12px;
        white-space: nowrap;
      }
      .table-scroller .lw-table th:first-child,
      .table-scroller .lw-table td:first-child {
        width: 240px;
      }
    }

    /* =========================
      Mobile Modal
    ========================= */
    .modal-backdrop{
      position: fixed;
      inset: 0;
      background: rgba(2,6,23,.62);
      display: none;
      align-items: flex-end; /* bottom sheet feel on mobile */
      justify-content: center;
      z-index: 50;
      padding: 10px;
    }
    .modal-backdrop.show { display: flex; }

    .modal-sheet{
      width: 100%;
      max-width: 520px;
      background: #ffffff;
      border-radius: 18px;
      box-shadow: 0 20px 50px rgba(0,0,0,.35);
      overflow: hidden;
      transform: translateY(12px);
      opacity: 0;
      transition: all .18s ease;
    }
    .modal-backdrop.show .modal-sheet{
      transform: translateY(0);
      opacity: 1;
    }

    /* Hide desktop table container on mobile (we’ll toggle with JS too) */
    .only-desktop { display: block; }
    .only-mobile  { display: none; }
    @media (max-width: 640px) {
      .only-desktop { display: none; }
      .only-mobile  { display: block; }
    }
  </style>
</head>

<body class="min-h-screen">
  <div class="pt-4">

    <!-- =========================
      MOBILE LIST (cards)
    ========================= -->
    <div class="only-mobile mt-4">
      <div class="rounded-2xl bg-white border shadow-sm overflow-hidden">
        <div class="p-3 border-b flex items-center justify-between">
          <div>
            <div class="text-sm font-semibold">Ledger Entries</div>
            <div class="text-xs text-slate-500">Tap an item to view details</div>
          </div>
          <div class="lw-mobile-count text-xs text-slate-600"></div>
        </div>

        <div class="lw-mobile-list p-3 space-y-3">
          <div class="text-sm text-slate-500">No data yet. Enter account then Sync.</div>
        </div>
      </div>
    </div>

    <!-- =========================
      DESKTOP TABLE (your layout)
    ========================= -->
    <div class="only-desktop">
      <div class="table-scroller border rounded-2xl bg-white shadow-sm ledger-widget">
        <table class="lw-table min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr class="border-b">
              <th class="px-3 py-2 text-left">Description</th>
              <th class="px-3 py-2 text-left">Reference</th>
              <th class="px-3 py-2 text-right">Prev</th>
              <th class="px-3 py-2 text-right">Present</th>
              <th class="px-3 py-2 text-right">kWh</th>
              <th class="px-3 py-2 text-right">Demand kW</th>
              <th class="px-3 py-2 text-right">Debit</th>
              <th class="px-3 py-2 text-right">Credit</th>
              <th class="px-3 py-2 text-right">Balance</th>
              <th class="px-3 py-2 text-left">Bill Month</th>
            </tr>
          </thead>
          <tbody></tbody>
          <tfoot class="bg-gray-50">
            <tr class="border-t">
              <th colspan="6" class="px-3 py-2 text-right font-semibold">Totals</th>
              <th class="lw-tot-debit px-3 py-2 text-right font-semibold text-red-700"></th>
              <th class="lw-tot-credit px-3 py-2 text-right font-semibold text-green-700"></th>
              <th class="lw-closing px-3 py-2 text-right font-semibold"></th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
      <div class="lw-state text-xs text-slate-600 mt-2"></div>
    </div>
  </div>

  <!-- =========================
    MODAL (mobile only)
  ========================= -->
  <div class="modal-backdrop" id="rowModal" aria-hidden="true">
    <div class="modal-sheet">
      <div class="p-3 border-b flex items-center justify-between">
        <div>
          <div class="text-sm font-semibold modal-title">Entry Details</div>
          <div class="text-xs text-slate-500 modal-subtitle">—</div>
        </div>
        <button class="modal-close rounded-lg px-2 py-1 hover:bg-slate-100" aria-label="Close">
          ✕
        </button>
      </div>
      <div class="p-3">
        <div class="grid grid-cols-2 gap-2 text-sm">
          <div class="rounded-xl bg-slate-50 p-2">
            <div class="text-[11px] text-slate-500">Description</div>
            <div class="modal-desc font-medium break-words">—</div>
          </div>
          <div class="rounded-xl bg-slate-50 p-2">
            <div class="text-[11px] text-slate-500">Reference</div>
            <div class="modal-ref font-medium break-words">—</div>
          </div>

          <div class="rounded-xl bg-slate-50 p-2">
            <div class="text-[11px] text-slate-500">Prev / Present</div>
            <div class="modal-readings font-medium">—</div>
          </div>
          <div class="rounded-xl bg-slate-50 p-2">
            <div class="text-[11px] text-slate-500">kWh / Demand kW</div>
            <div class="modal-usage font-medium">—</div>
          </div>

          <div class="rounded-xl bg-red-50 p-2">
            <div class="text-[11px] text-red-600">Debit</div>
            <div class="modal-debit font-semibold text-red-700">—</div>
          </div>
          <div class="rounded-xl bg-green-50 p-2">
            <div class="text-[11px] text-green-700">Credit</div>
            <div class="modal-credit font-semibold text-green-700">—</div>
          </div>

          <div class="rounded-xl bg-slate-50 p-2 col-span-2">
            <div class="text-[11px] text-slate-500">Balance / Bill Month</div>
            <div class="modal-balance font-semibold">—</div>
          </div>
        </div>

        <div class="mt-3 flex gap-2">
          <button class="modal-close w-full rounded-xl bg-slate-900 text-white px-3 py-2 text-sm font-semibold">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Sticky header/footer shadows
    document.querySelectorAll('.table-scroller').forEach(scroller => {
      const onScroll = () => {
        const hasTop = scroller.scrollTop > 0;
        const hasBottom = scroller.scrollHeight - scroller.clientHeight - scroller.scrollTop > 1;
        scroller.classList.toggle('scrolled-top', hasTop);
        scroller.classList.toggle('scrolled-bottom', hasBottom);
      };
      scroller.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
    });
  </script>

  <script>
    // =========================
    // PWA service worker
    // =========================
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => navigator.serviceWorker.register('./sw.js'));
    }

    // Install prompt
    let deferredPrompt = null;
    const installBtn = document.getElementById('installBtn');

    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt = e;
      installBtn.classList.remove('hidden');
    });

    installBtn?.addEventListener('click', async () => {
      if (!deferredPrompt) return;
      deferredPrompt.prompt();
      await deferredPrompt.userChoice;
      deferredPrompt = null;
      installBtn.classList.add('hidden');
    });
  </script>

  <script>
    (() => {
      // =========================
      // Config
      // =========================
      // If CORS blocks direct calls, set API_URL_BASE to your own proxy endpoint.
      const API_URL_BASE = 'https://coop.eduisbotswave.com/AselcoCollection/GetConsumersLeger/';

      // =========================
      // Helpers
      // =========================
      const peso = (n) => isNaN(Number(n)) ? '0.00' : Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const fmtNum = (n) => (n == null || isNaN(Number(n))) ? '' : Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      const yyyymmToLabel = (yyyymm) => {
        if (!yyyymm || String(yyyymm).length < 6) return '—';
        const y = String(yyyymm).slice(0, 4), m = String(yyyymm).slice(4, 6);
        const d = new Date(+y, +m - 1, 1);
        return d.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
      };

      // Inline icons (tiny)
      const ico = {
        calendar: `<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>`,
        zap: `<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M13 2 L3 14h7l-1 8 10-12h-7l1-8z"/></svg>`,
        chevron: `<svg class="w-4 h-4 transition-transform" data-chevron viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>`,
      };

      // =========================
      // Fetch
      // =========================
      async function fetchLedger(acct) {
        const url = API_URL_BASE + encodeURIComponent(acct);
        const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const json = await resp.json();
        if (!Array.isArray(json) || !json.length) throw new Error('No data for that account.');
        return json.find(r => (r.consumerId || '').trim() === String(acct).trim()) || json[0];
      }

      // =========================
      // KPI computation
      // =========================
      function computeKpis(details) {
        const groups = new Map();
        let totalDebit = 0, totalCredit = 0;

        (details || []).forEach(d => {
          const bm = (d.billMonth && String(d.billMonth).length === 6) ? String(d.billMonth) : 'Unknown';
          if (!groups.has(bm)) groups.set(bm, []);
          groups.get(bm).push(d);
          totalDebit += Number(d.debit || 0);
          totalCredit += Number(d.credit || 0);
        });

        const keys = Array.from(groups.keys()).filter(k => k !== 'Unknown').sort((a, b) => Number(b) - Number(a));
        if (groups.has('Unknown')) keys.push('Unknown');
        const latestKey = keys[0] || 'Unknown';
        const latestDebit = (groups.get(latestKey) || []).reduce((s, r) => s + Number(r.debit || 0), 0);

        let pendingCount = 0;
        keys.forEach(k => {
          const rows = groups.get(k) || [];
          const dSum = rows.reduce((s, r) => s + Number(r.debit || 0), 0);
          const cSum = rows.reduce((s, r) => s + Number(r.credit || 0), 0);
          if (dSum > cSum) pendingCount++;
        });

        return { latestMonthDebit: latestDebit, outstanding: totalDebit - totalCredit, pendingCount };
      }

      // =========================
      // Grouping
      // =========================
      function parseDetailDate(d) {
        const md1 = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/.exec(d.descriptions || '');
        if (md1) return new Date(+md1[3], +md1[1] - 1, +md1[2]);
        const md2 = /^([A-Za-z]{3,})\s*(\d{4})$/.exec(d.descriptions || '');
        if (md2) {
          const map = { jan:1,feb:2,mar:3,apr:4,may:5,jun:6,jul:7,aug:8,sep:9,oct:10,nov:11,dec:12 };
          const m = map[md2[1].slice(0, 3).toLowerCase()];
          if (m) return new Date(+md2[2], m - 1, 1);
        }
        if (d.billMonth && String(d.billMonth).length === 6) {
          const y = +String(d.billMonth).slice(0, 4), m = +String(d.billMonth).slice(4, 6);
          return new Date(y, m - 1, 1);
        }
        return new Date(0);
      }

      function groupByMonth(details = []) {
        const map = new Map();
        details.forEach(r => {
          const k = r.billMonth || 'Unknown';
          if (!map.has(k)) map.set(k, []);
          map.get(k).push(r);
        });
        const keys = Array.from(map.keys()).filter(k => k !== 'Unknown').sort((a, b) => Number(b) - Number(a));
        if (map.has('Unknown')) keys.push('Unknown');
        keys.forEach(k => map.get(k).sort((a, b) => parseDetailDate(b) - parseDetailDate(a)));
        return keys.map(k => [k, map.get(k)]);
      }

      function monthlyTotals(items) {
        let d = 0, c = 0, lastBal = '';
        items.forEach(i => {
          d += +(i.debit || 0);
          c += +(i.credit || 0);
          if (i.balance != null) lastBal = fmtNum(i.balance);
        });
        return { d, c, lastBal };
      }

      // =========================
      // Desktop Table Render
      // =========================
      function buildMonthHeaderRow(monthKey, items) {
        const { d, c } = monthlyTotals(items);
        const tr = document.createElement('tr');
        tr.id = `m-${monthKey}`;
        tr.dataset.month = monthKey;
        tr.className = "month-header bg-slate-100";
        tr.innerHTML = `
          <td colspan="10" class="px-3 py-2">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <span class="inline-block w-1.5 h-6 rounded-full bg-blue-500"></span>
                <button type="button" class="flex items-center gap-2 font-semibold text-gray-800 group" aria-expanded="true">
                  ${ico.chevron}
                  <span class="inline-flex items-center gap-2">
                    <span class="text-blue-600">${ico.calendar}</span>
                    <span>${yyyymmToLabel(monthKey)}</span>
                  </span>
                </button>
              </div>
              <div class="flex items-center gap-2 text-xs">
                <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700">${items.length} entr${items.length>1?'ies':'y'}</span>
                <span class="px-2 py-0.5 rounded bg-red-50 text-red-700">Debit: <b>${fmtNum(d)}</b></span>
                <span class="px-2 py-0.5 rounded bg-green-50 text-green-700">Credit: <b>${fmtNum(c)}</b></span>
              </div>
            </div>
          </td>`;
        return tr;
      }

      function buildDetailRow(d, monthKey) {
        const tr = document.createElement('tr');
        tr.dataset.month = monthKey;
        tr.className = "month-row";
        tr.innerHTML = `
          <td class="px-3 py-2">${d.descriptions ?? ''}</td>
          <td class="px-3 py-2">${d.reference ?? ''}</td>
          <td class="px-3 py-2 text-right">${fmtNum(d.previousReading)}</td>
          <td class="px-3 py-2 text-right">${fmtNum(d.presentReading)}</td>
          <td class="px-3 py-2 text-right"><div class="inline-flex items-center gap-1">${ico.zap}<span>${fmtNum(d.kwhUsed)}</span></div></td>
          <td class="px-3 py-2 text-right">${fmtNum(d.demandKW)}</td>
          <td class="px-3 py-2 text-right text-red-700">${fmtNum(d.debit)}</td>
          <td class="px-3 py-2 text-right text-green-700">${fmtNum(d.credit)}</td>
          <td class="px-3 py-2 text-right font-medium">${fmtNum(d.balance)}</td>
          <td class="px-3 py-2">${d.billMonth ?? ''}</td>`;
        return tr;
      }

      function buildMonthFooterRow(monthKey, items) {
        const { d, c, lastBal } = monthlyTotals(items);
        const tr = document.createElement('tr');
        tr.dataset.month = monthKey;
        tr.className = "month-row bg-slate-50";
        tr.innerHTML = `
          <td colspan="6" class="px-3 py-2 text-right text-xs text-gray-600">Subtotal for ${yyyymmToLabel(monthKey)}</td>
          <td class="px-3 py-2 text-right font-semibold text-red-700">${fmtNum(d)}</td>
          <td class="px-3 py-2 text-right font-semibold text-green-700">${fmtNum(c)}</td>
          <td class="px-3 py-2 text-right font-semibold">${lastBal}</td>
          <td></td>`;
        return tr;
      }

      function renderLedgerTable(record) {
        const ledgerWidget = document.querySelector('.ledger-widget');
        if (!ledgerWidget) return;

        const tbody = ledgerWidget.querySelector('.lw-table tbody');
        const totD = ledgerWidget.querySelector('.lw-tot-debit');
        const totC = ledgerWidget.querySelector('.lw-tot-credit');
        const totB = ledgerWidget.querySelector('.lw-closing');

        tbody.innerHTML = '';
        totD.textContent = '';
        totC.textContent = '';
        totB.textContent = '';

        let gD = 0, gC = 0, gBal = 0;
        const groupsArr = groupByMonth(record.details || []);

        for (const [monthKey, items] of groupsArr) {
          tbody.appendChild(buildMonthHeaderRow(monthKey, items));
          for (const d of items) {
            gD += +(d.debit || 0);
            gC += +(d.credit || 0);
            if (d.balance != null) gBal = +d.balance;
            tbody.appendChild(buildDetailRow(d, monthKey));
          }
          tbody.appendChild(buildMonthFooterRow(monthKey, items));
        }

        totD.textContent = fmtNum(gD);
        totC.textContent = fmtNum(gC);
        totB.textContent = fmtNum(gBal);

        // Toggle month sections (simple)
        ledgerWidget.querySelectorAll('tr.month-header').forEach(header => {
          const k = header.dataset.month;
          const btn = header.querySelector('button');
          btn?.addEventListener('click', () => {
            const open = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', open ? 'false' : 'true');
            const chev = header.querySelector('[data-chevron]');
            if (chev) chev.style.transform = open ? 'rotate(-90deg)' : 'rotate(0deg)';
            ledgerWidget.querySelectorAll(`tr.month-row[data-month="${k}"]`).forEach(r => {
              r.style.display = open ? 'none' : '';
            });
          });
        });
      }

      // =========================
      // Mobile Cards + Modal
      // =========================
      const modal = document.getElementById('rowModal');
      const modalTitle = modal.querySelector('.modal-title');
      const modalSubtitle = modal.querySelector('.modal-subtitle');
      const modalDesc = modal.querySelector('.modal-desc');
      const modalRef = modal.querySelector('.modal-ref');
      const modalReadings = modal.querySelector('.modal-readings');
      const modalUsage = modal.querySelector('.modal-usage');
      const modalDebit = modal.querySelector('.modal-debit');
      const modalCredit = modal.querySelector('.modal-credit');
      const modalBalance = modal.querySelector('.modal-balance');

      function openModal(row) {
        modalTitle.textContent = 'Entry Details';
        modalSubtitle.textContent = `${yyyymmToLabel(row.billMonth || '')} • ${row.billMonth ?? '—'}`;

        modalDesc.textContent = row.descriptions ?? '—';
        modalRef.textContent = row.reference ?? '—';
        modalReadings.textContent = `${fmtNum(row.previousReading)} → ${fmtNum(row.presentReading)}`;
        modalUsage.textContent = `${fmtNum(row.kwhUsed)} kWh • ${fmtNum(row.demandKW)} kW`;
        modalDebit.textContent = fmtNum(row.debit) || '0.00';
        modalCredit.textContent = fmtNum(row.credit) || '0.00';
        modalBalance.textContent = `${fmtNum(row.balance)} • ${row.billMonth ?? '—'}`;

        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
      }

      function closeModal() {
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
      }

      modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
      });
      modal.querySelectorAll('.modal-close').forEach(btn => btn.addEventListener('click', closeModal));
      window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

      function renderMobileList(record) {
        const root = document.querySelector('.lw-mobile-list');
        const countEl = document.querySelector('.lw-mobile-count');
        if (!root) return;

        const groupsArr = groupByMonth(record.details || []);
        const totalRows = (record.details || []).length;
        if (countEl) countEl.textContent = `${totalRows} item${totalRows !== 1 ? 's' : ''}`;

        root.innerHTML = '';

        if (!groupsArr.length) {
          root.innerHTML = `<div class="text-sm text-slate-500">No details found.</div>`;
          return;
        }

        for (const [monthKey, items] of groupsArr) {
          const { d, c, lastBal } = monthlyTotals(items);

          const section = document.createElement('div');
          section.className = "rounded-2xl border bg-white overflow-hidden";

          section.innerHTML = `
            <div class="p-3 bg-slate-50 border-b">
              <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-blue-600 text-white">
                    ${ico.calendar}
                  </span>
                  <div>
                    <div class="text-sm font-semibold">${yyyymmToLabel(monthKey)}</div>
                    <div class="text-[11px] text-slate-500">${items.length} entr${items.length>1?'ies':'y'} • Bal: <span class="font-medium">${lastBal || '—'}</span></div>
                  </div>
                </div>
                <div class="text-right text-[11px]">
                  <div class="text-red-700">D: <span class="font-semibold">${fmtNum(d)}</span></div>
                  <div class="text-green-700">C: <span class="font-semibold">${fmtNum(c)}</span></div>
                </div>
              </div>
            </div>
            <div class="divide-y"></div>
          `;

          const list = section.querySelector('.divide-y');

          items.forEach(row => {
            const isDebit = +(row.debit || 0) > 0;
            const badgeCls = isDebit ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100';
            const badgeTxt = isDebit ? `Debit: ${fmtNum(row.debit)}` : `Credit: ${fmtNum(row.credit)}`;

            const card = document.createElement('button');
            card.type = 'button';
            card.className = "w-full text-left p-3 hover:bg-slate-50 active:bg-slate-100 transition";
            card.innerHTML = `
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="text-sm font-semibold truncate">${row.descriptions ?? ''}</div>
                  <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 ${badgeCls}">
                      ${badgeTxt}
                    </span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5">Ref: <b>${row.reference ?? '—'}</b></span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5">Bal: <b>${fmtNum(row.balance) || '—'}</b></span>
                  </div>
                </div>
                <div class="shrink-0 text-right text-[11px] text-slate-500">
                  <div>${fmtNum(row.kwhUsed) || '—'} kWh</div>
                  <div>${fmtNum(row.demandKW) || '—'} kW</div>
                </div>
              </div>
            `;
            card.addEventListener('click', () => openModal(row));
            list.appendChild(card);
          });

          root.appendChild(section);
        }
      }

      // =========================
      // Figures update
      // =========================
      function updateFiguresFromDetails(details) {
        const { latestMonthDebit, outstanding, pendingCount } = computeKpis(details);
        const el1 = document.querySelector('.kpi-total-billing');
        const el2 = document.querySelector('.kpi-total-pending-amount');
        const el3 = document.querySelector('.kpi-total-pending-count');

        if (el1) el1.textContent = peso(latestMonthDebit);
        if (el2) el2.textContent = peso(outstanding);
        if (el3) el3.textContent = String(pendingCount);
      }

      // =========================
      // Orchestrator
      // =========================
      const acctInput = document.querySelector('.lf-acct');
      const syncBtn = document.querySelector('.lf-sync');
      const stateEl = document.querySelector('.lf-state');
      const lwState = document.querySelector('.only-desktop .lw-state');

      async function runSync() {
        const acct = (acctInput?.value || '').trim();
        stateEl.textContent = '';
        if (lwState) lwState.textContent = '';

        if (!acct) {
          stateEl.textContent = 'Enter a ledger account number.';
          return;
        }

        try {
          stateEl.textContent = 'Loading…';
          const record = await fetchLedger(acct);

          // Summary
          document.querySelector('.lw-name').textContent = record.consumerName || '—';
          document.querySelector('.lw-address').textContent = record.consumerAddress || '—';
          document.querySelector('.lw-status').textContent = record.consumerStatus || '—';
          document.querySelector('.lw-summary')?.classList.remove('hidden');

          // KPIs
          updateFiguresFromDetails(record.details || []);

          // Desktop table
          renderLedgerTable(record);

          // Mobile list
          renderMobileList(record);

          stateEl.innerHTML = '<span class="text-emerald-300">✓ Synced from ledger.</span>';
          if (lwState) lwState.textContent = 'Synced.';
        } catch (err) {
          console.error(err);
          const msg = `Failed to sync: ${err.message}. If CORS, use a proxy endpoint.`;
          stateEl.innerHTML = `<span class="text-red-200">${msg}</span>`;
          if (lwState) lwState.textContent = msg;
        }
      }

      syncBtn.addEventListener('click', runSync);
      acctInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') runSync(); });
    })();
  </script>
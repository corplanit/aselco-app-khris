<!-- Put this once in your layout head -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<!-- after jQuery, before your custom <script> -->
<script src="https://unpkg.com/preline/dist/preline.js"></script>

<style>
    /* When the modal is shown (no .hidden), allow interactions */
    #chatDetails:not(.hidden),
    #chatDetails.hs-overlay-open {
        pointer-events: auto;
    }

    /* Keep Select2 on top */
    .select2-container,
    .select2-container .select2-dropdown {
        z-index: 10000 !important;
    }

    /* FullCalendar minimal Tailwind-like cosmetics (pure CSS, no @apply) */
    .fc {
        --fc-border-color: rgb(229 231 235);
        font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Inter, Arial;
    }

    .fc .fc-toolbar-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .fc .fc-button {
        background: #fff;
        border: 1px solid #d1d5db;
        color: #374151;
        font-size: .875rem;
        border-radius: .375rem;
        padding: .375rem .75rem;
        transition: background .15s ease;
    }

    .fc .fc-button:hover {
        background: #f9fafb;
    }

    .fc .fc-button.fc-button-primary {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .fc .fc-button.fc-button-primary:hover {
        background: #1d4ed8;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background: rgba(59, 130, 246, .08);
    }

    .fc .fc-timegrid-now-indicator-line {
        border-color: #ef4444;
    }

    .fc .fc-timegrid-now-indicator-arrow {
        border-top-color: #ef4444;
    }
</style>
<style>
    .swal2-container {
        z-index: 11050 !important;
    }

    /* above your modal overlay */
</style>
<div id="chatDetails" class="hs-overlay ti-modal pointer-events-none hidden mt-6">
    <div class="hs-overlay:mt-7 ti-modal-box mt-0 ease-out lg:!max-w-4xl lg:w-full m-3 lg:!mx-auto">
        <div class="max-h-full w-100 overflow-hidden ti-modal-content">
            <div class="ti-modal-header">
                <h6 class="modal-title text-[1rem] font-semibold">Chat Details</h6>
                <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" data-hs-overlay="#chatDetails">
                    <span class="sr-only">Close</span>
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <div class="bg-white shadow-md rounded-lg">
                <div class="ti-modal-body p-5 space-y-4">

                    <!-- Tabs Nav -->
                    <div
                        class="sm:flex bg-gray-100 hover:bg-gray-200 rounded-sm transition p-1 dark:bg-black/20 dark:hover:bg-black/20">
                        <nav class="sm:flex sm:space-x-2" role="tablist">
                            <a id="chat-tab-1" data-hs-tab="#chat-segment-1"
                                class="w-full sm:w-auto hs-tab-active:bg-white hs-tab-active:text-gray-700 py-2 px-3 inline-flex items-center gap-2 bg-transparent text-sm font-medium rounded-sm active"
                                aria-controls="chat-segment-1" aria-selected="true" href="javascript:void(0);">
                                <i class="bi bi-info-circle"></i> Overview
                            </a>

                            <a id="chat-tab-4" data-hs-tab="#chat-segment-4"
                                class="w-full sm:w-auto hs-tab-active:bg-white hs-tab-active:text-gray-700 py-2 px-3 inline-flex items-center gap-2 bg-transparent text-sm font-medium rounded-sm"
                                aria-controls="chat-segment-4" aria-selected="false" href="javascript:void(0);">
                                <i class="bi bi-folder"></i> Files
                            </a>

                            <a id="chat-tab-2" data-hs-tab="#chat-segment-2"
                                class="w-full sm:w-auto hs-tab-active:bg-white hs-tab-active:text-gray-700 py-2 px-3 inline-flex items-center gap-2 bg-transparent text-sm font-medium rounded-sm"
                                aria-controls="chat-segment-2" aria-selected="false" href="javascript:void(0);">
                                <i class="bi bi-people"></i> Members
                            </a>

                            <a id="chat-tab-5" data-hs-tab="#chat-segment-5"
                                class="w-full sm:w-auto hs-tab-active:bg-white hs-tab-active:text-gray-700 py-2 px-3 inline-flex items-center gap-2 bg-transparent text-sm font-medium rounded-sm"
                                aria-controls="chat-segment-5" aria-selected="false" href="javascript:void(0);">
                                <i class="bi bi-calendar-event"></i> Schedules
                            </a>

                            <a id="chat-tab-3" data-hs-tab="#chat-segment-3"
                                class="w-full sm:w-auto hs-tab-active:bg-white hs-tab-active:text-gray-700 py-2 px-3 inline-flex items-center gap-2 bg-transparent text-sm font-medium rounded-sm"
                                aria-controls="chat-segment-3" aria-selected="false" href="javascript:void(0);">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </nav>

                    </div>

                    <!-- Tabs Content -->
                    <div class="mt-3">
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const panels = document.querySelectorAll('#chatDetails [role="tabpanel"]');
                                panels.forEach(p => p.id === 'chat-segment-1' ? p.classList.remove('hidden') : p.classList.add(
                                    'hidden'));

                                // Make only Overview tab "active"
                                document.querySelectorAll('#chatDetails [role="tablist"] a[data-hs-tab]').forEach(a => {
                                    const isOverview = a.getAttribute('data-hs-tab') === '#chat-segment-1';
                                    a.classList.toggle('active', isOverview);
                                    a.setAttribute('aria-selected', isOverview ? 'true' : 'false');
                                });
                            });
                        </script>
                        <!-- Overview -->
                        <div id="chat-segment-1" role="tabpanel" aria-labelledby="chat-tab-1">
                            <div class="border p-4 space-y-6 rounded-lg bg-white">
                                <div class="flex items-start gap-4">
                                    <div class="relative shrink-0">
                                        <img id="chatPhotoImg" src="/user.png" alt="Chat Photo"
                                            class="w-24 h-24 rounded-2xl object-cover border border-gray-200">
                                        <label for="chatPhotoInput"
                                            class="absolute bottom-1 right-1 inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-[11px] cursor-pointer">
                                            <i class="bi bi-camera"></i>
                                        </label>
                                        <input id="chatPhotoInput" type="file" accept="image/*" class="hidden">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <label class="text-xs text-gray-500">Name</label>
                                            <span id="nameSaveHint"
                                                class="text-[11px] text-gray-400 hidden">Unsaved…</span>
                                        </div>
                                        <div class="mt-1 flex items-center gap-2">
                                            <input id="chatNameInput" type="text"
                                                class="flex-1 rounded-lg border-gray-200 focus:ring-2 focus:ring-blue-500"
                                                placeholder="Group name">
                                            <button id="btnSaveName"
                                                class="inline-flex items-center px-3 py-2 rounded-md bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                                disabled>
                                                <i class="bi bi-save mr-1"></i> Save
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="p-3 rounded-lg border border-gray-200">
                                        <div class="text-[11px] text-gray-500">Messages</div>
                                        <div id="messagesCount" class="text-base font-semibold">0</div>
                                    </div>
                                    <div class="p-3 rounded-lg border border-gray-200">
                                        <div class="text-[11px] text-gray-500">Files</div>
                                        <div id="filesCountOverview" class="text-base font-semibold">0</div>
                                    </div>
                                    <div class="p-3 rounded-lg border border-gray-200">
                                        <div class="text-[11px] text-gray-500">Last activity</div>
                                        <div id="lastActivity" class="text-base font-semibold">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Files -->
                        <div id="chat-segment-4" role="tabpanel" class="hidden" aria-labelledby="chat-tab-4">
                            <div class="border p-4 space-y-6 rounded-lg bg-white">
                                <div class="mt-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-700">Files</h3>
                                        <span id="filesCount" class="text-xs text-gray-500"></span>
                                    </div>
                                    <div id="id_attachments" class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedules -->
                        <div id="chat-segment-5" role="tabpanel" class="hidden" aria-labelledby="chat-tab-5">
                            <div class="border p-4 space-y-6 rounded-lg bg-white">
                                <div class="mt-2">
                                    <div id="calendarHost" class="rounded-lg border border-gray-200 p-2"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Members -->
                        <div id="chat-segment-2" class="hidden" role="tabpanel" aria-labelledby="chat-tab-2">
                            <div class="border p-4 rounded-lg bg-white space-y-3 overflow-visible">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="flex-1">
                                        <select id="addMemberSelect" class="w-full"></select>
                                    </div>
                                    <button id="btnAddMember"
                                        class="inline-flex items-center px-4 py-2 h-10 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                                        <i class="bi bi-person-plus mr-2"></i> Add Member
                                    </button>
                                </div>

                                <div class="flex items-center justify-between border-t pt-2">
                                    <h3 class="text-sm font-semibold text-gray-700">Participants</h3>
                                </div>

                                <div id="id_member"></div>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div id="chat-segment-3" class="hidden" role="tabpanel" aria-labelledby="chat-tab-3">
                            <div class="border p-4 rounded-lg bg-white space-y-4">
                                {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" class="ti-form-checkbox">
                                        <span>Mute notifications</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" class="ti-form-checkbox">
                                        <span>Pin conversation</span>
                                    </label>
                                </div> --}}
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                        class="px-3 py-1.5 rounded-md border border-gray-200 text-sm hover:bg-gray-50">
                                        Leave chat
                                    </button>
                                    <button type="button"
                                        class="px-3 py-1.5 rounded-md bg-red-50 text-red-600 border border-red-200 text-sm hover:bg-red-100">
                                        Delete chat
                                    </button>
                                    {{-- <button type="button"
                                        class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700">
                                        Save changes
                                    </button> --}}
                                </div>
                            </div>
                        </div>
                    </div><!-- /Tabs Content -->

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // ===== One-time config & selectors
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const membersBox = document.getElementById('id_member');
        const filesBox = document.getElementById('id_attachments');
        const filesCount = document.getElementById('filesCount');

        // Overview fields
        const $photoImg = document.getElementById('chatPhotoImg');
        const $nameInp = document.getElementById('chatNameInput');
        const $msgCnt = document.getElementById('messagesCount');
        const $fileCnt = document.getElementById('filesCountOverview');
        const $lastAct = document.getElementById('lastActivity');
        const btnSaveName = document.getElementById('btnSaveName');
        const nameSaveHint = document.getElementById('nameSaveHint');

        // Select2
        const $select = $('#addMemberSelect');
        const btnAdd = document.getElementById('btnAddMember');
        let select2Ready = false;
        let originalName = '';

        // Calendar
        const calendarHost = document.getElementById('calendarHost');
        let calendar = null;
        // let EVENTS = []; // local store
        let EVENTS = [{
                id: '1',
                title: 'Project Kickoff',
                start: '2025-08-19T09:00:00',
                end: '2025-08-19T10:30:00'
            },
            {
                id: '2',
                title: 'Design Review',
                start: '2025-08-26T13:00:00',
                end: '2025-08-26T14:00:00'
            },
            {
                id: '3',
                title: 'Marketing Meeting',
                start: '2025-08-07',
                allDay: true // all-day event
            },
            {
                id: '4',
                title: 'Client Demo',
                start: '2025-08-28T15:00:00',
                end: '2025-08-28T16:00:00'
            }
        ];

        const firstEventDate = EVENTS.length ?
            new Date(EVENTS.slice().sort((a, b) => new Date(a.start || a.date) - new Date(b.start || b.date))[0]
                .start) :
            new Date();

        function ep(convId) {
            return {
                list: `/chats/conversations/${convId}/participants`,
                add: `/chats/conversations/${convId}/participants/add`,
                remove: (uid) => `/chats/conversations/${convId}/participants/${uid}/remove`,
                files: `/chats/conversations/${convId}/attachments`,
                search: `/chats/users/search`,
                rename: `/chats/conversations/${convId}/rename`,
                // scheduler endpoints (optional to wire later)
                evList: `/chats/conversations/${convId}/schedule/events`,
                evCreate: `/chats/conversations/${convId}/schedule/events`,
                evUpdate: (id) => `/chats/conversations/${convId}/schedule/events/${id}`,
                evRemove: (id) => `/chats/conversations/${convId}/schedule/events/${id}`,
            };
        }

        // ===== Helpers
        function escapeHtml(s) {
            return String(s || '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [c]));
        }

        function bytes(n) {
            const u = ['B', 'KB', 'MB', 'GB', 'TB'];
            let i = 0,
                v = +n || 0;
            while (v >= 1024 && i < u.length - 1) {
                v /= 1024;
                i++
            }
            return `${v.toFixed(v<10&&i?1:0)} ${u[i]}`;
        }

        function ficon(m = '') {
            m = m || '';
            if (m.startsWith('image/')) return 'bi bi-image';
            if (m.includes('pdf')) return 'bi bi-filetype-pdf';
            if (m.includes('zip') || m.includes('compressed')) return 'bi bi-file-zip';
            if (m.includes('excel') || m.includes('spreadsheet')) return 'bi bi-filetype-xls';
            if (m.includes('word') || m.includes('msword')) return 'bi bi-filetype-doc';
            if (m.includes('text')) return 'bi bi-filetype-txt';
            return 'bi bi-file-earmark';
        }
        async function ask(title, text, confirmText) {
            if (window.Swal?.fire) {
                const r = await Swal.fire({
                    title,
                    text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: confirmText
                });
                return r.isConfirmed;
            }
            return confirm(`${title}\n${text}`);
        }

        function ok(msg) {
            window.Swal?.fire({
                icon: 'success',
                title: msg,
                timer: 1200,
                showConfirmButton: false
            }) || alert(msg);
        }

        function err(msg) {
            window.Swal?.fire({
                icon: 'error',
                title: msg
            }) || alert(msg);
        }

        function fillOverview(conv = {}) {
            if ($photoImg && conv.photo) $photoImg.src = conv.photo;
            if ($nameInp) {
                $nameInp.value = conv.name || '';
                originalName = $nameInp.value || '';
            }
            if ($msgCnt) $msgCnt.textContent = String(conv.messages_count ?? 0);
            if ($fileCnt) $fileCnt.textContent = String(conv.files_count ?? 0);
            if ($lastAct) $lastAct.textContent = conv.last_activity || '-';
            if (btnSaveName) btnSaveName.disabled = true;
            if (nameSaveHint) nameSaveHint.classList.add('hidden');
        }

        function refreshNameDirtyState() {
            if (!$nameInp || !btnSaveName || !nameSaveHint) return;
            const dirty = ($nameInp.value || '') !== originalName;
            btnSaveName.disabled = !dirty;
            nameSaveHint.classList.toggle('hidden', !dirty);
        }

        // ===== Select2 init (after modal/tab is visible)
        function initSelect2() {
            if (select2Ready || !$.fn.select2) return;
            $select.select2({
                placeholder: 'Search users…',
                width: '100%',
                dropdownParent: $('#chatDetails .ti-modal-content'),
                ajax: {
                    url: ep(window.ACTIVE_CONV_ID || '').search,
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => ({
                        results: (data || []).map(u => ({
                            id: u.id,
                            text: `${u.name} <${u.email}> #${u.id}`,
                            avatar: u.avatar
                        }))
                    })
                },
                templateResult: item => {
                    if (!item.id) return item.text;
                    return $(`
          <div class="flex items-center gap-2">
            <img src="${item.avatar || '/user.png'}" class="w-6 h-6 rounded-full border object-cover">
            <span>${item.text}</span>
          </div>
        `);
                },
                templateSelection: item => item.text || item.id,
                minimumInputLength: 1
            });
            select2Ready = true;
        }
        document.addEventListener('click', (e) => {
            const tgt = e.target.closest('[data-hs-overlay="#chatDetails"]');
            if (tgt) setTimeout(initSelect2, 150);
        });
        document.getElementById('chat-tab-2')?.addEventListener('click', () => {
            requestAnimationFrame(() => setTimeout(initSelect2, 0));
        });

        // ===== Loaders
        async function loadMembers(convId) {
            const res = await fetch(ep(convId).list, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    conv_id: convId
                })
            });
            if (!res.ok) throw new Error(`members ${res.status}`);
            const data = await res.json();

            const participants = Array.isArray(data) ? data : (data.participants || []);
            const conv = Array.isArray(data) ? {} : (data.conversation || {});
            membersBox.innerHTML = participants.length ? participants.map(m => `
      <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50" data-user-id="${m.id}">
        <div class="flex items-center gap-3">
          <img src="${m.avatar || '/user.png'}" class="w-9 h-9 rounded-full object-cover border border-gray-200" onerror="this.src='/user.png'">
          <div>
            <div class="text-sm font-medium">${escapeHtml(m.name)}</div>
            <div class="text-xs text-gray-500">${escapeHtml(m.role || '')}</div>
          </div>
        </div>
        <button class="btn-remove px-2 py-1 text-xs rounded-md border border-gray-200 hover:bg-gray-50">Remove</button>
      </div>
    `).join('') : `<div class="text-sm text-gray-500">No members found.</div>`;

            fillOverview(conv);
        }

        async function loadFiles(convId) {
            const res = await fetch(ep(convId).files, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error(`files ${res.status}`);
            const files = await res.json();
            if (filesCount) filesCount.textContent = files.length ? `${files.length} file(s)` : '';
            filesBox.innerHTML = files.length ? files.map(f => {
                const isImg = (f.mime || '').startsWith('image/');
                const thumb = f.thumb || f.url;
                return `
        <a href="${f.url}" target="_blank" class="group flex items-center gap-3 p-2 rounded-lg border border-gray-200 hover:bg-gray-50">
          ${isImg
            ? `<img src="${thumb}" class="w-10 h-10 rounded-md object-cover border border-gray-200" onerror="this.remove()">`
            : `<div class="w-10 h-10 grid place-items-center rounded-md bg-gray-100 border border-gray-200">
                 <i class="${ficon(f.mime)} text-gray-600"></i>
               </div>`
          }
          <div class="min-w-0">
            <div class="text-sm font-medium truncate">${escapeHtml(f.name || f.path || 'file')}</div>
            <div class="text-xs text-gray-500">${bytes(f.size)} • ${escapeHtml((f.mime||'').split('/').pop())}</div>
          </div>
        </a>
      `;
            }).join('') : `<div class="text-sm text-gray-500">No files yet.</div>`;
        }

        // --- Helpers: put these above initCalendar() ---
        function pad2(n) {
            return String(n).padStart(2, '0');
        }

        function fmtDateLabel(d) {
            const y = d.getFullYear(),
                m = pad2(d.getMonth() + 1),
                day = pad2(d.getDate());
            return `${y}-${m}-${day}`;
        }

        function fmtTimeHHMM(d) {
            return `${pad2(d.getHours())}:${pad2(d.getMinutes())}`;
        }

        function combineDateTime(dateOnly, timeStr) {
            const [hh, mm] = (timeStr || '09:00').split(':').map(v => parseInt(v, 10));
            const d = new Date(dateOnly);
            d.setHours(hh || 9, mm || 0, 0, 0);

            // Format as local "YYYY-MM-DDTHH:MM" (no Z)
            const pad = n => String(n).padStart(2, '0');
            const y = d.getFullYear();
            const m = pad(d.getMonth() + 1);
            const day = pad(d.getDate());
            const h = pad(d.getHours());
            const min = pad(d.getMinutes());
            return `${y}-${m}-${day}T${h}:${min}:00`; // local time
        }

        function combineDateTimeLocal(dateOnly, timeStr) {
            const [hh, mm] = (timeStr || '09:00').split(':').map(v => parseInt(v, 10));
            const d = new Date(dateOnly);
            d.setHours(hh || 9, mm || 0, 0, 0);
            const pad = n => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}:00`;
        }


        // ===== Calendar (FullCalendar) =====
        function initCalendar() {
            if (!calendarHost || calendar) return;

            calendar = new FullCalendar.Calendar(calendarHost, {
                height: 'auto',
                initialView: 'dayGridMonth',
                initialDate: firstEventDate, // 👈 go to the week of your events
                nowIndicator: true,
                navLinks: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEventRows: true,
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listWeek' // ,timeGridWeek,timeGridDay
                },
                buttonText: {
                    today: 'Today',
                    month: 'Calendar',
                    week: 'Week',
                    day: 'Day',
                    list: 'Schedules'
                },
                viewDidMount: () => {
                    // run after calendar is rendered
                    setTimeout(() => {
                        const calBtn = document.querySelector('.fc-dayGridMonth-button');
                        const listBtn = document.querySelector('.fc-listWeek-button');

                        if (calBtn && !calBtn.querySelector('i')) {
                            calBtn.innerHTML = `<i class="bi bi-calendar3 mr-1"></i> `;
                        }
                        if (listBtn && !listBtn.querySelector('i')) {
                            listBtn.innerHTML =
                                `<i class="bi bi-list-task mr-1"></i> `;
                        }
                    }, 0);
                },
                events: (info, success) => {
                    success(EVENTS);
                },
                eventOrder: (a, b) => new Date(b.start || b.date) - new Date(a.start || a.date),
                select: async (arg) => {
                    const startDate = new Date(arg.start);
                    const dateLabel = fmtDateLabel(startDate);
                    const defaultStart = arg.allDay ? '09:00' : fmtTimeHHMM(startDate);
                    const defaultEnd = arg.allDay ? '10:00' : fmtTimeHHMM(new Date(startDate.getTime() +
                        60 * 60000));

                    let values = null;
                    if (window.Swal?.fire) {
                        const {
                            value
                        } = await Swal.fire({
                            title: 'Create Schedule',
                            html: `
        <div class="space-y-3 text-left">
          <label class="block text-sm">Date</label>
          <input class="swal2-input" value="${dateLabel}" disabled>
          <label class="block text-sm">Title</label>
          <input id="evt-title" class="swal2-input" placeholder="Event title">
          <label class="block text-sm">From</label>
          <input id="evt-time-from" class="swal2-input" type="time" value="${defaultStart}">
          <label class="block text-sm">To</label>
          <input id="evt-time-to" class="swal2-input" type="time" value="${defaultEnd}">
        </div>`,
                            focusConfirm: false,
                            preConfirm: () => {
                                const t = (document.getElementById('evt-title').value || '')
                                    .trim();
                                const from = document.getElementById('evt-time-from').value;
                                const to = document.getElementById('evt-time-to').value;
                                if (!t || !from) {
                                    Swal.showValidationMessage(
                                        'Title and From time are required');
                                    return false;
                                }
                                if (to && to < from) {
                                    Swal.showValidationMessage(
                                        'End time must be after start time');
                                    return false;
                                }
                                return {
                                    title: t,
                                    from,
                                    to
                                };
                            },
                            confirmButtonText: 'Save',
                            showCancelButton: true
                        });
                        values = value || null;
                    } else {
                        const t = prompt(`Title for ${dateLabel}:`, '');
                        if (!t) {
                            calendar.unselect();
                            return;
                        }
                        const from = prompt('From time (HH:MM):', defaultStart) || defaultStart;
                        const to = prompt('To time (HH:MM):', defaultEnd) || defaultEnd;
                        values = {
                            title: t.trim(),
                            from,
                            to
                        };
                    }

                    calendar.unselect();
                    if (!values) return;

                    // Local (non-UTC) so 9pm stays 9pm
                    const startLocal = combineDateTimeLocal(startDate, values.from);
                    const endLocal = values.to ? combineDateTimeLocal(startDate, values.to) : null;

                    const newEvent = {
                        id: String(Date.now()),
                        title: values.title,
                        start: startLocal,
                        end: endLocal,
                        allDay: false
                    };

                    // --- How your events are loaded matters ---
                    // A) If your calendar was initialized with a STATIC array in options.events: just addEvent
                    // B) If you use a FUNCTION/URL source that returns EVENTS on each refetch: also push into EVENTS and refetch

                    // Always add to the UI immediately
                    calendar.addEvent(newEvent);

                    // If you have a function/URL events source, also keep that source in sync:
                    if (typeof EVENTS !== 'undefined' && Array.isArray(EVENTS)) {
                        EVENTS.push(newEvent);
                        // If you're currently in a list view, refresh so it appears right away there too
                        if (calendar.view?.type?.startsWith('list')) {
                            calendar
                        .refetchEvents(); // ensures function/URL source re-reads EVENTS (now with the new item)
                        } else {
                            // Not in a list view now—no need to switch; when you go to listWeek later, it will be there
                        }
                    } else {
                        // Static options.events path: ensure list views re-sort immediately if you're already on list*
                        if (calendar.view?.type?.startsWith('list')) {
                            calendar.rerenderEvents();
                        }
                    }
                },
                // ===== Edit Event =====
                eventClick: async (info) => {
                    const evt = info.event;
                    const dateLabel = fmtDateLabel(evt.start || new Date());
                    const defaultFrom = fmtTimeHHMM(evt.start || new Date());
                    const defaultTo = evt.end ? fmtTimeHHMM(evt.end) : '';

                    let values = null;
                    if (window.Swal?.fire) {
                        const {
                            value,
                            isDenied
                        } = await Swal.fire({
                            title: 'Edit Schedule',
                            html: `
        <div class="space-y-3 text-left">
          <label class="block text-sm">Date</label>
          <input class="swal2-input" value="${dateLabel}" disabled>
          <label class="block text-sm">Title</label>
          <input id="evt-title" class="swal2-input" value="${evt.title || ''}">
          <label class="block text-sm">From</label>
          <input id="evt-time-from" class="swal2-input" type="time" value="${defaultFrom}">
          <label class="block text-sm">To</label>
          <input id="evt-time-to" class="swal2-input" type="time" value="${defaultTo}">
        </div>`,
                            showDenyButton: true,
                            showCancelButton: true,
                            confirmButtonText: 'Save',
                            denyButtonText: 'Delete',
                            focusConfirm: false,
                            preConfirm: () => {
                                const t = (document.getElementById('evt-title').value || '')
                                    .trim();
                                const from = document.getElementById('evt-time-from').value;
                                const to = document.getElementById('evt-time-to').value;
                                if (!t || !from) {
                                    Swal.showValidationMessage(
                                        'Title and From time are required');
                                    return false;
                                }
                                if (to && to < from) {
                                    Swal.showValidationMessage(
                                        'End time must be after start time');
                                    return false;
                                }
                                return {
                                    title: t,
                                    from,
                                    to
                                };
                            }
                        });

                        if (isDenied) {
                            evt.remove();
                            EVENTS = EVENTS.filter(e => e.id !== evt.id);
                            return;
                        }
                        values = value || null;
                    }

                    if (!values) return;

                    const startISO = combineDateTime(evt.start || new Date(), values.from);
                    const endISO = values.to ? combineDateTime(evt.start || new Date(), values.to) :
                        null;

                    evt.setProp('title', values.title);
                    evt.setStart(startISO);
                    evt.setEnd(endISO);

                    const idx = EVENTS.findIndex(e => e.id === evt.id);
                    if (idx >= 0) EVENTS[idx] = {
                        ...EVENTS[idx],
                        title: values.title,
                        start: startISO,
                        end: endISO
                    };
                },
                eventClick: async (info) => {
                    const evt = info.event;

                    // current values
                    const start = evt.start || new Date();
                    const end = evt.end || null;
                    const dateLabel = fmtDateLabel(start);
                    const defaultFrom = fmtTimeHHMM(start);
                    const defaultTo = end ? fmtTimeHHMM(end) : '';

                    // SweetAlert UI (falls back to prompt/confirm if Swal missing)
                    let values = null;
                    if (window.Swal?.fire) {
                        const res = await Swal.fire({
                            title: 'Edit Schedule',
                            html: `
        <div class="space-y-3 text-left">
          <label class="block text-sm">Date</label>
          <input class="swal2-input" value="${dateLabel}" disabled>
          <label class="block text-sm">Title</label>
          <input id="evt-title" class="swal2-input" value="${evt.title || ''}" placeholder="Event title">
          <label class="block text-sm">From</label>
          <input id="evt-time-from" class="swal2-input" type="time" value="${defaultFrom}">
          <label class="block text-sm">To</label>
          <input id="evt-time-to" class="swal2-input" type="time" value="${defaultTo}">
        </div>`,
                            showDenyButton: true,
                            showCancelButton: true,
                            confirmButtonText: 'Save',
                            denyButtonText: 'Delete',
                            focusConfirm: false,
                            preConfirm: () => {
                                const t = (document.getElementById('evt-title').value || '')
                                    .trim();
                                const from = document.getElementById('evt-time-from').value;
                                const to = document.getElementById('evt-time-to').value;
                                if (!t || !from) {
                                    Swal.showValidationMessage(
                                        'Title and From time are required');
                                    return false;
                                }
                                if (to && to < from) {
                                    Swal.showValidationMessage(
                                        'End time must be after start time');
                                    return false;
                                }
                                return {
                                    title: t,
                                    from,
                                    to
                                };
                            }
                        });

                        if (res.isDenied) {
                            // Delete
                            evt.remove();
                            EVENTS = EVENTS.filter(e => e.id !== evt.id);
                            return;
                        }
                        values = res.value || null;
                    } else {
                        // Fallback (no Swal)
                        const edit = confirm('Edit this event? (Cancel = Delete)');
                        if (!edit) {
                            evt.remove();
                            EVENTS = EVENTS.filter(e => e.id !== evt.id);
                            return;
                        }
                        const t = prompt('Title:', evt.title || '');
                        if (t == null) return;
                        const f = prompt('From (HH:MM):', defaultFrom) || defaultFrom;
                        const to = prompt('To (HH:MM):', defaultTo) || defaultTo;
                        values = {
                            title: t.trim(),
                            from: f,
                            to
                        };
                    }

                    if (!values) return;

                    // Build new start/end on the same date
                    const startISO = combineDateTime(start, values.from);
                    const endISO = values.to ? combineDateTime(start, values.to) : null;

                    // Apply to calendar
                    evt.setProp('title', values.title);
                    evt.setStart(startISO);
                    evt.setEnd(endISO);

                    // Keep your local store in sync
                    const i = EVENTS.findIndex(e => e.id === evt.id);
                    if (i >= 0) EVENTS[i] = {
                        ...EVENTS[i],
                        title: values.title,
                        start: startISO,
                        end: endISO
                    };
                },
                eventDrop: (info) => {
                    const {
                        event
                    } = info;
                    const idx = EVENTS.findIndex(e => e.id === event.id);
                    if (idx >= 0) EVENTS[idx] = {
                        ...EVENTS[idx],
                        start: event.start?.toISOString(),
                        end: event.end?.toISOString() || null
                    };
                },
                eventResize: (info) => {
                    const {
                        event
                    } = info;
                    const idx = EVENTS.findIndex(e => e.id === event.id);
                    if (idx >= 0) EVENTS[idx] = {
                        ...EVENTS[idx],
                        start: event.start?.toISOString(),
                        end: event.end?.toISOString() || null
                    };
                }
            });

            calendar.render();
        }

        function addEventAndShow({
            title,
            date,
            from,
            to
        }) {
            // Build start/end
            const startISO = combineDateTimeLocal(date, from);
            const endISO = to ? combineDateTimeLocal(date, to) : null;

            // Create event object
            const ev = {
                id: String(Date.now()),
                title,
                start: startISO,
                end: endISO,
                allDay: false
            };

            // Update your store (if you keep one)
            EVENTS.push(ev);

            // Add to calendar
            const added = calendar.addEvent(ev);

            // If we're already in a list view, jump to the week containing the new event.
            // If not, switch to listWeek on insert (optional behavior).
            const startDate = new Date(startISO);
            const isList = calendar.view?.type?.startsWith('list');
            if (isList) {
                calendar.changeView(calendar.view.type, startDate);
            } else {
                // Optional: auto-switch to list view so the user “sees it in the list”
                calendar.changeView('listWeek', startDate);
            }

            // No refetch needed; addEvent respects eventOrder on render.
            return added;
        }


        // Render calendar when Schedules tab shown
        document.getElementById('chat-tab-5')?.addEventListener('click', () => {
            requestAnimationFrame(() => setTimeout(() => {
                initCalendar();
                calendar?.updateSize();
            }, 0));
        });

        // If opening directly on Schedules tab
        if (!document.getElementById('chat-segment-5')?.classList.contains('hidden')) {
            initCalendar();
            calendar?.updateSize();
        }

        // ===== Actions
        async function saveConversationName() {
            const convId = window.ACTIVE_CONV_ID;
            if (!convId) return err('No conversation selected.');
            const newName = ($nameInp?.value || '').trim();
            if (!newName) return err('Name cannot be empty.');

            try {
                const res = await fetch(ep(convId).rename, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        name: newName
                    })
                });
                if (!res.ok) {
                    const e = await res.json().catch(() => ({}));
                    throw new Error(e?.message || `Rename failed (${res.status})`);
                }
                originalName = newName;
                refreshNameDirtyState();
                ok('Name updated');
            } catch (e) {
                console.error(e);
                err(e.message || 'Failed to rename.');
            }
        }

        async function addSelectedMember() {
            const convId = window.ACTIVE_CONV_ID;
            const userId = $select.val();
            if (!convId || !userId) return;

            const okGo = await ask('Add member?', `User ID: ${userId}`, 'Add');
            if (!okGo) return;

            const res = await fetch(ep(convId).add, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    member_ids: [Number(userId)]
                })
            });
            if (!res.ok) {
                err(`Add failed (${res.status})`);
                return;
            }
            ok('Member added');
            $select.val(null).trigger('change');
            await trigger_member();
        }

        async function handleRemoveClick(e) {
            const btn = e.target.closest('.btn-remove');
            if (!btn) return;
            const row = btn.closest('[data-user-id]');
            const uid = row?.getAttribute('data-user-id');
            const convId = window.ACTIVE_CONV_ID;
            if (!uid || !convId) return;

            const okGo = await ask('Remove member?', `User ID: ${uid}`, 'Remove');
            if (!okGo) return;

            const res = await fetch(ep(convId).remove(uid), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                }
            });
            if (!res.ok) {
                err(`Remove failed (${res.status})`);
                return;
            }
            row.remove();
            ok('Member removed');
        }

        // ===== Public triggers
        async function trigger_member() {
            const convId = window.ACTIVE_CONV_ID;
            if (!convId) {
                membersBox.innerHTML = `<div class="text-sm text-gray-500">No conversation selected.</div>`;
                filesBox.innerHTML = '';
                if (filesCount) filesCount.textContent = '';
                return;
            }
            initSelect2();
            await loadMembers(convId);
            await loadFiles(convId);
        }

        // ===== Wire events
        if (btnAdd) btnAdd.addEventListener('click', addSelectedMember);
        if (membersBox) membersBox.addEventListener('click', handleRemoveClick);
        if ($nameInp) {
            $nameInp.addEventListener('input', refreshNameDirtyState);
            $nameInp.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (btnSaveName && !btnSaveName.disabled) saveConversationName();
                }
            });
        }
        if (btnSaveName) btnSaveName.addEventListener('click', saveConversationName);

        // Expose for your open flow
        window.trigger_member = trigger_member;
        window.openChatDetails = async function({
            id
        }) {
            window.ACTIVE_CONV_ID = id;
            await trigger_member();
            setTimeout(initSelect2, 150);
        };
    })();
</script>

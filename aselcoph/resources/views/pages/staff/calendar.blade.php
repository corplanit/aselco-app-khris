<x-app-layout>

    <x-slot name="title">Manage Calendar</x-slot>
    <x-slot name="url_1">{"link": "/consumer/list", "text": "Manage Consumer"}</x-slot>
    <x-slot name="active">Information</x-slot>
    <x-slot name="buttons">
        <button class="ti-btn ti-btn-light text-dark bg-white !border-0 btn-wave me-0" data-hs-overlay="#create-contact">
            <i class="bi bi-person-plus-fill me-1"></i>Register New Consumer
        </button>
    </x-slot>

    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-9">
            <div class="box">
                <div class="box-body">
                    <h1 class="text-2xl font-bold text-gray-800 mb-4">My Calendar</h1>
                    <div id="calendar" class="rounded-lg overflow-hidden"></div>
                </div>
            </div>
        </div>
        <div class="col-span-3">
            <div class="box">
                <div class="box-body">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3M16 7V3M4 11h16M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Today’s Schedule
                    </h2>
                    <ul id="today-events" class="space-y-3 text-sm text-gray-700">
                        <!-- JavaScript will inject events here -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @php
        date_default_timezone_set('Asia/Manila');
    @endphp


    <!-- FullCalendar CDN -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

    <!-- Modal Styles -->
    <style>
        #eventModal {
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -20%);
            z-index: 9999;
            background: white;
            border: 1px solid #ccc;
            padding: 20px;
            width: 500px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
        }

        .fc-event,
        .fc-event-time,
        .fc-event-title {
            color: #fff !important;
        }

        .fc-event {
            text-transform: uppercase;
        }
    </style>

    <!-- Custom Event Modal -->
    <div id="eventModal" class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl p-6 hidden">
        <form id="eventForm" class="space-y-5">
            <h2 class="text-xl font-bold text-dark"><strong>Create New Event</strong></h2>
            <hr>
            <!-- Title -->
            <div>
                <label for="modal-title" class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                <input type="text" id="modal-title" required placeholder="Event Title here.."
                    class="w-full rounded-md border bg-light  border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <!-- Description -->
            <div>
                <label for="modal-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="modal-description" rows="3" placeholder="Write Description here.."
                    class="w-full rounded-md border bg-light border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>


            <!-- Start and End Time -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="modal-start" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                    <input type="datetime-local" id="modal-start" required
                        class="w-full rounded-md border bg-light border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label for="modal-end" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                    <input type="datetime-local" id="modal-end"
                        class="w-full rounded-md border bg-light  border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
            </div>

            <!-- Meeting Link -->
            <div style="display: none">
                <label for="modal-link" class="block text-sm font-medium text-gray-700 mb-1">Meeting Link</label>
                <input type="url" id="modal-link" placeholder="https://"
                    class="w-full rounded-md border bg-light border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <!-- Appointment Checkbox -->
            <div class="flex items-center" style="display: none">
                <input type="checkbox" id="modal-appointment" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                <label for="modal-appointment" class="ml-2 block text-sm text-gray-700 mx-3">
                    Mark as Appointment (auto-generate link)
                </label>
            </div>




            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-2">
                <!-- Color and Font Color (font color hidden by default) -->
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <input type="color"
                            class="p-1 h-10 w-10 block bg-white dark:bg-bodybg border border-gray-200 cursor-pointer rounded-sm  disabled:opacity-50 disabled:pointer-events-none dark:bg-bgdark dark:border-white/10"
                            id="modal-color" value="#2ecc71" title="Choose your color">
                    </div>
                    <div class="hidden">
                        <label for="modal-font-color" class="block text-sm font-medium text-gray-700 mb-1">Font
                            Color</label>
                        <input type="color" id="modal-font-color" value="#ffffff"
                            class="w-full rounded-md border border-gray-300 px-2 py-2" />
                    </div>
                </div>

                <button type="button" id="cancelModal"
                    class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">Cancel</button>
                <button type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 transition">Save</button>
            </div>
        </form>
    </div>


    <script>
        document.getElementById('modal-appointment').addEventListener('change', function() {
            const linkInput = document.getElementById('modal-link');
            if (this.checked) {
                const randomCode = Math.random().toString(36).substring(2, 12);
                linkInput.value = `https://meet.hillbcs.com/${randomCode}`;
            } else {
                linkInput.value = '';
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const modal = document.getElementById('eventModal');
            const form = document.getElementById('eventForm');
            const cancelBtn = document.getElementById('cancelModal');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

            const modalFields = {
                title: document.getElementById('modal-title'),
                description: document.getElementById('modal-description'),
                color: document.getElementById('modal-color'),
                fontColor: document.getElementById('modal-font-color'),
                start: document.getElementById('modal-start'),
                end: document.getElementById('modal-end'),
                link: document.getElementById('modal-link'),
                appointment: document.getElementById('modal-appointment'),
            };

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                editable: true,
                selectable: true,
                selectMirror: true,
                nowIndicator: true,
                slotMinTime: "06:00:00",
                slotMaxTime: "22:00:00",
                eventDisplay: 'block',
                dayMaxEventRows: true,
                timeZone: 'local',

                eventDidMount: function(info) {
                    const titleEl = info.el.querySelector('.fc-event-title');
                    const timeEl = info.el.querySelector('.fc-event-time');

                    if (timeEl && info.event.start) {
                        const localTime = new Date(info.event.start);
                        const formatted = localTime.toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true,
                        });
                        timeEl.innerText = formatted;
                    }

                    if (titleEl && info.event.extendedProps.text_color) {
                        titleEl.style.color = info.event.extendedProps.text_color;
                    }

                    if (titleEl) {
                        titleEl.style.display = 'none';
                    }
                },

                events: function(fetchInfo, successCallback) {
                    fetch('/calendar-events')
                        .then(res => res.json())
                        .then(data => {
                            const formatted = data.map(event => ({
                                id: event.id,
                                title: event.title,
                                start: event.start_time,
                                end: event.end_time,
                                color: event.color || '#3b82f6',
                                textColor: event.text_color || '#ffffff',
                                extendedProps: {
                                    description: event.description,
                                    meeting_link: event.meeting_link,
                                    is_appointment: event.is_appointment,
                                    text_color: event.text_color
                                }
                            }));
                            renderTodayEvents(formatted);
                            successCallback(formatted);
                        });
                },

                select: function(info) {
                    const start = new Date(info.start);
                    const format = (d) => d.toISOString().slice(0, 16);

                    form.reset();
                    delete form.dataset.editing;

                    modalFields.start.value = format(start);
                    modalFields.end.value = '';

                    modal.style.display = 'block';
                    modalFields.title.focus();
                },

                eventClick: function(info) {
                    const props = info.event.extendedProps;

                    Swal.fire({
                        title: info.event.title,
                        html: `
                    <div class="text-left">
                        <p><b>Description:</b> ${props.description || 'N/A'}</p>
                        <p><b>Meeting:</b> ${props.meeting_link || 'None'}</p>
                        <p><b>Type:</b> ${props.is_appointment ? 'Appointment' : 'Event'}</p>
                        <p><b>Start:</b> ${info.event.start.toLocaleString()}</p>
                        ${info.event.end ? `<p><b>End:</b> ${info.event.end.toLocaleString()}</p>` : ''}
                    </div>
                `,
                        showCancelButton: true,
                        confirmButtonText: 'Edit',
                        cancelButtonText: 'Close',
                        showDenyButton: true,
                        denyButtonText: 'Delete',
                        icon: 'info'
                    }).then(result => {
                        if (result.isConfirmed) {
                            openEditModal(info.event);
                        } else if (result.isDenied) {
                            confirmDelete(info.event.id);
                        }
                    });
                },

                eventDrop: function(info) {
                    const event = info.event;
                    const originalStart = info.oldEvent.start;
                    const originalEnd = info.oldEvent.end;

                    // Use dropped date but keep original time (HH:mm)
                    const droppedDate = event.start;

                    const newStart = new Date(
                        droppedDate.getFullYear(),
                        droppedDate.getMonth(),
                        droppedDate.getDate(),
                        originalStart.getHours(),
                        originalStart.getMinutes()
                    );

                    let newEnd = null;
                    if (originalEnd) {
                        newEnd = new Date(
                            droppedDate.getFullYear(),
                            droppedDate.getMonth(),
                            droppedDate.getDate(),
                            originalEnd.getHours(),
                            originalEnd.getMinutes()
                        );
                    }

                    // Update the event on the server
                    fetch(`/calendar-events/${event.id}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            start_time: newStart.toISOString(),
                            end_time: newEnd ? newEnd.toISOString() : null
                        })
                    }).then(() => calendar.refetchEvents());
                },


                eventResize: function(info) {
                    updateEventTime(info.event);
                }
            });

            calendar.render();

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const data = {
                    title: modalFields.title.value,
                    description: modalFields.description.value,
                    color: modalFields.color.value,
                    text_color: modalFields.fontColor.value,
                    meeting_link: modalFields.link.value,
                    is_appointment: modalFields.appointment.checked,
                    start_time: modalFields.start.value,
                    end_time: modalFields.end.value || null,
                };

                const eventId = form.dataset.editing;
                const method = eventId ? 'PUT' : 'POST';
                const url = eventId ? `/calendar-events/${eventId}` : '/calendar-events';

                fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                }).then(res => {
                    if (res.ok) {
                        calendar.refetchEvents();
                        modal.style.display = 'none';
                        form.reset();
                        delete form.dataset.editing;
                        Swal.fire({
                            icon: 'success',
                            title: eventId ? 'Updated!' : 'Saved!',
                            text: eventId ? 'Event updated.' : 'Event added.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });

            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                form.reset();
                delete form.dataset.editing;
            });

            function updateEventTime(event) {
                const originalEvent = calendar.getEventById(event.id);
                const duration = originalEvent.end ?
                    originalEvent.end.getTime() - originalEvent.start.getTime() :
                    0;

                const newStart = event.start;
                const newEnd = duration ? new Date(newStart.getTime() + duration) : null;

                fetch(`/calendar-events/${event.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        start_time: newStart.toISOString(),
                        end_time: newEnd ? newEnd.toISOString() : null
                    })
                }).then(() => calendar.refetchEvents());
            }


            function openEditModal(event) {

                function formatLocalDatetime(dateInput) {
                    const date = new Date(dateInput); // Already in local time
                    const pad = (n) => n.toString().padStart(2, '0');
                    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
                }

                form.dataset.editing = event.id;
                modalFields.title.value = event.title;
                modalFields.description.value = event.extendedProps.description || '';
                modalFields.color.value = event.backgroundColor || event.color || '#3b82f6';
                modalFields.fontColor.value = event.extendedProps.text_color || '#ffffff';
                modalFields.link.value = event.extendedProps.meeting_link || '';
                modalFields.appointment.checked = event.extendedProps.is_appointment || false;

                modalFields.start.value = formatLocalDatetime(event.start);
                modalFields.end.value = event.end ? formatLocalDatetime(event.end) : '';

                modal.style.display = 'block';
                modalFields.title.focus();

            }


            function confirmDelete(id) {
                Swal.fire({
                    title: 'Delete this event?',
                    text: "This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch(`/calendar-events/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        }).then(() => {
                            calendar.refetchEvents();
                            Swal.fire('Deleted!', 'The event has been removed.', 'success');
                        });
                    }
                });
            }

            function renderTodayEvents(events) {
                const container = document.getElementById('today-events');
                if (!container) return;

                container.innerHTML = '';
                const today = new Date().toISOString().split('T')[0];

                const todaysEvents = events.filter(e => e.start.startsWith(today));
                if (todaysEvents.length === 0) {
                    container.innerHTML = '<li>No events today.</li>';
                    return;
                }

                todaysEvents.forEach(e => {
                    const time = new Date(e.start).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    container.innerHTML += `<li><b>${time}</b> - ${e.title}</li>`;
                });
            }
        });
    </script>


</x-app-layout>

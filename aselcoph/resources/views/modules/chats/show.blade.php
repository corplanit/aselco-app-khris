@php
    use Illuminate\Support\Str;

    $isGroup = $conversation->is_group ?? $conversation->type === 'group';
    $participants = $conversation->participants ?? collect();
    $messages = $conversation->messages ?? collect();

    $participantsCount = $participants->count();
    $messagesCount = $messages->count();
@endphp

<x-app-layout>
    <x-slot name="return">{"link": "/chats/monitor", "text": "Back to Monitor"}</x-slot>
    <x-slot name="title">Conversation Details</x-slot>
    <x-slot name="url_1">{"link": "/chats", "text": "Chats"}</x-slot>
    <x-slot name="active">Messages</x-slot>
    <x-slot name="buttons"></x-slot>

    <div class="space-y-6">
        {{-- HEADER --}}
        <div
            class="rounded-xl border bg-white px-3 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-start gap-3">
                <div
                    class="h-12 w-12  mt-1 rounded-full bg-warning flex items-center justify-center text-dark text-lg font-semibold">
                    <span class="bi bi-people-fill"></span>
                </div>
                <div>
                    <p class="text-xs text-dark">

                        Conversation ID: <span
                            class="font-mono text-dark">#{{ date_format($conversation->created_at, 'dmy') . $conversation->id }}</span>
                        <span
                            class="inline-flex items-center gap-1 rounded-full mx-2 px-2 py-0.5 text-[10px] uppercase tracking-wide {{ $isGroup ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/40' : 'bg-indigo-500/20 text-indigo-200 border border-indigo-400/40' }}">
                            <i class="bi {{ $isGroup ? 'bi-people-fill' : 'bi-person' }} text-[11px]"></i>
                            {{ $isGroup ? 'Group Chat' : 'Direct Message' }}
                        </span>
                    </p>
                    <h1 class="text-2xl font-semibold text-dark flex items-center gap-2">
                        <strong> {{ $conversation->display_title }}</strong>
                    </h1>

                </div>
            </div>

            <div class="flex items-center gap-4 text-xs text-slate-200  mx-5">

                <div class="flex flex-col items-end">
                    <span class="uppercase tracking-wide text-slate-400 text-[10px]">Messages</span>
                    <span class="font-semibold text-2xl"><strong>{{ $messagesCount }}</strong></span>
                </div>
                <div class="h-8 w-px bg-slate-600/60"></div>
                <div class="flex flex-col items-end">
                    <span class="uppercase tracking-wide text-slate-400 text-[10px]">Members</span>
                    <span class="font-semibold text-2xl"><strong>{{ $participantsCount }}</strong></span>
                </div>

            </div>
        </div>

        {{-- 2-COLUMN LAYOUT --}}
        <div class="grid grid-cols-12 gap-6">
            {{-- LEFT: Conversation Info & Members (col-span-4) --}}
            <div class="col-span-4">
                <div class="bg-white border rounded-xl shadow-sm p-4 min-h-[300px] flex flex-col gap-4">
                    {{-- Conversation Info --}}
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-800 flex items-center gap-1.5">
                                <i class="bi bi-info-circle text-slate-400"></i>
                                <strong> Conversation Info</strong>
                            </h2>
                            <p class="text-md text-slate-500 mt-1">
                                Overview and status for this chat room.
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5  uppercase tracking-wide text-slate-600">
                                {{ $isGroup ? 'Group Chat' : 'Direct Message' }}
                            </span>
                            {{-- Status placeholder (active/disabled). Adjust with your actual field --}}
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5  font-medium text-emerald-700 border border-emerald-100">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Active
                            </span>
                        </div>
                    </div>

                    <hr>

                    {{-- Meta info --}}
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-md text-slate-600">
                        <div>
                            <dt class="text-slate-400">Created At</dt>
                            <dd class="font-medium">
                                {{ optional($conversation->created_at)->format('Y-m-d H:i') ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Last Activity</dt>
                            <dd class="font-medium">
                                {{ optional($conversation->updated_at)->diffForHumans() ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Total Messages</dt>
                            <dd class="font-medium">{{ $messagesCount }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Members</dt>
                            <dd class="font-medium">{{ $participantsCount }}</dd>
                        </div>
                    </dl>

                    <hr class="border-dashed border-slate-200">

                    {{-- Members List --}}
                    <div class="flex-1 flex flex-col min-h-[120px]">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-1.5">
                                <i class="bi bi-people text-slate-400"></i>
                                Members
                            </h3>
                            <span class="text-[11px] text-slate-400">
                                {{ $participantsCount }} member{{ $participantsCount === 1 ? '' : 's' }}
                            </span>
                        </div>

                        <div class="border rounded-lg bg-slate-50/60 max-h-64 overflow-y-auto">
                            @forelse ($participants as $p)
                                @php
                                    $member = $p->user;
                                    $name = $member->name ?? 'User';
                                    $email = $member->email ?? null;
                                    $role = $p->role ?? 'Member';
                                @endphp
                                <div
                                    class="flex items-center gap-2 px-3 py-2 border-b border-slate-100 last:border-b-0">
                                    <div
                                        class="h-12 w-12 rounded-full bg-slate-200 flex items-center justify-center font-semibold text-slate-700">
                                        {{ Str::upper(Str::substr($name, 0, 2)) }}
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-slate-800 leading-tight truncate">
                                            {{ $name }}
                                        </div>
                                        @if ($email)
                                            <div class="text-xs text-slate-400 truncate">
                                                {{ $email }}
                                            </div>
                                        @endif
                                        <div class="mt-0.5 text-xs text-slate-400">
                                            Role: <span
                                                class="font-medium text-slate-600">{{ Str::title($role) }}</span>
                                        </div>
                                    </div>

                                    {{-- Per-member moderator actions (no functionality yet) --}}
                                    <div class="flex items-center gap-1">
                                        {{-- Mute member --}}
                                        <button type="button" title="Mute member"
                                            class="inline-flex items-center justify-center rounded-full border border-amber-200 bg-amber-50 p-2 text-sm text-amber-700 hover:bg-amber-100">
                                            <i class="bi bi-bell-slash"></i>
                                        </button>

                                        {{-- Remove member --}}
                                        <button type="button" title="Remove member"
                                            class="inline-flex items-center justify-center rounded-full border border-rose-200 bg-rose-50 p-2 text-sm text-rose-700 hover:bg-rose-100">
                                            <i class="bi bi-person-dash"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="px-3 py-4 text-xs text-slate-400 text-center">
                                    No members found.
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <hr class="border-dashed border-slate-200">

                    <div class="mt-2 flex flex-wrap items-center justify-end gap-2">
                        {{-- Add member (for groups / DMs turned into group) --}}
                        <button type="button"
                            class="inline-flex justify-start items-center gap-1 rounded-md border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-700 hover:bg-slate-100">
                            <i class="bi bi-person-plus-fill"></i>
                            <span class="mx-1">Add Member</span>
                        </button>

                        {{-- Mute conversation --}}
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50  px-3 py-3 text-sm  font-medium text-amber-700 hover:bg-amber-100">
                            <i class="bi bi-bell-slash-fill"></i>
                            <span class="mx-1">Mute Chat</span>
                        </button>

                        {{-- Lock / disable chat --}}
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-md border border-rose-400 bg-rose-50  px-3 py-3 text-sm  font-medium text-rose-700 hover:bg-rose-100">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span class="mx-1">Lock Chat</span>
                        </button>

                        {{-- More (placeholder) --}}
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-3 py-3 text-sm text-slate-600 hover:bg-slate-50">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                    </div>
                </div>
            </div>


            {{-- RIGHT: Messages / Conversation (col-span-8) --}}
            <div class="col-span-8">
                <div class="bg-white border rounded-xl shadow-sm min-h-[300px] max-h-[440px] flex flex-col" style="max-height: 700px">
                    {{-- Header --}}
                    <div class="px-4 py-3 border-b flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-1.5">
                                <i class="bi bi-chat-dots text-slate-400"></i>
                                <strong>Messages</strong>
                            </h2>
                            <p class="text-sm text-slate-400">
                                Showing full conversation history (monitor view).
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <i class="bi bi-clock-history"></i>
                            <span>Newest at the bottom</span>
                        </div>
                    </div>

                    {{-- Messages list --}}
                    @php $currentDate = null; @endphp

                    <div class="flex-1 overflow-y-auto !max-h-[540px] chat-bg px-4 py-4 space-y-3">
                        @forelse ($messages->sortBy('created_at') as $msg)
                            @php
                                $sender = $msg->user;
                                $senderName = $sender->name ?? 'System';
                                $isSystem = ($msg->type ?? null) === 'system';
                                $dateLabel = optional($msg->created_at)->format('M d, Y');
                            @endphp

                            {{-- Date separator --}}
                            @if ($dateLabel && $dateLabel !== $currentDate)
                                @php $currentDate = $dateLabel; @endphp
                                <div class="flex items-center justify-center my-1">
                                    <span
                                        class="inline-flex items-center px-3 py-0.5 rounded-full bg-white/80 border border-slate-200 text-[10px] text-slate-500 shadow-sm">
                                        <i class="bi bi-calendar3 mr-1 text-[11px]"></i>{{ $dateLabel }}
                                    </span>
                                </div>
                            @endif

                            @if ($isSystem)
                                {{-- System message centered --}}
                                <div class="flex items-center justify-center message-enter ">
                                    <span class="message-system">
                                        <i class="bi bi-gear-wide-connected mr-1"></i>
                                        {{ $msg->body }}
                                    </span>
                                </div>
                            @else
                                {{-- Normal message row --}}
                                <div class="flex items-start gap-2 message-enter">
                                    {{-- Avatar --}}
                                    <div
                                        class="message-avatar mt-0.5 h-7 w-7 rounded-full bg-slate-200 flex items-center justify-center text-[11px] font-semibold text-slate-700">
                                        {{ Str::upper(Str::substr($senderName, 0, 2)) }}
                                    </div>

                                    <div class="max-w-[90%]">
                                        {{-- Header (name + time) --}}
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-xs font-semibold text-slate-800">
                                                {{ $senderName }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">
                                                {{ optional($msg->created_at)->format('Y-m-d H:i') }}
                                            </span>
                                        </div>

                                        {{-- Bubble --}}
                                        <div class="mt-1 inline-flex items-start gap-1 message-bubble">
                                            <div
                                                class="rounded-2xl px-3 py-2 text-xs bg-white text-slate-800 border border-slate-200 shadow-sm">
                                                <span class="break-words">
                                                    {{ $msg->body }}
                                                </span>
                                            </div>

                                            {{-- Hover actions (optional, just visual for monitor) --}}
                                            <div class="message-actions items-center">
                                                <button type="button"
                                                    class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white/80 px-1.5 py-0.5 text-[10px] text-slate-500 hover:bg-slate-100">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="h-full flex items-center justify-center text-xs text-slate-400">
                                No messages in this conversation yet.
                            </div>
                        @endforelse
                    </div>

                    {{-- Footer (optional quick actions for moderator) --}}
                    <div class="px-4 py-2 border-t bg-slate-50 flex items-center justify-between text-[11px]">
                        <div class="flex items-center gap-2 text-slate-500">
                            <i class="bi bi-shield-check"></i>
                            <span>Moderator view only – no messages can be sent here.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href=""
                                class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] text-slate-700 hover:bg-slate-100">
                                <i class="bi bi-box-arrow-up-right text-[11px]"></i>
                                <span>Open in Chat</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Styles specific to this view --}}
    <br>
    <style>
        .chat-bg {
            background-color: #ffffff;
            background-image:
                radial-gradient(circle, rgba(0, 0, 0, 0.04) 1px, transparent 1px),
                radial-gradient(circle, rgba(0, 0, 0, 0.02) 1.2px, transparent 1px),
                url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'><g fill='none' stroke='%23e5e5e5' stroke-width='1' opacity='0.5' stroke-linecap='round' stroke-linejoin='round'><g transform='rotate(-15 40 40)'><rect x='18' y='18' width='46' height='26' rx='8'/><path d='M24 44l-5 7'/></g><g transform='rotate(12 116 46)'><path d='M120 32c6 6 10 14 10 20 0 3-1 6-3 8l-3 3c-2 2-5 3-8 3-6 0-12-5-18-10'/><path d='M114 30l5-3'/><path d='M126 60l-3 5'/></g><g transform='rotate(-10 60 120)'><rect x='34' y='104' width='48' height='24' rx='8'/><path d='M42 126l-4 6'/></g></g></svg>");
            background-size:
                26px 26px,
                32px 32px,
                220px 220px;
            background-repeat:
                repeat,
                repeat,
                repeat;
            background-position:
                center center,
                center center,
                center center;
        }

        .message-enter {
            animation: messageFadeInUp 0.18s ease-out;
        }

        @keyframes messageFadeInUp {
            0% {
                transform: translateY(4px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .message-bubble {
            position: relative;
        }

        .message-actions {
            display: none;
        }

        .message-bubble:hover .message-actions {
            display: inline-flex;
        }

        .message-system {
            font-size: 11px;
            color: #6b7280;
            background: rgba(243, 244, 246, 0.95);
            border-radius: 9999px;
            padding: 4px 10px;
            border: 1px dashed #d1d5db;
        }

        .message-avatar {
            flex-shrink: 0;
        }
    </style>
</x-app-layout>

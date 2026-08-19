<li class="slide__category"><span class="category-name">Manage Services</span></li>

<li class="slide">
    <a href="/supp/chat" class="side-menu__item relative" data-unread-badge-anchor="support-messages">
        <i class="w-6 h-4 side-menu__icon bi bi-headset" style="color: #5D66F7"></i>
        <span class="side-menu__label">Customer Support</span>

        @php
            $uid = Auth::id();
            $unread = 0;

            if (Auth::user()->role == 'support' || Auth::user()->role == 'Administrator') {
                $parts = \App\Models\SuppParticipant::query()
                    ->where('user_id', $uid)
                    ->get(['conversation_id', 'last_read_message_id']);

                foreach ($parts as $p) {
                    $q = \App\Models\SuppMessage::query()
                        ->where('conversation_id', $p->conversation_id)
                        ->where('user_id', '!=', $uid);

                    if ($p->last_read_message_id) {
                        $q->where('id', '>', $p->last_read_message_id);
                    }

                    $unread += $q->count();
                }
            }
        @endphp

        <span id="count_unread_msg"
              class="translate-middle badge !rounded-full bg-danger absolute top-0 end-0"
              style="{{ $unread > 0 ? '' : 'display:none' }}">
            {{ $unread > 9 ? '9+' : $unread }}
        </span>
    </a>
</li>


<li class="slide">
    <a href="/complaint" class="side-menu__item">
        <i class="w-6 h-4 side-menu__icon bi bi-hand-index" style="color: #5D66F7"></i>
        <span class="side-menu__label">Customer Complaint</span>
    </a>
</li>

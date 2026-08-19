<script>
  (function () {
    const qs = new URLSearchParams(location.search);
    const POPUP_NAME   = 'JitsiMeetPopup';
    const STORAGE_KEY  = 'hbcs_jitsi_open';

    let jitsiWin   = null;
    let closeTimer = null;
    let joinCount  = 1;

    function renderCount() {
      const el = document.getElementById('count_join');
      if (el) el.textContent = String(joinCount);
    }

    function incJoined() {
      const id = window.ACTIVE_CONV_ID;
      if (!id) {
        console.warn('No conversation id found; skipping join count increment.');
        return;
      }
      console.log('Room', window.ACTIVE_CONV_ID);
      console.log('Meeting Join, count now', joinCount + ' | ID {{ Auth::user()->id }}');

      $.post({
        url: `/chats/conversations/${id}/meet/join`,
        headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
        data: { meet_join: window.ACTIVE_CONV_ID, user_id: {{ Auth::user()->id }} },
      })
      .done(function (res) {
        joinCount = res.conv.joined;
        renderCount();
      })
      .fail(function (xhr) {
        console.error('Error fetching info:', xhr.responseText);
      });
    }

    function decJoined() {
      joinCount = Math.max(0, joinCount - 1);
      renderCount();
      console.log('Meeting left, count now', joinCount);

      const id = window.ACTIVE_CONV_ID;
      if (!id) {
        console.warn('No conversation id found; skipping leave count.');
        return;
      }

      $.post({
        url: `/chats/conversations/${id}/meet/left`,
        headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') },
        data: { meet_join: window.ACTIVE_CONV_ID, user_id: {{ Auth::user()->id }} },
      })
      .done(function(res){ console.log(res); })
      .fail(function(xhr){ console.error('Error fetching info:', xhr.responseText); });
    }

    // Build a direct Jitsi URL on meet.hillbcs.com (no portal wrapper)
    function buildJitsiUrl(room, name, email = '', avatar = '') {
      const base = `https://meet.hillbcs.com/${encodeURIComponent(room)}`;
      const hash = new URLSearchParams();
      hash.set('config.prejoinPageEnabled', 'false');
      hash.set('config.disableDeepLinking', 'true');
      if (name)   hash.set('userInfo.displayName', name);
      if (email)  hash.set('userInfo.email', email);
      if (avatar) hash.set('userInfo.avatarUrl', avatar); // some builds ignore this safely
      return `${base}#${hash.toString()}`;
    }

    function startCloseWatcher() {
      if (closeTimer) return;
      closeTimer = setInterval(() => {
        if (!jitsiWin || jitsiWin.closed) {
          clearInterval(closeTimer);
          closeTimer = null;
          if (localStorage.getItem(STORAGE_KEY) === '1') {
            localStorage.setItem(STORAGE_KEY, '0');
            decJoined();
          }
          jitsiWin = null;
        }
      }, 800);
    }

    function openCenteredSinglePopup(url, name, w, h) {
      if (jitsiWin && !jitsiWin.closed) {
        try { jitsiWin.location.replace(url); } catch (e) { jitsiWin.location = url; }
        jitsiWin.focus();
        return;
      }

      const sx = (window.screenLeft ?? screen.left) || 0;
      const sy = (window.screenTop  ?? screen.top)  || 0;
      const ww = window.innerWidth  || document.documentElement.clientWidth  || screen.width;
      const wh = window.innerHeight || document.documentElement.clientHeight || screen.height;

      const left = Math.max(0, (ww - w) / 2 + sx);
      const top  = Math.max(0, (wh - h) / 2 + sy);

      const features = [
        `width=${w}`, `height=${h}`, `left=${left}`, `top=${top}`,
        'resizable=yes','scrollbars=yes'
      ].join(',');

      const win = window.open('', name, features);
      if (!win) return;

      if (localStorage.getItem(STORAGE_KEY) !== '1') {
        localStorage.setItem(STORAGE_KEY, '1');
        incJoined();
      }

      try { win.location.replace(url); } catch (e) { win.location = url; }
      win.focus();
      jitsiWin = win;
      startCloseWatcher();
    }

    // --- Join button (always open meet.hillbcs.com directly) ---
    document.getElementById('joinBtn')?.addEventListener('click', function () {
      const room   = (window.ACTIVE_CONVERSATION || qs.get('room') || 'SupportRoom').trim();
      const name   = (this.dataset.name   || qs.get('name')   || 'Guest').trim();
      const email  = (this.dataset.email  || qs.get('email')  || '').trim();
      const avatar = (this.dataset.avatar || qs.get('avatar') || '').trim();

      const url = buildJitsiUrl(room, name, email, avatar); // <- always direct
      openCenteredSinglePopup(url, POPUP_NAME, 1200, 800);
    });

    // --- Late join button (also always direct) ---
    document.getElementById('joinBtnLate')?.addEventListener('click', async function () {
      const room   = (window.ACTIVE_CONVERSATION || qs.get('room') || 'SupportRoom').trim();
      const name   = (this.dataset.name   || qs.get('name')   || 'Guest').trim();
      const email  = (this.dataset.email  || qs.get('email')  || '').trim();
      const avatar = (this.dataset.avatar || qs.get('avatar') || '').trim();

      const url = buildJitsiUrl(room, name, email, avatar); // <- always direct
      openCenteredSinglePopup(url, (typeof POPUP_NAME !== 'undefined' ? POPUP_NAME : 'MEET'), 1200, 800);

      // Send system message "Joined the Meeting"
      const bodyHtml = `${name} Joined the Meeting`;
      const convId = (window.CURRENT_CONV || window.ACTIVE_CONV_ID || qs.get('conv_id') || '').toString().trim();
      if (!convId) {
        console.warn('No conversation id found; skipping system message.');
        return;
      }

      try {
        const form = new FormData();
        form.append('type', 'system');
        form.append('status', 'system');
        form.append('is_system', '1');
        form.append('meet', '1');
        form.append('is_html', '1');
        form.append('body', bodyHtml);

        const res = await $.ajax({
          url: `${API_BASE}/conversations/${encodeURIComponent(convId)}/messages`,
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          data: form,
          processData: false,
          contentType: false,
          dataType: 'json'
        });

        $('#editor').empty();
        const justSent = { ...res, body_html: bodyHtml, type: 'system', status: 'system', is_system: 1, meet: 1 };
        if (typeof appendMessage === 'function') appendMessage(justSent);
        $('#chat-messages').find('.no-msg-placeholder').remove();
        if (typeof scrollChatToBottom === 'function') scrollChatToBottom();
        if (typeof window.fetchConversations === 'function') window.fetchConversations();

      } catch (xhr) {
        console.warn('system send failed', xhr?.status, xhr?.responseText);
        if (typeof toast === 'function') toast('Failed to send system message.');
      }
    });
  })();
</script>

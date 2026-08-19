<div class="bottom-0 left-0 right-0 p-2 border-t bg-white">
    <!-- Attachments preview -->
    <div id="pending-preview" class="flex flex-wrap gap-2 mb-2 hidden"></div>

    <div class="flex items-center border border-gray-300 rounded-sm p-0 bg-white shadow-sm w-full max-w-4xl mx-auto">
        <!-- Attach buttons -->
        <button id="btn-attach" type="button" class="mr-2 p-2 rounded hover:bg-gray-100" title="Attach files">
            <i data-lucide="paperclip" class="w-5 h-5 text-gray-600"></i>
        </button>
        <button id="btn-image" type="button" class="mr-2 p-2 rounded hover:bg-gray-100" title="Attach image">
            <i data-lucide="image" class="w-5 h-5 text-gray-600"></i>
        </button>

        <!-- Hidden inputs -->
        <input id="file-input" type="file" multiple class="hidden">
        <input id="img-input" type="file" accept="image/*" multiple class="hidden">

        <i data-lucide="message-circle" class="w-5 h-5 text-gray-400 mr-3"></i>

        <!-- Scrollable editor -->
        <div id="editor"
             class="flex-1 min-h-[32px] max-h-52 overflow-y-auto px-3 py-2 outline-none text-sm break-words"
             contenteditable="true" role="textbox" aria-multiline="true"
             data-placeholder="Write a message… (Shift+Enter for new line)"></div>

        <button id="btn-send" class="px-3 py-2 rounded-lg border-none hover:bg-gray-100 flex items-center gap-2">
            <i data-lucide="send"></i>
        </button>
    </div>
</div>

{{--<button id="btn-system-send" class="ml-2 px-3 py-2 rounded-md bg-gray-600 text-white text-sm" title="Send as system">--}}
{{--    <i class="bi bi-shield-lock"></i> System--}}
{{--</button>--}}

{{--<button id="btn-end-meet" class="ml-2 px-3 py-2 rounded-md bg-red-600 text-white text-sm" title="End meeting">--}}
{{--    <i class="bi bi-x-circle"></i> End Meeting--}}
{{--</button>--}}


@include('modules.chats.hook.send-hook')

 <div class="box-body p-0">
     <div class="p-1 border-defaultborder dark:border-defaultborder/10" id="ledger-figures">
         <div class="flex mb-3 search-result-input gap-2 relative">
             <select type="text"
                 class="lf-acct form-control form-control-lg !border-s !ps-[1rem] !bg-light dark:!bg-light !rounded-full form-select ti-form-select"
                 placeholder="Account Number Here ..." name="account_number" id="account_number"
                 aria-label="Search Here ..." required>
                 @php
                     $linkedAccounts = App\Models\TAccountRaw::where('user_id', Auth::user()->id)
                         ->where('status', 'Linked')
                         ->get();
                 @endphp
                 <option value="" disabled selected> - SELECT ACCOUNT NO. -</option>
                 @foreach ($linkedAccounts as $account)
                     <option value="{{ $account->account_no }}">{{ $account->account_no }} -
                         {{ $account->customer }}</option>
                 @endforeach
             </select>
             <button type="button"
                 class="lf-sync inline-flex items-center gap-2 px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 active:scale-[.99]">
                 <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M4 4v6h6M20 20v-6h-6M20 8A8 8 0 0 0 4.93 6.34M4 16A8 8 0 0 0 19.07 17.66" />
                 </svg>
                 <span class="lf-sync-label">Check</span>
             </button>

             <script>
                 document.addEventListener('DOMContentLoaded', () => {
                     const btn = document.querySelector('.lf-sync');

                     btn.addEventListener('click', () => {
                         // prevent double-click while loading
                         if (btn.dataset.loading === '1') return;

                         btn.dataset.loading = '1';
                         btn.disabled = true;
                         btn.classList.add('opacity-70', 'cursor-not-allowed');

                         // make the icon spin
                         const icon = btn.querySelector('svg');
                         icon.classList.add('animate-spin');

                         // change label text
                         const label = btn.querySelector('.lf-sync-label');
                         label.textContent = 'Checking...';

                         // TODO: replace this with your real AJAX/fetch call
                         setTimeout(() => {
                             // reset state after done
                             btn.dataset.loading = '0';
                             btn.disabled = false;
                             btn.classList.remove('opacity-70', 'cursor-not-allowed');
                             icon.classList.remove('animate-spin');
                             label.textContent = 'Check';
                         }, 5000);
                     });
                 });
             </script>

             <span class="lf-state text-sm text-gray-600 hidden"></span>
         </div>
     </div>
 </div>

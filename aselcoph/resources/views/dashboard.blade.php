<x-app-layout>

    <x-slot name="title">Welcome back! {{ Auth::user()->name }}</x-slot>
    <x-slot name="url_1">{"link": "/dashboard", "text": "Manage"}</x-slot>
    <x-slot name="url_2">{"link": "/dashboard", "text": "Dashboard"}</x-slot>
    <x-slot name="active">Information</x-slot>
    <x-slot name="buttons"></x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12 mb-0">

            @php
                // Server-side numbers (fallbacks for first render)
                $billings = App\Models\BillingUpload::with(['accountLink', 'uploader'])
                    ->whereHas('accountLink', fn($q) => $q->where('user_id', Auth::id()))
                    ->where('status', 'Pending')
                    ->whereDate('billing_date', '>=', \Carbon\Carbon::now()->startOfMonth())
                    ->get();
                $totalAmount = $billings->sum('amount');

                $previousBillings = App\Models\BillingUpload::with(['accountLink', 'uploader'])
                    ->whereHas('accountLink', fn($q) => $q->where('user_id', Auth::id()))
                    ->whereBetween('billing_date', [
                        \Carbon\Carbon::now()->subMonth()->startOfMonth(),
                        \Carbon\Carbon::now()->subMonth()->endOfMonth(),
                    ])
                    ->get();
                $previousAmount = $previousBillings->sum('amount');

                $billings_pending = App\Models\BillingUpload::with(['accountLink', 'uploader'])
                    ->whereHas('accountLink', fn($q) => $q->where('user_id', Auth::id()))
                    ->where('status', 'Pending')
                    ->get();

                $change = $previousAmount > 0 ? (($totalAmount - $previousAmount) / $previousAmount) * 100 : 0;
                $change = round($change, 2);
            @endphp



            <!-- FIGURES -->
            <div class="grid grid-cols-12 gap-x-6">
                <!-- KPI 1 -->
                <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
                    <div class="box overflow-hidden main-content-card">
                        <div class="box-body">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="text-textmuted dark:text-textmuted/50 block mb-1">
                                        Total Billing as of {{ now()->format('D, F d, Y') }}
                                        <span
                                            class="lf-badge hidden text-[11px] ml-1 px-1.5 py-0.5 rounded bg-blue-50 text-blue-700">from
                                            ledger</span>
                                    </span>
                                    <h4 class="font-medium mb-0">
                                        ₱ <span class="kpi-total-billing">{{ number_format($totalAmount, 2) }}</span>
                                    </h4>
                                </div>
                                <div class="leading-none">
                                    <span class="avatar avatar-md avatar-rounded bg-danger">
                                        <i class="bi bi-cash-coin text-[1.25rem]"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="text-textmuted dark:text-textmuted/50 text-[13px]">
                                @if ($previousAmount == 0)
                                    <span class="text-secondary">No previous data to compare</span>
                                @elseif ($change == 0)
                                    <span class="text-warning">No change from last month</span>
                                @else
                                    <span>{{ $change > 0 ? 'Increased' : 'Decreased' }} By</span>
                                    <span class="{{ $change > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ abs($change) }}%
                                        <i
                                            class="ti {{ $change > 0 ? 'ti-arrow-narrow-up' : 'ti-arrow-narrow-down' }}"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI 2 -->
                <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
                    <div class="box overflow-hidden main-content-card">
                        <div class="box-body">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="block text-textmuted dark:text-textmuted/50 mb-1">
                                        Total Pending Amount
                                        <span
                                            class="lf-badge hidden text-[11px] ml-1 px-1.5 py-0.5 rounded bg-blue-50 text-blue-700">from
                                            ledger</span>
                                    </span>
                                    <h4 class="font-medium mb-0">
                                        ₱ <span
                                            class="kpi-total-pending-amount">{{ number_format($billings_pending->sum('amount'), 2) }}</span>
                                    </h4>
                                </div>
                                <div class="leading-none">
                                    <span class="avatar avatar-md avatar-rounded bg-info">
                                        <i class="bi bi-cash text-[1.25rem]"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="text-textmuted dark:text-textmuted/50 text-[13px]">As of
                                {{ now()->format('D, F d, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>

                <div class="xxl:col-span-6 xl:col-span-6 col-span-12">
                    <div class="box overflow-hidden main-content-card">
                        <div class="box-body pb-1">
                            <i class="bi bi-info-circle px-1 text-primary"></i>
                            <span class="text-primary">You can check your billing balance here.</span>
                            <hr class="mb-3 mt-3">
                            @if (Auth::user()->role === 'Administrator')                            
                                @include('pages.dashboard.billing-check-admin')
                            @else
                                @include('pages.dashboard.billing-check-user')
                            @endif
                        </div>
                    </div>
                </div>

                <!-- KPI 3 -->
                <div class="xxl:col-span-3 xl:col-span-6 col-span-12 hidden">
                    <div class="box overflow-hidden main-content-card">
                        <div class="box-body">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="text-textmuted dark:text-textmuted/50 block mb-1">
                                        Total Pending
                                        <span
                                            class="lf-badge hidden text-[11px] ml-1 px-1.5 py-0.5 rounded bg-blue-50 text-blue-700">from
                                            ledger</span>
                                    </span>
                                    <h4 class="font-medium mb-0">
                                        x <span class="kpi-total-pending-count">{{ $billings_pending->count() }}</span>
                                    </h4>
                                </div>
                                <div class="leading-none">
                                    <span class="avatar avatar-md avatar-rounded bg-warning">
                                        <i class="bi bi-clock text-[1.25rem]"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="text-textmuted dark:text-textmuted/50 text-[13px]">-</div>
                        </div>
                    </div>
                </div>

                <!-- KPI 4 (server-driven) -->
                <div class="xxl:col-span-3 xl:col-span-6 col-span-12 hidden">
                    <div class="box overflow-hidden main-content-card">
                        <div class="box-body">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="text-textmuted dark:text-textmuted/50 block mb-1">Total Linked
                                        Account</span>
                                    <h4 class="font-medium mb-0">
                                        x
                                        {{ App\Models\AccountLink::where('user_id', Auth::user()->id)->where('validated_by', '<>', '')->count() }}
                                    </h4>
                                </div>
                                <div class="leading-none">
                                    <span class="avatar avatar-md avatar-rounded bg-success">
                                        <i class="bi bi-people text-[1.25rem]"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="text-textmuted dark:text-textmuted/50 text-[13px]">-</div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- LEDGER TABLE -->
            <div class="box">
                <div class="box-body">
                    <i class="bi bi-info-circle px-1"></i> You can check your ledger table here.
                    <hr class="mb-3 mt-3">

                    <!-- Ledger Widget (reads account from #ledger-figures .lf-acct) -->
                    <div class="ledger-widget" data-acct-input="#ledger-figures .lf-acct">
                        <!-- Summary -->
                        <div class="lw-summary mt-2">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-12 md:col-span-4">
                                    <div class="p-3 rounded-lg border bg-white h-full">
                                        <div class="text-xs text-gray-500">Name</div>
                                        <div class="lw-name font-semibold text-gray-900">—</div>
                                    </div>
                                </div>

                                <div class="col-span-12 md:col-span-4">
                                    <div class="p-3 rounded-lg border bg-white h-full">
                                        <div class="text-xs text-gray-500">Address</div>
                                        <div class="lw-address font-medium text-gray-900">—</div>
                                    </div>
                                </div>

                                <div class="col-span-12 md:col-span-4">
                                    <div class="p-3 rounded-lg border bg-white h-full">
                                        <div class="text-xs text-gray-500">Status</div>
                                        <div
                                            class="lw-status inline-flex px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                            —</div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="lw-tabs-wrap mt-4 hidden min-w-0">
                            <div class="lw-tabs-scroller relative min-w-0">
                                <nav class="lw-tabs flex gap-2 overflow-x-auto overflow-y-hidden whitespace-nowrap scroll-smooth
             min-w-0 rounded-xl p-1 ring-1 ring-gray-200 bg-white
             [-webkit-overflow-scrolling:touch] pl-14 pr-14"
                                    role="tablist">
                                    <!-- tabs are injected by JS -->
                                </nav>
                                <span
                                    class="lw-fade lw-left-fade pointer-events-none absolute left-0 top-0 h-full w-12
                 bg-gradient-to-r from-white to-transparent rounded-l-xl hidden"></span>
                                <span
                                    class="lw-fade lw-right-fade pointer-events-none absolute right-0 top-0 h-full w-12
                 bg-gradient-to-l from-white to-transparent rounded-r-xl hidden"></span>
                            </div>
                        </div>



                        <!-- Table -->

                        @include('pages.dashboard.ledger')


                        <!-- Status line -->
                        <div class="lw-state text-sm text-gray-600 mt-2"></div>
                    </div>
                </div>
            </div>


            <div class="box">
                <div class="box-body">
                    <i class="bi bi-info-circle px-1"></i> You can check your complaints here.
                    <hr class="mb-3 mt-3">
                    @include('pages.dashboard.complaint-user')
                </div>
            </div>

        </div>
    </div>
    
</x-app-layout>

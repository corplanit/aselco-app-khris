<x-app-layout>

    <x-slot name="title">Welcome back! {{ Auth::user()->name }}</x-slot>
    <x-slot name="url_1">{"link": "/dashboard", "text": "Manage"}</x-slot>
    <x-slot name="url_2">{"link": "/dashboard", "text": "Dashboard"}</x-slot>
    <x-slot name="active">Information</x-slot>
    <x-slot name="buttons"></x-slot>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12 mb-0">


            @php
                // Current total (this month)
                $billings = App\Models\BillingUpload::with(['accountLink', 'uploader'])
                    ->where('status', 'Pending')
                    ->whereDate('billing_date', '>=', \Carbon\Carbon::now()->startOfMonth())
                    ->get();

                $totalAmount = $billings->sum('amount');

                // Previous total (last month)
                $previousBillings = App\Models\BillingUpload::with(['accountLink', 'uploader'])
                    ->whereBetween('billing_date', [
                        \Carbon\Carbon::now()->subMonth()->startOfMonth(),
                        \Carbon\Carbon::now()->subMonth()->endOfMonth(),
                    ])
                    ->get();
                $previousAmount = $previousBillings->sum('amount');

                $billings_pending = App\Models\BillingUpload::with(['accountLink', 'uploader'])
                    ->where('status', 'Pending')
                    ->get();

                // Calculate percent change
                $change = $previousAmount > 0 ? (($totalAmount - $previousAmount) / $previousAmount) * 100 : 0;

                $change = round($change, 2);
            @endphp

            <div class="grid grid-cols-12 gap-x-6">
                <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
                    <div class="box overflow-hidden main-content-card">
                        <div class="box-body">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="text-textmuted dark:text-textmuted/50 block mb-1">
                                        Total Billing as of {{ now()->format('D, F d, Y') }}
                                    </span>
                                    <h4 class="font-medium mb-0">₱ {{ number_format($totalAmount, 2) }}</h4>
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
                <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
                    <div class="box overflow-hidden main-content-card">
                        <div class="box-body">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="block text-textmuted dark:text-textmuted/50 mb-1">Total Pending
                                        Amount</span>
                                    <h4 class="font-medium mb-0">₱
                                        {{ number_format($billings_pending->sum('amount') - $totalAmount + $totalAmount, 2) }}
                                    </h4>
                                </div>
                                <div class="leading-none">
                                    <span class="avatar avatar-md avatar-rounded bg-info">
                                        <i class="bi bi-cash text-[1.25rem]"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="text-textmuted dark:text-textmuted/50 text-[13px]">
                                As of {{ now()->format('D, F d, Y h:i A') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
                    <div class="box overflow-hidden main-content-card">
                        <div class="box-body">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="text-textmuted dark:text-textmuted/50 block mb-1">Total Pending</span>
                                    <h4 class="font-medium mb-0">x {{ $billings_pending->count() }}</h4>
                                </div>
                                <div class="leading-none">
                                    <span class="avatar avatar-md avatar-rounded bg-warning">
                                        <i class="bi bi-clock text-[1.25rem]"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="text-textmuted dark:text-textmuted/50 text-[13px]">
                                -
                            </div>
                        </div>
                    </div>
                </div>
                <div class="xxl:col-span-3 xl:col-span-6 col-span-12">
                    <div class="box overflow-hidden main-content-card">
                        <div class="box-body">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="text-textmuted dark:text-textmuted/50 block mb-1">Total Linked
                                        Account</span>
                                    <h4 class="font-medium mb-0">x
                                        {{ App\Models\AccountLink::where('validated_by', '<>', '')->count() }}
                                    </h4>
                                </div>
                                <div class="leading-none">
                                    <span class="avatar avatar-md avatar-rounded bg-success">
                                        <i class="bi bi-people text-[1.25rem]"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="text-textmuted dark:text-textmuted/50 text-[13px]">
                                -
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="box-body">

                    <i class="bi bi-info-circle px-1"></i> You can check the billing balance here.
                    <hr class="mb-3 mt-3">

                    @include('pages.dashboard.billing-check-admin')

                </div>
            </div>

            <div class="box">
                <div class="box-body">

                    <i class="bi bi-info-circle px-1"></i> You can check the complaints here.
                    <hr class="mb-3 mt-3">

                    @include('pages.dashboard.complaint-admin')

                </div>
            </div>

        </div>

    </div>

</x-app-layout>

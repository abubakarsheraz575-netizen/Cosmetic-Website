<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Withdrawals — Cosmetics Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    @include('user.include.base')
    <style>
        .withdrawals-main {
            min-width: 0;
            padding-top: 28px !important
        }

        .withdrawals-topbar {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px
        }

        .withdrawals-title {
            min-width: 0;
            flex: 1
        }

        .withdrawals-title h1 {
            color: var(--ink);
            font-size: 21px;
            font-weight: 600
        }

        .withdrawals-title p {
            margin-top: 4px;
            color: var(--txt);
            font-size: 12px
        }

        .withdrawals-head {
            position: relative;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            padding: 23px 25px;
            overflow: hidden;
            border: 1px solid rgba(56, 170, 180, .17);
            border-radius: 20px;
            background: linear-gradient(120deg, rgba(255, 255, 255, .96), rgba(230, 247, 246, .88) 60%, rgba(239, 235, 255, .75));
            box-shadow: 0 14px 36px rgba(31, 51, 89, .08)
        }

        .withdrawals-head:after {
            position: absolute;
            top: -106px;
            right: -40px;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: rgba(56, 170, 180, .12);
            content: "";
            pointer-events: none
        }

        .withdrawals-head-icon {
            display: grid;
            place-items: center;
            flex: none;
            width: 52px;
            height: 52px;
            border: 1px solid rgba(56, 170, 180, .22);
            border-radius: 16px;
            background: linear-gradient(145deg, #e5f8f5, #e8f1ff);
            color: var(--teal-2);
            font-size: 24px
        }

        .withdrawals-head-copy {
            position: relative;
            z-index: 1;
            min-width: 0;
            flex: 1
        }

        .withdrawals-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 5px;
            color: var(--teal-2);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase
        }

        .withdrawals-head h2 {
            color: var(--ink);
            font-size: 23px;
            font-weight: 700;
            letter-spacing: -.4px
        }

        .withdrawals-head p {
            max-width: 640px;
            margin-top: 5px;
            color: var(--txt);
            font-size: 12px;
            line-height: 1.6
        }

        .withdrawals-messages-link {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            flex: none;
            padding: 10px 13px;
            border: 1px solid rgba(56, 170, 180, .2);
            border-radius: 11px;
            background: rgba(255, 255, 255, .78);
            color: #19725f;
            font-size: 11px;
            font-weight: 700
        }

        .withdrawals-messages-link:hover {
            background: #fff;
            box-shadow: 0 5px 15px rgba(31, 51, 89, .08)
        }

        .withdrawals-feedback {
            margin-bottom: 16px;
            padding: 12px 15px;
            border: 1px solid #b9ebd4;
            border-radius: 12px;
            background: #eaf9f0;
            color: #176a3b;
            font-size: 12px
        }

        .withdrawals-feedback.errors {
            border-color: #fecaca;
            background: #fff1f2;
            color: #a52b40
        }

        .withdrawals-feedback ul {
            margin: 0;
            padding-left: 18px
        }

        .withdrawals-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px
        }

        .withdrawals-stat {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            padding: 16px;
            border: 1px solid rgba(22, 35, 60, .08);
            border-radius: 15px;
            background: rgba(255, 255, 255, .83);
            box-shadow: 0 9px 22px rgba(31, 51, 89, .045)
        }

        .withdrawals-stat-icon {
            display: grid !important;
            place-items: center !important;
            flex: none;
            width: 40px;
            height: 40px;
            border-radius: 13px;
            background: rgba(56, 170, 180, .11);
            color: var(--teal-2);
            font-size: 19px;
            line-height: 1
        }

        .withdrawals-stat-icon i {
            display: grid;
            place-items: center;
            flex: none;
            width: 1em;
            height: 1em;
            font-size: 19px;
            line-height: 1
        }

        .withdrawals-stat span {
            display: block;
            color: var(--txt);
            font-size: 10px
        }

        .withdrawals-stat strong {
            display: block;
            margin-top: 3px;
            color: var(--ink);
            font-size: 18px;
            line-height: 1.2
        }

        .withdrawals-grid {
            display: grid;
            grid-template-columns: minmax(0, .92fr) minmax(0, 1.35fr);
            gap: 18px;
            align-items: start
        }

        .withdrawals-panel {
            min-width: 0;
            padding: 21px;
            border: 1px solid rgba(22, 35, 60, .08);
            border-radius: 18px;
            background: rgba(255, 255, 255, .87);
            box-shadow: 0 12px 32px rgba(31, 51, 89, .065)
        }

        .withdrawals-panel-head {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 5px
        }

        .withdrawals-panel-icon {
            display: grid;
            place-items: center;
            flex: none;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: rgba(56, 170, 180, .11);
            color: var(--teal-2);
            font-size: 19px
        }

        .withdrawals-panel h3 {
            color: var(--ink);
            font-size: 15px
        }

        .withdrawals-panel-intro {
            margin: 0 0 17px;
            color: var(--txt);
            font-size: 11px;
            line-height: 1.6
        }

        .withdrawals-form {
            display: grid;
            gap: 14px
        }

        .withdrawals-field {
            display: grid;
            gap: 6px;
            min-width: 0;
            color: var(--ink);
            font-size: 11px;
            font-weight: 700
        }

        .withdrawals-field input,
        .withdrawals-field textarea {
            display: block;
            width: 100%;
            min-width: 0;
            min-height: 44px;
            padding: 11px 12px;
            border: 1px solid rgba(22, 35, 60, .13);
            border-radius: 11px;
            background: #fff;
            color: var(--ink);
            font: 500 12px Poppins, sans-serif;
            transition: border-color .18s, box-shadow .18s
        }

        .withdrawals-field textarea {
            min-height: 100px;
            resize: vertical;
            line-height: 1.55
        }

        .withdrawals-field input:focus,
        .withdrawals-field textarea:focus {
            outline: 0;
            border-color: rgba(56, 170, 180, .75);
            box-shadow: 0 0 0 3px rgba(56, 170, 180, .13)
        }

        .withdrawals-field .site-select-wrap {
            margin-top: 1px
        }

        .withdrawals-field .site-select-trigger {
            min-height: 44px;
            border-radius: 11px;
            font-family: Poppins, sans-serif;
            font-size: 12px;
            font-weight: 500
        }

        .withdrawals-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 12px 16px;
            border: 0;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--teal), var(--teal-2));
            color: #fff;
            font: 700 11px Poppins, sans-serif;
            box-shadow: 0 8px 18px rgba(43, 127, 184, .2);
            cursor: pointer;
            transition: transform .18s, box-shadow .18s
        }

        .withdrawals-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 11px 23px rgba(43, 127, 184, .25)
        }

        .withdrawals-submit:disabled {
            opacity: .6;
            cursor: wait;
            transform: none
        }

        .withdrawals-note {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 14px;
            padding: 11px 12px;
            border: 1px solid rgba(56, 170, 180, .12);
            border-radius: 11px;
            background: rgba(56, 170, 180, .07);
            color: #386d7a;
            font-size: 10px;
            line-height: 1.6
        }

        .withdrawals-note i {
            flex: none;
            margin-top: 1px;
            color: var(--teal-2);
            font-size: 15px
        }

        .withdrawals-table-wrap {
            overflow: auto;
            border: 1px solid rgba(22, 35, 60, .07);
            border-radius: 13px;
            background: rgba(255, 255, 255, .65);
            -webkit-overflow-scrolling: touch
        }

        .withdrawals-table {
            width: 100%;
            min-width: 460px;
            border-collapse: collapse
        }

        .withdrawals-table th,
        .withdrawals-table td {
            padding: 12px 11px;
            border-bottom: 1px solid rgba(22, 35, 60, .07);
            text-align: left;
            font-size: 11px
        }

        .withdrawals-table th {
            background: rgba(56, 170, 180, .045);
            color: var(--txt);
            font-size: 9px;
            letter-spacing: .08em;
            text-transform: uppercase
        }

        .withdrawals-table tbody tr:last-child td {
            border-bottom: 0
        }

        .withdrawals-sales-table {
            min-width: 880px
        }

        .withdrawal-items {
            max-width: 230px;
            color: var(--txt);
            font-size: 10px;
            line-height: 1.55
        }

        .withdrawal-payment-ref {
            max-width: 180px;
            overflow-wrap: anywhere;
            color: var(--txt);
            font: 10px ui-monospace, Consolas, monospace
        }

        .withdrawals-amount {
            font-variant-numeric: tabular-nums;
            white-space: nowrap
        }

        .withdrawals-order-option {
            font-variant-numeric: tabular-nums
        }

        .withdrawal-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border-radius: 999px;
            background: #fff3d6;
            color: #946300;
            font-size: 10px;
            font-weight: 700;
            text-transform: capitalize;
            white-space: nowrap
        }

        .withdrawal-status:before {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            content: ""
        }

        .withdrawal-status.completed {
            background: #dcf7eb;
            color: #108255
        }

        .withdrawal-status.rejected {
            background: #ffe8e8;
            color: #b83232
        }

        .withdrawals-empty {
            padding: 34px 16px;
            text-align: center
        }

        .withdrawals-empty i {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            margin: 0 auto 10px;
            border-radius: 14px;
            background: #e3f5f3;
            color: var(--teal-2);
            font-size: 21px
        }

        .withdrawals-empty strong {
            display: block;
            color: var(--ink);
            font-size: 12px
        }

        .withdrawals-empty p {
            margin-top: 5px;
            color: var(--txt);
            font-size: 10px
        }

        @media(max-width:1000px) {
            .withdrawals-grid {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr)
            }
        }

        @media(max-width:850px) {
            .withdrawals-grid {
                grid-template-columns: 1fr
            }
        }

        @media(max-width:560px) {
            .withdrawals-main {
                padding-top: 18px !important
            }

            .withdrawals-topbar {
                gap: 10px
            }

            .withdrawals-title h1 {
                font-size: 18px
            }

            .withdrawals-title p {
                font-size: 10px
            }

            .withdrawals-head {
                align-items: flex-start;
                padding: 17px
            }

            .withdrawals-head-icon {
                width: 44px;
                height: 44px;
                border-radius: 13px;
                font-size: 21px
            }

            .withdrawals-head h2 {
                font-size: 20px
            }

            .withdrawals-messages-link {
                padding: 9px;
                font-size: 0
            }

            .withdrawals-messages-link i {
                font-size: 17px
            }

            .withdrawals-stats {
                grid-template-columns: 1fr;
                gap: 9px
            }

            .withdrawals-stat {
                padding: 12px
            }

            .withdrawals-panel {
                padding: 15px
            }
        }
    </style>
</head>

<body>
    <div class="scene" aria-hidden="true"><span class="orb orb-1"></span><span class="orb orb-2"></span><span class="orb orb-3"></span></div>
    <div class="layout">
        @include('user.include.sidebar')
        <main class="main withdrawals-main">
            <header class="withdrawals-topbar">
                <button class="hamburger" type="button" id="withdrawalsMenuToggle" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M3 6h18M3 12h18M3 18h18" />
                    </svg>
                </button>
                <div class="withdrawals-title">
                    <h1>Vendor payouts</h1>
                    <p>Request payment for your verified product sales.</p>
                </div>
            </header>
            <header class="withdrawals-head">
                <span class="withdrawals-head-icon" aria-hidden="true"><i class="ri-wallet-3-line"></i></span>
                <div class="withdrawals-head-copy"><span class="withdrawals-eyebrow"><i class="ri-sparkling-2-line"></i> Vendor workspace</span>
                    <h2>Sales &amp; payouts</h2>
                    <p>Only completed, payment-verified orders containing your approved products can be requested. The current marketplace commission is {{ number_format($commissionRate, 2) }}%.</p>
                </div>
                <a class="withdrawals-messages-link" href="{{ route('user.marketplace') }}"><i class="ri-message-3-line" aria-hidden="true"></i><span>Marketplace messages</span></a>
            </header>
            @if(session('success'))<div class="withdrawals-feedback" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="withdrawals-feedback errors" role="alert"><strong>Please check the request details:</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>@endif
            <section class="withdrawals-stats" aria-label="Withdrawal request summary">
                <div class="withdrawals-stat"><span class="withdrawals-stat-icon"><i class="ri-file-list-3-line" aria-hidden="true"></i></span>
                    <div><span>Total requests</span><strong>{{ $withdrawals->count() }}</strong></div>
                </div>
                <div class="withdrawals-stat"><span class="withdrawals-stat-icon"><i class="ri-time-line" aria-hidden="true"></i></span>
                    <div><span>Awaiting review</span><strong>{{ $withdrawals->where('status', 'pending')->count() }}</strong></div>
                </div>
                <div class="withdrawals-stat"><span class="withdrawals-stat-icon"><i class="ri-check-double-line" aria-hidden="true"></i></span>
                    <div><span>Completed</span><strong>{{ $withdrawals->where('status', 'completed')->count() }}</strong></div>
                </div>
            </section>
            <div class="withdrawals-grid">
                <section class="withdrawals-panel" aria-labelledby="withdrawalFormTitle">
                    <div class="withdrawals-panel-head"><span class="withdrawals-panel-icon"><i class="ri-add-circle-line" aria-hidden="true"></i></span>
                        <h3 id="withdrawalFormTitle">Request payment</h3>
                    </div>
                    <p class="withdrawals-panel-intro">Choose one of your paid orders. The payable amount is calculated from your sold products after any order discount and the current {{ number_format($commissionRate, 2) }}% marketplace commission; shipping and tax are excluded.</p>
                    <form class="withdrawals-form" method="POST" action="{{ route('user.marketplace.withdrawals.store') }}">
                        @csrf
                        <label class="withdrawals-field" for="withdrawalOrder">Paid order
                            <select id="withdrawalOrder" name="order_id" required>
                                <option value="">Choose an available paid sale</option>
                                @foreach($sales as $sale)
                                @if($sale['can_request'])
                                <option class="withdrawals-order-option" value="{{ $sale['order']->id }}" data-payout="{{ number_format($sale['payout']['payout_amount'], 2, '.', '') }}" @selected(old('order_id')==$sale['order']->id)>
                                    {{ $sale['order']->order_number }} · £{{ number_format($sale['payout']['payout_amount'], 2) }} payable
                                </option>
                                @endif
                                @endforeach
                            </select>
                        </label>
                        <div class="withdrawals-field">Calculated payout
                            <strong class="withdrawals-amount" id="calculatedPayout">Choose an order to see the verified amount.</strong>
                        </div>
                        <label class="withdrawals-field" for="withdrawalMethod">Payout method
                            <select id="withdrawalMethod" name="payout_method" required>
                                <option value="">Choose a method</option>@foreach(['Bank transfer','PayPal','Other'] as $method)<option value="{{ $method }}" @selected(old('payout_method')===$method)>{{ $method }}</option>@endforeach
                            </select>
                        </label>
                        <label class="withdrawals-field" for="withdrawalDetails">Payout destination
                            <textarea id="withdrawalDetails" name="payout_details" maxlength="2000" required placeholder="Provide the payout account or destination details">{{ old('payout_details') }}</textarea>
                        </label>
                        <button class="withdrawals-submit" type="submit" @disabled($sales->where('can_request', true)->isEmpty())><i class="ri-send-plane-line" aria-hidden="true"></i> Submit payout request</button>
                    </form>
                    <p class="withdrawals-note"><i class="ri-lock-2-line" aria-hidden="true"></i><span>Your request is linked to a verified customer payment transaction. Payout details are encrypted at rest and visible only to marketplace administrators. Admin approval is required before funds are sent.</span></p>
                </section>
            </div>
            <section class="withdrawals-panel" aria-labelledby="paidSalesTitle" style="margin-top: 20px;">
                <div class="withdrawals-panel-head"><span class="withdrawals-panel-icon"><i class="ri-shopping-bag-3-line" aria-hidden="true"></i></span>
                    <h3 id="paidSalesTitle">Your paid product sales</h3>
                </div>
                <p class="withdrawals-panel-intro">Payment reference is checked by the admin before a request can be accepted. Rejected requests can be submitted again.</p>
                <div class="withdrawals-table-wrap">
                    <table class="withdrawals-table withdrawals-sales-table">
                        <thead>
                            <tr>
                                <th>Paid order</th>
                                <th>Your products</th>
                                <th>Product sales</th>
                                <th>Coupon share</th>
                                <th>Commission</th>
                                <th>Your payout</th>
                                <th>Customer transaction</th>
                                <th>Request status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                            @php
                            $saleRequest = $sale['request'];
                            $saleStatus = $saleRequest?->status ?? 'Available';
                            $saleProducts = $sale['payout']['items']->map(fn ($item) => $item->product_name.' × '.$item->qty)->implode(', ');
                            @endphp
                            <tr>
                                <td><strong>{{ $sale['order']->order_number }}</strong><small class="submission-hint">{{ $sale['order']->paid_at?->format('M j, Y') }}</small></td>
                                <td class="withdrawal-items">{{ $saleProducts }}</td>
                                <td class="withdrawals-amount">£{{ number_format($sale['payout']['sales_amount'], 2) }}</td>
                                <td class="withdrawals-amount">£{{ number_format($sale['payout']['coupon_discount_share'], 2) }}</td>
                                <td class="withdrawals-amount">{{ number_format($sale['payout']['commission_rate'], 2) }}% · £{{ number_format($sale['payout']['commission_amount'], 2) }}</td>
                                <td class="withdrawals-amount"><strong>£{{ number_format($sale['payout']['payout_amount'], 2) }}</strong></td>
                                <td class="withdrawal-payment-ref">{{ $sale['order']->transaction_id }}</td>
                                <td><span class="withdrawal-status {{ $saleStatus }}">{{ $saleStatus }}</span>@if($saleRequest?->status === 'rejected')<small class="submission-hint">You may submit again</small>@endif</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8">
                                    <div class="withdrawals-empty"><i class="ri-wallet-3-line" aria-hidden="true"></i><strong>No paid marketplace sales yet</strong>
                                        <p>Your approved products' customer purchases will appear here after payment is confirmed.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="withdrawals-panel" aria-labelledby="withdrawalHistoryTitle" style="margin-top: 20px;">
                <div class="withdrawals-panel-head"><span class="withdrawals-panel-icon"><i class="ri-history-line" aria-hidden="true"></i></span>
                    <h3 id="withdrawalHistoryTitle">Request history</h3>
                </div>
                <p class="withdrawals-panel-intro">Track when each payment request was submitted and its latest status.</p>
                <div class="withdrawals-table-wrap">
                    <table class="withdrawals-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Requested</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td>{{ $withdrawal->order_number ?? 'Legacy request' }}</td>
                                <td>{{ $withdrawal->created_at->format('M j, Y') }}</td>
                                <td>£{{ number_format((float)$withdrawal->amount, 2) }}</td>
                                <td><span class="withdrawal-status {{ $withdrawal->status }}">{{ $withdrawal->status }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">
                                    <div class="withdrawals-empty"><i class="ri-wallet-3-line" aria-hidden="true"></i><strong>No payout requests yet</strong>
                                        <p>Request payment for an eligible paid sale above.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    <div class="overlay" id="overlay"></div>
    <script>
        (function() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('overlay');
            var toggle = document.getElementById('withdrawalsMenuToggle');
            if (!sidebar || !overlay || !toggle) return;

            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
            }
            toggle.addEventListener('click', function() {
                var open = !sidebar.classList.contains('open');
                sidebar.classList.toggle('open', open);
                overlay.classList.toggle('show', open);
                toggle.setAttribute('aria-expanded', String(open));
            });
            overlay.addEventListener('click', closeSidebar);
            sidebar.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', closeSidebar);
            });
            var orderSelect = document.getElementById('withdrawalOrder');
            var payoutOutput = document.getElementById('calculatedPayout');

            function updatePayoutAmount() {
                var selected = orderSelect.options[orderSelect.selectedIndex];
                payoutOutput.textContent = selected && selected.dataset.payout ?
                    '£' + Number(selected.dataset.payout).toFixed(2) :
                    'Choose an order to see the verified amount.';
            }
            orderSelect.addEventListener('change', updatePayoutAmount);
            updatePayoutAmount();
        })();
    </script>
</body>

</html>
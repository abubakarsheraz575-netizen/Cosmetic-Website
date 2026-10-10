<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Announcement Customers — Cosmetics Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style data-page>
        .audience-page{max-width:1450px;margin:auto;padding:27px}
        .audience-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px}
        .audience-head h1{margin:0;color:var(--text);font-size:23px;font-weight:800}
        .audience-head p{margin:5px 0 0;color:var(--muted);font-size:12px}
        .audience-back{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--text);font-size:11px;font-weight:700;text-decoration:none}
        .audience-card{overflow:hidden;border:1px solid var(--border);border-radius:16px;background:var(--card);box-shadow:var(--shadow)}
        .audience-summary{display:flex;align-items:center;flex-wrap:wrap;gap:10px;padding:16px 20px;border-bottom:1px solid var(--border);color:var(--muted);font-size:11px}
        .audience-summary strong{color:var(--text)}
        .audience-categories{display:flex;flex-wrap:wrap;gap:5px}
        .audience-category{padding:5px 8px;border-radius:20px;background:var(--lime-soft);color:var(--forest);font-size:10px;font-weight:800}
        .audience-table{width:100%;min-width:850px;border-collapse:collapse}
        .audience-table th{padding:12px 15px;background:var(--bg);color:var(--muted);font-size:9px;font-weight:800;letter-spacing:.1em;text-align:left;text-transform:uppercase}
        .audience-table td{padding:14px 15px;border-top:1px solid var(--border);color:var(--text);font-size:11px;vertical-align:top}
        .audience-table tbody tr:hover{background:rgba(62,197,224,.035)}
        .audience-name{font-weight:800}.audience-email{display:block;margin-top:4px;color:var(--muted)}
        .audience-order{font-weight:800;color:var(--forest)}
        .audience-items{display:grid;gap:5px}
        .audience-item{display:flex;align-items:center;gap:6px}
        .audience-item i{color:var(--forest);font-size:14px}
        .delivery-status{display:inline-flex;padding:5px 8px;border-radius:20px;background:#ecfdf3;color:#067647;font-size:9px;font-weight:800}
        .delivery-status.late{background:#fff1f0;color:#b42318}
        .delivery-status.delivered{background:#eff8ff;color:#175cd3}
        .audience-empty{padding:45px 20px;color:var(--muted);text-align:center}
        .audience-empty i{display:block;margin-bottom:8px;color:var(--lime);font-size:28px}
        .audience-pagination{padding:15px 20px;border-top:1px solid var(--border)}
        .audience-pagination nav{display:flex;justify-content:space-between;align-items:center;gap:12px}
        .audience-pagination nav>div:first-child{color:var(--muted);font-size:11px}
        .audience-pagination nav>div:last-child{display:flex;align-items:center;gap:4px}
        .audience-pagination a,.audience-pagination span[aria-current],.audience-pagination span[aria-disabled]{min-width:32px;min-height:32px;display:grid;place-items:center;padding:5px 8px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font-size:11px;text-decoration:none}
        .audience-pagination span[aria-current]{border-color:var(--forest);background:var(--forest);color:#fff}
        .audience-pagination span[aria-disabled]{color:var(--muted);opacity:.55}
        @media(max-width:700px){.audience-page{padding:18px 12px}.audience-head{align-items:flex-start;flex-direction:column}.audience-pagination nav{align-items:flex-start;flex-direction:column}.audience-pagination nav>div:last-child{flex-wrap:wrap}}
    </style>
    @include('admin.include.shell-layout')
</head>
<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="audience-page">
                <header class="audience-head">
                    <div>
                        <h1>Announcement Customers</h1>
                        <p>{{ $announcement->title }} · matching paid orders and their delivery status</p>
                    </div>
                    <a class="audience-back" href="{{ route('admin.announcements.manage') }}"><i class="ri-arrow-left-line"></i>Back to announcements</a>
                </header>
                <section class="audience-card">
                    <div class="audience-summary">
                        <i class="ri-time-line" aria-hidden="true"></i>
                        <span>Orders overdue are marked late after {{ $deliveryMaxDays }} days.</span>
                        <strong>{{ $orders->total() }} matching orders</strong>
                        <strong>Target categories</strong>
                        <div class="audience-categories">@foreach ($announcement->categories as $category)<span class="audience-category">{{ $category->name }}</span>@endforeach</div>
                    </div>
                    <div class="manage-scroll">
                        <table class="audience-table">
                            <thead><tr><th>Customer</th><th>Order</th><th>Matching products</th><th>Order status</th><th>Expected by</th></tr></thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    @php
                                        $expectedBy = $order->created_at->copy()->addDays($deliveryMaxDays);
                                        $isLate = $order->status !== 'delivered' && $expectedBy->isPast();
                                    @endphp
                                    <tr>
                                        <td><span class="audience-name">{{ $order->user?->name ?? $order->name }}</span><a class="audience-email" href="mailto:{{ $order->user?->email ?? $order->email }}">{{ $order->user?->email ?? $order->email }}</a></td>
                                        <td><span class="audience-order">{{ $order->order_number }}</span><span class="audience-email">{{ $order->created_at->format('M j, Y') }}</span></td>
                                        <td><div class="audience-items">@foreach ($order->items as $item)<span class="audience-item"><i class="ri-box-3-line"></i>{{ $item->product_name }} × {{ $item->qty }}</span>@endforeach</div></td>
                                        <td><span class="delivery-status {{ $order->status === 'delivered' ? 'delivered' : ($isLate ? 'late' : '') }}">{{ $isLate ? 'Late' : ucfirst($order->status) }}</span></td>
                                        <td>{{ $expectedBy->format('M j, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="audience-empty"><i class="ri-user-search-line"></i>No customers have a matching paid order yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($orders->hasPages())
                        <div class="audience-pagination">{{ $orders->links() }}</div>
                    @endif
                </section>
            </main>
        </div>
    </div>
</body>
</html>

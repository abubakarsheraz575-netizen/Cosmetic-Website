@extends('admin.layouts.ecommerce')

@section('title', 'E-commerce reports')

@section('content')
<style>
    .stats .stat > .stat-icon { display:grid; place-items:center; margin:0; line-height:1; }
    .stats .stat > .stat-icon i { display:block; line-height:1; }
    .charts .card { overflow:hidden; background:linear-gradient(160deg,var(--card),rgba(62,197,224,.025)); }
    .charts .card-title { display:flex; align-items:center; gap:9px; }
    .charts .card-title::before { content:""; width:7px; height:7px; flex:none; border-radius:50%; background:var(--accent); box-shadow:0 0 0 4px rgba(62,197,224,.12); }
</style>
<header class="page-head">
    <div><h1><i class="ri-line-chart-line"></i>E-commerce reports</h1><p>Live performance from orders, payments, and customer returns.</p></div>
    <a class="button" href="{{ route('admin.ecommerce.invoices') }}"><i class="ri-file-list-3-line"></i> View invoices</a>
</header>

<section class="stats">
    <article class="card stat"><span class="stat-icon"><i class="ri-money-pound-circle-line"></i></span><div><strong>£{{ number_format($metrics['revenue'], 2) }}</strong><span>Paid revenue</span></div></article>
    <article class="card stat"><span class="stat-icon amber"><i class="ri-shopping-bag-3-line"></i></span><div><strong>{{ number_format($metrics['orders']) }}</strong><span>Paid orders</span></div></article>
    <article class="card stat"><span class="stat-icon green"><i class="ri-funds-line"></i></span><div><strong>£{{ number_format($metrics['average'], 2) }}</strong><span>Average paid order</span></div></article>
    <article class="card stat"><span class="stat-icon violet"><i class="ri-arrow-go-back-line"></i></span><div><strong>{{ number_format($metrics['returns']) }}</strong><span>Return requests</span></div></article>
</section>

<section class="charts">
    <article class="card"><h2 class="card-title">Monthly revenue and orders</h2><div class="chart"><canvas id="revenueChart" aria-label="Monthly revenue and order chart"></canvas></div></article>
    <article class="card"><h2 class="card-title">Order status mix</h2><div class="chart"><canvas id="statusChart" aria-label="Order status chart"></canvas></div></article>
    <article class="card"><h2 class="card-title">Return requests</h2><div class="chart chart-empty-wrap"><canvas id="returnsChart" aria-label="Return request status chart"></canvas>@if($returnsByStatus->isEmpty())<div class="chart-empty-center" aria-hidden="true"><strong>No returns yet</strong><span>Requests will appear here</span></div>@endif</div></article>
</section>

<section class="card">
    <div class="toolbar"><h2 class="card-title" style="margin:0">Recent paid orders</h2><input type="search" data-table-search="reportsOrdersTable" placeholder="Search order, customer, or product..." aria-label="Search paid orders"><select data-table-status="reportsOrdersTable" aria-label="Filter paid orders"><option value="">All delivery statuses</option>@foreach(['pending','processing','shipped','delivered','cancelled'] as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach</select></div>
    <div class="table-wrap"><table id="reportsOrdersTable">
        <thead><tr><th>Order</th><th>Customer</th><th>Products</th><th>Ordered</th><th>Delivery</th><th>Items</th><th>Paid total</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($recentOrders as $order)
            <tr data-status="{{ $order->status }}">
                <td><strong>{{ $order->order_number }}</strong><small>INV-{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</small></td>
                <td>{{ $order->name }}<small>{{ $order->email }}</small></td>
                <td><div class="dt-products">@foreach($order->items as $item)<span class="dt-product-chip"><span class="product-thumb">@if($item->product?->images->first())<img src="{{ asset('storage/'.$item->product->images->first()->path) }}" alt="{{ $item->product_name }}" loading="lazy">@else{{ $item->emoji ?: '✦' }}@endif</span><span>{{ $item->product_name }} × {{ $item->qty }}</span></span>@foreach($item->returns as $return)<small>Return {{ $return->return_number }} · {{ $return->status }}</small>@endforeach @endforeach</div></td>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
                <td><span class="badge {{ $order->status }}">{{ $order->status }}</span></td>
                <td>{{ $order->items->sum('qty') }}</td>
                <td><strong>£{{ number_format($order->total, 2) }}</strong></td>
                <td><a class="button primary" href="{{ route('admin.ecommerce.orders.edit', $order) }}">View / edit</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="empty">No paid orders are available for the selected report period.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        const revenueCanvas = document.getElementById('revenueChart');
        if (!revenueCanvas) return;

        const chartSources = [
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
            'https://unpkg.com/chart.js@4.4.1/dist/chart.umd.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js'
        ];
        if (!window.__commerceChartsLifecycleBound) {
            window.__commerceChartsLifecycleBound = true;
            window.addEventListener('ajax:navigating', function () {
                if (window.Chart) {
                    document.querySelectorAll('.charts canvas').forEach(function (canvas) {
                        const existingChart = Chart.getChart(canvas);
                        if (existingChart) existingChart.destroy();
                    });
                }
                window.__commerceChartsInitializer = null;
            });
            window.addEventListener('ajax:page-ready', function () {
                if (typeof window.__commerceChartsInitializer === 'function') {
                    window.__commerceChartsInitializer();
                }
            });
        }
        function loadChartSource(index) {
            if (window.Chart) return Promise.resolve();
            if (index >= chartSources.length) return Promise.reject(new Error('Chart library could not be loaded from the available CDNs.'));
            return new Promise(function (resolve, reject) {
                const script = document.createElement('script');
                script.src = chartSources[index];
                script.onload = function () { window.Chart ? resolve() : loadChartSource(index + 1).then(resolve, reject); };
                script.onerror = function () { loadChartSource(index + 1).then(resolve, reject); };
                document.head.appendChild(script);
            });
        }
        function loadChart() {
            if (window.Chart) return Promise.resolve();
            if (!window.__commerceChartLibraryPromise) {
                window.__commerceChartLibraryPromise = loadChartSource(0).catch(function (error) {
                    window.__commerceChartLibraryPromise = null;
                    throw error;
                });
            }
            return window.__commerceChartLibraryPromise;
        }
        function renderCharts() {
            if (!revenueCanvas.isConnected || !window.Chart) return;
            const chartCanvases = [
                revenueCanvas,
                document.getElementById('statusChart'),
                document.getElementById('returnsChart')
            ];
            if (chartCanvases.every(function (canvas) { return canvas && Chart.getChart(canvas); })) return;
            chartCanvases.forEach(function (canvas) {
                const existingChart = canvas && Chart.getChart(canvas);
                if (existingChart) existingChart.destroy();
            });
            const chartText = getComputedStyle(document.body).getPropertyValue('--muted').trim();
            const chartBorder = getComputedStyle(document.body).getPropertyValue('--border').trim();
            const chartGrid = { color: chartBorder };
            new Chart(revenueCanvas, {
                type: 'line',
                data: { labels: @json($monthlyLabels), datasets: [
                    { label: 'Revenue (£)', data: @json($monthlyRevenue), borderColor: '#17739b', backgroundColor: 'rgba(62,197,224,.16)', fill: true, tension: .38, pointRadius: 3, pointHoverRadius: 6, borderWidth: 3, yAxisID: 'revenue' },
                    { label: 'Paid orders', data: @json($monthlyOrders), borderColor: '#e6a52b', backgroundColor: '#e6a52b', tension: .38, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2, yAxisID: 'orders' }
                ] },
                options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { labels: { usePointStyle: true, padding: 18, color: chartText } }, tooltip: { backgroundColor: '#102a3a', padding: 12, cornerRadius: 10, displayColors: true } }, scales: {
                    x: { ticks: { color: chartText }, grid: { display: false } },
                    revenue: { position: 'left', beginAtZero: true, ticks: { color: chartText }, grid: chartGrid },
                    orders: { position: 'right', beginAtZero: true, ticks: { color: chartText, precision: 0 }, grid: { display: false } }
                } }
            });
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: { labels: @json($statusLabels), datasets: [{ data: @json($statusCounts), backgroundColor: ['#e6a52b', '#3ec5e0', '#17739b', '#26a269', '#e05d5d'], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { color: chartText, usePointStyle: true, padding: 18, boxWidth: 10, boxHeight: 10 } }, tooltip: { backgroundColor: '#102a3a', padding: 12, cornerRadius: 10 } } }
            });
            const returnValues = @json($returnsByStatus->values());
            const returnLabels = @json($returnsByStatus->keys()->map(fn ($status) => ucfirst($status))->values());
            const hasReturns = returnValues.some(function (value) { return Number(value) > 0; });
            new Chart(document.getElementById('returnsChart'), {
                type: 'doughnut',
                data: { labels: hasReturns ? returnLabels : ['No return requests'], datasets: [{ data: hasReturns ? returnValues : [1], backgroundColor: hasReturns ? ['#e6a52b', '#17739b', '#e05d5d', '#26a269'] : ['#dce5ec'], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { color: chartText, usePointStyle: true, padding: 18, boxWidth: 10, boxHeight: 10 } }, tooltip: { backgroundColor: '#102a3a', padding: 12, cornerRadius: 10 } } }
            });
        }
        function initializeCharts() {
            return loadChart().then(renderCharts).catch(function (error) {
                if (!revenueCanvas.isConnected) return;
                console.error(error);
                document.querySelectorAll('.chart').forEach(function (chart) {
                    chart.innerHTML = '<p role="alert" class="chart-error">Charts are temporarily unavailable. Please refresh to retry.</p>';
                });
            });
        }
        window.__commerceChartsInitializer = initializeCharts;
        initializeCharts();
    })();
</script>
@endpush

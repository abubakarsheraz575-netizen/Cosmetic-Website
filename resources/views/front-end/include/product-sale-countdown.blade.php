@if($saleProduct->sale_price !== null
    && $saleProduct->sale_ends_at
    && (! $saleProduct->sale_starts_at || $saleProduct->sale_starts_at->lte(now()))
    && $saleProduct->sale_ends_at->gt(now()))
    <div class="product-sale-timer" data-sale-countdown-end="{{ $saleProduct->sale_ends_at->toIso8601String() }}" aria-label="Time remaining in this sale">
        <span class="product-sale-timer-label">Sale ends in</span>
        <span><b data-sale-days>00</b><small>DAYS</small></span>
        <span><b data-sale-hours>00</b><small>HRS</small></span>
        <span><b data-sale-minutes>00</b><small>MINS</small></span>
        <span><b data-sale-seconds>00</b><small>SECS</small></span>
    </div>
@endif

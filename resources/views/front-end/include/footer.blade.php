{{-- ============================================================
     FOOTER — DYNAMIC (admin Store Settings se)
     • Phone / Email / Address  → Store Contact Info
     • Social links             → Social Links (khali ho to '#')
     • Baaki design/structure   → 100% same
============================================================ --}}
@php
    /* 🛡️ SELF-SUFFICIENT — footer har page pe include hota hai,
       settings seedha DB se (route se $s pass karne ki zaroorat nahi) */
    $fPhone    = trim((string) \App\Models\Setting::get('site_phone'))    ?: '929-242-6868';
    $fEmail    = trim((string) \App\Models\Setting::get('site_email'))    ?: 'contact@info.com';
    $fAddress  = trim((string) \App\Models\Setting::get('site_address'))  ?: '123 Fifth Avenue, New York, NY 10160';
    $fInsta    = trim((string) \App\Models\Setting::get('social_instagram')) ?: '#';
    $fFacebook = trim((string) \App\Models\Setting::get('social_facebook'))  ?: '#';
    $fTwitter  = trim((string) \App\Models\Setting::get('social_twitter'))   ?: '#';
    $fYoutube  = trim((string) \App\Models\Setting::get('social_youtube'))   ?: '#';
    $fContactTitle = trim((string) \App\Models\Setting::get('footer_contact_title')) ?: 'Contact Details';
    $fLinksTitle = trim((string) \App\Models\Setting::get('footer_links_title')) ?: 'Quick Links';
    $fTagline = trim((string) \App\Models\Setting::get('footer_tagline')) ?: 'Thoughtful beauty, made for your everyday glow.';
    $fCopyright = trim((string) \App\Models\Setting::get('footer_copyright')) ?: 'Copyright © {year} Cosmetics Store | Powered by Cosmetics Store';
    $footerLinks = [
        1 => [trim((string) \App\Models\Setting::get('footer_link_1_label')) ?: 'Shop', trim((string) \App\Models\Setting::get('footer_link_1_url')) ?: route('shop')],
        2 => [trim((string) \App\Models\Setting::get('footer_link_2_label')) ?: 'Beauty Journal', trim((string) \App\Models\Setting::get('footer_link_2_url')) ?: route('blog.index')],
        3 => [trim((string) \App\Models\Setting::get('footer_link_3_label')) ?: 'FAQs', trim((string) \App\Models\Setting::get('footer_link_3_url')) ?: route('faqs.index')],
        4 => [trim((string) \App\Models\Setting::get('footer_link_4_label')) ?: 'About Us', trim((string) \App\Models\Setting::get('footer_link_4_url')) ?: route('about')],
        5 => [trim((string) \App\Models\Setting::get('footer_link_5_label')) ?: 'Testimonials', trim((string) \App\Models\Setting::get('footer_link_5_url')) ?: route('testimonials')],
    ];
    $safeFooterUrl = static function (string $url): string {
        return (str_starts_with($url, '/') && ! str_starts_with($url, '//')) || preg_match('/^https?:\/\//i', $url) === 1
            ? $url
            : '#';
    };
@endphp

<!-- ================= FOOTER ================= -->
<footer class="site-footer" id="contact">
    <div class="container">
        <div class="footer-main">
            <div>
                <h4>{{ $fContactTitle }}</h4>
                <ul class="contact-list">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M1.5 4.5c0-1.1.9-2 2-2h3c.9 0 1.7.6 1.9 1.5l.8 3.2c.2.8-.1 1.6-.8 2L6.8 10.5a14.4 14.4 0 006.7 6.7l1.3-1.6c.5-.6 1.3-.9 2-.7l3.2.8c.9.2 1.5 1 1.5 1.9v3c0 1.1-.9 2-2 2h-1C9.6 22.6 1.5 14.5 1.5 5.5v-1z" />
                        </svg>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $fPhone) }}"><span>{{ $fPhone }}</span></a>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M1.5 8.7V6c0-1.1.9-2 2-2h17c1.1 0 2 .9 2 2v2.7l-10.5 5L1.5 8.7z" />
                            <path d="M1.5 11.2V18c0 1.1.9 2 2 2h17c1.1 0 2-.9 2-2v-6.8l-10 4.8c-.3.1-.7.1-1 0l-10-4.8z" />
                        </svg>
                        <a href="mailto:{{ $fEmail }}"><span>{{ $fEmail }}</span></a>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2a7.5 7.5 0 00-7.5 7.5c0 5.2 6.4 11.8 6.9 12.3.3.3.9.3 1.2 0 .5-.5 6.9-7.1 6.9-12.3A7.5 7.5 0 0012 2zm0 10.5a3 3 0 110-6 3 3 0 010 6z" />
                        </svg>
                        <span>{{ $fAddress }}</span>
                    </li>
                </ul>
            </div>

            <div class="footer-brand">
                <a href="/" class="footer-logo">Cosmetics</a>
                <p class="footer-tagline">{{ $fTagline }}</p>
            </div>

            <div class="quick-links">
                <h4>{{ $fLinksTitle }}</h4>
                <ul>
                    @foreach($footerLinks as [$footerLabel, $footerUrl])
                    <li><a href="{{ $safeFooterUrl($footerUrl) }}">{{ $footerLabel }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>{{ str_replace('{year}', date('Y'), $fCopyright) }}</p>
            <div class="socials">
                <a href="{{ $fYoutube }}" {{ $fYoutube !== '#' ? 'target="_blank" rel="noopener"' : '' }} aria-label="YouTube">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M23.5 6.2a3 3 0 00-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 00.5 6.2 31.3 31.3 0 000 12a31.3 31.3 0 00.5 5.8 3 3 0 002.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 002.1-2.1A31.3 31.3 0 0024 12a31.3 31.3 0 00-.5-5.8zM9.6 15.6V8.4L15.8 12l-6.2 3.6z" /></svg>
                </a>
                <a href="{{ $fInsta }}" {{ $fInsta !== '#' ? 'target="_blank" rel="noopener"' : '' }} aria-label="Instagram">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.8.1 1.2.1 1.8.2 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.2.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 1.2-.2 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.2.1-1.6.1-4.8.1s-3.6 0-4.8-.1c-1.2-.1-1.8-.2-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2-.1-1.2-.1-1.6-.1-4.8s0-3.6.1-4.8c.1-1.2.2-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2zm0 1.8c-3.1 0-3.5 0-4.7.1-1.1.1-1.5.2-1.8.3-.5.2-.8.4-1.1.7-.3.3-.5.6-.7 1.1-.1.3-.3.7-.3 1.8-.1 1.2-.1 1.6-.1 4.7s0 3.5.1 4.7c.1 1.1.2 1.5.3 1.8.2.5.4.8.7 1.1.3.3.6.5 1.1.7.3.1.7.3 1.8.3 1.2.1 1.6.1 4.7.1s3.5 0 4.7-.1c1.1-.1 1.5-.2 1.8-.3.5-.2.8-.4 1.1-.7.3-.3.5-.6.7-1.1.1-.3.3-.7.3-1.8.1-1.2.1-1.6.1-4.7s0-3.5-.1-4.7c-.1-1.1-.2-1.5-.3-1.8-.2-.5-.4-.8-.7-1.1-.3-.3-.6-.5-1.1-.7-.3-.1-.7-.3-1.8-.3-1.2-.1-1.6-.1-4.7-.1zm0 3.1a4.9 4.9 0 110 9.8 4.9 4.9 0 010-9.8zm0 8.1a3.2 3.2 0 100-6.4 3.2 3.2 0 000 6.4zm6.3-8.3a1.1 1.1 0 11-2.3 0 1.1 1.1 0 012.3 0z" /></svg>
                </a>
                <a href="{{ $fFacebook }}" {{ $fFacebook !== '#' ? 'target="_blank" rel="noopener"' : '' }} aria-label="Facebook">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 21v-8.2h2.8l.4-3.2h-3.2V7.5c0-.9.3-1.6 1.6-1.6h1.7V3.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.3H7.3v3.2h2.8V21h3.4z" /></svg>
                </a>
                <a href="{{ $fTwitter }}" {{ $fTwitter !== '#' ? 'target="_blank" rel="noopener"' : '' }} aria-label="Twitter">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 5.9c-.8.4-1.6.6-2.5.7a4.3 4.3 0 001.9-2.4c-.8.5-1.7.9-2.7 1.1a4.3 4.3 0 00-7.3 3.9A12.2 12.2 0 013.1 4.7a4.3 4.3 0 001.3 5.7c-.7 0-1.4-.2-1.9-.5v.1c0 2.1 1.5 3.8 3.4 4.2-.6.2-1.3.2-1.9.1a4.3 4.3 0 004 3 8.6 8.6 0 01-5.3 1.8c-.3 0-.7 0-1-.1a12.1 12.1 0 006.6 1.9c7.9 0 12.2-6.5 12.2-12.2v-.6c.8-.6 1.5-1.3 2-2.2z" /></svg>
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- ================= FLOATING CART ================= -->
<button type="button" class="floating-cart" aria-label="Open cart">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
    </svg>
    <span class="cart-badge">{{ collect(session('cart', []))->sum('qty') }}</span>
</button>
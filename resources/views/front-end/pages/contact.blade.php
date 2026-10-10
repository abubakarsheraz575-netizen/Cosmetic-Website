{{-- ============================================================
     CONTACT PAGE — DYNAMIC (Store Settings se) + AJAX form with POST fallback
============================================================ --}}
@include('front-end.include.header')
@include('front-end.include.loader')
@php
    $tel = preg_replace('/[^0-9+]/', '', (string) $s['site_phone']);
    $sundayClosed = str_contains(strtolower((string) $s['hours_sunday']), 'clos');
@endphp
<style>
    /* ═══════════ PAGE HERO ═══════════ */
    .page-hero {
        position: relative;
        min-height: 46vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        background:linear-gradient(115deg, rgba(9, 52, 58, .88) 0%, rgba(24, 116, 126, .72) 50%, rgba(111, 211, 218, .55) 100%);
    }
    .ph-inner { position: relative; z-index: 2; padding: 150px 20px 80px; }
    .ph-title { font-size: clamp(38px, 6vw, 62px); font-weight: 300; letter-spacing: 4px; text-transform: uppercase; color: #fff; animation: heroUp .9s cubic-bezier(.22,1,.36,1) both; }
    .ph-crumb { margin-top: 14px; font-family: 'Montserrat', sans-serif; font-size: 12px; letter-spacing: 2.5px; text-transform: uppercase; color: rgba(255,255,255,.85); animation: heroUp .9s cubic-bezier(.22,1,.36,1) .12s both; }
    .ph-crumb a { color: #a9e8ee; }
    .ph-crumb a:hover { color: #fff; }

    @keyframes heroUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: none; } }

    /* ═══════════ SPLIT TOP (note + image) ═══════════ */
    .contact-top { position: relative; background: var(--bg); }
    .ct-grid { display: grid; grid-template-columns: 1fr 1fr; min-height: 540px; }

    .ct-note {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 60px 46px 180px;
        overflow: hidden;
    }
    .ct-ghost {
        position: absolute;
        top: 6%;
        left: 0;
        font-family: 'Allura', cursive;
        font-size: clamp(110px, 13vw, 200px);
        line-height: 1;
        color: rgba(56, 170, 180, .08);
        pointer-events: none;
        user-select: none;
        white-space: nowrap;
    }
    .ct-note-in { position: relative; z-index: 2; max-width: 320px; }
    .pulse-dot { display: inline-flex; align-items: center; gap: 9px; font-family: 'Montserrat', sans-serif; font-size: 11.5px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; color: var(--teal); margin-bottom: 10px; }
    .pulse-dot i { width: 8px; height: 8px; border-radius: 50%; background: #2bb673; box-shadow: 0 0 0 4px rgba(43, 182, 115, .18); animation: pulseDot 1.8s infinite; }
    @keyframes pulseDot { 0%, 100% { box-shadow: 0 0 0 4px rgba(43,182,115,.18); } 50% { box-shadow: 0 0 0 9px rgba(43,182,115,.05); } }
    .ct-note-in p { font-size: 13.5px; color: #8a9096; }

    .ct-img { position: relative; overflow: hidden; min-height: 420px; }
    .ct-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 5s ease; }
    .contact-top:hover .ct-img img { transform: scale(1.06); }

    /* ═══════════ FORM — flow-based, NO overlap bugs ═══════════ */
    .form-sec { background: var(--bg); padding: 0 20px 100px; }
    .form-card {
        position: relative;
        z-index: 5;
        max-width: 470px;
        margin: -320px auto 0;
        background: #fff;
        padding: 46px 44px 42px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, .16);
        border-top: 4px solid var(--teal);
        animation: heroUp 1s cubic-bezier(.22, 1, .36, 1) .2s both;
    }
    .form-card::before {
        content: 'hello';
        position: absolute;
        top: 18px;
        right: 30px;
        font-family: 'Allura', cursive;
        font-size: 44px;
        line-height: 1;
        color: rgba(56, 170, 180, .14);
        pointer-events: none;
    }
    .f-tag { color: var(--teal); font-family: 'Montserrat', sans-serif; font-size: 11.5px; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; text-align: center; }
    .f-title { text-align: center; font-size: 26px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; margin: 8px 0 28px; }

    .f-field { margin-bottom: 18px; }
    .f-field label { display: block; font-family: 'Montserrat', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #7d838a; margin-bottom: 7px; }
    .f-field label i { color: #e0566e; font-style: normal; }
    .f-field input, .f-field textarea {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #dde1e4;
        background: #fbfcfc;
        font-family: inherit;
        font-size: 14px;
        color: var(--dark);
        outline: none;
        transition: border-color .3s, box-shadow .3s, background .3s;
    }
    .f-field textarea { resize: vertical; min-height: 110px; }
    .f-field input:focus, .f-field textarea:focus { border-color: var(--teal); background: #fff; box-shadow: 0 0 0 4px rgba(56, 170, 180, .13); }
    .f-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .f-send { width: 100%; margin-top: 6px; padding: 15px; border: none; background: var(--teal); color: #fff; font-family: 'Montserrat', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; cursor: pointer; transition: all .35s; display: flex; align-items: center; justify-content: center; gap: 10px; }
    .f-send:hover { background: var(--teal-dark); box-shadow: 0 12px 26px rgba(56,170,180,.4); transform: translateY(-2px); }
    .f-send:disabled { opacity: .7; cursor: wait; transform: none; }

    .form-ok { display: none; margin-top: 16px; padding: 12px 14px; background: rgba(43, 182, 115, .08); border: 1px solid rgba(43, 182, 115, .4); color: #1f9d63; font-size: 13px; text-align: center; animation: heroUp .5s ease both; }
    .form-ok.show { display: block; }
    .form-err { display: block; margin-top: 16px; padding: 12px 14px; background: rgba(224, 86, 110, .08); border: 1px solid rgba(224, 86, 110, .4); color: #c23a52; font-size: 13px; text-align: center; animation: heroUp .5s ease both; word-break: break-word; }

    /* ═══════════ QUICK CARDS ═══════════ */
    .qc-sec { padding: 0 0 30px; background: var(--bg); }
    .qc-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
    .qc-card {
        background: #fff;
        text-align: center;
        padding: 42px 30px 38px;
        box-shadow: 0 14px 40px rgba(0, 0, 0, .06);
        border-bottom: 3px solid transparent;
        transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease;
    }
    .qc-card:hover { transform: translateY(-8px); border-bottom-color: var(--teal); box-shadow: 0 26px 60px rgba(0, 0, 0, .11); }
    .qc-icon { width: 72px; height: 72px; border: 1px solid var(--teal); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: var(--teal); transition: all .35s; }
    .qc-card:hover .qc-icon { background: var(--teal); color: #fff; box-shadow: 0 12px 30px rgba(56,170,180,.35); transform: rotate(8deg); }
    .qc-card h3 { font-size: 16px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px; }
    .qc-card p { font-size: 13.5px; }
    .qc-card a { color: var(--teal); font-weight: 600; }
    .qc-card a:hover { color: var(--teal-dark); }

    /* ═══════════ INFO + MAP ═══════════ */
    .info-sec { position: relative; padding: 80px 0 95px; background: #fff; overflow: hidden; }
    .info-ghost-img {
        position: absolute;
        right: -60px;
        bottom: -80px;
        width: min(460px, 42vw);
        opacity: .1;
        pointer-events: none;
        user-select: none;
        -webkit-mask-image: radial-gradient(ellipse at center, #000 30%, transparent 72%);
        mask-image: radial-gradient(ellipse at center, #000 30%, transparent 72%);
    }
    .info-grid { position: relative; z-index: 2; display: grid; grid-template-columns: .95fr 1.05fr; gap: 50px; align-items: start; }

    .info-card { background: var(--bg); padding: 44px 40px; }
    .info-card h2 { font-size: 24px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 12px; }
    .info-card > p { font-size: 14px; margin-bottom: 26px; }

    .hours { margin: 6px 0 30px; }
    .hours li { display: flex; justify-content: space-between; align-items: center; padding: 10px 2px; border-bottom: 1px dashed #d8dcde; font-size: 13.5px; }
    .hours li:last-child { border-bottom: none; }
    .hours li span { font-family: 'Montserrat', sans-serif; font-weight: 600; color: var(--dark); }
    .hours li span:last-child { color: var(--teal); font-weight: 600; }
    .hours li.closed span:last-child { color: #e0566e; }

    .info-card h4 { font-size: 12.5px; font-weight: 600; letter-spacing: 2.5px; text-transform: uppercase; margin-bottom: 14px; }
    .info-socials { display: flex; gap: 10px; }
    .info-socials a { width: 38px; height: 38px; border-radius: 50%; border: 1px solid #cfd4d7; display: flex; align-items: center; justify-content: center; color: #555; transition: all .3s; }
    .info-socials a:hover { background: var(--teal); border-color: var(--teal); color: #fff; transform: translateY(-3px); }

    .detail-list { margin-bottom: 28px; }
    .detail-list li { display: flex; align-items: flex-start; gap: 16px; padding: 14px 0; border-bottom: 1px solid #eef0f1; }
    .detail-list li:last-child { border-bottom: none; }
    .d-ic { width: 46px; height: 46px; min-width: 46px; border-radius: 13px; background: rgba(56, 170, 180, .1); color: var(--teal); display: flex; align-items: center; justify-content: center; transition: all .3s; }
    .detail-list li:hover .d-ic { background: var(--teal); color: #fff; box-shadow: 0 8px 20px rgba(56,170,180,.35); }
    .detail-list b { display: block; font-family: 'Montserrat', sans-serif; font-size: 13px; color: var(--dark); letter-spacing: .5px; }
    .detail-list span { font-size: 13.5px; color: #7d838a; }

    .map-frame { position: relative; border: 8px solid #fff; box-shadow: 0 24px 60px rgba(0, 0, 0, .12); overflow: hidden; background: #e8edf0; }
    .map-frame iframe { display: block; width: 100%; height: 320px; border: 0; filter: grayscale(.55) contrast(1.05); transition: filter .5s ease; }
    .map-frame:hover iframe { filter: grayscale(0); }
    .map-pin { position: absolute; top: 18px; left: 18px; background: rgba(255, 255, 255, .95); padding: 9px 16px; font-family: 'Montserrat', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--teal); box-shadow: 0 10px 24px rgba(0, 0, 0, .14); pointer-events: none; }

    /* ═══════════ RESPONSIVE ═══════════ */
    @media (max-width: 991px) {
        .ct-grid { grid-template-columns: 1fr; min-height: 0; }
        .ct-img { order: -1; min-height: 300px; }
        .ct-note { padding: 34px 24px 40px; }
        .ct-note-in { max-width: 100%; }
        .form-card { margin-top: -80px; }
        .qc-grid { grid-template-columns: 1fr; max-width: 440px; margin: 0 auto; }
        .info-grid { grid-template-columns: 1fr; }
        .info-ghost-img { display: none; }
    }
    @media (max-width: 575px) {
        .f-row { grid-template-columns: 1fr; }
        .form-card { padding: 36px 24px 32px; }
        .info-card { padding: 32px 24px; }
    }
</style>

<main>
    <!-- ================= PAGE HERO ================= -->
    <section class="page-hero" @if($pageHeader['image']) style="background-image:linear-gradient(115deg, rgba(9, 52, 58, .88) 0%, rgba(24, 116, 126, .72) 50%, rgba(111, 211, 218, .55) 100%), url('{{ $pageHeader['image'] }}'); background-position:center, center; background-size:cover;" @endif>
        <div class="ph-inner">
            <h1 class="ph-title">{{ $pageHeader['title'] }}</h1>
            <p class="ph-crumb"><a href="{{ url('/') }}">Home</a> &nbsp;/&nbsp; Get in Touch</p>
        </div>
    </section>

    <!-- ================= SPLIT TOP (note + image) ================= -->
    <section class="contact-top">
        <div class="ct-grid">
            <div class="ct-note">
                <span class="ct-ghost" aria-hidden="true">Say Hello</span>
                <div class="ct-note-in">
                    <p class="pulse-dot"><i></i> {{ $contactPage['contact_intro_label'] }}</p>
                    <p>{{ $contactPage['contact_intro_text'] }}</p>
                </div>
            </div>
            <div class="ct-img">
                <img src="{{ $contactImages['contact_image'] }}" alt="{{ $contactPage['contact_image_alt'] }}">
            </div>
        </div>
    </section>

    <!-- ================= FORM (real POST — admin ko email jata hai) ================= -->
    <section class="form-sec">
        <form class="form-card" id="contactForm" action="{{ route('contact.send') }}" method="POST">
            @csrf
            <p class="f-tag">{{ $contactPage['contact_form_tag'] }}</p>
            <h2 class="f-title">{{ $contactPage['contact_form_title'] }}</h2>

            @if (session('contact_success'))
            <p class="form-ok show">{{ session('contact_success') }}</p>
            @endif

            @if (session('contact_error'))
            <p class="form-err">{{ session('contact_error') }}</p>
            @elseif ($errors->any())
            <p class="form-err">{{ $errors->first() }}</p>
            @endif

            <div class="f-row">
                <div class="f-field">
                    <label for="cName">Name <i>*</i></label>
                    <input id="cName" type="text" name="name" placeholder="Your name" required value="{{ old('name') }}">
                </div>
                <div class="f-field">
                    <label for="cEmail">Email <i>*</i></label>
                    <input id="cEmail" type="email" name="email" placeholder="you@example.com" required value="{{ old('email') }}">
                </div>
            </div>

            <div class="f-field">
                <label for="cSubject">Subject</label>
                <input id="cSubject" type="text" name="subject"  value="{{ old('subject') }}">
            </div>

            <div class="f-field">
                <label for="cMsg">Message <i>*</i></label>
                <textarea id="cMsg" name="message"  required>{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="f-send">
                Send Message
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>

            <p class="form-ok" id="formOk" aria-live="polite" style="display:none"></p>
        </form>
    </section>

    <!-- ================= QUICK CARDS (DYNAMIC) ================= -->
    <section class="qc-sec">
        <div class="container qc-grid">
            <div class="qc-card reveal">
                <div class="qc-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <h3>Visit the Store</h3>
                <p>{{ $s['site_address'] }}</p>
            </div>
            <div class="qc-card reveal" style="transition-delay:.1s">
                <div class="qc-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.4 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.6 1.9z"/>
                    </svg>
                </div>
                <h3>Call Us Anytime</h3>
                <p>Weekdays: {{ $s['hours_weekdays'] }}<br><a href="tel:{{ $tel }}">{{ $s['site_phone'] }}</a></p>
            </div>
            <div class="qc-card reveal" style="transition-delay:.2s">
                <div class="qc-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>
                    </svg>
                </div>
                <h3>Email Us</h3>
                <p>We reply within 24 hours<br><a href="mailto:{{ $s['site_email'] }}">{{ $s['site_email'] }}</a></p>
            </div>
        </div>
    </section>

    <!-- ================= INFO + MAP (DYNAMIC) ================= -->
    <section class="info-sec">
        <img class="info-ghost-img" src="{{ $contactImages['contact_info_image'] }}" alt="" aria-hidden="true">
        <div class="container">
            <div class="info-grid">

                <div class="info-card reveal">
                    <h2>{{ $contactPage['contact_info_title'] }}</h2>
                    <p>{{ $contactPage['contact_info_text'] }}</p>

                    <h4>Opening Hours</h4>
                    <ul class="hours">
                        <li><span>Monday — Friday</span><span>{{ $s['hours_weekdays'] }}</span></li>
                        <li><span>Saturday</span><span>{{ $s['hours_saturday'] }}</span></li>
                        <li class="{{ $sundayClosed ? 'closed' : '' }}"><span>Sunday</span><span>{{ $s['hours_sunday'] }}</span></li>
                    </ul>

                    <h4>Follow Us</h4>
                    <div class="info-socials">
                        <a href="{{ $s['social_instagram'] }}" aria-label="Instagram" target="_blank" rel="noopener">
                            <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.8.1 1.2.1 1.8.2 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.2.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 1.2-.2 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.2.1-1.6.1-4.8.1s-3.6 0-4.8-.1c-1.2-.1-1.8-.2-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2-.1-1.2-.1-1.6-.1-4.8s0-3.6.1-4.8c.1-1.2.2-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2zm0 4.9a4.9 4.9 0 110 9.8 4.9 4.9 0 010-9.8zm0 8.1a3.2 3.2 0 100-6.4 3.2 3.2 0 000 6.4zm6.3-8.3a1.1 1.1 0 11-2.3 0 1.1 1.1 0 012.3 0z"/></svg>
                        </a>
                        <a href="{{ $s['social_facebook'] }}" aria-label="Facebook" target="_blank" rel="noopener">
                            <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 21v-8.2h2.8l.4-3.2h-3.2V7.5c0-.9.3-1.6 1.6-1.6h1.7V3.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.3H7.3v3.2h2.8V21h3.4z"/></svg>
                        </a>
                        <a href="{{ $s['social_twitter'] }}" aria-label="Twitter" target="_blank" rel="noopener">
                            <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 5.9c-.8.4-1.6.6-2.5.7a4.3 4.3 0 001.9-2.4c-.8.5-1.7.9-2.7 1.1a4.3 4.3 0 00-7.3 3.9A12.2 12.2 0 013.1 4.7a4.3 4.3 0 001.3 5.7c-.7 0-1.4-.2-1.9-.5v.1c0 2.1 1.5 3.8 3.4 4.2-.6.2-1.3.2-1.9.1a4.3 4.3 0 004 3 8.6 8.6 0 01-5.3 1.8c-.3 0-.7 0-1-.1a12.1 12.1 0 006.6 1.9c7.9 0 12.2-6.5 12.2-12.2v-.6c.8-.6 1.5-1.3 2-2.2z"/></svg>
                        </a>
                        <a href="{{ $s['social_youtube'] }}" aria-label="YouTube" target="_blank" rel="noopener">
                            <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M23.5 6.2a3 3 0 00-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 00.5 6.2 31.3 31.3 0 000 12a31.3 31.3 0 00.5 5.8 3 3 0 002.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 002.1-2.1A31.3 31.3 0 0024 12a31.3 31.3 0 00-.5-5.8zM9.6 15.6V8.4L15.8 12l-6.2 3.6z"/></svg>
                        </a>
                    </div>
                </div>

                <div class="reveal" style="transition-delay:.12s">
                    <ul class="detail-list">
                        <li>
                            <span class="d-ic">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            <div><b>Our Address</b><span>{{ $s['site_address'] }}</span></div>
                        </li>
                        <li>
                            <span class="d-ic">
                                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.4 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.6 1.9z"/></svg>
                            </span>
                            <div><b>Phone</b><span>{{ $s['site_phone'] }}</span></div>
                        </li>
                        <li>
                            <span class="d-ic">
                                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                            </span>
                            <div><b>Email</b><span>{{ $s['site_email'] }}</span></div>
                        </li>
                    </ul>

                    <div class="map-frame">
                        <span class="map-pin">📍 Find Us</span>
                        <iframe
                            src="https://maps.google.com/maps?q={{ urlencode($s['site_map_query']) }}&t=&z=15&ie=UTF8&iwloc=&output=embed"
                            loading="eager"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Our store location">
                        </iframe>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ================= NEWSLETTER ================= -->
  
</main>

@include('front-end.include.newsletter')
@include('front-end.include.footer')
@include('front-end.include.js')

<script>
    (function () {
        var form = document.getElementById('contactForm');
        if (!form) return;

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var button = form.querySelector('.f-send');
            var ok = document.getElementById('formOk');
            var error = form.querySelector('.form-err');
            if (error) error.remove();
            ok.style.display = 'none';
            button.disabled = true;
            button.firstChild.textContent = 'Sending… ';

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.json().catch(function () {
                        return { status: 'error', message: 'We could not process your message. Please try again.' };
                    }).then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (!result.ok || result.data.status !== 'success') {
                        var errors = result.data.errors ? Object.keys(result.data.errors).map(function (key) {
                            return result.data.errors[key][0];
                        }) : [];
                        throw new Error(errors[0] || result.data.message || 'Your message could not be sent.');
                    }

                    form.reset();
                    ok.textContent = '✓ ' + result.data.message;
                    ok.style.display = 'block';
                    ok.classList.add('show');
                })
                .catch(function (err) {
                    var message = document.createElement('p');
                    message.className = 'form-err';
                    message.setAttribute('role', 'alert');
                    message.textContent = err.message || 'Something went wrong. Please try again.';
                    button.parentNode.insertBefore(message, button.nextSibling);
                })
                .finally(function () {
                    button.disabled = false;
                    button.firstChild.textContent = 'Send Message ';
                });
        });
    })();
</script>
<section class="beauty-newsletter" aria-labelledby="beautyNewsletterTitle">
    <div class="beauty-newsletter-inner">
        <div class="newsletter-copy">
            <span class="newsletter-eyebrow"><i class="ri-sparkling-2-line"></i> A little beauty in your inbox</span>
            <h2 id="beautyNewsletterTitle">Sign up for the<br>makeup fan club.</h2>
            <p>Fresh routines, thoughtful product notes, and occasional treats — only the good stuff.</p>
        </div>
        <form class="beauty-newsletter-form" method="POST" action="{{ route('newsletter.subscribe') }}">
            @csrf
            <input type="hidden" name="source" value="{{ request()->path() ?: 'home' }}">
            <label class="newsletter-email-label" for="beautyNewsletterEmail">Your email address</label>
            <div class="newsletter-input-row">
                <input id="beautyNewsletterEmail" type="email" name="email" required maxlength="254" autocomplete="email" placeholder="you@example.com" aria-describedby="beautyNewsletterConsent beautyNewsletterMessage">
                <button type="submit"><span>Subscribe</span><i class="ri-arrow-right-line" aria-hidden="true"></i></button>
            </div>
            <label class="newsletter-consent" id="beautyNewsletterConsent"><input type="checkbox" name="consent" value="1" required><span>I agree to receive Cosmetics email updates. I can unsubscribe at any time.</span></label>
            <p class="newsletter-message" id="beautyNewsletterMessage" role="status" aria-live="polite"></p>
            @if(session('success'))<p class="newsletter-message is-success" role="status">{{ session('success') }}</p>@endif
        </form>
    </div>
</section>
<div class="newsletter-modal" id="newsletterSuccessModal" hidden>
    <div class="newsletter-modal-backdrop" data-newsletter-close></div>
    <section class="newsletter-modal-card" role="dialog" aria-modal="true" aria-labelledby="newsletterSuccessTitle" aria-describedby="newsletterSuccessDescription">
        <button class="newsletter-modal-close" type="button" aria-label="Close confirmation" data-newsletter-close><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
        <span class="newsletter-success-icon"><svg viewBox="0 0 48 48" aria-hidden="true"><rect x="6" y="10" width="29" height="23" rx="5"/><path d="m8 14 12.5 9L33 14"/><circle cx="35" cy="32" r="9"/><path d="m31.5 32 2.3 2.3 4.3-4.6"/></svg></span>
        <span class="newsletter-modal-eyebrow">A lovely choice</span>
        <h2 id="newsletterSuccessTitle">You're on the list.</h2>
        <p id="newsletterSuccessDescription">Thanks for joining the Cosmetics fan club. A little more beauty is headed your way.</p>
        <strong class="newsletter-confirm-email" id="newsletterConfirmEmail"></strong>
        <button class="newsletter-modal-done" type="button" data-newsletter-close>Lovely, thank you</button>
    </section>
</div>
<style>
    .beauty-newsletter{position:relative;isolation:isolate;overflow:hidden;padding:clamp(44px,7vw,84px) 22px;background:radial-gradient(circle at 8% 15%,rgba(190,229,219,.7),transparent 28%),radial-gradient(circle at 96% 90%,rgba(227,199,183,.48),transparent 27%),linear-gradient(120deg,#f4f7f4,#e8f0ed 48%,#f6f1ec);color:#203c39}
    .beauty-newsletter:before,.beauty-newsletter:after{position:absolute;z-index:-1;width:230px;height:230px;border:1px solid rgba(56,129,116,.12);border-radius:50%;content:""}
    .beauty-newsletter:before{top:-160px;left:7%}.beauty-newsletter:after{right:12%;bottom:-185px}
    .beauty-newsletter-inner{display:grid;grid-template-columns:minmax(0,.9fr) minmax(320px,1fr);gap:clamp(30px,7vw,100px);align-items:center;width:min(1030px,100%);margin:0 auto}
    .newsletter-eyebrow{display:inline-flex;align-items:center;gap:7px;color:#39877b;font:700 10px Montserrat,sans-serif;letter-spacing:1.7px;text-transform:uppercase}
    .newsletter-copy h2{margin:12px 0;color:#173d38;font:500 clamp(26px,4vw,42px)/1.16 Montserrat,sans-serif;letter-spacing:-1px;text-transform:uppercase}
    .newsletter-copy p{max-width:430px;color:#6e817c;font-size:13px;line-height:1.8}
    .beauty-newsletter-form{min-width:0}.newsletter-email-label{display:block;margin-bottom:8px;color:#405b56;font:700 10px Montserrat,sans-serif;letter-spacing:1px;text-transform:uppercase}
    .newsletter-input-row{display:flex;min-height:55px;padding:5px;border:1px solid rgba(75,120,111,.24);border-radius:12px;background:rgba(255,255,255,.85);box-shadow:0 12px 34px rgba(45,83,75,.08)}
    .newsletter-input-row input{width:100%;min-width:0;padding:0 13px;border:0;outline:0;background:transparent;color:#203c39;font:13px 'Open Sans',sans-serif}
    .newsletter-input-row button{display:inline-flex;flex:none;align-items:center;justify-content:center;gap:10px;padding:0 19px;border:0;border-radius:8px;background:#1b6864;color:#fff;font:700 10px Montserrat,sans-serif;letter-spacing:.8px;text-transform:uppercase;cursor:pointer;transition:background .2s,transform .2s}
    .newsletter-input-row button:hover{transform:translateY(-1px);background:#124e4b}.newsletter-input-row button:disabled{opacity:.65;cursor:wait;transform:none}
    .newsletter-consent{display:flex;align-items:flex-start;gap:9px;margin-top:13px;color:#75847f;font-size:10px;line-height:1.55}.newsletter-consent input{width:15px;height:15px;flex:none;margin-top:1px;accent-color:#1b6864}
    .newsletter-message{min-height:17px;margin:8px 0 0;color:#a12d38;font-size:11px}.newsletter-message.is-success{color:#16744f}
    .newsletter-modal[hidden]{display:none}.newsletter-modal{position:fixed;z-index:15000;inset:0;display:grid;place-items:center;padding:20px}
    .newsletter-modal-backdrop{position:absolute;inset:0;background:rgba(12,35,33,.56);backdrop-filter:blur(5px)}
    .newsletter-modal-card{position:relative;width:min(445px,100%);padding:42px 32px 30px;border:1px solid rgba(255,255,255,.7);border-radius:22px;background:linear-gradient(145deg,#fff,#f1f8f4);box-shadow:0 30px 90px rgba(6,31,30,.28);text-align:center;animation:newsletterPop .35s cubic-bezier(.2,.8,.2,1) both}
    .newsletter-modal-close{position:absolute;top:12px;right:12px;display:grid;width:36px;height:36px;place-items:center;border:0;border-radius:50%;background:#edf4f0;color:#48645c;cursor:pointer}.newsletter-modal-close svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round}
    .newsletter-success-icon{display:grid;width:66px;height:66px;place-items:center;margin:0 auto 18px;border-radius:22px;background:linear-gradient(135deg,#def2e8,#cde8de);color:#236f62}.newsletter-success-icon svg{width:38px;height:38px;fill:none;stroke:currentColor;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
    .newsletter-modal-eyebrow{color:#438b7e;font:700 9px Montserrat,sans-serif;letter-spacing:1.9px;text-transform:uppercase}
    .newsletter-modal-card h2{margin:8px 0 9px;color:#173d38;font:600 26px Montserrat,sans-serif}
    .newsletter-modal-card p{margin:0 auto;color:#71817c;font-size:12px;line-height:1.7}
    .newsletter-confirm-email{display:block;margin:13px 0 20px;color:#345c54;font-size:12px;overflow-wrap:anywhere}
    .newsletter-modal-done{width:100%;min-height:46px;border:0;border-radius:10px;background:#1b6864;color:#fff;font:700 11px Montserrat,sans-serif;cursor:pointer}
    @keyframes newsletterPop{from{opacity:0;transform:translateY(12px) scale(.97)}to{opacity:1;transform:none}}
    @media(max-width:700px){.beauty-newsletter-inner{grid-template-columns:1fr;gap:23px}.newsletter-copy h2 br{display:none}.newsletter-input-row button{padding:0 12px}.newsletter-modal-card{padding:38px 23px 24px}}
</style>
<script>
    (function () {
        var form = document.querySelector('.beauty-newsletter-form');
        var modal = document.getElementById('newsletterSuccessModal');
        if (!form || !modal) return;
        var message = document.getElementById('beautyNewsletterMessage');
        var closeButtons = modal.querySelectorAll('[data-newsletter-close]');
        function closeModal() { modal.hidden = true; document.body.style.overflow = ''; }
        closeButtons.forEach(function (button) { button.addEventListener('click', closeModal); });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) closeModal();
        });
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            message.textContent = '';
            var button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            try {
                var response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value
                    }
                });
                var result = await response.json();
                if (!response.ok) {
                    var errors = result.errors || {};
                    message.textContent = errors.email?.[0] || errors.consent?.[0] || result.message || 'Please check your details and try again.';
                    return;
                }
                document.getElementById('newsletterConfirmEmail').textContent = form.elements.email.value;
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
                form.reset();
            } catch (error) {
                message.textContent = 'We could not complete your sign-up just now. Please try again.';
            } finally {
                button.disabled = false;
            }
        });
    }());
</script>

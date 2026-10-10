@extends('admin.layouts.ecommerce')

@section('title', 'Footer settings')
@section('content')
<style>
    .footer-settings-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap}
    .footer-settings-head h1{display:flex;align-items:center;gap:11px;margin:0;font-size:24px}
    .footer-settings-head p{margin:5px 0 0;color:var(--muted);font-size:12px}
    .footer-settings-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .footer-settings-card{min-width:0;padding:22px;border:1px solid var(--border);border-radius:16px;background:var(--card);box-shadow:var(--shadow)}
    .footer-settings-card h2{margin:0 0 5px;font-size:16px}
    .footer-settings-card>p{margin:0 0 18px;color:var(--muted);font-size:12px;line-height:1.6}
    .footer-settings-field{display:grid;gap:7px;margin-bottom:15px}
    .footer-settings-field label{font-size:12px;font-weight:700}
    .footer-settings-field input,.footer-settings-field textarea{width:100%;min-height:42px;padding:11px 12px;border:1px solid var(--border);border-radius:10px;background:var(--bg);color:var(--text);font:inherit;font-size:12px}
    .footer-settings-field textarea{min-height:84px;resize:vertical}
    .footer-link-row{display:grid;grid-template-columns:minmax(0,.8fr) minmax(0,1.2fr);gap:10px}
    .footer-settings-note{padding:12px 14px;border:1px solid #cce8f4;border-radius:11px;background:#eff9fd;color:#315b6d;font-size:11px;line-height:1.6}
    .footer-settings-actions{display:flex;justify-content:flex-end;margin-top:18px}
    @media(max-width:900px){.footer-settings-grid{grid-template-columns:1fr}}
    @media(max-width:520px){.footer-settings-card{padding:16px}.footer-link-row{grid-template-columns:1fr;gap:0}}
</style>
<header class="footer-settings-head">
    <div><h1><i class="ri-layout-bottom-line"></i> Footer settings</h1><p>Manage the content and quick links shown in the storefront footer.</p></div>
</header>
@if(session('success'))<div class="flash-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admin.footer-settings.update') }}">
    @csrf
    @method('PATCH')
    <div class="footer-settings-grid">
        <section class="footer-settings-card">
            <h2>Footer identity</h2>
            <p>Set the footer headings, brand message, and copyright text.</p>
            <div class="footer-settings-field"><label for="footer_contact_title">Contact column heading</label><input id="footer_contact_title" name="footer_contact_title" maxlength="80" value="{{ old('footer_contact_title', $settings['footer_contact_title']) }}"></div>
            <div class="footer-settings-field"><label for="footer_links_title">Quick links heading</label><input id="footer_links_title" name="footer_links_title" maxlength="80" value="{{ old('footer_links_title', $settings['footer_links_title']) }}"></div>
            <div class="footer-settings-field"><label for="footer_tagline">Brand tagline</label><textarea id="footer_tagline" name="footer_tagline" maxlength="255">{{ old('footer_tagline', $settings['footer_tagline']) }}</textarea></div>
            <div class="footer-settings-field"><label for="footer_copyright">Copyright line</label><input id="footer_copyright" name="footer_copyright" maxlength="255" value="{{ old('footer_copyright', $settings['footer_copyright']) }}"><small>Use {year} to insert the current year automatically.</small></div>
            <div class="footer-settings-note">Contact details and social destinations are managed from the Contact Page settings. Unsafe link protocols are not allowed in footer quick links.</div>
        </section>
        <section class="footer-settings-card">
            <h2>Quick links</h2>
            <p>Customize the five links displayed in the storefront footer. Use a site path or an HTTP(S) URL.</p>
            @foreach(range(1, 5) as $linkNumber)
                <div class="footer-link-row">
                    <div class="footer-settings-field"><label for="footer_link_{{ $linkNumber }}_label">Link {{ $linkNumber }} label</label><input id="footer_link_{{ $linkNumber }}_label" name="footer_link_{{ $linkNumber }}_label" maxlength="80" value="{{ old("footer_link_{$linkNumber}_label", $settings["footer_link_{$linkNumber}_label"]) }}"></div>
                    <div class="footer-settings-field"><label for="footer_link_{{ $linkNumber }}_url">Destination</label><input id="footer_link_{{ $linkNumber }}_url" name="footer_link_{{ $linkNumber }}_url" maxlength="255" value="{{ old("footer_link_{$linkNumber}_url", $settings["footer_link_{$linkNumber}_url"]) }}" placeholder="/shop or https://example.com"></div>
                </div>
            @endforeach
        </section>
    </div>
    <div class="footer-settings-actions"><button class="button primary" type="submit"><i class="ri-save-line"></i> Save footer settings</button></div>
</form>
@endsection

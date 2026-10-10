<style>
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    html {
        min-height: 100%;
        background: var(--bg, #f3f5f7);
    }

    body {
        min-height: 100vh;
        margin: 0;
        background: var(--bg, #f3f5f7);
        color: var(--text, #101828);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 14px;
        -webkit-font-smoothing: antialiased;
        transition: background .35s ease, color .35s ease;
    }

    .layout {
        position: relative;
        min-height: 100vh;
    }

    .layout > .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        width: 268px;
        height: 100vh;
        z-index: 50;
        display: flex;
        flex-direction: column;
    }

    .layout > .sidebar .s-nav {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
    }

    .layout.collapsed > .sidebar {
        width: 84px;
    }

    .layout > .main {
        min-width: 0;
        min-height: 100vh;
        margin-left: 268px;
    }

    .layout.collapsed > .main {
        margin-left: 84px;
    }

    .main {
        min-height: 100vh;
        background: var(--bg, #f3f5f7);
        transition: margin .35s cubic-bezier(.4, 0, .2, 1), background .35s ease;
    }

    body.dark {
        color-scheme: dark;
    }

    body.dark .main {
        background: var(--bg, #0b1220);
    }

    @media (max-width: 992px) {
        .layout > .sidebar {
            inset: 0 auto 0 0;
            width: 268px;
            height: 100dvh;
            transform: translateX(-105%);
            transition: transform .38s cubic-bezier(.22, 1, .36, 1);
        }

        .layout.mobile-open > .sidebar,
        .layout > .sidebar.mobile-open {
            transform: none;
        }

        .layout > .main,
        .layout.collapsed > .main {
            margin-left: 0;
        }
    }
</style>

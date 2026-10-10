

<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
    
    
</head>

<style>



    :root {
        --teal: #38aab4;
        --teal-2: #2b7fb8;
        --teal-light: #7ddbe2;
        --ink: #16233c;
        --glass: rgba(255, 255, 255, .66);
        --glass-border: rgba(22, 35, 60, .08);
        --txt: rgba(22, 35, 60, .62);
        --white: #fff;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        color: var(--txt);
        background: linear-gradient(160deg, #f5f0e8 0%, #fbf9f4 52%, #ecf3f3 100%);
        min-height: 100vh;
        overflow-x: hidden;
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    ul {
        list-style: none;
    }

    img {
        display: block;
        max-width: 100%;
    }

    button {
        font-family: inherit;
        cursor: pointer;
    }

    select:not([multiple]):not([size]) {
        -webkit-appearance: none;
        appearance: none;
        padding-right: 42px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2368788f' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m7 10 5 5 5-5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 14px 14px;
        vertical-align: middle;
    }

    select:not([multiple]):not([size]):focus-visible {
        outline: 3px solid rgba(56, 170, 180, .2);
        outline-offset: 1px;
    }

    ::-webkit-scrollbar {
        width: 9px;
        height: 9px;
    }

    ::-webkit-scrollbar-track {
        background: #f1ece3;
    }

    ::-webkit-scrollbar-thumb {
        background: rgba(56, 170, 180, .4);
        border-radius: 10px;
    }

    /* Scene orbs */
    .scene {
        position: fixed;
        inset: 0;
        z-index: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(100px);
    }

    .orb-1 {
        width: 430px;
        height: 430px;
        background: rgba(56, 170, 180, .30);
        top: -140px;
        left: -120px;
        animation: orbFloat1 18s ease-in-out infinite;
    }

    .orb-2 {
        width: 390px;
        height: 390px;
        background: rgba(123, 92, 255, .16);
        bottom: -160px;
        right: -110px;
        animation: orbFloat2 22s ease-in-out infinite;
    }

    .orb-3 {
        width: 260px;
        height: 260px;
        background: rgba(255, 122, 158, .18);
        top: 40%;
        left: 55%;
        animation: orbFloat3 17s ease-in-out infinite;
    }

    @keyframes orbFloat1 {

        0%,
        100% {
            transform: translate(0, 0) scale(1);
        }

        50% {
            transform: translate(70px, 55px) scale(1.12);
        }
    }

    @keyframes orbFloat2 {

        0%,
        100% {
            transform: translate(0, 0) scale(1);
        }

        50% {
            transform: translate(-70px, -45px) scale(1.14);
        }
    }

    @keyframes orbFloat3 {

        0%,
        100% {
            transform: translate(0, 0);
        }

        50% {
            transform: translate(-55px, -60px);
        }
    }

    /* Layout */
    .layout {
        position: relative;
        z-index: 2;
        display: flex;
        min-height: 100vh;
    }

    .main {
        flex: 1;
        margin-left: 262px;
        padding: 26px 32px 60px;
        max-width: 1460px;
    }

    /* Glass */
    .glass {
        background: var(--glass);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        backdrop-filter: blur(22px) saturate(140%);
        -webkit-backdrop-filter: blur(22px) saturate(140%);
        box-shadow: 0 20px 45px rgba(31, 51, 89, .08);
    }

    .reveal {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity .7s ease, transform .7s cubic-bezier(.22, 1, .36, 1);
        transition-delay: var(--d, 0s);
    }

    .reveal.show {
        opacity: 1;
        transform: none;
        
    }

    /* Sidebar */
    .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        width: 262px;
        height: 100vh;
        height: 100dvh;
        z-index: 60;
        display: flex;
        flex-direction: column;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior-y: contain;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding: 26px 18px 20px;
        background: rgba(255, 255, 255, .78);
        backdrop-filter: blur(24px) saturate(140%);
        -webkit-backdrop-filter: blur(24px) saturate(140%);
        border-right: 1px solid var(--glass-border);
    }

    .sidebar::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }

    .side-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 6px;
        margin-bottom: 20px;
    }

    .brand-tile {
        width: 46px;
        height: 46px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border-radius: 14px;
        font-weight: 700;
        font-size: 20px;
        color: #fff;
        background: linear-gradient(135deg, var(--teal), var(--teal-2));
        box-shadow: 0 8px 22px rgba(56, 170, 180, .35), inset 0 0 0 1px rgba(255, 255, 255, .25);
    }

    .brand-txt b {
        display: block;
        color: var(--ink);
        font-size: 19px;
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: .3px;
    }

    .brand-txt small {
        display: block;
        margin-top: 3px;
        font-size: 8.5px;
        font-weight: 600;
        letter-spacing: 2.8px;
        text-transform: uppercase;
        color: var(--teal-2);
    }

    .side-badge {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 2px 24px;
        padding: 10px 13px;
        border-radius: 12px;
        background: rgba(56, 170, 180, .09);
        border: 1px solid rgba(56, 170, 180, .28);
    }

    .side-badge i {
        width: 7px;
        height: 7px;
        flex-shrink: 0;
        border-radius: 50%;
        background: var(--teal);
        box-shadow: 0 0 10px rgba(56, 170, 180, .8);
        animation: badgePulse 2.2s ease-in-out infinite;
    }

    @keyframes badgePulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: .35;
        }
    }

    .side-badge span {
        font-size: 9px;
        font-weight: 500;
        letter-spacing: 1.9px;
        text-transform: uppercase;
        color: rgba(22, 35, 60, .55);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .side-badge b {
        color: var(--teal-2);
        font-weight: 600;
    }

    .side-label {
        margin: 0 0 10px;
        padding-left: 14px;
        font-size: 9.5px;
        font-weight: 500;
        letter-spacing: 2.6px;
        text-transform: uppercase;
        color: rgba(22, 35, 60, .4);
    }

    .side-nav {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .side-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 12px 15px;
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 500;
        color: rgba(22, 35, 60, .62);
        transition: color .3s, background .3s, transform .3s;
    }

    .side-link svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        opacity: .85;
        transition: opacity .3s, color .3s;
    }

    .side-link:hover {
        color: var(--ink);
        background: rgba(56, 170, 180, .08);
        transform: translateX(3px);
    }

    .side-link:hover svg {
        opacity: 1;
    }

    .side-link.active {
        color: var(--ink);
        background: linear-gradient(135deg, rgba(56, 170, 180, .16), rgba(43, 127, 184, .08));
        box-shadow: inset 0 0 0 1px rgba(56, 170, 180, .35);
        transform: none;
    }

    .side-link.active svg {
        opacity: 1;
        color: var(--teal-2);
    }

    .side-link.active::before {
        content: '';
        position: absolute;
        left: -18px;
        top: 22%;
        bottom: 22%;
        width: 4px;
        border-radius: 6px;
        background: var(--teal);
        box-shadow: 0 0 14px rgba(56, 170, 180, .7);
    }

    .side-form {
        display: block;
    }

    .side-form .side-link {
        width: 100%;
        text-align: left;
        font-family: inherit;
        background: none;
        border: none;
        cursor: pointer;
    }

    .side-link-danger:hover {
        color: #e0566e;
        background: rgba(224, 86, 110, .08);
    }

    .side-user {
        margin-top: auto;
        padding-top: 18px;
        border-top: 1px solid rgba(22, 35, 60, .1);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar {
        position: relative;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-weight: 600;
        font-size: 16px;
        color: var(--white);
        background: linear-gradient(135deg, var(--teal), var(--teal-2));
        box-shadow: 0 6px 18px rgba(56, 170, 180, .35);
    }

    .avatar .online {
        position: absolute;
        right: 0;
        bottom: 1px;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: #2ec181;
        border: 2px solid #fff;
    }

    .side-user-info {
        min-width: 0;
        flex: 1;
    }

    .side-user-info b {
        display: block;
        color: var(--ink);
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .side-user-info span {
        display: block;
        font-size: 11px;
        color: rgba(22, 35, 60, .5);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Topbar */
    .topbar {
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 26px;
    }

    .hamburger {
        display: none;
        background: var(--glass);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        width: 42px;
        height: 42px;
        color: var(--ink);
        place-items: center;
        backdrop-filter: blur(14px);
    }

    .greet-block h1 {
        color: var(--ink);
        font-size: 21px;
        font-weight: 600;
        letter-spacing: .3px;
    }

    .greet-block p {
        font-size: 12.5px;
        color: rgba(22, 35, 60, .5);
    }

    .icon-btn {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid var(--glass-border);
        background: var(--glass);
        color: rgba(22, 35, 60, .7);
        display: grid;
        place-items: center;
        backdrop-filter: blur(14px);
        transition: all .3s;
    }

    .icon-btn:hover {
        color: var(--ink);
        border-color: rgba(56, 170, 180, .6);
    }

    .overlay {
        position: fixed;
        inset: 0;
        background: rgba(22, 35, 60, .35);
        backdrop-filter: blur(3px);
        z-index: 55;
        opacity: 0;
        visibility: hidden;
        transition: all .3s;
    }

    .overlay.show {
        opacity: 1;
        visibility: visible;
    }

    @media (max-width: 860px) {
        .sidebar {
            transform: translateX(-105%);
            transition: transform .38s cubic-bezier(.22, 1, .36, 1);
        }

        .sidebar.open {
            transform: none;
        }

        .main {
            margin-left: 0;
            padding: 20px 18px 50px;
        }

        .hamburger {
            display: grid;
        }
    }
</style>
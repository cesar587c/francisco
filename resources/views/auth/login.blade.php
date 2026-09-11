<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel de Pesquisa — Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        :root {
            color-scheme: light;
            --page: #f9f9f7;
            --surface: #fcfcfb;
            --ink: #0b0b0b;
            --ink-2: #52514e;
            --ink-muted: #898781;
            --border: rgba(11,11,11,0.10);
            --border-strong: rgba(11,11,11,0.16);
            --cat-1: #2a78d6;
            --cat-1-tint: rgba(42,120,214,.10);
            --cat-2: #eb6834;
            --cat-3: #1baf7a;
            --critical: #d03b3b;
            --critical-tint: rgba(208,59,59,.08);
        }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) {
                color-scheme: dark;
                --page: #0d0d0d;
                --surface: #1a1a19;
                --ink: #ffffff;
                --ink-2: #c3c2b7;
                --ink-muted: #898781;
                --border: rgba(255,255,255,0.10);
                --border-strong: rgba(255,255,255,0.16);
                --cat-1: #3987e5;
                --cat-1-tint: rgba(57,135,229,.14);
                --cat-2: #d95926;
                --cat-3: #199e70;
                --critical: #e66767;
                --critical-tint: rgba(230,103,103,.12);
            }
        }
        * { box-sizing: border-box; }
        html, body { padding: 0; margin: 0; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: var(--page);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }
        .chart-bg {
            position: fixed;
            inset: 0;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
            animation: chart-rise .9s cubic-bezier(.16,.8,.3,1) both;
        }
        .chart-grid {
            position: absolute;
            left: 0; right: 0;
            top: 0;
            height: 100%;
            background-image: repeating-linear-gradient(
                to bottom,
                var(--border) 0, var(--border) 1px,
                transparent 1px, transparent 16.66%
            );
        }
        .chart-bg svg {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        }
        .chart-bg svg.l1 { opacity: .22; }
        .chart-bg svg.l2 { opacity: .16; }
        .chart-bg svg.l3 { opacity: .13; }
        .chart-bg path {
            stroke-dasharray: 1000;
            animation: chart-draw linear infinite;
        }
        .chart-bg svg.l1 path { animation-duration: 5.5s; }
        .chart-bg svg.l2 path { animation-duration: 4.5s; animation-delay: -1.5s; }
        .chart-bg svg.l3 path { animation-duration: 6.5s; animation-delay: -3s; }
        @keyframes chart-draw {
            0%   { stroke-dashoffset: 1000; }
            60%  { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes chart-rise {
            from { transform: translateY(12%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .card {
            position: relative;
            z-index: 1;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px 36px;
            width: 380px;
            max-width: calc(100vw - 32px);
            box-shadow: 0 1px 2px rgba(11,11,11,.04);
        }
        .mark {
            width: 44px; height: 44px; border-radius: 11px;
            background: var(--cat-1-tint); color: var(--cat-1);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
        }
        h1 { margin: 0 0 4px; font-size: 20px; font-weight: 600; color: var(--ink); letter-spacing: -.01em; }
        p.sub { color: var(--ink-muted); font-size: 13.5px; margin: 0 0 28px; }
        label { display: block; font-size: 12.5px; font-weight: 500; color: var(--ink-2); margin-bottom: 6px; }
        .field { margin-bottom: 16px; }
        input {
            width: 100%;
            padding: 11px 13px;
            border-radius: 9px;
            border: 1px solid var(--border-strong);
            background: var(--page);
            color: var(--ink);
            font-size: 14px;
            outline: none;
        }
        input:focus { border-color: var(--cat-1); }
        .error {
            display: flex; align-items: center; gap: 8px;
            color: var(--critical); background: var(--critical-tint);
            border-radius: 8px; padding: 9px 12px;
            font-size: 12.5px; margin-bottom: 16px;
        }
        button {
            width: 100%;
            background: var(--ink);
            color: var(--page);
            border: none;
            border-radius: 9px;
            padding: 12px 24px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 6px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        button:hover { background: var(--cat-1); color: #fff; }
        button .arrow { transition: transform .15s ease; }
        button:hover .arrow { animation: arrow-slide .7s ease-in-out infinite; }
        @keyframes arrow-slide {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(4px); }
        }
    </style>
</head>
<body>
    <div class="chart-bg" aria-hidden="true">
        <div class="chart-grid"></div>
        <svg class="l1" viewBox="0 0 800 300" preserveAspectRatio="none">
            <path pathLength="1000" d="M0,150 L120,60 L220,180 L300,20 L380,200 L460,90 L540,280 L620,50 L700,190 L800,150"
                  fill="none" stroke="var(--cat-1)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="miter"/>
        </svg>
        <svg class="l2" viewBox="0 0 800 300" preserveAspectRatio="none">
            <path pathLength="1000" d="M0,105 L100,190 L180,40 L260,150 L340,15 L420,170 L500,60 L580,195 L660,80 L740,140 L800,105"
                  fill="none" stroke="var(--cat-2)" stroke-width="2" stroke-linecap="round" stroke-linejoin="miter"/>
        </svg>
        <svg class="l3" viewBox="0 0 800 300" preserveAspectRatio="none">
            <path pathLength="1000" d="M0,200 L90,270 L170,120 L250,290 L330,150 L410,260 L490,110 L570,280 L650,160 L730,230 L800,200"
                  fill="none" stroke="var(--cat-3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="miter"/>
        </svg>
    </div>

    <div class="card">
        <div class="mark">
            <svg width="22" height="22" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 15.5V9M10 15.5V4.5M16 15.5v-7"/></svg>
        </div>
        <h1>Painel de Pesquisa</h1>
        <p class="sub">Acesso restrito ao administrador</p>

        @if ($errors->any())
            <div class="error">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="10" cy="10" r="7.2"/><path d="M10 6.5v4M10 13.2h.01"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus required>
            </div>
            <div class="field">
                <label for="password">Senha</label>
                <input id="password" type="password" name="password" required>
            </div>
            <button type="submit">
                Entrar
                <svg class="arrow" width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h12M11 5.5l4.5 4.5-4.5 4.5"/></svg>
            </button>
        </form>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Painel de Pesquisa</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        :root {
            color-scheme: light;
            --page: #f9f9f7;
            --surface: #fcfcfb;
            --ink: #0b0b0b;
            --ink-2: #52514e;
            --ink-muted: #898781;
            --grid: #e1e0d9;
            --baseline: #c3c2b7;
            --border: rgba(11,11,11,0.10);
            --border-strong: rgba(11,11,11,0.16);
            --success-text: #006300;

            --cat-1: #2a78d6; /* participantes / esquerda */
            --cat-2: #eb6834; /* novos hoje / direita */
            --cat-3: #1baf7a; /* centro */
            --cat-1-tint: rgba(42,120,214,.10);
            --cat-2-tint: rgba(235,104,52,.10);
            --cat-3-tint: rgba(27,175,122,.10);

            --status-good: #0ca30c;
            --status-good-tint: rgba(12,163,12,.10);
            --status-warning: #d97a06;
            --status-warning-tint: rgba(250,178,25,.16);

            --shadow-card: 0 1px 2px rgba(11,11,11,.04);
        }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) {
                color-scheme: dark;
                --page: #0d0d0d;
                --surface: #1a1a19;
                --ink: #ffffff;
                --ink-2: #c3c2b7;
                --ink-muted: #898781;
                --grid: #2c2c2a;
                --baseline: #383835;
                --border: rgba(255,255,255,0.10);
                --border-strong: rgba(255,255,255,0.16);
                --success-text: #0ca30c;

                --cat-1: #3987e5;
                --cat-2: #d95926;
                --cat-3: #199e70;
                --cat-1-tint: rgba(57,135,229,.14);
                --cat-2-tint: rgba(217,89,38,.14);
                --cat-3-tint: rgba(25,158,112,.14);

                --status-good-tint: rgba(12,163,12,.16);
                --status-warning-tint: rgba(250,178,25,.16);

                --shadow-card: 0 1px 2px rgba(0,0,0,.3);
            }
        }

        * { box-sizing: border-box; }
        html, body { padding: 0; margin: 0; }
        body {
            min-height: 100vh;
            background: var(--page);
            color: var(--ink);
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        [hidden] { display: none !important; }
        button { font-family: inherit; }

        .icon { width: 18px; height: 18px; flex-shrink: 0; }
        .icon-sm { width: 14px; height: 14px; }

        .loading-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; gap: 12px; }
        .spinner { width: 28px; height: 28px; border: 2.5px solid var(--grid); border-top-color: var(--cat-1); border-radius: 50%; animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .inner { max-width: 1080px; margin: 0 auto; padding: 0 24px 64px; }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 16px 24px;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 10;
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand-mark {
            width: 36px; height: 36px; border-radius: 9px;
            background: var(--cat-1-tint); color: var(--cat-1);
            display: flex; align-items: center; justify-content: center;
        }
        .brand-title { font-weight: 600; font-size: 15px; color: var(--ink); letter-spacing: -.01em; }
        .brand-sub { font-size: 12.5px; color: var(--ink-muted); margin-top: 1px; }

        .btn-ghost {
            display: inline-flex; align-items: center; gap: 6px;
            background: transparent; border: 1px solid var(--border);
            border-radius: 8px; padding: 7px 12px; cursor: pointer;
            color: var(--ink-2); font-size: 13px; font-weight: 500;
            transition: background .12s, border-color .12s;
        }
        .btn-ghost:hover { background: var(--border); border-color: var(--border-strong); }

        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            background: var(--ink); color: var(--page);
            border: none; border-radius: 9px; padding: 12px 22px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: opacity .12s;
        }
        .btn-primary:hover { opacity: .88; }
        .btn-primary:disabled { opacity: .5; cursor: default; }

        .btn-secondary {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--surface); border: 1px solid var(--border-strong);
            border-radius: 8px; padding: 8px 14px; cursor: pointer;
            color: var(--ink); font-size: 13px; font-weight: 600;
        }
        .btn-secondary:hover { background: var(--border); }

        .tabs {
            display: flex; gap: 28px;
            border-bottom: 1px solid var(--grid);
            margin: 28px 0 24px;
        }
        .tab {
            display: flex; align-items: center; gap: 7px;
            padding: 4px 2px 12px; margin-bottom: -1px;
            border: none; border-bottom: 2px solid transparent;
            background: none; cursor: pointer;
            color: var(--ink-muted); font-size: 14px; font-weight: 500;
        }
        .tab.active { color: var(--ink); border-bottom-color: var(--ink); font-weight: 600; }
        .tab .count { color: var(--ink-muted); font-weight: 500; }
        .tab.active .count { color: var(--ink-2); }

        .section-title { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin-bottom: 4px; }
        .h2 { font-size: 15px; font-weight: 600; color: var(--ink); margin: 0; letter-spacing: -.01em; }
        .h2-sub { font-size: 12.5px; color: var(--ink-muted); }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: var(--shadow-card);
            padding: 22px;
            margin-bottom: 16px;
        }

        .grid4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; margin-bottom: 16px; }
        .stat-tile {
            background: var(--surface); border: 1px solid var(--border); border-radius: 12px;
            padding: 18px 20px; box-shadow: var(--shadow-card);
            display: flex; flex-direction: column; gap: 14px;
        }
        .stat-chip {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .stat-value { font-size: 30px; font-weight: 600; color: var(--ink); line-height: 1; letter-spacing: -.02em; }
        .stat-label { font-size: 12.5px; color: var(--ink-2); font-weight: 500; margin-top: 6px; }
        .stat-sub { font-size: 12px; color: var(--ink-muted); margin-top: 2px; }

        .pb-row { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
        .pb-row:last-child { margin-bottom: 0; }
        .pb-label { width: 76px; font-size: 13px; color: var(--ink-2); text-align: right; flex-shrink: 0; }
        .pb-track { flex: 1; background: var(--grid); border-radius: 5px; height: 8px; overflow: hidden; }
        .pb-fill { height: 100%; border-radius: 5px; transition: width .5s ease; }
        .pb-count { width: 100px; font-size: 13px; color: var(--ink); font-variant-numeric: tabular-nums; flex-shrink: 0; }
        .pb-count .pct { color: var(--ink-muted); }

        .search-wrap { position: relative; }
        .search-wrap .icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--ink-muted); }
        .search-input {
            padding: 8px 12px 8px 34px; border-radius: 8px; border: 1px solid var(--border-strong);
            background: var(--page); font-size: 13.5px; outline: none; width: 240px; color: var(--ink);
        }
        .search-input:focus { border-color: var(--cat-1); }

        .resp-card { border: 1px solid var(--border); border-radius: 10px; margin-bottom: 8px; overflow: hidden; }
        .resp-card:last-child { margin-bottom: 0; }
        .resp-header { display: flex; justify-content: space-between; align-items: center; padding: 13px 16px; cursor: pointer; gap: 8px; flex-wrap: wrap; }
        .resp-avatar {
            width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
            background: var(--cat-1-tint); color: var(--cat-1);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 600;
        }
        .resp-name { font-weight: 600; font-size: 13.5px; color: var(--ink); }
        .resp-phone { font-size: 12.5px; color: var(--ink-muted); font-variant-numeric: tabular-nums; }
        .resp-date { font-size: 12px; color: var(--ink-muted); font-variant-numeric: tabular-nums; }
        .resp-body { padding: 4px 16px 18px 60px; border-top: 1px solid var(--grid); }
        .resp-q { margin-top: 14px; }
        .q-label { font-size: 11px; font-weight: 600; color: var(--ink-muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
        .q-answer { font-size: 13.5px; color: var(--ink-2); line-height: 1.6; }
        .q-empty { color: var(--ink-muted); font-size: 13px; font-style: italic; margin-top: 14px; }

        .status-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
        .status-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: var(--ink-2); font-weight: 500; }

        .btn-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border-radius: 7px; flex-shrink: 0;
            background: none; border: 1px solid transparent; cursor: pointer; color: var(--ink-muted);
        }
        .btn-icon:hover { background: var(--border); color: var(--ink); }
        .btn-icon.danger-confirm { background: var(--status-warning-tint); border-color: transparent; color: #b45309; }
        .chevron { color: var(--ink-muted); transition: transform .15s; }
        .chevron.open { transform: rotate(180deg); }

        .report-meta { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; padding-bottom: 18px; border-bottom: 1px solid var(--grid); }
        .chip-row { display: flex; flex-wrap: wrap; gap: 6px; }
        .meta-chip { display: inline-flex; align-items: center; gap: 5px; background: var(--page); border: 1px solid var(--border); border-radius: 6px; padding: 4px 10px; font-size: 12px; color: var(--ink-2); }
        .report-body { line-height: 1.75; }
        .report-body h2 { font-size: 18px; font-weight: 600; color: var(--ink); margin: 26px 0 8px; letter-spacing: -.01em; }
        .report-body h3 { font-size: 15.5px; font-weight: 600; color: var(--ink); margin: 20px 0 6px; }
        .report-body h4 { font-size: 14px; font-weight: 600; color: var(--ink-2); margin: 16px 0 6px; }
        .report-body p { font-size: 13.5px; color: var(--ink-2); margin: 6px 0; line-height: 1.75; }

        .empty-box { text-align: center; padding: 56px 24px; }
        .empty-icon { width: 40px; height: 40px; color: var(--ink-muted); margin: 0 auto 14px; }
        .empty-title { color: var(--ink-2); font-size: 14px; margin-bottom: 20px; }
        .empty-sub { color: var(--ink-muted); font-size: 13px; }

        .field-label { font-size: 12.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 8px; }
        .code-row {
            display: flex; align-items: center; gap: 10px;
            background: var(--page); border: 1px solid var(--border); border-radius: 8px;
            padding: 10px 12px; margin-bottom: 6px;
        }
        .code-row code {
            flex: 1; min-width: 0; overflow-x: auto; white-space: pre;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 13px; color: var(--ink);
        }
        .code-hint { font-size: 12px; color: var(--ink-muted); margin: 0 0 20px; }
        .method-badge {
            flex-shrink: 0; font-size: 11px; font-weight: 700; letter-spacing: .03em;
            padding: 3px 8px; border-radius: 5px; font-family: ui-monospace, monospace;
        }
        .method-post { background: var(--cat-1-tint); color: var(--cat-1); }
        .method-patch { background: var(--cat-2-tint); color: var(--cat-2); }
        .endpoint-block { margin-bottom: 22px; }
        .endpoint-block:last-child { margin-bottom: 0; }
        .endpoint-desc { font-size: 13px; color: var(--ink-2); margin: 0 0 8px; }
        .endpoint-body { font-size: 12px; color: var(--ink-muted); margin: 6px 0 0; }
        .endpoint-body code { background: var(--page); border: 1px solid var(--border); border-radius: 4px; padding: 1px 5px; font-family: ui-monospace, monospace; }
        .section-divider { border: none; border-top: 1px solid var(--grid); margin: 24px 0; }
    </style>
</head>
<body>
    <div id="loading" class="loading-wrap"><div class="spinner"></div></div>

    <div id="app" hidden>
        <div class="topbar">
            <div class="brand">
                <div class="brand-mark">
                    <svg class="icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 15.5V9M10 15.5V4.5M16 15.5v-7"/></svg>
                </div>
                <div>
                    <div class="brand-title">Painel de Pesquisa</div>
                    <div class="brand-sub">Análise e monitoramento das respostas</div>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <button class="btn-ghost" id="btn-refresh" title="Atualizar">
                    <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 10a6.5 6.5 0 1 1-2-4.7"/><path d="M16.5 3.5V8h-4.5"/></svg>
                    Atualizar
                </button>
                <form method="POST" action="{{ url('/logout') }}">
                    @csrf
                    <button class="btn-ghost" type="submit">
                        <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M7.5 17H4.8A1.8 1.8 0 0 1 3 15.2V4.8A1.8 1.8 0 0 1 4.8 3H7.5"/><path d="M12.5 13.5 16 10l-3.5-3.5"/><path d="M16 10H7.5"/></svg>
                        Sair
                    </button>
                </form>
            </div>
        </div>

        <div class="inner">
            <div class="tabs">
                <button class="tab active" data-tab="overview">
                    <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M4 15.5V9M10 15.5V4.5M16 15.5v-7"/></svg>
                    Visão geral
                </button>
                <button class="tab" data-tab="responses">
                    <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4.5h14v9H7l-3 3v-3H3z"/></svg>
                    Respostas <span class="count">(<span id="tab-resp-count">0</span>)</span>
                </button>
                <button class="tab" data-tab="report">
                    <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3v2.2M10 14.8V17M3 10h2.2M14.8 10H17M5.6 5.6l1.5 1.5M12.9 12.9l1.5 1.5M14.4 5.6l-1.5 1.5M7.1 12.9l-1.5 1.5"/></svg>
                    Relatório
                </button>
                <button class="tab" data-tab="integration">
                    <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M7 4.5 3 10l4 5.5M13 4.5l4 5.5-4 5.5M11.5 3.5l-3 13"/></svg>
                    Integração
                </button>
            </div>

            <div id="tab-overview">
                <div class="grid4">
                    <div class="stat-tile">
                        <div class="stat-chip" style="background:var(--cat-1-tint);color:var(--cat-1);">
                            <svg class="icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="7.2" cy="6.5" r="2.5"/><path d="M2.5 16c.4-3 2.3-4.5 4.7-4.5s4.3 1.5 4.7 4.5"/><circle cx="14" cy="7" r="2"/><path d="M12.8 11.6c1.9.2 3.2 1.6 3.7 4.4"/></svg>
                        </div>
                        <div>
                            <div class="stat-value" id="stat-total">0</div>
                            <div class="stat-label">Total de participantes</div>
                        </div>
                    </div>
                    <div class="stat-tile">
                        <div class="stat-chip" style="background:var(--status-good-tint);color:var(--status-good);">
                            <svg class="icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17 10A7 7 0 1 1 8.5 3.2"/><path d="M7 10l2.2 2.2L17 4.5"/></svg>
                        </div>
                        <div>
                            <div class="stat-value" id="stat-completed">0</div>
                            <div class="stat-label">Pesquisas completas</div>
                            <div class="stat-sub" id="stat-completed-pct">0% do total</div>
                        </div>
                    </div>
                    <div class="stat-tile">
                        <div class="stat-chip" style="background:var(--status-warning-tint);color:#b45309;">
                            <svg class="icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7"/><path d="M10 6v4.2l3 1.8"/></svg>
                        </div>
                        <div>
                            <div class="stat-value" id="stat-pending">0</div>
                            <div class="stat-label">Pendentes</div>
                        </div>
                    </div>
                    <div class="stat-tile">
                        <div class="stat-chip" style="background:var(--cat-3-tint);color:var(--cat-3);">
                            <svg class="icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="14" height="12.5" rx="2"/><path d="M3 8h14M7 2.5v3M13 2.5v3"/><path d="M7.5 11.5h1.2M11.3 11.5h1.2M7.5 13.8h1.2M11.3 13.8h1.2"/></svg>
                        </div>
                        <div>
                            <div class="stat-value" id="stat-today">0</div>
                            <div class="stat-label">Novos hoje</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="section-title" style="margin-bottom:18px;">
                        <h2 class="h2">Espectro político</h2>
                        <span class="h2-sub">Baseado no último relatório gerado</span>
                    </div>
                    <div id="spectrum-empty" class="empty-box" style="padding:20px 0;">
                        <p style="color:var(--ink-muted);font-size:13.5px;">Gere um relatório para ver o espectro político.</p>
                    </div>
                    <div id="spectrum-bars" hidden>
                        <div class="pb-row"><div class="pb-label">Esquerda</div><div class="pb-track"><div class="pb-fill" id="pb-left" style="background:var(--cat-1);"></div></div><div class="pb-count" id="pb-left-count"></div></div>
                        <div class="pb-row"><div class="pb-label">Direita</div><div class="pb-track"><div class="pb-fill" id="pb-right" style="background:var(--cat-2);"></div></div><div class="pb-count" id="pb-right-count"></div></div>
                        <div class="pb-row"><div class="pb-label">Centro</div><div class="pb-track"><div class="pb-fill" id="pb-center" style="background:var(--cat-3);"></div></div><div class="pb-count" id="pb-center-count"></div></div>
                        <div class="pb-row"><div class="pb-label">Indefinido</div><div class="pb-track"><div class="pb-fill" id="pb-other" style="background:var(--ink-muted);"></div></div><div class="pb-count" id="pb-other-count"></div></div>
                    </div>
                </div>

                <div style="text-align:center;margin-top:24px;">
                    <button class="btn-primary" id="btn-generate">
                        <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3v2.2M10 14.8V17M3 10h2.2M14.8 10H17M5.6 5.6l1.5 1.5M12.9 12.9l1.5 1.5M14.4 5.6l-1.5 1.5M7.1 12.9l-1.5 1.5"/></svg>
                        Gerar novo relatório
                    </button>
                    <p id="last-report-date" style="color:var(--ink-muted);font-size:12.5px;margin-top:10px;"></p>
                </div>
            </div>

            <div id="tab-responses" hidden>
                <div class="card">
                    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
                        <h2 class="h2">Respostas dos participantes</h2>
                        <div class="search-wrap">
                            <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="8.5" cy="8.5" r="5.5"/><path d="M16 16l-3.8-3.8"/></svg>
                            <input class="search-input" id="search" placeholder="Buscar por nome ou telefone...">
                        </div>
                    </div>
                    <div id="responses-list"></div>
                    <div id="responses-empty" class="empty-box" hidden><p style="color:var(--ink-muted);font-size:13.5px;">Nenhum resultado encontrado.</p></div>
                </div>
            </div>

            <div id="tab-report" hidden>
                <div class="card">
                    <div id="report-empty" class="empty-box">
                        <svg class="empty-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3v2.2M10 14.8V17M3 10h2.2M14.8 10H17M5.6 5.6l1.5 1.5M12.9 12.9l1.5 1.5M14.4 5.6l-1.5 1.5M7.1 12.9l-1.5 1.5"/></svg>
                        <p class="empty-title">Nenhum relatório gerado ainda.</p>
                        <button class="btn-primary" id="btn-generate-first">
                            <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3v2.2M10 14.8V17M3 10h2.2M14.8 10H17M5.6 5.6l1.5 1.5M12.9 12.9l1.5 1.5M14.4 5.6l-1.5 1.5M7.1 12.9l-1.5 1.5"/></svg>
                            Gerar primeiro relatório
                        </button>
                    </div>
                    <div id="report-content" hidden>
                        <div class="report-meta">
                            <div class="chip-row" id="report-meta-chips"></div>
                            <button class="btn-secondary" id="btn-generate-again">
                                <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M16.5 10a6.5 6.5 0 1 1-2-4.7"/><path d="M16.5 3.5V8h-4.5"/></svg>
                                Novo relatório
                            </button>
                        </div>
                        <div class="report-body" id="report-body"></div>
                    </div>
                </div>
            </div>

            <div id="tab-integration" hidden>
                <div class="card">
                    <div class="section-title" style="margin-bottom:18px;">
                        <h2 class="h2">Integração com o bot</h2>
                        <span class="h2-sub">Visível só para você, logado</span>
                    </div>

                    <div class="field-label">URL base da API</div>
                    <div class="code-row">
                        <code id="api-base-url">{{ $apiBaseUrl }}</code>
                        <button class="btn-icon" data-copy="{{ $apiBaseUrl }}" title="Copiar">
                            <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="7" width="9" height="10" rx="1.5"/><path d="M4.5 13V5.5A1.5 1.5 0 0 1 6 4h7"/></svg>
                        </button>
                    </div>
                    <p class="code-hint">Todos os endpoints abaixo são relativos a essa URL.</p>

                    <div class="field-label">Token (header <code style="font-family:ui-monospace,monospace;">X-Ingest-Token</code>)</div>
                    <div class="code-row">
                        <code id="ingest-token" data-token="{{ $ingestToken }}" data-visible="0">••••••••••••••••••••••••</code>
                        <button class="btn-icon" id="btn-toggle-token" title="Mostrar/ocultar">
                            <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2 10s3-5.5 8-5.5 8 5.5 8 5.5-3 5.5-8 5.5-8-5.5-8-5.5Z"/><circle cx="10" cy="10" r="2.3"/></svg>
                        </button>
                        <button class="btn-icon" id="btn-copy-token" data-copy="{{ $ingestToken }}" title="Copiar">
                            <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="7" width="9" height="10" rx="1.5"/><path d="M4.5 13V5.5A1.5 1.5 0 0 1 6 4h7"/></svg>
                        </button>
                        <button class="btn-secondary" id="btn-regenerate-token" style="flex-shrink:0;">
                            <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M16.5 10a6.5 6.5 0 1 1-2-4.7"/><path d="M16.5 3.5V8h-4.5"/></svg>
                            Gerar novo
                        </button>
                    </div>
                    <p class="code-hint">Gerado automaticamente e guardado no banco. Não compartilhe — quem tiver esse token consegue criar/editar respondentes. Gerar um novo <strong>invalida o anterior na hora</strong> — o bot vai precisar ser atualizado com o novo valor.</p>

                    <hr class="section-divider">

                    <div class="endpoint-block">
                        <div><span class="method-badge method-post">POST</span></div>
                        <p class="endpoint-desc">Único endpoint: cria/atualiza o respondente, grava as respostas em <code>answers</code> e atualiza o andamento da conversa. Só <code>phone</code> e <code>name</code> são obrigatórios — o resto você manda como e quando quiser: tudo numa chamada só, ou dividido em quantas chamadas achar melhor.</p>
                        <div class="code-row">
                            <code>{{ $apiBaseUrl }}/respondents</code>
                            <button class="btn-icon" data-copy="{{ $apiBaseUrl }}/respondents" title="Copiar"><svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="7" width="9" height="10" rx="1.5"/><path d="M4.5 13V5.5A1.5 1.5 0 0 1 6 4h7"/></svg></button>
                        </div>
                        <p class="endpoint-body">Tudo de uma vez: <code>{"phone": "5561999999999", "name": "Maria Souza", "answers": {"resposta_1": "Sim", "resposta_2": "Ótimo"}, "completed": true}</code></p>
                        <p class="endpoint-body">Ou dividido, em quantas chamadas quiser — ex.: primeiro só <code>{"phone": "5561999999999", "name": "Maria Souza"}</code>, depois <code>{"phone": "5561999999999", "name": "Maria Souza", "answers": {"resposta_1": "Sim"}}</code>, e assim por diante.</p>
                        <p class="code-hint">Campos: <code>phone</code> (obrigatório) e <code>name</code> (obrigatório) identificam o respondente. <code>answers</code> (opcional, objeto <code>{"pergunta": "resposta"}</code>) e <code>completed</code> (opcional, true/false) você manda só quando tiver a informação — se <code>completed</code> não for enviado, o sistema calcula sozinho a partir da quantidade de respostas em <code>answers</code>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const fmt = (n) => new Intl.NumberFormat('pt-BR').format(n);
    const fmtDate = (d) => new Date(d).toLocaleString('pt-BR');
    const pct = (v, t) => (t === 0 ? 0 : Math.round((v / t) * 100));
    const initials = (name) => (name || '').trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join('').toUpperCase();

    function api(url, options = {}) {
        return fetch(url, {
            ...options,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...(options.headers || {}),
            },
        }).then(async (res) => {
            if (res.status === 401) {
                window.location.href = '{{ route('login') }}';
                throw new Error('unauthorized');
            }
            return res;
        });
    }

    let data = null;
    let generating = false;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function renderOverview() {
        const { stats, lastAnalysis } = data;
        document.getElementById('stat-total').textContent = fmt(stats.total);
        document.getElementById('stat-completed').textContent = fmt(stats.completed);
        document.getElementById('stat-completed-pct').textContent = `${pct(stats.completed, stats.total)}% do total`;
        document.getElementById('stat-pending').textContent = fmt(stats.pending);
        document.getElementById('stat-today').textContent = fmt(stats.today);

        const genBtn = document.getElementById('btn-generate');
        genBtn.disabled = generating;
        genBtn.querySelector('svg').nextSibling.textContent = generating ? ' Gerando relatório…' : ' Gerar novo relatório';

        if (lastAnalysis) {
            document.getElementById('spectrum-empty').hidden = true;
            document.getElementById('spectrum-bars').hidden = false;
            const polTotal = lastAnalysis.left_wing_count + lastAnalysis.right_wing_count + lastAnalysis.center_count + lastAnalysis.other_count;
            const bars = [
                ['left', lastAnalysis.left_wing_count],
                ['right', lastAnalysis.right_wing_count],
                ['center', lastAnalysis.center_count],
                ['other', lastAnalysis.other_count],
            ];
            bars.forEach(([key, value]) => {
                const p = pct(value, polTotal);
                document.getElementById(`pb-${key}`).style.width = `${p}%`;
                document.getElementById(`pb-${key}-count`).innerHTML = `${fmt(value)} <span class="pct">(${p}%)</span>`;
            });
            document.getElementById('last-report-date').textContent = `Último relatório: ${fmtDate(lastAnalysis.generated_at)}`;
        } else {
            document.getElementById('spectrum-empty').hidden = false;
            document.getElementById('spectrum-bars').hidden = true;
            document.getElementById('last-report-date').textContent = '';
        }
    }

    function renderResponseBody(r) {
        const byKey = {};
        (r.responses || []).forEach((res) => { byKey[res.question] = res.answer; });

        if (!byKey.resposta_1 && !byKey.resposta_2) {
            return `<div class="q-empty">Ainda não respondeu.</div>`;
        }

        return `
            ${byKey.resposta_1
                ? `<div class="resp-q"><div class="q-label">1 · Primeira pergunta</div><div class="q-answer">${escapeHtml(byKey.resposta_1)}</div></div>`
                : `<div class="q-empty">Ainda não respondeu a 1ª pergunta.</div>`}
            ${byKey.resposta_2
                ? `<div class="resp-q"><div class="q-label">2 · Segunda pergunta</div><div class="q-answer">${escapeHtml(byKey.resposta_2)}</div></div>`
                : `<div class="q-empty">Ainda não respondeu a 2ª pergunta.</div>`}
        `;
    }

    function renderResponses() {
        const search = document.getElementById('search').value.toLowerCase();
        const filtered = data.respondents.filter((r) =>
            r.name.toLowerCase().includes(search) || r.phone.includes(search)
        );

        document.getElementById('tab-resp-count').textContent = data.respondents.length;
        const list = document.getElementById('responses-list');
        document.getElementById('responses-empty').hidden = filtered.length !== 0;

        list.innerHTML = filtered.map((r) => `
            <div class="resp-card" data-id="${r.id}">
                <div class="resp-header" data-toggle="${r.id}">
                    <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;">
                        <div class="resp-avatar">${escapeHtml(initials(r.name) || '?')}</div>
                        <div style="min-width:0;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <span class="resp-name">${escapeHtml(r.name)}</span>
                                <span class="resp-phone">${escapeHtml(r.phone)}</span>
                            </div>
                            <span class="status-pill">
                                <span class="status-dot" style="background:${r.completed ? 'var(--status-good)' : 'var(--status-warning)'};"></span>
                                ${r.completed ? 'Completo' : 'Pendente'}
                            </span>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="resp-date">${fmtDate(r.created_at)}</span>
                        <button class="btn-icon" data-delete="${r.id}" title="Remover registro">
                            <svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h12M8 6V4.5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1V6M6 6l.6 9.4a1 1 0 0 0 1 .9h4.8a1 1 0 0 0 1-.9L14 6"/></svg>
                        </button>
                        <span class="btn-icon" data-arrow="${r.id}" style="cursor:pointer;">
                            <svg class="icon-sm chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7.5l5 5 5-5"/></svg>
                        </span>
                    </div>
                </div>
                <div class="resp-body" data-body="${r.id}" hidden>${renderResponseBody(r)}</div>
            </div>
        `).join('');
    }

    function renderReport() {
        const { lastAnalysis } = data;
        document.getElementById('report-empty').hidden = !!lastAnalysis;
        document.getElementById('report-content').hidden = !lastAnalysis;
        if (!lastAnalysis) return;

        document.getElementById('report-meta-chips').innerHTML = `
            <span class="meta-chip">${fmtDate(lastAnalysis.generated_at)}</span>
            <span class="meta-chip">${lastAnalysis.completed_respondents} participantes</span>
            <span class="meta-chip"><span class="status-dot" style="background:var(--cat-1);"></span> Esquerda ${lastAnalysis.left_wing_count}</span>
            <span class="meta-chip"><span class="status-dot" style="background:var(--cat-2);"></span> Direita ${lastAnalysis.right_wing_count}</span>
            <span class="meta-chip"><span class="status-dot" style="background:var(--cat-3);"></span> Centro ${lastAnalysis.center_count}</span>
        `;

        const body = document.getElementById('report-body');
        body.innerHTML = lastAnalysis.report.split('\n').map((line) => {
            if (line.startsWith('## ')) return `<h3>${escapeHtml(line.slice(3))}</h3>`;
            if (line.startsWith('# ')) return `<h2>${escapeHtml(line.slice(2))}</h2>`;
            if (line.startsWith('**') && line.endsWith('**')) return `<h4>${escapeHtml(line.replace(/\*\*/g, ''))}</h4>`;
            const withBold = escapeHtml(line).replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            return `<p>${withBold}</p>`;
        }).join('');
    }

    function renderAll() {
        renderOverview();
        renderResponses();
        renderReport();
    }

    function setTab(tab) {
        document.querySelectorAll('.tab').forEach((b) => b.classList.toggle('active', b.dataset.tab === tab));
        document.getElementById('tab-overview').hidden = tab !== 'overview';
        document.getElementById('tab-responses').hidden = tab !== 'responses';
        document.getElementById('tab-report').hidden = tab !== 'report';
        document.getElementById('tab-integration').hidden = tab !== 'integration';
    }

    async function load() {
        const res = await api('{{ route('dashboard-data.index') }}');
        data = await res.json();
        document.getElementById('loading').hidden = true;
        document.getElementById('app').hidden = false;
        renderAll();
    }

    async function generateReport() {
        generating = true;
        renderOverview();
        try {
            const res = await api('{{ route('dashboard-data.store') }}', { method: 'POST' });
            if (!res.ok) {
                const d = await res.json();
                alert(d.error || 'Erro ao gerar relatório');
                return;
            }
            await load();
            setTab('report');
        } finally {
            generating = false;
            renderOverview();
        }
    }

    document.querySelectorAll('.tab').forEach((btn) => {
        btn.addEventListener('click', () => setTab(btn.dataset.tab));
    });

    document.getElementById('btn-refresh').addEventListener('click', load);
    document.getElementById('btn-generate').addEventListener('click', generateReport);
    document.getElementById('btn-generate-first').addEventListener('click', generateReport);
    document.getElementById('btn-generate-again').addEventListener('click', generateReport);
    document.getElementById('search').addEventListener('input', renderResponses);

    document.getElementById('btn-toggle-token').addEventListener('click', (e) => {
        const codeEl = document.getElementById('ingest-token');
        const visible = codeEl.dataset.visible === '1';
        codeEl.textContent = visible ? '••••••••••••••••••••••••' : codeEl.dataset.token;
        codeEl.dataset.visible = visible ? '0' : '1';
    });

    document.getElementById('btn-regenerate-token').addEventListener('click', async () => {
        if (!confirm('Gerar um novo token vai invalidar o atual imediatamente. O bot vai parar de conseguir enviar dados até você atualizar o token nele. Continuar?')) {
            return;
        }
        const res = await api('{{ route('integration.token.regenerate') }}', { method: 'POST' });
        if (!res.ok) {
            alert('Erro ao gerar novo token.');
            return;
        }
        const { token } = await res.json();
        const codeEl = document.getElementById('ingest-token');
        codeEl.dataset.token = token;
        codeEl.dataset.visible = '1';
        codeEl.textContent = token;
        document.getElementById('btn-copy-token').dataset.copy = token;
    });

    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(btn.dataset.copy);
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = btn.dataset.copy;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                ta.remove();
            }
            const original = btn.innerHTML;
            btn.innerHTML = '<svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 10.5l3.5 3.5 7.5-8"/></svg>';
            setTimeout(() => { btn.innerHTML = original; }, 1500);
        });
    });

    document.getElementById('responses-list').addEventListener('click', async (e) => {
        const toggle = e.target.closest('[data-toggle]');
        const del = e.target.closest('[data-delete]');

        if (del) {
            const id = del.dataset.delete;
            if (del.dataset.confirm !== '1') {
                del.dataset.confirm = '1';
                del.classList.add('danger-confirm');
                del.innerHTML = '<svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M5 5l10 10M15 5L5 15"/></svg>';
                del.title = 'Clique novamente para confirmar';
                setTimeout(() => {
                    if (del.dataset.confirm !== '1') return;
                    del.dataset.confirm = '';
                    del.classList.remove('danger-confirm');
                    del.title = 'Remover registro';
                    del.innerHTML = '<svg class="icon-sm" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h12M8 6V4.5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1V6M6 6l.6 9.4a1 1 0 0 0 1 .9h4.8a1 1 0 0 0 1-.9L14 6"/></svg>';
                }, 3000);
                return;
            }

            const res = await api(`{{ url('/respondents') }}/${id}`, { method: 'DELETE' });
            if (res.ok) {
                data.respondents = data.respondents.filter((r) => String(r.id) !== String(id));
                data.stats.total -= 1;
                renderAll();
            } else {
                const d = await res.json();
                alert(d.error || 'Erro ao deletar.');
            }
            return;
        }

        if (toggle) {
            const id = toggle.dataset.toggle;
            const body = document.querySelector(`[data-body="${id}"]`);
            const arrow = document.querySelector(`[data-arrow="${id}"] svg`);
            body.hidden = !body.hidden;
            arrow.classList.toggle('open', !body.hidden);
        }
    });

    load();
})();
</script>
</body>
</html>

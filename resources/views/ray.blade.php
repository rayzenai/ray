<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ray Debug Viewer</title>
    <style>
        :root {
            --bg-primary: #0f172a; --bg-secondary: #1e293b; --bg-card: #334155;
            --text-primary: #f1f5f9; --text-secondary: #94a3b8; --text-muted: #64748b;
            --accent: #3b82f6; --accent-hover: #2563eb; --danger: #ef4444; --border: #475569; --code-bg: #1e293b;
            --color-red: #ef4444; --color-green: #22c55e; --color-blue: #3b82f6;
            --color-yellow: #eab308; --color-purple: #a855f7; --color-orange: #f97316;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, var(--bg-primary) 0%, #1a2744 100%); color: var(--text-primary); min-height: 100vh; line-height: 1.6; }
        .container { max-width: 1400px; margin: 0 auto; padding: 1.5rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .header-left { display: flex; align-items: center; gap: 1rem; }
        .logo { font-size: 1.75rem; font-weight: 800; background: linear-gradient(135deg, var(--accent), #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header-actions { display: flex; gap: 0.75rem; align-items: center; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 500; cursor: pointer; transition: all 0.2s; border: none; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-ghost { background: transparent; color: var(--text-secondary); border: 1px solid var(--border); }
        .btn-ghost:hover { background: var(--bg-card); }
        .btn-icon { padding: 0.5rem; }

        /* Tabs */
        .tabs { display: flex; gap: 0.25rem; margin-bottom: 1rem; background: var(--bg-secondary); padding: 0.25rem; border-radius: 0.75rem; width: fit-content; }
        .tab { padding: 0.625rem 1.25rem; border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem; font-weight: 500; color: var(--text-muted); transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem; border: none; background: transparent; }
        .tab:hover { color: var(--text-primary); background: var(--bg-card); }
        .tab.active { background: var(--accent); color: white; }
        .tab-count { font-size: 0.75rem; background: rgba(255,255,255,0.2); padding: 0.125rem 0.5rem; border-radius: 9999px; }
        .tab.active .tab-count { background: rgba(255,255,255,0.3); }

        .controls { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; margin-bottom: 1rem; }
        .search-wrapper { position: relative; flex: 1; max-width: 350px; }
        .search-input { width: 100%; padding: 0.75rem 1rem 0.75rem 2.75rem; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 0.5rem; color: var(--text-primary); font-size: 0.875rem; }
        .search-input:focus { outline: none; border-color: var(--accent); }
        .search-icon { position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
        .toggle-container { display: flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); font-size: 0.8125rem; }
        .toggle { position: relative; width: 40px; height: 22px; background: var(--bg-card); border-radius: 9999px; cursor: pointer; }
        .toggle.active { background: var(--accent); }
        .toggle::after { content: ''; position: absolute; top: 2px; left: 2px; width: 18px; height: 18px; background: white; border-radius: 9999px; transition: transform 0.2s; }
        .toggle.active::after { transform: translateX(18px); }

        .entries { display: flex; flex-direction: column; gap: 0.75rem; }
        .entry { background: var(--bg-secondary); border-radius: 0.5rem; overflow: hidden; animation: slideIn 0.25s ease; border-left: 4px solid var(--border); }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .entry:hover { box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25); }
        .entry.color-red { border-left-color: var(--color-red); }
        .entry.color-green { border-left-color: var(--color-green); }
        .entry.color-blue { border-left-color: var(--color-blue); }
        .entry.color-yellow { border-left-color: var(--color-yellow); }
        .entry.color-purple { border-left-color: var(--color-purple); }
        .entry.color-orange { border-left-color: var(--color-orange); }
        .entry.color-default { border-left-color: var(--accent); }
        .entry-header { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: var(--bg-card); gap: 1rem; cursor: pointer; }
        .entry-header:hover { background: #3d4f66; }
        .entry-meta { display: flex; flex-direction: column; gap: 0.125rem; flex: 1; min-width: 0; }
        .entry-title-row { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .entry-title { font-weight: 600; font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .entry-type { font-size: 0.625rem; padding: 0.125rem 0.375rem; background: rgba(59, 130, 246, 0.2); color: var(--accent); border-radius: 0.25rem; }
        .entry-category { font-size: 0.625rem; padding: 0.125rem 0.375rem; background: rgba(168, 85, 247, 0.2); color: var(--color-purple); border-radius: 0.25rem; text-transform: uppercase; }
        .entry-location { font-size: 0.6875rem; color: var(--text-muted); font-family: 'SF Mono', monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .entry-time { font-size: 0.625rem; color: var(--text-secondary); background: var(--bg-secondary); padding: 0.25rem 0.5rem; border-radius: 0.25rem; white-space: nowrap; }
        .entry-actions { display: flex; gap: 0.25rem; align-items: center; }
        .entry-body { padding: 0.875rem 1rem; display: none; }
        .entry-body.expanded { display: block; }
        .entry-data { background: var(--code-bg); border-radius: 0.375rem; padding: 0.875rem; overflow-x: auto; font-family: 'SF Mono', SFMono-Regular, Consolas, monospace; font-size: 0.75rem; line-height: 1.6; white-space: pre-wrap; word-break: break-word; }
        .json-key { color: #7dd3fc; } .json-string { color: #86efac; } .json-number { color: #fcd34d; } .json-boolean { color: #f472b6; } .json-null { color: #a78bfa; }
        .empty-state { text-align: center; padding: 3rem 2rem; color: var(--text-muted); }
        .empty-state-icon { font-size: 3rem; margin-bottom: 0.75rem; opacity: 0.5; }
        .empty-state h3 { font-size: 1.125rem; color: var(--text-secondary); margin-bottom: 0.375rem; }
        .empty-state code { display: inline-block; margin-top: 0.75rem; background: var(--bg-card); padding: 0.5rem 1rem; border-radius: 0.375rem; font-family: monospace; color: var(--accent); font-size: 0.8125rem; }
        .copy-btn { background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 0.25rem; border-radius: 0.25rem; }
        .copy-btn:hover { color: var(--text-primary); background: var(--bg-secondary); }
        .copy-btn.copied { color: var(--color-green); }
        .chevron { transition: transform 0.2s; }
        .chevron.rotated { transform: rotate(180deg); }
        .color-btn { background: transparent; border: none; cursor: pointer; opacity: 0.5; transition: all 0.2s; padding: 0.25rem; border-radius: 0.25rem; position: relative; display: flex; align-items: center; justify-content: center; }
        .color-btn:hover { opacity: 0.8; transform: scale(1.1); }
        .color-btn.active { opacity: 1; background: var(--bg-card); }
        .color-btn::after { content: attr(data-tooltip); position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: var(--bg-card); color: var(--text-primary); padding: 0.375rem 0.625rem; border-radius: 0.375rem; font-size: 0.6875rem; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.15s; margin-bottom: 0.375rem; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        .color-btn:hover::after { opacity: 1; }
        .color-dot { display: inline-block; width: 14px; height: 14px; border-radius: 50%; border: 2px solid currentColor; }
        .dot-all { background: linear-gradient(135deg, var(--color-red) 0%, var(--color-yellow) 50%, var(--color-green) 100%); border-color: var(--text-muted); }
        .dot-red { background: var(--color-red); border-color: var(--color-red); }
        .dot-orange { background: var(--color-orange); border-color: var(--color-orange); }
        .dot-yellow { background: var(--color-yellow); border-color: var(--color-yellow); }
        .dot-green { background: var(--color-green); border-color: var(--color-green); }
        .dot-blue { background: var(--color-blue); border-color: var(--color-blue); }
        @media (max-width: 768px) { .container { padding: 1rem; } .header, .controls { flex-direction: column; align-items: stretch; } .tabs { width: 100%; overflow-x: auto; } }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <h1 class="logo">Ray Debug</h1>
            </div>
            <div class="header-actions">
                <div class="toggle-container"><span>Auto</span><div class="toggle" id="auto-refresh-toggle"></div></div>
                <button class="btn btn-primary" onclick="location.reload()">Refresh</button>
                <button class="btn btn-danger" onclick="clearAll()" {{ count($entries) === 0 ? 'disabled' : '' }}>Clear</button>
            </div>
        </header>

        @php
            $allCount = count($entries);
            $debugEntries = array_filter($entries, fn($e) => ($e['category'] ?? 'debug') === 'debug');
            $queryEntries = array_filter($entries, fn($e) => ($e['category'] ?? '') === 'query');
            $requestEntries = array_filter($entries, fn($e) => ($e['category'] ?? '') === 'request');
        @endphp

        <div class="tabs">
            <button class="tab active" data-category="debug">Debug<span class="tab-count">{{ count($debugEntries) }}</span></button>
            <button class="tab" data-category="request">Requests<span class="tab-count">{{ count($requestEntries) }}</span></button>
            <button class="tab" data-category="query">Queries<span class="tab-count">{{ count($queryEntries) }}</span></button>
        </div>
        <div class="filter-hint" style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <span style="opacity: 0.7;">Requests >100ms or >25MB | Queries >100ms</span>
            <div class="color-filters" style="display: flex; gap: 0.5rem; align-items: center;">
                <span style="font-size: 0.6875rem; opacity: 0.6;">Filter:</span>
                <button class="color-btn active" data-color="all" data-tooltip="All"><span class="color-dot dot-all"></span></button>
                <button class="color-btn" data-color="red" data-tooltip="Severe (500ms+ / N+1)"><span class="color-dot dot-red"></span></button>
                <button class="color-btn" data-color="orange" data-tooltip="Danger (200-500ms)"><span class="color-dot dot-orange"></span></button>
                <button class="color-btn" data-color="yellow" data-tooltip="Warning (100-200ms)"><span class="color-dot dot-yellow"></span></button>
                <button class="color-btn" data-color="green" data-tooltip="Success"><span class="color-dot dot-green"></span></button>
                <button class="color-btn" data-color="blue" data-tooltip="Info"><span class="color-dot dot-blue"></span></button>
            </div>
        </div>

        <div class="controls">
            <div class="search-wrapper">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" class="search-input" id="search-input" placeholder="Search... (Cmd+K)" autocomplete="off">
            </div>
        </div>

        <div class="entries" id="entries-container">
            @if(count($entries) === 0)
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>No debug entries yet</h3>
                    <p>Start debugging with ray() or browse pages to see slow queries</p>
                    <code>ray($data)->green();</code>
                </div>
            @else
                @foreach($entries as $entry)
                    <div class="entry color-{{ $entry['color'] ?? 'default' }}" data-id="{{ $entry['id'] }}" data-title="{{ strtolower($entry['title']) }}" data-content="{{ strtolower(json_encode($entry['data'])) }}" data-category="{{ $entry['category'] ?? 'debug' }}">
                        <div class="entry-header" onclick="toggleEntry(this)">
                            <div class="entry-meta">
                                <div class="entry-title-row">
                                    <span class="entry-title">{{ $entry['title'] }}</span>
                                    <span class="entry-category">{{ $entry['category'] ?? 'debug' }}</span>
                                    <span class="entry-type">{{ $entry['type'] }}</span>
                                </div>
                                @if($entry['file'])<span class="entry-location">{{ $entry['file'] }}:{{ $entry['line'] }}</span>@endif
                            </div>
                            <div class="entry-actions" onclick="event.stopPropagation()">
                                <span class="entry-time">{{ $entry['timestamp_human'] }}</span>
                                <button class="copy-btn" onclick="copyEntry(this)" title="Copy"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                                <button class="btn btn-icon btn-ghost" onclick="deleteEntry('{{ $entry['id'] }}')" title="Delete"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
                                <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                        <div class="entry-body"><pre class="entry-data">{!! formatDebugData($entry['data']) !!}</pre></div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let autoRefreshInterval = null;
        let activeCategory = 'debug';
        let activeColor = 'all';
        const searchInput = document.getElementById('search-input');

        function filterEntries() {
            const query = searchInput.value.toLowerCase().trim();
            const entries = document.querySelectorAll('.entry');
            entries.forEach(entry => {
                const matchesSearch = query === '' || (entry.dataset.title || '').includes(query) || (entry.dataset.content || '').includes(query);
                const matchesCategory = entry.dataset.category === activeCategory;
                const entryColor = entry.className.match(/color-(\w+)/)?.[1] || 'default';
                const matchesColor = activeColor === 'all' || entryColor === activeColor;
                entry.style.display = matchesSearch && matchesCategory && matchesColor ? '' : 'none';
            });
        }

        filterEntries();

        searchInput.addEventListener('input', filterEntries);

        document.querySelectorAll('.tab').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeCategory = this.dataset.category;
                filterEntries();
            });
        });

        document.querySelectorAll('.color-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeColor = this.dataset.color;
                filterEntries();
            });
        });

        document.getElementById('auto-refresh-toggle').addEventListener('click', function() {
            this.classList.toggle('active');
            if (this.classList.contains('active')) autoRefreshInterval = setInterval(() => location.reload(), 3000);
            else { clearInterval(autoRefreshInterval); autoRefreshInterval = null; }
        });

        function clearAll() {
            if (!confirm('Clear all entries?')) return;
            fetch('{{ url("/debug/ray/clear") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
        }

        function deleteEntry(id) {
            fetch(`{{ url("/debug/ray") }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } })
            .then(r => r.json()).then(d => { if (d.success) { document.querySelector(`.entry[data-id="${id}"]`)?.remove(); filterEntries(); } });
        }

        function toggleEntry(header) {
            const body = header.nextElementSibling;
            const chevron = header.querySelector('.chevron');
            body.classList.toggle('expanded');
            chevron.classList.toggle('rotated');
        }

        function copyEntry(btn) {
            const entry = btn.closest('.entry');
            const data = entry.querySelector('.entry-data').textContent;
            navigator.clipboard.writeText(data).then(() => {
                btn.classList.add('copied');
                setTimeout(() => btn.classList.remove('copied'), 1500);
            });
        }

        document.addEventListener('keydown', e => { if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); searchInput.focus(); } });
    </script>
</body>
</html>

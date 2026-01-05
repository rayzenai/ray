<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ray Debug</title>
    <style>
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --bg-hover: #30363d;
            --border: #30363d;
            --border-muted: #21262d;
            --text-primary: #e6edf3;
            --text-secondary: #8b949e;
            --text-muted: #6e7681;
            --accent: #58a6ff;
            --accent-muted: #388bfd26;
            --danger: #f85149;
            --warning: #d29922;
            --success: #3fb950;
            --info: #58a6ff;
            --purple: #a371f7;
            --orange: #db6d28;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.5;
            min-height: 100vh;
        }

        .app {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-muted);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--purple) 100%);
            border-radius: 6px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--bg-secondary);
            color: var(--text-secondary);
            cursor: pointer;
            transition: 0.15s ease;
        }

        .btn:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border-color: var(--border);
        }

        .btn-danger {
            color: var(--danger);
            border-color: transparent;
        }

        .btn-danger:hover {
            background: rgba(248, 81, 73, 0.1);
            border-color: var(--danger);
        }

        .btn-icon {
            padding: 5px 8px;
        }

        .btn svg {
            width: 14px;
            height: 14px;
        }

        /* Toggle */
        .toggle-group {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .toggle {
            width: 32px;
            height: 18px;
            background: var(--bg-tertiary);
            border-radius: 9px;
            cursor: pointer;
            position: relative;
            transition: 0.2s;
        }

        .toggle::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 14px;
            height: 14px;
            background: var(--text-muted);
            border-radius: 50%;
            transition: 0.2s;
        }

        .toggle.active {
            background: var(--accent);
        }

        .toggle.active::after {
            transform: translateX(14px);
            background: white;
        }

        /* Tabs & Filters */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .tabs {
            display: flex;
            gap: 4px;
            background: var(--bg-secondary);
            padding: 4px;
            border-radius: 8px;
        }

        .tab {
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            background: transparent;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.15s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .tab:hover {
            color: var(--text-primary);
        }

        .tab.active {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .tab-count {
            font-size: 11px;
            padding: 1px 6px;
            background: var(--bg-tertiary);
            border-radius: 10px;
            color: var(--text-muted);
        }

        .tab.active .tab-count {
            background: var(--bg-hover);
            color: var(--text-secondary);
        }

        .filters {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-box {
            position: relative;
        }

        .search-box svg {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            color: var(--text-muted);
        }

        .search-input {
            width: 220px;
            padding: 6px 12px 6px 32px;
            font-size: 13px;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-primary);
            outline: none;
            transition: 0.15s;
        }

        .search-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-muted);
        }

        .search-input::placeholder {
            color: var(--text-muted);
        }

        .color-filters {
            display: flex;
            gap: 2px;
            background: var(--bg-secondary);
            padding: 4px;
            border-radius: 6px;
        }

        .color-filter {
            width: 24px;
            height: 24px;
            border: none;
            background: transparent;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.15s;
            opacity: 0.5;
        }

        .color-filter:hover {
            opacity: 0.8;
            background: var(--bg-tertiary);
        }

        .color-filter.active {
            opacity: 1;
            background: var(--bg-tertiary);
        }

        .color-filter .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .dot-all { background: conic-gradient(var(--danger), var(--warning), var(--success), var(--info), var(--danger)); }
        .dot-red { background: var(--danger); }
        .dot-orange { background: var(--orange); }
        .dot-yellow { background: var(--warning); }
        .dot-green { background: var(--success); }
        .dot-blue { background: var(--info); }
        .dot-purple { background: var(--purple); }

        /* Entries */
        .entries {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .entry {
            background: var(--bg-secondary);
            border: 1px solid var(--border-muted);
            border-radius: 8px;
            overflow: hidden;
            transition: 0.15s;
        }

        .entry:hover {
            border-color: var(--border);
        }

        .entry-color {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
        }

        .entry.color-red .entry-color { background: var(--danger); }
        .entry.color-orange .entry-color { background: var(--orange); }
        .entry.color-yellow .entry-color { background: var(--warning); }
        .entry.color-green .entry-color { background: var(--success); }
        .entry.color-blue .entry-color { background: var(--info); }
        .entry.color-purple .entry-color { background: var(--purple); }
        .entry.color-default .entry-color { background: var(--text-muted); }

        .entry-header {
            position: relative;
            padding: 12px 12px 12px 16px;
            cursor: pointer;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: start;
        }

        .entry-header:hover {
            background: var(--bg-tertiary);
        }

        .entry-main {
            min-width: 0;
        }

        .entry-title-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .entry-label {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-primary);
        }

        .entry-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .badge-debug { background: var(--accent-muted); color: var(--accent); }
        .badge-query { background: rgba(163, 113, 247, 0.15); color: var(--purple); }
        .badge-request { background: rgba(219, 109, 40, 0.15); color: var(--orange); }

        .entry-preview {
            font-family: 'SF Mono', 'Fira Code', Consolas, monospace;
            font-size: 12px;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .entry-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 6px;
        }

        .entry-location {
            font-size: 11px;
            color: var(--text-muted);
            font-family: 'SF Mono', 'Fira Code', Consolas, monospace;
        }

        .entry-location:hover {
            color: var(--accent);
        }

        .entry-time {
            font-size: 11px;
            color: var(--text-muted);
        }

        .entry-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            opacity: 0;
            transition: 0.15s;
        }

        .entry:hover .entry-actions {
            opacity: 1;
        }

        .entry-action {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            border-radius: 4px;
            color: var(--text-muted);
            cursor: pointer;
            transition: 0.15s;
        }

        .entry-action:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .entry-action.copied {
            color: var(--success);
        }

        .entry-action svg {
            width: 14px;
            height: 14px;
        }

        .chevron {
            transition: transform 0.2s;
        }

        .chevron.open {
            transform: rotate(180deg);
        }

        .entry-body {
            display: none;
            padding: 0 16px 12px;
        }

        .entry-body.expanded {
            display: block;
        }

        .entry-data {
            background: var(--bg-primary);
            border: 1px solid var(--border-muted);
            border-radius: 6px;
            padding: 12px;
            font-family: 'SF Mono', 'Fira Code', Consolas, monospace;
            font-size: 12px;
            line-height: 1.6;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
            color: var(--text-secondary);
        }

        .json-key { color: #79c0ff; }
        .json-string { color: #a5d6ff; }
        .json-number { color: #ffa657; }
        .json-boolean { color: #ff7b72; }
        .json-null { color: var(--purple); }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 16px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-icon svg {
            width: 24px;
            height: 24px;
            color: var(--text-muted);
        }

        .empty-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .empty-text {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        .empty-code {
            display: inline-block;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: 6px;
            font-family: 'SF Mono', 'Fira Code', Consolas, monospace;
            font-size: 13px;
            color: var(--accent);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .app { padding: 16px; }
            .toolbar { flex-direction: column; align-items: stretch; }
            .filters { flex-wrap: wrap; }
            .search-input { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="app">
        <header class="header">
            <div class="header-left">
                <div class="logo">
                    <div class="logo-icon"></div>
                    <span>Ray</span>
                </div>
            </div>
            <div class="header-actions">
                <div class="toggle-group">
                    <span>Auto</span>
                    <div class="toggle" id="auto-refresh"></div>
                </div>
                <button class="btn btn-icon" onclick="location.reload()" title="Refresh">
                    <svg viewBox="0 0 16 16" fill="currentColor"><path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/></svg>
                </button>
                <button class="btn btn-danger" onclick="clearAll()" {{ count($entries) === 0 ? 'disabled' : '' }}>Clear</button>
            </div>
        </header>

        @php
            $debugEntries = array_filter($entries, fn($e) => ($e['category'] ?? 'debug') === 'debug');
            $queryEntries = array_filter($entries, fn($e) => ($e['category'] ?? '') === 'query');
            $requestEntries = array_filter($entries, fn($e) => ($e['category'] ?? '') === 'request');
        @endphp

        <div class="toolbar">
            <div class="tabs">
                <button class="tab active" data-category="debug">
                    Debug<span class="tab-count">{{ count($debugEntries) }}</span>
                </button>
                <button class="tab" data-category="request">
                    Requests<span class="tab-count">{{ count($requestEntries) }}</span>
                </button>
                <button class="tab" data-category="query">
                    Queries<span class="tab-count">{{ count($queryEntries) }}</span>
                </button>
            </div>

            <div class="filters">
                <div class="search-box">
                    <svg viewBox="0 0 16 16" fill="currentColor"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                    <input type="text" class="search-input" id="search-input" placeholder="Search...">
                </div>
                <div class="color-filters">
                    <button class="color-filter active" data-color="all" title="All"><span class="dot dot-all"></span></button>
                    <button class="color-filter" data-color="red" title="Error"><span class="dot dot-red"></span></button>
                    <button class="color-filter" data-color="orange" title="Warning"><span class="dot dot-orange"></span></button>
                    <button class="color-filter" data-color="yellow" title="Caution"><span class="dot dot-yellow"></span></button>
                    <button class="color-filter" data-color="green" title="Success"><span class="dot dot-green"></span></button>
                    <button class="color-filter" data-color="blue" title="Info"><span class="dot dot-blue"></span></button>
                </div>
            </div>
        </div>

        <div class="entries" id="entries">
            @if(count($entries) === 0)
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg>
                    </div>
                    <h3 class="empty-title">No debug entries</h3>
                    <p class="empty-text">Start debugging by adding ray() to your code</p>
                    <code class="empty-code">ray($variable)->green();</code>
                </div>
            @else
                @foreach($entries as $entry)
                    @php
                        $preview = is_string($entry['data'])
                            ? $entry['data']
                            : json_encode($entry['data'], JSON_UNESCAPED_SLASHES);
                        $preview = strlen($preview) > 100 ? substr($preview, 0, 100) . '...' : $preview;
                        $category = $entry['category'] ?? 'debug';
                    @endphp
                    <div class="entry color-{{ $entry['color'] ?? 'default' }}"
                         data-id="{{ $entry['id'] }}"
                         data-title="{{ strtolower($entry['title']) }}"
                         data-content="{{ strtolower(json_encode($entry['data'])) }}"
                         data-category="{{ $category }}">
                        <div class="entry-color"></div>
                        <div class="entry-header" onclick="toggleEntry(this)">
                            <div class="entry-main">
                                <div class="entry-title-row">
                                    <span class="entry-label">{{ $entry['title'] }}</span>
                                    <span class="entry-badge badge-{{ $category }}">{{ $category }}</span>
                                </div>
                                <div class="entry-preview">{{ $preview }}</div>
                                <div class="entry-meta">
                                    @if($entry['file'])
                                        <span class="entry-location">{{ $entry['file'] }}:{{ $entry['line'] }}</span>
                                    @endif
                                    <span class="entry-time">{{ $entry['timestamp_human'] }}</span>
                                </div>
                            </div>
                            <div class="entry-actions" onclick="event.stopPropagation()">
                                <button class="entry-action" onclick="copyEntry(this)" title="Copy">
                                    <svg viewBox="0 0 16 16" fill="currentColor"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
                                </button>
                                <button class="entry-action" onclick="deleteEntry('{{ $entry['id'] }}')" title="Delete">
                                    <svg viewBox="0 0 16 16" fill="currentColor"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                                </button>
                                <svg class="chevron" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;color:var(--text-muted)"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                            </div>
                        </div>
                        <div class="entry-body">
                            <pre class="entry-data">{!! formatDebugData($entry['data']) !!}</pre>
                        </div>
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
            document.querySelectorAll('.entry').forEach(entry => {
                const matchesSearch = !query ||
                    (entry.dataset.title || '').includes(query) ||
                    (entry.dataset.content || '').includes(query);
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

        document.querySelectorAll('.color-filter').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.color-filter').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeColor = this.dataset.color;
                filterEntries();
            });
        });

        document.getElementById('auto-refresh').addEventListener('click', function() {
            this.classList.toggle('active');
            if (this.classList.contains('active')) {
                autoRefreshInterval = setInterval(() => location.reload(), 2000);
            } else {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        });

        function clearAll() {
            if (!confirm('Clear all entries?')) return;
            fetch('{{ url("/debug/ray/clear") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
        }

        function deleteEntry(id) {
            fetch(`{{ url("/debug/ray") }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    document.querySelector(`.entry[data-id="${id}"]`)?.remove();
                }
            });
        }

        function toggleEntry(header) {
            const body = header.nextElementSibling;
            const chevron = header.querySelector('.chevron');
            body.classList.toggle('expanded');
            chevron.classList.toggle('open');
        }

        function copyEntry(btn) {
            const entry = btn.closest('.entry');
            const data = entry.querySelector('.entry-data').textContent;
            navigator.clipboard.writeText(data).then(() => {
                btn.classList.add('copied');
                setTimeout(() => btn.classList.remove('copied'), 1500);
            });
        }

        document.addEventListener('keydown', e => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
        });
    </script>
</body>
</html>

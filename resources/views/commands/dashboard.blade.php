<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Artisan Command Control Center | Laravel {{ $health['laravel_version'] }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bs-body-font-family: 'Inter', system-ui, -apple-system, sans-serif;
            --code-font: 'Fira Code', monospace;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: var(--bs-body-font-family);
        }
        .navbar-custom {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.25);
        }
        .stat-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }
        .command-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .command-card:hover {
            border-color: #6366f1;
            box-shadow: 0 8px 24px -4px rgba(99, 102, 241, 0.12);
            transform: translateY(-2px);
        }
        .terminal-container {
            background: #090d16;
            border-radius: 14px;
            border: 1px solid #1e293b;
            overflow: hidden;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
        }
        .terminal-header {
            background: #0f172a;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #1e293b;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .terminal-body {
            padding: 1.25rem;
            min-height: 400px;
            max-height: 540px;
            overflow-y: auto;
            color: #e2e8f0;
            font-family: var(--code-font);
            font-size: 0.88rem;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .terminal-body::-webkit-scrollbar {
            width: 8px;
        }
        .terminal-body::-webkit-scrollbar-track {
            background: #090d16;
        }
        .terminal-body::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .nav-pills .nav-link {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: #64748b;
            font-weight: 500;
        }
        .nav-pills .nav-link.active {
            background-color: #4f46e5;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .table-custom th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        .filter-badge {
            cursor: pointer;
            user-select: none;
            transition: all 0.2s;
        }
        .filter-badge.active {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-custom navbar-dark py-3">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-25 p-2 rounded-3 text-info">
                    <i class="bi bi-terminal-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-0 text-white fw-bold">Artisan Control Center</h5>
                    <small class="text-slate-400 text-light opacity-75">Interactive Artisan Web Terminal & Management Suite</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark border border-secondary text-light px-3 py-2">
                    <i class="bi bi-code-square me-1 text-primary"></i> Laravel v{{ $health['laravel_version'] }}
                </span>
                <span class="badge bg-dark border border-secondary text-light px-3 py-2">
                    <i class="bi bi-gear-fill me-1 text-warning"></i> PHP v{{ $health['php_version'] }}
                </span>
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2">
                    <i class="bi bi-circle-fill me-1 small"></i> {{ strtoupper($health['environment']) }}
                </span>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid px-4 py-4">

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Quick Stats Cards Bar -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <div class="stat-card d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Database Connection</small>
                        <h6 class="mb-0 mt-1 fw-bold {{ $health['db_connected'] ? 'text-success' : 'text-danger' }}">
                            <i class="bi {{ $health['db_connected'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                            {{ $health['db_connected'] ? 'Online (' . $health['db_latency'] . ' ms)' : 'Disconnected' }}
                        </h6>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="bi bi-database fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="stat-card d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Disk Free Space</small>
                        <h6 class="mb-0 mt-1 fw-bold text-dark">{{ $health['disk_free'] }} Free</h6>
                        <small class="text-muted">{{ $health['disk_used_percent'] }}% used of {{ $health['disk_total'] }}</small>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="bi bi-hdd-network fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="stat-card d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Memory Usage</small>
                        <h6 class="mb-0 mt-1 fw-bold text-dark">{{ $health['memory_used'] }}</h6>
                        <small class="text-muted">Peak: {{ $health['memory_peak'] }}</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="bi bi-memory fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="stat-card d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Stored Backups</small>
                        <h6 class="mb-0 mt-1 fw-bold text-dark">{{ count($backups) }} Backups</h6>
                        <small class="text-muted">Storage: storage/backups/</small>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="bi bi-archive fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation Tabs -->
        <ul class="nav nav-pills mb-4 gap-2" id="dashboardTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center gap-2" id="runner-tab" data-bs-toggle="pill" data-bs-target="#runner-content" type="button" role="tab">
                    <i class="bi bi-terminal"></i> Command Runner
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2" id="backups-tab" data-bs-toggle="pill" data-bs-target="#backups-content" type="button" role="tab">
                    <i class="bi bi-database-check"></i> Database Backups
                    <span class="badge bg-light text-dark rounded-pill">{{ count($backups) }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2" id="history-tab" data-bs-toggle="pill" data-bs-target="#history-content" type="button" role="tab">
                    <i class="bi bi-clock-history"></i> Execution Audit Log
                    <span class="badge bg-light text-dark rounded-pill">{{ count($logs) }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2" id="diagnostics-tab" data-bs-toggle="pill" data-bs-target="#diagnostics-content" type="button" role="tab">
                    <i class="bi bi-speedometer2"></i> Server Diagnostics
                </button>
            </li>
        </ul>

        <div class="tab-content" id="dashboardTabContent">

            <!-- TAB 1: COMMAND RUNNER -->
            <div class="tab-pane fade show active" id="runner-content" role="tabpanel">
                <div class="row g-4">
                    <!-- Left: Command Grid & Filter -->
                    <div class="col-12 col-xl-7">
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-grid-fill text-primary fs-5"></i>
                                        <h6 class="fw-bold mb-0">Available Commands</h6>
                                    </div>
                                    <!-- Category Filters -->
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-light text-dark border filter-badge active px-3 py-2 rounded-pill" onclick="filterCommands('all', this)">All</span>
                                        @foreach($categories as $cat)
                                            <span class="badge bg-light text-dark border filter-badge px-3 py-2 rounded-pill" onclick="filterCommands('{{ strtolower($cat) }}', this)">{{ $cat }}</span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="row g-3" id="commandGrid">
                                    @foreach($catalog as $cmd)
                                        <div class="col-12 col-md-6 command-item" data-category="{{ strtolower($cmd['category']) }}">
                                            <div class="command-card p-3">
                                                <div>
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="badge bg-{{ $cmd['badge'] }} bg-opacity-10 text-{{ $cmd['badge'] }} border border-{{ $cmd['badge'] }} border-opacity-25 px-2 py-1 rounded">
                                                            <i class="bi {{ $cmd['icon'] }} me-1"></i> {{ $cmd['category'] }}
                                                        </span>
                                                        <code class="small text-muted">{{ $cmd['command'] }}</code>
                                                    </div>
                                                    <h6 class="fw-bold text-dark mb-1">{{ $cmd['name'] }}</h6>
                                                    <p class="text-muted small mb-3" style="min-height: 40px;">{{ $cmd['description'] }}</p>
                                                </div>
                                                <div class="d-flex gap-2 pt-2 border-top">
                                                    @if(empty($cmd['options']))
                                                        <button class="btn btn-sm btn-primary w-100 fw-semibold d-flex align-items-center justify-content-center gap-1"
                                                            onclick="runArtisanCommand('{{ $cmd['command'] }}', {})">
                                                            <i class="bi bi-play-fill fs-6"></i> Run Command
                                                        </button>
                                                    @else
                                                        <button class="btn btn-sm btn-outline-primary flex-grow-1 fw-semibold d-flex align-items-center justify-content-center gap-1"
                                                            onclick="openConfigureModal('{{ $cmd['command'] }}')">
                                                            <i class="bi bi-sliders me-1"></i> Configure & Run
                                                        </button>
                                                        <button class="btn btn-sm btn-primary px-3" title="Quick Run with defaults"
                                                            onclick="runArtisanCommand('{{ $cmd['command'] }}', {})">
                                                            <i class="bi bi-lightning-charge-fill"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Web Terminal Output -->
                    <div class="col-12 col-xl-5">
                        <div class="terminal-container sticky-top" style="top: 20px;">
                            <!-- Terminal Header -->
                            <div class="terminal-header">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle bg-danger d-inline-block" style="width: 12px; height: 12px;"></span>
                                    <span class="rounded-circle bg-warning d-inline-block" style="width: 12px; height: 12px;"></span>
                                    <span class="rounded-circle bg-success d-inline-block" style="width: 12px; height: 12px;"></span>
                                    <span class="text-white-50 ms-2 small fw-semibold font-monospace" id="terminalTitle">terminal: artisan</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary text-light px-2 py-1 font-monospace small" id="terminalStatusBadge">IDLE</span>
                                    <span class="badge bg-dark text-white-50 px-2 py-1 font-monospace small" id="terminalDurationBadge">0.00s</span>
                                    <button class="btn btn-sm btn-dark text-white-50 border-0 p-1" title="Copy Output" onclick="copyTerminalOutput()">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                    <button class="btn btn-sm btn-dark text-white-50 border-0 p-1" title="Clear Screen" onclick="clearTerminal()">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Terminal Body -->
                            <div class="terminal-body" id="terminalOutput">
                                <div class="text-muted">
<span class="text-info"># Artisan Command Control Center</span>
Welcome to the interactive web artisan console.
Select a command from the left panel to execute and inspect real-time outputs.
Ready for instructions...
                                </div>
                            </div>

                            <!-- Terminal Footer Actions -->
                            <div class="p-3 bg-dark border-top border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                                <small class="text-secondary font-monospace" id="terminalTimestamp">Ready</small>
                                <button class="btn btn-sm btn-outline-light d-flex align-items-center gap-1" id="btnRerun" style="display: none !important;" onclick="rerunLastCommand()">
                                    <i class="bi bi-arrow-repeat"></i> Re-run
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: DATABASE BACKUPS -->
            <div class="tab-pane fade" id="backups-content" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Database Backup Archives</h5>
                                <p class="text-muted small mb-0">Manage point-in-time snapshots, download SQL archives, or restore the database directly.</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary d-flex align-items-center gap-2" onclick="runArtisanCommand('db:backup', {'--compress': true})">
                                    <i class="bi bi-file-earmark-zip"></i> New Gzip Backup (.sql.gz)
                                </button>
                                <button class="btn btn-outline-secondary d-flex align-items-center gap-2" onclick="runArtisanCommand('db:backup', {'--compress': false})">
                                    <i class="bi bi-filetype-sql"></i> Plain SQL Backup (.sql)
                                </button>
                            </div>
                        </div>

                        @if(empty($backups))
                            <div class="text-center py-5">
                                <i class="bi bi-archive text-muted fs-1 mb-3 d-block"></i>
                                <h6 class="fw-bold text-muted">No database backups found</h6>
                                <p class="text-muted small">Click "New Gzip Backup" above to generate your first snapshot.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-custom table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Backup File Name</th>
                                            <th>Format</th>
                                            <th>File Size</th>
                                            <th>Created Timestamp</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($backups as $b)
                                            <tr>
                                                <td class="fw-semibold font-monospace">
                                                    <i class="bi {{ $b['is_compressed'] ? 'bi-file-earmark-zip text-warning' : 'bi-filetype-sql text-primary' }} me-2 fs-5"></i>
                                                    {{ $b['filename'] }}
                                                </td>
                                                <td>
                                                    @if($b['is_compressed'])
                                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-50">Gzip Compressed (.sql.gz)</span>
                                                    @else
                                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-50">Plain SQL (.sql)</span>
                                                    @endif
                                                </td>
                                                <td class="font-monospace">{{ $b['size'] }}</td>
                                                <td class="text-muted small">{{ $b['created_at'] }}</td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-1">
                                                        <a href="{{ route('commands.backup.download', $b['filename']) }}" class="btn btn-sm btn-outline-secondary" title="Download Backup">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-danger" title="Restore this Backup" onclick="confirmRestore('{{ $b['filename'] }}')">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                        </button>
                                                        <form action="{{ route('commands.backup.delete', $b['filename']) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this backup file?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary text-danger" title="Delete Backup">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 3: EXECUTION HISTORY -->
            <div class="tab-pane fade" id="history-content" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Execution History & Audit Trail</h5>
                                <p class="text-muted small mb-0">Record of every command executed via the web dashboard or scheduled tasks.</p>
                            </div>
                            @if(count($logs) > 0)
                                <form action="{{ route('commands.logs.clear') }}" method="POST" onsubmit="return confirm('Clear all execution history logs?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                                        <i class="bi bi-trash3"></i> Clear Audit Log
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if($logs->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-journal-text text-muted fs-1 mb-3 d-block"></i>
                                <h6 class="fw-bold text-muted">No execution history recorded yet</h6>
                                <p class="text-muted small">Execute any command to see the audit trail logged here.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-custom table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th># ID</th>
                                            <th>Command</th>
                                            <th>Arguments / Options</th>
                                            <th>Status</th>
                                            <th>Exit Code</th>
                                            <th>Duration</th>
                                            <th>Executed At</th>
                                            <th class="text-end">Output</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($logs as $log)
                                            <tr>
                                                <td class="text-muted font-monospace">#{{ $log->id }}</td>
                                                <td><span class="badge bg-dark font-monospace">{{ $log->command }}</span></td>
                                                <td>
                                                    @if(!empty($log->arguments))
                                                        <code class="small text-muted">{{ json_encode($log->arguments) }}</code>
                                                    @else
                                                        <span class="text-muted small"><em>None</em></span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $log->status === 'SUCCESS' ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $log->status }}
                                                    </span>
                                                </td>
                                                <td class="font-monospace">{{ $log->exit_code }}</td>
                                                <td class="font-monospace text-muted small">{{ $log->formatted_duration }}</td>
                                                <td class="text-muted small">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewLogModal({{ $log->id }}, '{{ $log->command }}')">
                                                        <i class="bi bi-eye"></i> View Log
                                                    </button>
                                                    <div id="log-content-{{ $log->id }}" style="display: none;">{{ $log->output }}</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 4: SERVER HEALTH & DIAGNOSTICS -->
            <div class="tab-pane fade" id="diagnostics-content" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-cpu-fill text-primary"></i> System & Environment Metrics
                                </h6>
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted">PHP Version</td>
                                            <td class="fw-semibold font-monospace">{{ PHP_VERSION }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Laravel Framework</td>
                                            <td class="fw-semibold font-monospace">{{ app()->version() }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Environment</td>
                                            <td class="fw-semibold"><span class="badge bg-success">{{ app()->environment() }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Operating System</td>
                                            <td class="fw-semibold font-monospace">{{ PHP_OS_FAMILY }} ({{ php_uname('s') }})</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Default Timezone</td>
                                            <td class="fw-semibold font-monospace">{{ config('app.timezone') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Database Engine</td>
                                            <td class="fw-semibold font-monospace">{{ config('database.default') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="mt-4 pt-3 border-top d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm" onclick="runArtisanCommand('system:info', {})">
                                        <i class="bi bi-info-circle me-1"></i> Full System Info
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="runArtisanCommand('env:details', {})">
                                        <i class="bi bi-gear me-1"></i> Env Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-activity text-success"></i> Health & Resource Diagnostics
                                </h6>
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted">Database Ping</td>
                                            <td class="fw-semibold text-success"><i class="bi bi-check-circle-fill me-1"></i> Connected ({{ $health['db_latency'] }} ms)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Disk Storage Capacity</td>
                                            <td class="fw-semibold">{{ $health['disk_free'] }} free / {{ $health['disk_total'] }} ({{ $health['disk_used_percent'] }}% used)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Active Memory Usage</td>
                                            <td class="fw-semibold font-monospace">{{ $health['memory_used'] }} (Peak: {{ $health['memory_peak'] }})</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Cache Store</td>
                                            <td class="fw-semibold font-monospace">{{ config('cache.default') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Session Driver</td>
                                            <td class="fw-semibold font-monospace">{{ config('session.driver') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Queue Driver</td>
                                            <td class="fw-semibold font-monospace">{{ config('queue.default') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="mt-4 pt-3 border-top d-flex gap-2">
                                    <button class="btn btn-success btn-sm" onclick="runArtisanCommand('app:monitor', {})">
                                        <i class="bi bi-heart-pulse me-1"></i> Run Health Monitor
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="runArtisanCommand('db:optimize', {})">
                                        <i class="bi bi-tools me-1"></i> Run Table Optimizer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Dynamic Command Configuration Modal -->
    <div class="modal fade" id="configureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="configModalTitle">Configure Command</h5>
                        <small class="text-muted font-monospace" id="configModalCommandName"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <form id="configForm">
                        <div id="dynamicFormFields" class="d-flex flex-col gap-3">
                            <!-- Injected dynamically by JS -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary fw-semibold" id="btnExecuteConfigured" onclick="executeConfiguredCommand()">
                        <i class="bi bi-play-fill me-1"></i> Execute Command
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Output View Modal (for Audit Log) -->
    <div class="modal fade" id="logViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow bg-dark text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title font-monospace fw-bold" id="logModalTitle">Command Output Log</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <pre class="m-0 p-4 font-monospace" id="logModalBody" style="max-height: 500px; overflow-y: auto; color: #cbd5e1; font-size: 0.88rem;"></pre>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div class="modal fade" id="restoreConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-danger text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Database Restore</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-danger fw-semibold mb-2">Warning: This action will completely drop existing tables and restore data from the selected archive!</p>
                    <p class="text-muted small mb-3">Target Backup File:</p>
                    <div class="p-3 bg-light rounded-3 font-monospace fw-bold text-dark mb-3" id="restoreTargetFilename"></div>
                    <p class="text-muted small mb-0">Are you absolutely sure you want to proceed with the restore?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger fw-semibold" id="btnConfirmRestoreAction" onclick="proceedRestore()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Proceed with Restore
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Command Catalog definitions passed from backend
        const catalog = @json($catalog);
        const backups = @json($backups);
        let activeCommand = null;
        let lastExecuted = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Category Filter Function
        function filterCommands(cat, el) {
            document.querySelectorAll('.filter-badge').forEach(b => b.classList.remove('active'));
            el.classList.add('active');

            const items = document.querySelectorAll('.command-item');
            items.forEach(item => {
                if (cat === 'all' || item.getAttribute('data-category') === cat) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Open Configure Modal
        function openConfigureModal(commandName) {
            const cmd = catalog.find(c => c.command === commandName);
            if (!cmd) return;

            activeCommand = cmd;
            document.getElementById('configModalTitle').innerText = cmd.name;
            document.getElementById('configModalCommandName').innerText = cmd.command;

            const fieldsContainer = document.getElementById('dynamicFormFields');
            fieldsContainer.innerHTML = '';

            cmd.options.forEach(opt => {
                const wrapper = document.createElement('div');
                wrapper.className = 'mb-3';
                const fieldId = 'field_' + opt.name.replace(/[^a-zA-Z0-9]/g, '_');

                if (opt.type === 'boolean') {
                    wrapper.className = 'form-check form-switch mb-3';
                    wrapper.innerHTML = `
                        <input class="form-check-input" type="checkbox" id="${fieldId}" name="${opt.name}" ${opt.default ? 'checked' : ''}>
                        <label class="form-check-label fw-semibold" for="${fieldId}">${opt.label}</label>
                    `;
                } else if (opt.type === 'select_backup') {
                    let optionsHtml = '<option value="">-- Choose Backup File --</option>';
                    backups.forEach(b => {
                        optionsHtml += `<option value="${b.filename}">${b.filename} (${b.size})</option>`;
                    });
                    wrapper.innerHTML = `
                        <label class="form-label fw-semibold" for="${fieldId}">${opt.label}</label>
                        <select class="form-select font-monospace" id="${fieldId}" name="${opt.name}">
                            ${optionsHtml}
                        </select>
                    `;
                } else if (opt.type === 'select') {
                    let optionsHtml = '';
                    for (const [key, val] of Object.entries(opt.options)) {
                        optionsHtml += `<option value="${key}" ${key === opt.default ? 'selected' : ''}>${val}</option>`;
                    }
                    wrapper.innerHTML = `
                        <label class="form-label fw-semibold" for="${fieldId}">${opt.label}</label>
                        <select class="form-select" id="${fieldId}" name="${opt.name}">
                            ${optionsHtml}
                        </select>
                    `;
                } else {
                    wrapper.innerHTML = `
                        <label class="form-label fw-semibold" for="${fieldId}">${opt.label}</label>
                        <input type="${opt.type || 'text'}" class="form-control" id="${fieldId}" name="${opt.name}"
                            value="${opt.default !== undefined ? opt.default : ''}" placeholder="${opt.placeholder || ''}">
                    `;
                }
                fieldsContainer.appendChild(wrapper);
            });

            const modal = new bootstrap.Modal(document.getElementById('configureModal'));
            modal.show();
        }

        // Execute Configured Command from Modal
        function executeConfiguredCommand() {
            if (!activeCommand) return;
            const params = {};
            const form = document.getElementById('configForm');
            const formData = new FormData(form);

            activeCommand.options.forEach(opt => {
                const cleanKey = opt.name.replace(/^--/, '');
                if (opt.type === 'boolean') {
                    const el = form.querySelector(`[name="${opt.name}"]`);
                    params[cleanKey] = el ? el.checked : false;
                } else {
                    const el = form.querySelector(`[name="${opt.name}"]`);
                    if (el && el.value !== '') {
                        params[cleanKey] = el.value;
                    }
                }
            });

            // Hide modal
            const modalEl = document.getElementById('configureModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();

            runArtisanCommand(activeCommand.command, params);
        }

        // Run Artisan Command via AJAX
        function runArtisanCommand(commandName, params = {}) {
            lastExecuted = { command: commandName, params: params };

            // Switch to runner tab if not active
            const runnerTab = new bootstrap.Tab(document.getElementById('runner-tab'));
            runnerTab.show();

            const terminalTitle = document.getElementById('terminalTitle');
            const terminalOutput = document.getElementById('terminalOutput');
            const terminalStatusBadge = document.getElementById('terminalStatusBadge');
            const terminalDurationBadge = document.getElementById('terminalDurationBadge');
            const terminalTimestamp = document.getElementById('terminalTimestamp');
            const btnRerun = document.getElementById('btnRerun');

            terminalTitle.innerText = `terminal: php artisan ${commandName}`;
            terminalStatusBadge.className = 'badge bg-warning text-dark px-2 py-1 font-monospace small';
            terminalStatusBadge.innerText = 'RUNNING';
            terminalDurationBadge.innerText = '...';
            terminalOutput.innerHTML = `<span class="text-warning"><i class="bi bi-arrow-repeat spin"></i> Executing: php artisan ${commandName}...</span>\n`;

            const payload = { command: commandName, ...params };

            fetch("{{ route('commands.run') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    terminalStatusBadge.className = 'badge bg-success text-light px-2 py-1 font-monospace small';
                    terminalStatusBadge.innerText = 'SUCCESS (0)';
                } else {
                    terminalStatusBadge.className = 'badge bg-danger text-light px-2 py-1 font-monospace small';
                    terminalStatusBadge.innerText = `FAILED (${data.exit_code})`;
                }

                terminalDurationBadge.innerText = data.duration;
                terminalTimestamp.innerText = `Executed at ${data.created_at || new Date().toLocaleTimeString()} (Exit: ${data.exit_code})`;
                terminalOutput.innerText = data.output || '(No console output returned)';
                btnRerun.style.setProperty('display', 'inline-flex', 'important');
            })
            .catch(err => {
                terminalStatusBadge.className = 'badge bg-danger text-light px-2 py-1 font-monospace small';
                terminalStatusBadge.innerText = 'NETWORK ERROR';
                terminalOutput.innerText = 'Error dispatching command:\n' + err.toString();
            });
        }

        // Re-run Last Command
        function rerunLastCommand() {
            if (lastExecuted) {
                runArtisanCommand(lastExecuted.command, lastExecuted.params);
            }
        }

        // Clear Terminal
        function clearTerminal() {
            document.getElementById('terminalOutput').innerText = 'Ready for instructions...';
            document.getElementById('terminalStatusBadge').className = 'badge bg-secondary text-light px-2 py-1 font-monospace small';
            document.getElementById('terminalStatusBadge').innerText = 'IDLE';
            document.getElementById('terminalDurationBadge').innerText = '0.00s';
            document.getElementById('btnRerun').style.setProperty('display', 'none', 'important');
        }

        // Copy Terminal Output
        function copyTerminalOutput() {
            const text = document.getElementById('terminalOutput').innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Terminal output copied to clipboard!');
            });
        }

        // View Log Modal from History Tab
        function viewLogModal(id, cmd) {
            const content = document.getElementById('log-content-' + id).innerText;
            document.getElementById('logModalTitle').innerText = 'Command: ' + cmd + ' (# ' + id + ')';
            document.getElementById('logModalBody').innerText = content;
            new bootstrap.Modal(document.getElementById('logViewModal')).show();
        }

        // Restore Backup Modal flow
        let targetRestoreFile = null;
        function confirmRestore(filename) {
            targetRestoreFile = filename;
            document.getElementById('restoreTargetFilename').innerText = filename;
            new bootstrap.Modal(document.getElementById('restoreConfirmModal')).show();
        }

        function proceedRestore() {
            if (!targetRestoreFile) return;
            const modalEl = document.getElementById('restoreConfirmModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();

            runArtisanCommand('db:restore', { file: targetRestoreFile, force: true });
        }
    </script>
</body>
</html>

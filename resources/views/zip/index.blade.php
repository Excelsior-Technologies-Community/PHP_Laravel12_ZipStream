<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ZIP File Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            color: #333;
        }

        .topbar {
            background: #1e1e2e;
            padding: 14px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar h1 { color: #fff; font-size: 20px; font-weight: 700; }
        .topbar span { color: #a0a0b0; font-size: 13px; }

        .tab-nav {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            gap: 0;
            padding: 0 30px;
        }
        .tab-btn {
            padding: 14px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .tab-btn.active {
            color: #4f46e5;
            border-bottom-color: #4f46e5;
        }
        .tab-btn:hover { color: #4f46e5; }

        .container { max-width: 1300px; margin: 0 auto; padding: 25px 20px; }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #fff;
            padding: 18px 22px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-left: 4px solid #4f46e5;
        }
        .stat-card h2 { font-size: 30px; color: #4f46e5; font-weight: 800; }
        .stat-card p  { font-size: 12px; color: #888; margin-top: 4px; }

        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        .two-col { display: flex; gap: 22px; flex-wrap: wrap; }
        .col      { flex: 1; min-width: 280px; }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .card-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            padding: 28px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
            background: #fafafa;
            margin-bottom: 16px;
        }
        .upload-area:hover, .upload-area.drag-over {
            border-color: #4f46e5;
            background: #eef2ff;
        }
        .upload-area .icon { font-size: 36px; margin-bottom: 8px; }
        .upload-area p { font-size: 14px; color: #555; }
        .upload-area small { font-size: 12px; color: #aaa; }

        .select-all-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }
        .file-list {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 14px;
        }
        .file-item {
            display: flex;
            align-items: center;
            padding: 9px 12px;
            border-bottom: 1px solid #f0f0f0;
            gap: 10px;
        }
        .file-item:last-child { border-bottom: none; }
        .file-item:hover { background: #f9f9ff; }
        .file-item input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; accent-color: #4f46e5; }
        .file-item label { flex: 1; cursor: pointer; font-size: 13px; }
        .file-size { font-size: 11px; color: #aaa; white-space: nowrap; }
        .file-actions { display: flex; gap: 4px; }
        .file-actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 13px;
            padding: 4px 7px;
            border-radius: 5px;
            opacity: 0.6;
        }
        .file-actions button:hover { opacity: 1; background: #f0f0f0; }

        .folder-item {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f0;
            gap: 10px;
        }
        .folder-item:last-child { border-bottom: none; }
        .folder-item:hover { background: #f9f9ff; }
        .folder-item input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; accent-color: #4f46e5; }
        .folder-badge {
            background: #eef2ff;
            color: #4f46e5;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: auto;
        }

        .password-wrap { margin: 12px 0; }
        .password-wrap input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
        }
        .password-wrap input:focus { outline: none; border-color: #4f46e5; }

        .progress-wrap { margin: 12px 0; display: none; }
        .progress-bar { height: 6px; background: #e5e7eb; border-radius: 10px; overflow: hidden; }
        .progress-fill { width: 0%; height: 100%; background: linear-gradient(90deg, #4f46e5, #7c3aed); transition: width 0.4s; border-radius: 10px; }
        .progress-text { text-align: center; font-size: 12px; color: #666; margin-top: 6px; }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
            width: 100%;
            margin-top: 8px;
        }
        .btn-primary   { background: #4f46e5; color: #fff; }
        .btn-primary:hover   { background: #4338ca; }
        .btn-green     { background: #10b981; color: #fff; }
        .btn-green:hover     { background: #059669; }
        .btn-orange    { background: #f59e0b; color: #fff; }
        .btn-orange:hover    { background: #d97706; }
        .btn-purple    { background: #7c3aed; color: #fff; }
        .btn-purple:hover    { background: #6d28d9; }
        .btn-red       { background: #ef4444; color: #fff; }
        .btn-red:hover       { background: #dc2626; }
        .btn:disabled  { opacity: 0.6; cursor: not-allowed; }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        th { color: #888; font-weight: 500; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:hover td { background: #f9f9f9; }
        .empty { text-align: center; padding: 40px; color: #bbb; font-size: 14px; }

        .badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-green  { background: #dcfce7; color: #16a34a; }
        .badge-red    { background: #fee2e2; color: #dc2626; }
        .badge-yellow { background: #fef9c3; color: #ca8a04; }
        .badge-blue   { background: #dbeafe; color: #2563eb; }
        .badge-gray   { background: #f3f4f6; color: #6b7280; }

        .job-row td { font-size: 12px; }
        .poll-btn {
            font-size: 11px;
            padding: 3px 10px;
            background: #eef2ff;
            color: #4f46e5;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .poll-btn:hover { background: #e0e7ff; }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-box {
            background: #fff;
            border-radius: 14px;
            padding: 26px;
            max-width: 520px;
            width: 92%;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .modal-header h3 { font-size: 17px; font-weight: 700; }
        .modal-close { background: none; border: none; font-size: 22px; cursor: pointer; color: #aaa; }
        .preview-body {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 14px;
            font-family: monospace;
            font-size: 12px;
            max-height: 400px;
            overflow: auto;
            white-space: pre-wrap;
        }
        .modal-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 14px;
        }
        .modal-input:focus { outline: none; border-color: #4f46e5; }

        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            color: #fff;
            display: none;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            max-width: 320px;
        }
        @keyframes slideIn { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body>

<div class="topbar">
    <h1>📦 ZIP File Manager</h1>
    <span>Laravel ZipStream — Background Queue + Multi-Folder Support</span>
</div>

<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('files')">📁 Files</button>
    <button class="tab-btn" onclick="switchTab('folders')">🗂 Folders</button>
    <button class="tab-btn" onclick="switchTab('queue')">⚙️ Background Jobs</button>
    <button class="tab-btn" onclick="switchTab('history')">📋 History</button>
</div>

<div class="container">

    <div class="stats">
        <div class="stat-card">
            <h2 id="statFiles">{{ count($availableFiles) }}</h2>
            <p>Available Files</p>
        </div>
        <div class="stat-card">
            <h2 id="statFolders">{{ count($availableFolders) }}</h2>
            <p>Folders</p>
        </div>
        <div class="stat-card">
            <h2 id="statSelected">0</h2>
            <p>Selected</p>
        </div>
        <div class="stat-card">
            <h2>{{ $downloads->count() }}</h2>
            <p>Total Downloads</p>
        </div>
        <div class="stat-card">
            <h2>{{ $zipJobs->where('status','completed')->count() }}</h2>
            <p>Jobs Done</p>
        </div>
    </div>

    <div id="tab-files" class="tab-panel active">
        <div class="two-col">

            <div class="col">
                <div class="card">
                    <div class="card-title">📁 Select Files</div>

                    <div class="upload-area" id="uploadArea">
                        <div class="icon">☁️</div>
                        <p>Click or drag files here to upload</p>
                        <small>Max 10MB per file</small>
                        <input type="file" id="fileInput" style="display:none" multiple>
                    </div>

                    <div class="select-all-row" onclick="toggleSelectAll()">
                        <input type="checkbox" id="selectAllCb" onclick="event.stopPropagation(); toggleSelectAll()">
                        <span>Select All ({{ count($availableFiles) }} files)</span>
                    </div>

                    <div class="file-list">
                        @forelse($availableFiles as $index => $file)
                            <div class="file-item">
                                <input type="checkbox" class="file-cb" value="{{ $file }}" id="f_{{ $index }}">
                                <label for="f_{{ $index }}">
                                    {{ $file }}
                                    <span class="file-size">
                                        @php
                                            $p = storage_path('app/public/'.$file);
                                            echo file_exists($p) ? round(filesize($p)/1024,1).' KB' : '';
                                        @endphp
                                    </span>
                                </label>
                                <div class="file-actions">
                                    <button onclick="previewFile('{{ $file }}')" title="Preview">👁</button>
                                    <button onclick="deleteFile('{{ $file }}')" title="Delete">🗑</button>
                                </div>
                            </div>
                        @empty
                            <div class="empty">No files found. Upload some above.</div>
                        @endforelse
                    </div>

                    <div class="password-wrap">
                        <input type="password" id="zipPassword" placeholder="🔒 Password protect ZIP (optional)">
                    </div>

                    <div class="progress-wrap" id="progressWrap">
                        <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
                        <div class="progress-text" id="progressText">Preparing...</div>
                    </div>

                    <button class="btn btn-primary" id="btnDownload">⬇️ Download ZIP (Direct)</button>
                    <button class="btn btn-green"   id="btnEmail">📧 Send via Email</button>
                    <button class="btn btn-purple"  id="btnQueue">⚙️ Process in Background</button>
                </div>
            </div>

            <div class="col">
                <div class="card" id="linkCard" style="display:none">
                    <div class="card-title">🔗 Download Link Ready</div>
                    <p style="font-size:13px;color:#555;margin-bottom:10px;">Share this link (expires in 24 hours):</p>
                    <div style="background:#f0f4ff;padding:12px;border-radius:8px;font-size:12px;word-break:break-all;" id="generatedLink"></div>
                    <button class="btn btn-primary" style="margin-top:12px" onclick="copyLink()">📋 Copy Link</button>
                    <button class="btn btn-green" style="margin-top:8px" onclick="openLink()">⬇️ Download Now</button>
                </div>

                <div class="card" id="jobCard" style="display:none">
                    <div class="card-title">⚙️ Background Job Queued</div>
                    <p style="font-size:13px;color:#555;margin-bottom:12px">
                        ZIP is being processed in background. Run queue worker if not running:<br>
                        <code style="background:#f0f0f0;padding:4px 8px;border-radius:4px;font-size:12px;">php artisan queue:work</code>
                    </p>
                    <div id="jobStatus" style="font-size:13px"></div>
                    <button class="btn btn-orange" id="btnCheckJob" style="display:none" onclick="checkJobStatus()">🔄 Check Status</button>
                </div>

                <div class="card">
                    <div class="card-title">📋 Recent Downloads</div>
                    <div class="table-wrap">
                        @if($downloads->count())
                            <table>
                                <thead>
                                    <tr>
                                        <th>ZIP Name</th>
                                        <th>Files</th>
                                        <th>Password</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($downloads as $d)
                                        <tr>
                                            <td>{{ Str::limit($d->zip_name, 28) }}</td>
                                            <td>{{ $d->total_files }}</td>
                                            <td>
                                                @if($d->is_password_protected)
                                                    <span class="badge badge-yellow">🔒 Yes</span>
                                                @else
                                                    <span class="badge badge-gray">No</span>
                                                @endif
                                            </td>
                                            <td style="font-size:11px;color:#aaa">{{ $d->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="empty">No downloads yet</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-folders" class="tab-panel">
        <div class="two-col">
            <div class="col">
                <div class="card">
                    <div class="card-title">🗂 Select Folders to ZIP</div>
                    <p style="font-size:13px;color:#777;margin-bottom:14px">
                        Select folders — all files inside them (including sub-folders) will be added recursively.
                    </p>

                    <div class="file-list">
                        @forelse($availableFolders as $folder)
                            <div class="folder-item">
                                <input type="checkbox" class="folder-cb" value="{{ $folder['name'] }}" id="folder_{{ $loop->index }}">
                                <label for="folder_{{ $loop->index }}" style="cursor:pointer;font-size:13px">
                                    📁 {{ $folder['name'] }}
                                </label>
                                <span class="folder-badge">{{ $folder['file_count'] }} files</span>
                            </div>
                        @empty
                            <div class="empty">
                                No folders found in storage/app/public/<br>
                                <small>Create folders and add files there</small>
                            </div>
                        @endforelse
                    </div>

                    <div class="progress-wrap" id="folderProgressWrap">
                        <div class="progress-bar"><div class="progress-fill" id="folderProgressFill"></div></div>
                        <div class="progress-text" id="folderProgressText">Preparing...</div>
                    </div>

                    <button class="btn btn-primary" id="btnFolderDownload">📦 Download Folder ZIP</button>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-title">ℹ️ How Multi-Folder ZIP Works</div>
                    <div style="font-size:13px;color:#555;line-height:1.8">
                        <p>✅ Select multiple folders at once</p>
                        <p>✅ Sub-folders are included recursively</p>
                        <p>✅ Original folder structure is preserved in ZIP</p>
                        <p>✅ Direct streaming — no temporary file saved on server</p>
                        <br>
                        <p style="color:#888;font-size:12px">
                            Folders location: <code>storage/app/public/[folder-name]/</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-queue" class="tab-panel">
        <div class="card">
            <div class="card-title">⚙️ Background Job Status</div>
            <p style="font-size:13px;color:#666;margin-bottom:16px;">
                Jobs are processed in the queue. Make sure to run <code>php artisan queue:work</code>.
            </p>

            <div class="table-wrap">
                @if($zipJobs->count())
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ZIP Name</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="jobsTableBody">
                            @foreach($zipJobs as $job)
                                <tr id="jobRow_{{ $job->id }}" class="job-row">
                                    <td>{{ $job->id }}</td>
                                    <td>{{ Str::limit($job->zip_name, 30) }}</td>
                                    <td>
                                        @if($job->status === 'completed')
                                            <span class="badge badge-green">✅ Completed</span>
                                        @elseif($job->status === 'processing')
                                            <span class="badge badge-blue">⚙️ Processing</span>
                                        @elseif($job->status === 'failed')
                                            <span class="badge badge-red">❌ Failed</span>
                                        @else
                                            <span class="badge badge-yellow">⏳ Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $job->created_at->diffForHumans() }}</td>
                                    <td>
                                        @if($job->status === 'completed')
                                            <a href="{{ route('zip.job.download', $job->id) }}" class="poll-btn" style="text-decoration:none">⬇️ Download</a>
                                        @elseif(in_array($job->status, ['pending','processing']))
                                            <button class="poll-btn" onclick="pollJob({{ $job->id }})">🔄 Refresh</button>
                                        @else
                                            <span style="color:#aaa;font-size:11px;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty">
                        No background jobs yet.<br>
                        <small>Use "Process in Background" button in the Files tab.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="tab-history" class="tab-panel">
        <div class="card">
            <div class="card-title">📋 All Download History</div>
            <div class="table-wrap">
                @if($downloads->count())
                    <table>
                        <thead>
                            <tr>
                                <th>ZIP Name</th>
                                <th>Files</th>
                                <th>Password</th>
                                <th>IP</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($downloads as $d)
                                <tr>
                                    <td>{{ $d->zip_name }}</td>
                                    <td>{{ $d->total_files }}</td>
                                    <td>
                                        @if($d->is_password_protected)
                                            <span class="badge badge-yellow">🔒 Yes</span>
                                        @else
                                            <span class="badge badge-gray">No</span>
                                        @endif
                                    </td>
                                    <td style="font-size:12px;color:#aaa">{{ $d->user_ip ?? '—' }}</td>
                                    <td style="font-size:12px;color:#aaa">{{ $d->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty">No history yet</div>
                @endif
            </div>
        </div>
    </div>

</div>

<div class="modal" id="previewModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="previewTitle">Preview</h3>
            <button class="modal-close" onclick="closeModal('previewModal')">&times;</button>
        </div>
        <div id="previewBody" class="preview-body">Loading...</div>
    </div>
</div>

<div class="modal" id="emailModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>📧 Send Download Link</h3>
            <button class="modal-close" onclick="closeModal('emailModal')">&times;</button>
        </div>
        <input type="email" class="modal-input" id="recipientEmail" placeholder="Enter email address">
        <button class="btn btn-primary" onclick="sendEmailLink()">Send Link</button>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
function switchTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name)?.classList.add('active');
    event.target.classList.add('active');
}

let selectedFiles = new Set();
let currentJobId  = null;
let generatedLink = null;

function updateSelected() {
    selectedFiles.clear();
    document.querySelectorAll('.file-cb:checked').forEach(cb => selectedFiles.add(cb.value));
    document.getElementById('statSelected').innerText = selectedFiles.size;
}

function toggleSelectAll() {
    const cb = document.getElementById('selectAllCb');
    document.querySelectorAll('.file-cb').forEach(c => c.checked = cb.checked);
    updateSelected();
}

document.querySelectorAll('.file-cb').forEach(cb => cb.addEventListener('change', updateSelected));

document.getElementById('btnDownload')?.addEventListener('click', async () => {
    const files    = Array.from(selectedFiles);
    const password = document.getElementById('zipPassword').value;

    if (!files.length) { showToast('Please select at least one file', 'error'); return; }

    showProgress(true);
    updateProgress(30, 'Creating ZIP link...');

    try {
        const res  = await postJson('{{ route("zip.download.ajax") }}', { files, password });
        const data = await res.json();

        if (data.success) {
            updateProgress(100, 'Ready!');
            generatedLink = data.download_url;
            document.getElementById('generatedLink').innerText = data.download_url;
            document.getElementById('linkCard').style.display = 'block';
            showToast(`ZIP ready — ${data.total_files} files`, 'success');
        } else {
            showToast(data.message || 'Something went wrong', 'error');
            showProgress(false);
        }
    } catch (e) {
        showToast('Network error occurred', 'error');
        showProgress(false);
    }
});

function copyLink() {
    if (generatedLink) {
        navigator.clipboard.writeText(generatedLink);
        showToast('Link copied to clipboard!', 'success');
    }
}

function openLink() {
    if (generatedLink) window.location.href = generatedLink;
}

let emailFiles = [];

document.getElementById('btnEmail')?.addEventListener('click', () => {
    emailFiles = Array.from(selectedFiles);
    if (!emailFiles.length) { showToast('Please select at least one file', 'error'); return; }
    openModal('emailModal');
});

async function sendEmailLink() {
    const email    = document.getElementById('recipientEmail').value;
    const password = document.getElementById('zipPassword').value;

    if (!email || !email.includes('@')) { showToast('Please enter a valid email address', 'error'); return; }

    try {
        const res  = await postJson('{{ route("zip.email") }}', { email, files: emailFiles, password });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            closeModal('emailModal');
            generatedLink = data.download_url;
            document.getElementById('generatedLink').innerText = data.download_url;
            document.getElementById('linkCard').style.display = 'block';
        } else {
            showToast('Failed to send email', 'error');
        }
    } catch (e) {
        showToast('Network error occurred', 'error');
    }
}

document.getElementById('btnQueue')?.addEventListener('click', async () => {
    const files    = Array.from(selectedFiles);
    const password = document.getElementById('zipPassword').value;

    if (!files.length) { showToast('Please select at least one file', 'error'); return; }

    try {
        const res  = await postJson('{{ route("zip.queue") }}', { files, password });
        const data = await res.json();

        if (data.success) {
            currentJobId = data.job_id;
            document.getElementById('jobStatus').innerHTML =
                `Job ID: <strong>#${data.job_id}</strong> — Status: <span class="badge badge-yellow">⏳ Pending</span>`;
            document.getElementById('jobCard').style.display  = 'block';
            document.getElementById('btnCheckJob').style.display = 'inline-block';
            showToast('Job queued successfully! Run: php artisan queue:work', 'success');

            setTimeout(() => autoPolling(data.job_id), 5000);
        } else {
            showToast('Failed to queue job', 'error');
        }
    } catch (e) {
        showToast('Network error occurred', 'error');
    }
});

async function checkJobStatus() {
    if (!currentJobId) return;
    await pollJob(currentJobId);
}

async function pollJob(jobId) {
    try {
        const res  = await fetch(`{{ url('/zip/job') }}/${jobId}/status`);
        const data = await res.json();

        const statusMap = {
            'pending':    '<span class="badge badge-yellow">⏳ Pending</span>',
            'processing': '<span class="badge badge-blue">⚙️ Processing</span>',
            'completed':  '<span class="badge badge-green">✅ Completed</span>',
            'failed':     '<span class="badge badge-red">❌ Failed</span>',
        };

        const row = document.getElementById(`jobRow_${jobId}`);
        if (row) {
            row.cells[2].innerHTML = statusMap[data.status] || data.status;
            if (data.status === 'completed' && data.download_url) {
                row.cells[4].innerHTML = `<a href="${data.download_url}" class="poll-btn" style="text-decoration:none">⬇️ Download</a>`;
            }
        }

        if (jobId === currentJobId) {
            document.getElementById('jobStatus').innerHTML =
                `Job #${jobId} — ${statusMap[data.status] || data.status}` +
                (data.download_url ? ` <a href="${data.download_url}" class="poll-btn" style="margin-left:8px">⬇️ Download</a>` : '');
        }

        return data.status;
    } catch (e) {
        console.error('Poll error:', e);
    }
}

async function autoPolling(jobId) {
    const status = await pollJob(jobId);
    if (status === 'pending' || status === 'processing') {
        setTimeout(() => autoPolling(jobId), 5000);
    }
}

document.getElementById('btnFolderDownload')?.addEventListener('click', () => {
    const folders  = Array.from(document.querySelectorAll('.folder-cb:checked')).map(cb => cb.value);
    const password = document.getElementById('zipPassword').value;

    if (!folders.length) { showToast('Please select at least one folder', 'error'); return; }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("zip.download.folder") }}';

    const csrf = document.createElement('input');
    csrf.type  = 'hidden';
    csrf.name  = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrf);

    folders.forEach(f => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'folders[]';
        inp.value = f;
        form.appendChild(inp);
    });

    if (password) {
        const passInput = document.createElement('input');
        passInput.type  = 'hidden';
        passInput.name  = 'password';
        passInput.value = password;
        form.appendChild(passInput);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    showToast('Folder ZIP download started...', 'success');
});

const uploadArea = document.getElementById('uploadArea');
const fileInput  = document.getElementById('fileInput');

uploadArea?.addEventListener('click', () => fileInput.click());
uploadArea?.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('drag-over'); });
uploadArea?.addEventListener('dragleave', () => uploadArea.classList.remove('drag-over'));
uploadArea?.addEventListener('drop', async e => {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
    await uploadFiles(Array.from(e.dataTransfer.files));
});
fileInput?.addEventListener('change', async e => {
    await uploadFiles(Array.from(e.target.files));
    fileInput.value = '';
});

async function uploadFiles(files) {
    for (const file of files) {
        const formData = new FormData();
        formData.append('file', file);
        showToast(`Uploading ${file.name}...`, 'info');

        try {
            const res  = await fetch('{{ route("files.upload") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast(`${data.file} uploaded successfully ✅`, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(`Failed to upload: ${file.name}`, 'error');
            }
        } catch (e) {
            showToast(`Error uploading: ${file.name}`, 'error');
        }
    }
}

async function previewFile(filename) {
    openModal('previewModal');
    document.getElementById('previewTitle').innerText = filename;
    document.getElementById('previewBody').innerText  = 'Loading...';

    try {
        const res  = await fetch(`{{ url('files/preview') }}/${filename}`);
        const data = await res.json();
        document.getElementById('previewBody').innerText = data.content || data.message || 'Preview not available';
    } catch (e) {
        document.getElementById('previewBody').innerText = 'Error loading preview';
    }
}

async function deleteFile(filename) {
    if (!confirm(`Are you sure you want to delete "${filename}"?`)) return;

    try {
        const res  = await fetch(`{{ url('files/delete') }}/${filename}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        if (data.success) {
            showToast(`${filename} deleted successfully`, 'success');
            location.reload();
        } else {
            showToast('Failed to delete file', 'error');
        }
    } catch (e) {
        showToast('Error occurred while deleting', 'error');
    }
}

function showProgress(show) {
    document.getElementById('progressWrap').style.display = show ? 'block' : 'none';
    if (!show) document.getElementById('progressFill').style.width = '0%';
}

function updateProgress(pct, msg) {
    document.getElementById('progressFill').style.width = pct + '%';
    document.getElementById('progressText').innerText = msg;
}

function showToast(msg, type = 'info') {
    const toast = document.getElementById('toast');
    toast.style.background = type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#333';
    toast.innerText = msg;
    toast.style.display = 'block';
    setTimeout(() => toast.style.display = 'none', 3000);
}

function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
window.addEventListener('click', e => { if (e.target.classList.contains('modal')) e.target.style.display = 'none'; });

function postJson(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(body)
    });
}
</script>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ZIP Manager</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px 20px;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .header p {
            color: #666;
            margin-top: 8px;
            font-size: 14px;
        }

        /* Stats Cards */
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            flex: 1;
            min-width: 150px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-card h2 {
            font-size: 32px;
            color: #4f46e5;
            margin-bottom: 5px;
        }

        .stat-card p {
            font-size: 13px;
            color: #666;
        }

        /* Two Column Layout */
        .two-columns {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .column {
            flex: 1;
            min-width: 280px;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4f46e5;
            display: inline-block;
        }

        /* Upload Area */
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
            background: #fafafa;
        }

        .upload-area:hover {
            border-color: #4f46e5;
            background: #f0f0ff;
        }

        .upload-area .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .upload-area p {
            font-size: 14px;
            color: #666;
        }

        .upload-area small {
            font-size: 12px;
            color: #999;
        }

        /* File List */
        .file-list {
            max-height: 350px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .file-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .file-item:hover {
            background: #f9f9f9;
        }

        .file-item input {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .file-item label {
            flex: 1;
            cursor: pointer;
            font-size: 14px;
        }

        .file-size {
            font-size: 12px;
            color: #999;
            margin-left: 10px;
        }

        .file-actions {
            display: flex;
            gap: 8px;
        }

        .file-actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            padding: 4px 8px;
            border-radius: 6px;
            opacity: 0.6;
        }

        .file-actions button:hover {
            opacity: 1;
            background: #f0f0f0;
        }

        .select-all {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .select-all label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 14px;
        }

        /* Password Input */
        .password-input {
            margin: 15px 0;
        }

        .password-input input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .password-input input:focus {
            outline: none;
            border-color: #4f46e5;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-secondary {
            background: #10b981;
            color: white;
            margin-top: 10px;
        }

        .btn-secondary:hover {
            background: #059669;
        }

        /* Progress Bar */
        .progress-container {
            margin: 15px 0;
            display: none;
        }

        .progress-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            width: 0%;
            height: 100%;
            background: #4f46e5;
            transition: width 0.3s;
        }

        .progress-text {
            text-align: center;
            font-size: 12px;
            margin-top: 6px;
            color: #666;
        }

        /* Table */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            color: #666;
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 14px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 25px;
            max-width: 500px;
            width: 90%;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .modal-header h3 {
            font-size: 18px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .preview-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
            max-height: 400px;
            overflow-y: auto;
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            display: none;
            z-index: 2000;
        }

        /* Utility */
        .text-center {
            text-align: center;
        }
        .mt-2 {
            margin-top: 8px;
        }
        .mt-3 {
            margin-top: 12px;
        }
        .mb-2 {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1> ZIP File Manager</h1>
        
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-card">
            <h2 id="totalFiles">0</h2>
            <p>Available Files</p>
        </div>
        <div class="stat-card">
            <h2 id="selectedCount">0</h2>
            <p>Selected Files</p>
        </div>
        <div class="stat-card">
            <h2>{{ $downloads->count() }}</h2>
            <p>Downloads</p>
        </div>
    </div>

    <!-- Two Columns -->
    <div class="two-columns">
        <!-- Left Column - File Selection -->
        <div class="column">
            <div class="card">
                <h3>Select Files</h3>

                <!-- Upload Area -->
                <div class="upload-area" id="uploadArea">
                    <div class="icon"></div>
                    <p>Click or drag files here</p>
                    <small>Max 10MB per file</small>
                    <input type="file" id="fileInput" style="display: none" multiple>
                </div>

                <!-- Select All -->
                <div class="select-all">
                    <label>
                        <input type="checkbox" id="selectAllCheckbox">
                        <span>Select All Files</span>
                    </label>
                </div>

                <!-- File List -->
                <div class="file-list" id="fileList">
                    @if(count($availableFiles) > 0)
                        @foreach($availableFiles as $index => $file)
                            <div class="file-item">
                                <input type="checkbox" class="file-checkbox" value="{{ $file }}" id="file_{{ $index }}">
                                <label for="file_{{ $index }}">
                                     {{ $file }}
                                    <span class="file-size">
                                        @php
                                            $path = storage_path('app/public/'.$file);
                                            if(file_exists($path)) {
                                                echo round(filesize($path)/1024, 1) . ' KB';
                                            } else {
                                                echo '0 KB';
                                            }
                                        @endphp
                                    </span>
                                </label>
                                <div class="file-actions">
                                    <button onclick="previewFile('{{ $file }}')" title="Preview"></button>
                                    <button onclick="deleteFile('{{ $file }}')" title="Delete"></button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            No files available. Upload some files above.
                        </div>
                    @endif
                </div>

                <!-- Password -->
                <div class="password-input">
                    <input type="password" id="zipPassword" placeholder="Password (optional)">
                </div>

                <!-- Progress -->
                <div class="progress-container" id="progressContainer">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div class="progress-text" id="progressText">Preparing...</div>
                </div>

                <!-- Buttons -->
                <button class="btn btn-primary" id="downloadBtn"> Download ZIP</button>
                <button class="btn btn-secondary" id="emailBtn"> Email Link</button>
            </div>
        </div>

        <!-- Right Column - Recent Downloads -->
        <div class="column">
            <div class="card">
                <h3>Recent Downloads</h3>
                <div class="table-wrapper">
                    @if($downloads->count() > 0)
                        <table>
                            <thead>
                                <tr><th>ZIP Name</th><th>Files</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($downloads as $download)
                                    <tr>
                                        <td>{{ Str::limit($download->zip_name, 30) }}</td>
                                        <td>{{ $download->total_files }}</td>
                                        <td>{{ $download->is_password_protected ? 'Yes' : 'No' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">No downloads yet</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="previewTitle">File Preview</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <div id="previewBody" class="preview-content">Loading...</div>
    </div>
</div>

<!-- Email Modal -->
<div id="emailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Send Download Link</h3>
            <button class="close-modal" onclick="closeEmailModal()">&times;</button>
        </div>
        <input type="email" id="recipientEmail" placeholder="Email address" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px;">
        <button class="btn btn-primary" onclick="sendEmailLink()">Send Link</button>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
    let selectedFiles = new Set();

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.file-checkbox:checked');
        selectedFiles.clear();
        checkboxes.forEach(cb => selectedFiles.add(cb.value));
        document.getElementById('selectedCount').innerText = selectedFiles.size;
    }

    // Select All
    document.getElementById('selectAllCheckbox')?.addEventListener('change', (e) => {
        document.querySelectorAll('.file-checkbox').forEach(cb => cb.checked = e.target.checked);
        updateSelectedCount();
    });

    // Checkbox change
    document.querySelectorAll('.file-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    // Download
    document.getElementById('downloadBtn')?.addEventListener('click', async () => {
        const files = Array.from(selectedFiles);
        if (files.length === 0) {
            showToast('Select at least one file', 'error');
            return;
        }

        showProgress(true);
        updateProgress(30, 'Creating ZIP...');

        try {
            const response = await fetch('{{ route("zip.download.ajax") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ files: files })
            });

            const data = await response.json();
            
            if (data.success) {
                updateProgress(100, 'Complete!');
                window.location.href = data.download_url;
                showToast(`ZIP created with ${data.total_files} files`, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('Error creating ZIP', 'error');
                showProgress(false);
            }
        } catch (error) {
            showToast('Network error', 'error');
            showProgress(false);
        }
    });

    // Email
    let emailFiles = [];
    document.getElementById('emailBtn')?.addEventListener('click', () => {
        emailFiles = Array.from(selectedFiles);
        if (emailFiles.length === 0) {
            showToast('Select at least one file', 'error');
            return;
        }
        document.getElementById('emailModal').style.display = 'flex';
    });

    async function sendEmailLink() {
        const email = document.getElementById('recipientEmail').value;
        if (!email || !email.includes('@')) {
            showToast('Enter valid email', 'error');
            return;
        }

        showProgress(true);
        updateProgress(30, 'Sending...');

        try {
            const response = await fetch('{{ route("zip.email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email: email, files: emailFiles })
            });

            const data = await response.json();
            
            if (data.success) {
                updateProgress(100, 'Sent!');
                showToast(data.message, 'success');
                closeEmailModal();
                document.getElementById('recipientEmail').value = '';
                setTimeout(() => showProgress(false), 1500);
            } else {
                showToast('Error sending email', 'error');
                showProgress(false);
            }
        } catch (error) {
            showToast('Network error', 'error');
            showProgress(false);
        }
    }

    // Upload
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');

    uploadArea?.addEventListener('click', () => fileInput.click());
    uploadArea?.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.style.borderColor = '#4f46e5'; });
    uploadArea?.addEventListener('dragleave', () => { uploadArea.style.borderColor = '#ddd'; });
    uploadArea?.addEventListener('drop', async (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '#ddd';
        await uploadFiles(Array.from(e.dataTransfer.files));
    });

    fileInput?.addEventListener('change', async (e) => {
        await uploadFiles(Array.from(e.target.files));
        fileInput.value = '';
    });

    async function uploadFiles(files) {
        for (const file of files) {
            const formData = new FormData();
            formData.append('file', file);
            showToast(`Uploading ${file.name}...`, 'info');
            
            try {
                const response = await fetch('{{ route("files.upload") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    showToast(`${data.file} uploaded`, 'success');
                    location.reload();
                } else {
                    showToast(`Failed: ${file.name}`, 'error');
                }
            } catch (error) {
                showToast(`Error: ${file.name}`, 'error');
            }
        }
    }

    // Preview
    async function previewFile(filename) {
        const modal = document.getElementById('previewModal');
        const title = document.getElementById('previewTitle');
        const body = document.getElementById('previewBody');
        
        modal.style.display = 'flex';
        title.innerText = `Preview: ${filename}`;
        body.innerHTML = 'Loading...';
        
        try {
            const response = await fetch(`{{ url("files/preview") }}/${filename}`);
            const data = await response.json();
            if (data.content) {
                body.innerHTML = `<pre style="white-space:pre-wrap;">${data.content}</pre>`;
            } else {
                body.innerHTML = `<p>${data.message || 'Preview not available'}</p><p>Size: ${data.size}</p>`;
            }
        } catch (error) {
            body.innerHTML = '<p style="color:red">Error loading preview</p>';
        }
    }

    // Delete
    async function deleteFile(filename) {
        if (!confirm(`Delete "${filename}"?`)) return;
        
        try {
            const response = await fetch(`{{ url("files/delete") }}/${filename}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await response.json();
            if (data.success) {
                showToast(`Deleted ${filename}`, 'success');
                location.reload();
            } else {
                showToast('Delete failed', 'error');
            }
        } catch (error) {
            showToast('Error deleting', 'error');
        }
    }

    // Progress
    function showProgress(show) {
        document.getElementById('progressContainer').style.display = show ? 'block' : 'none';
        if (!show) document.getElementById('progressFill').style.width = '0%';
    }
    function updateProgress(percent, message) {
        document.getElementById('progressFill').style.width = percent + '%';
        document.getElementById('progressText').innerText = message;
    }

    // Toast
    function showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        toast.style.backgroundColor = type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#333';
        toast.innerText = message;
        toast.style.display = 'block';
        setTimeout(() => toast.style.display = 'none', 3000);
    }

    // Modal close
    function closeModal() { document.getElementById('previewModal').style.display = 'none'; }
    function closeEmailModal() { document.getElementById('emailModal').style.display = 'none'; }
    window.onclick = (e) => { if (e.target.classList.contains('modal')) e.target.style.display = 'none'; }

    // Initial
    updateSelectedCount();
    document.getElementById('totalFiles').innerText = {{ count($availableFiles) }};
</script>

</body>
</html>
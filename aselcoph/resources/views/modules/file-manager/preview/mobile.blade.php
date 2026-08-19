@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $info = App\Models\FileManager::where('google_drive_id', $googleDriveId)->first();
    $fileToPreview = '.' . ($info->format ?? 'txt');
    $fileExists = $info && $info->google_drive_id;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'B') }} - File Preview</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .mobile-header {
            background-color: #ffffff;
            color: #333;
            padding: 12px 16px;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .back-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f5f5f5;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #3b82f6;
            font-size: 20px;
        }
        .back-button:active {
            background: #e8e8e8;
            transform: scale(0.95);
        }
        .header-title {
            flex: 1;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            text-align: center;
            margin-right: 40px;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .loading-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 16px;
        }
        .spinner {
            width: 32px;
            height: 32px;
            border: 4px solid #e3f2fd;
            border-top: 4px solid #2196f3;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px;
            padding-bottom: 120px;  
        }
        .preview-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .file-info {
            background-color: #6c757d;
            color: white;
            padding: 16px;
            text-align: center;
            margin-bottom: 16px;
            border-radius: 12px;
        }
        .preview-content {
            padding: 16px;
            min-height: 60vh;
            max-height: 70vh;
            overflow-y: auto;
        }
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            margin-bottom: 40px; 
        }
        .btn {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #3b82f6;
            color: white;
        }
        .btn-success:hover {
            background-color: #2563eb;
        }
        .header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .file-info-flex {
            display: flex;
            justify-content: center;
            gap: 24px;
            font-size: 14px;
        }
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .text-lg { font-size: 18px; }
        .text-xl { font-size: 20px; }
        .font-medium { font-weight: 500; }
        .font-bold { font-weight: 700; }
    
        
        .bottom-sheet-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .bottom-sheet-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .bottom-sheet {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-radius: 20px 20px 0 0;
            z-index: 9999;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            max-height: 60vh;
            overflow-y: auto;
        }
        
        .bottom-sheet.active {
            transform: translateY(0);
        }
        
        .bottom-sheet-header {
            padding: 16px;
            border-bottom: 1px solid #e9ecef;
            text-align: center;
            position: relative;
        }
        
        .bottom-sheet-handle {
            width: 40px;
            height: 4px;
            background-color: #dee2e6;
            border-radius: 2px;
            margin: 0 auto 12px;
        }
        
        .bottom-sheet-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .bottom-sheet-close {
            position: absolute;
            right: 16px;
            top: 16px;
            background: none;
            border: none;
            font-size: 24px;
            color: #6c757d;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .bottom-sheet-content {
            padding: 0;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            padding: 20px 16px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .quick-action-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }
        
        .quick-action-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: transform 0.2s, background-color 0.2s;
            background: #f5f5f5;
        }
        
        .quick-action-item:active .quick-action-icon {
            transform: scale(0.9);
            background: #e8e8e8;
        }
        
        .quick-action-icon.purple {
            color: #3b82f6;
        }
        
        .quick-action-icon.pink {
            color: #3b82f6;
        }
        
        .quick-action-icon.blue {
            color: #3b82f6;
        }
        
        .quick-action-icon.green {
            color: #3b82f6;
        }
        
        .quick-action-label {
            font-size: 12px;
            color: #333;
            text-align: center;
            font-weight: 500;
        }
        
        .share-section {
            padding: 16px;
        }
        
        .share-section-title {
            font-size: 13px;
            color: #8e8e8e;
            font-weight: 600;
            margin: 0 0 12px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .share-apps-button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: background-color 0.2s;
            border: none;
            background: #f8f9fa;
            width: 100%;
        }
        
        .share-apps-button:active {
            background-color: #e9ecef;
        }
        
        .share-apps-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .share-apps-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: white;
            color: #2196f3;
        }
        
        .share-apps-text {
            text-align: left;
        }
        
        .share-apps-title {
            font-size: 15px;
            font-weight: 600;
            color: #333;
            margin: 0 0 2px 0;
        }
        
        .share-apps-desc {
            font-size: 12px;
            color: #8e8e8e;
            margin: 0;
        }
        
        .share-apps-arrow {
            color: #8e8e8e;
            font-size: 18px;
        }
        
        .download-loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        
        .download-loading.active {
            display: flex;
        }
        
        .download-loading-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            text-align: center;
            max-width: 280px;
        }
        
        .download-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #dbeafe;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }
        
        .download-loading-text {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            margin: 0 0 8px 0;
        }
        
        .download-loading-subtext {
            font-size: 13px;
            color: #6c757d;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <button onclick="goBack()" class="back-button">
            <i class="bi bi-chevron-left"></i>
        </button>
        <h1 class="header-title">File Preview</h1>
    </div>

    <div id="loadingIndicator" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <p class="text-muted">Please wait, loading preview...</p>
        </div>
    </div>

    <!-- Download Loading Overlay -->
    <div id="downloadLoading" class="download-loading">
        <div class="download-loading-content">
            <div class="download-spinner"></div>
            <p class="download-loading-text">Downloading...</p>
            <p class="download-loading-subtext">Please wait a moment</p>
        </div>
    </div>

    <!-- Share Bottom Sheet -->
    <div id="shareOverlay" class="bottom-sheet-overlay" onclick="closeShareSheet()"></div>
    <div id="shareSheet" class="bottom-sheet">
        <div class="bottom-sheet-header">
            <div class="bottom-sheet-handle"></div>
            <h3 class="bottom-sheet-title">Share to</h3>
            <button class="bottom-sheet-close" onclick="closeShareSheet()">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="bottom-sheet-content">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <button class="quick-action-item" onclick="shareToChats()">
                    <div class="quick-action-icon purple">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <span class="quick-action-label">Chats</span>
                </button>
                
                <button class="quick-action-item" onclick="shareToPeople()">
                    <div class="quick-action-icon pink">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span class="quick-action-label">People</span>
                </button>
                
                <button class="quick-action-item" onclick="shareMore()">
                    <div class="quick-action-icon blue">
                        <i class="bi bi-three-dots"></i>
                    </div>
                    <span class="quick-action-label">More</span>
                </button>
                
                <button class="quick-action-item" onclick="copyToClipboard()">
                    <div class="quick-action-icon green">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                    <span class="quick-action-label">Copy Link</span>
                </button>
            </div>
            
            <!-- Share via Apps Section -->
            <div class="share-section">
                <h4 class="share-section-title">Share via</h4>
                <button class="share-apps-button" onclick="shareViaApps()">
                    <div class="share-apps-left">
                        <div class="share-apps-icon">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                        <div class="share-apps-text">
                            <p class="share-apps-title">More apps</p>
                            <p class="share-apps-desc">WhatsApp, Telegram, Email & more</p>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right share-apps-arrow"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        @if (!$fileExists)
            <div class="preview-card">
                <div class="preview-content text-center">
                    <i class="bi bi-exclamation-triangle" style="color: #dc3545; font-size: 48px; margin-bottom: 16px;"></i>
                    <h3 class="text-lg font-bold mb-2">File Not Found</h3>
                    <p class="text-muted">The requested file could not be found or is inaccessible.</p>
                </div>
            </div>
        @else
            <div class="file-info">
                <h2 class="text-xl font-bold mb-2">{{ $info->name ?? 'Unknown File' }}</h2>
                <div class="file-info-flex">
                    <div class="text-center">
                        <div class="font-medium">File Type</div>
                        <div>{{ strtoupper($info->format ?? 'unknown') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="font-medium">File Size</div>
                        <div>{{ number_format(($info->size ?? 0) / 1024 / 1024, 2) }} MB</div>
                    </div>
                </div>
            </div>

            <div class="preview-card">
                <div class="preview-content">
                    @if (Str::endsWith($fileToPreview, ['.txt', '.csv', '.html', '.json']))
                        @include('modules.file-manager.preview.partials.txt')
                    @elseif (Str::endsWith($fileToPreview, ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp']))
                        @include('modules.file-manager.preview.partials.images')
                    @elseif (Str::endsWith($fileToPreview, ['.pdf']))
                        @include('modules.file-manager.preview.partials.pdf')
                    @elseif (Str::endsWith($fileToPreview, ['.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx']))
                        @include('modules.file-manager.preview.partials.msoffice')           
                    @elseif (Str::endsWith($fileToPreview, ['.zip']))
                        @include('modules.file-manager.preview.partials.zip')
                    @else
                        <div class="text-center" style="padding: 32px 0;">
                            <i class="bi bi-file-earmark-x" style="color: #6c757d; font-size: 48px; margin-bottom: 16px;"></i>
                            <h3 class="text-lg font-bold mb-2">Preview Not Available</h3>
                            <p class="text-muted">This file type cannot be previewed.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="action-buttons">
                <button onclick="downloadFile()" class="btn btn-success">
                    <i class="bi bi-download"></i>
                    Download
                </button>
                <button onclick="shareFile()" class="btn btn-primary">
                    <i class="bi bi-share"></i>
                    Share
                </button>
            </div>
        @endif
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('loadingIndicator').style.display = 'none';
            }, 1000);
        });

        function goBack() {
            window.location.href = '/file-manager/list';
        }

        function downloadFile() {
            const downloadLoading = document.getElementById('downloadLoading');
            downloadLoading.classList.add('active');
            
            window.location.href = '/download-file/{{ $info->google_drive_id ?? '' }}';
            
            setTimeout(() => {
                downloadLoading.classList.remove('active');
            }, 3000);
        }

        function shareFile() {
            const shareOverlay = document.getElementById('shareOverlay');
            const shareSheet = document.getElementById('shareSheet');
            
            shareOverlay.classList.add('active');
            shareSheet.classList.add('active');
            
            // Prevent body scroll when bottom sheet is open
            document.body.style.overflow = 'hidden';
        }

        function closeShareSheet() {
            const shareOverlay = document.getElementById('shareOverlay');
            const shareSheet = document.getElementById('shareSheet');
            
            shareOverlay.classList.remove('active');
            shareSheet.classList.remove('active');
            
            document.body.style.overflow = '';
        }

        async function shareToChats() {
            await shareViaApps();
        }

        async function shareToPeople() {
            await shareViaApps();
        }

        async function shareMore() {
            await shareViaApps();
        }

        async function shareViaApps() {
            const fileName = '{{ $info->name ?? "File" }}';
            const fileUrl = window.location.href;
            
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: fileName,
                        text: `Check out this file: ${fileName}`,
                        url: fileUrl
                    });
                    closeShareSheet();
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.error('Error sharing:', err);
                        alert('Unable to share. Please try copying the link instead.');
                    }
                }
            } else {
                alert('Sharing is not supported on this browser. Please use the "Copy Link" option instead.');
            }
        }

        async function copyToClipboard() {
            const fileUrl = window.location.href;
            
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(fileUrl);
                } else {
                    const textArea = document.createElement('textarea');
                    textArea.value = fileUrl;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-999999px';
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                }
                
                const copyButton = document.querySelector('.quick-action-item:nth-child(4)');
                const originalContent = copyButton.innerHTML;
                copyButton.innerHTML = `
                    <div class="quick-action-icon" style="background: #3b82f6; color: white;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <span class="quick-action-label">Copied!</span>
                `;
                
                setTimeout(() => {
                    copyButton.innerHTML = originalContent;
                    closeShareSheet();
                }, 1500);
                
            } catch (err) {
                console.error('Failed to copy:', err);
                alert('Failed to copy link. Please try again.');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            const iframes = document.querySelectorAll('iframe');
            
            let loadCount = 0;
            const totalElements = images.length + iframes.length;
            
            function checkAllLoaded() {
                loadCount++;
                if (loadCount >= totalElements || totalElements === 0) {
                    setTimeout(() => {
                        document.getElementById('loadingIndicator').style.display = 'none';
                    }, 500);
                }
            }
            
            images.forEach(img => {
                if (img.complete) {
                    checkAllLoaded();
                } else {
                    img.addEventListener('load', checkAllLoaded);
                    img.addEventListener('error', checkAllLoaded);
                }
            });
            
            iframes.forEach(iframe => {
                iframe.addEventListener('load', checkAllLoaded);
            });
            
            if (totalElements === 0) {
                checkAllLoaded();
            }
        });
    </script>
</body>
</html>

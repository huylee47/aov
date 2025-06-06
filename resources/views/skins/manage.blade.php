<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Quản Lý Skins - {{ $hero['name'] }}</title>
    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }

        .hero-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            backdrop-filter: blur(10px);
        }

        .hero-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
        }

        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: 30px;
        }

        .upload-area:hover {
            border-color: #007bff;
            background: #e3f2fd;
        }

        .upload-area.dragover {
            border-color: #28a745;
            background: #d4edda;
        }

        .skin-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 20px;
        }

        .skin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .skin-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .skin-actions {
            padding: 15px;
            background: white;
        }

        .upload-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .preview-item {
            position: relative;
            display: inline-block;
            margin: 10px;
            width: 280px;
            vertical-align: top;
        }

        .preview-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ddd;
        }

        .remove-preview {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 12px;
            cursor: pointer;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
    </style>
</head>

<body>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="hero-info">
                        <div class="d-flex align-items-center">
                            @if (isset($hero['avatar_url']) && $hero['avatar_url'])
                                <img src="{{ $hero['avatar_url'] }}" alt="{{ $hero['name'] }}" class="hero-avatar me-3">
                            @else
                                <div
                                    class="hero-avatar me-3 d-flex align-items-center justify-content-center bg-secondary">
                                    <i class="fas fa-user-circle text-white fa-2x"></i>
                                </div>
                            @endif
                            <div>
                                <h1 class="mb-0">{{ $hero['name'] }}</h1>
                                <p class="mb-0 opacity-75">Quản lý skins cho hero này</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="/heroes" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Quay lại Heroes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Stats -->
        <div class="stats-card">
            <div class="row text-center">
                <div class="col-md-4">
                    <h3 id="totalSkins">0</h3>
                    <p class="mb-0">Tổng số skins</p>
                </div>
                <div class="col-md-4">
                    <h3 id="uploadedToday">0</h3>
                    <p class="mb-0">Upload hôm nay</p>
                </div>
                <div class="col-md-4">
                    <h3 id="totalSize">0 MB</h3>
                    <p class="mb-0">Tổng dung lượng</p>
                </div>
            </div>
        </div>

        <!-- Upload Area -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cloud-upload-alt"></i> Thêm Skins Mới</h5>
            </div>
            <div class="card-body">
                <form id="skinsForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="hero_id" value="{{ $heroId }}">

                    <div class="upload-area" id="uploadArea">
                        <div id="uploadContent">
                            <i class="fas fa-images upload-icon"></i>
                            <h5>Chọn nhiều ảnh skin hoặc kéo thả vào đây</h5>
                            <p class="text-muted">Hỗ trợ: JPG, PNG, GIF (tối đa 2MB mỗi file)</p>
                            <button type="button" class="btn btn-primary"
                                onclick="document.getElementById('skins').click()">
                                <i class="fas fa-plus"></i> Chọn Files
                            </button>
                        </div>

                        <div id="previewContainer" style="display: none;">
                            <h6>Preview ảnh sẽ upload:</h6>
                            <div id="previewList"></div>
                        </div>
                    </div>

                    <input type="file" id="skins" name="skins[]" accept="image/*" multiple
                        style="display: none;">

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Có thể chọn nhiều file cùng lúc (Ctrl/Cmd + click)
                            </small>
                        </div>
                        <button type="submit" class="btn btn-success" id="uploadBtn" style="display: none;">
                            <i class="fas fa-upload"></i> Upload Skins
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Skins -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-images"></i> Skins Hiện Có</h5>
                <button class="btn btn-outline-primary btn-sm" onclick="loadSkins()">
                    <i class="fas fa-sync-alt"></i> Làm mới
                </button>
            </div>
            <div class="card-body">
                <!-- Loading State -->
                <div id="loadingState" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-3">Đang tải skins...</p>
                </div>

                <!-- Skins Grid -->
                <div id="skinsGrid" class="row">
                    <!-- Skins will be loaded here -->
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="empty-state" style="display: none;">
                    <i class="fas fa-images"></i>
                    <h4>Chưa có skin nào</h4>
                    <p>Bắt đầu bằng cách upload skin đầu tiên cho hero này!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Xem Skin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Skin preview" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-danger" id="deleteFromModal">
                        <i class="fas fa-trash"></i> Xóa Skin
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedFiles = [];
        let currentSkins = [];
        const heroId = '{{ $heroId }}';

        // Load skins on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSkins();
        });

        // File input change handler
        document.getElementById('skins').addEventListener('change', function(e) {
            handleFilesSelect(Array.from(e.target.files));
        });

        // Drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');

        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');

            const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
            if (files.length > 0) {
                handleFilesSelect(files);
            } else {
                alert('Vui lòng chọn file ảnh!');
            }
        });

        function handleFilesSelect(files) {
            selectedFiles = [];

            files.forEach(file => {
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert(`File "${file.name}" quá lớn! Vui lòng chọn file nhỏ hơn 2MB.`);
                    return;
                }

                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert(`File "${file.name}" không phải là ảnh!`);
                    return;
                }

                selectedFiles.push(file);
            });

            if (selectedFiles.length > 0) {
                showPreviews();
                document.getElementById('uploadBtn').style.display = 'inline-block';
            }
        }

        function showPreviews() {
            const previewList = document.getElementById('previewList');
            previewList.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    previewItem.style.cssText =
                        'border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin: 10px; background: white;';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="preview-image">
                        <button type="button" class="remove-preview" onclick="removePreview(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="mt-3">
                            <label for="skinName${index}" class="form-label fw-bold">Tên skin:</label>
                            <input type="text" class="form-control" id="skinName${index}" 
                                   placeholder="Nhập tên cho skin này..." required>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    `;
                    previewList.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });

            document.getElementById('uploadContent').style.display = 'none';
            document.getElementById('previewContainer').style.display = 'block';
        }

        function removePreview(index) {
            selectedFiles.splice(index, 1);

            if (selectedFiles.length === 0) {
                clearPreviews();
            } else {
                showPreviews();
            }
        }

        function clearPreviews() {
            document.getElementById('uploadContent').style.display = 'block';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('uploadBtn').style.display = 'none';
            document.getElementById('skins').value = '';
            selectedFiles = [];
        }

        // Form submission
        document.getElementById('skinsForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (selectedFiles.length === 0) {
                alert('Vui lòng chọn ít nhất một ảnh!');
                return;
            }

            // Validate that all skin names are filled
            let allNamesValid = true;
            const skinNames = [];
            for (let i = 0; i < selectedFiles.length; i++) {
                const nameInput = document.getElementById(`skinName${i}`);
                if (!nameInput || !nameInput.value.trim()) {
                    allNamesValid = false;
                    alert(`Vui lòng nhập tên cho skin thứ ${i + 1}!`);
                    if (nameInput) nameInput.focus();
                    return;
                }
                skinNames.push(nameInput.value.trim());
            }

            const uploadBtn = document.getElementById('uploadBtn');
            const originalText = uploadBtn.innerHTML;

            // Show loading state
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang upload...';

            const formData = new FormData();
            formData.append('hero_id', heroId);

            selectedFiles.forEach((file, index) => {
                formData.append(`skins[${index}][file]`, file);
                formData.append(`skins[${index}][name]`, skinNames[index]);
            });

            fetch('/skins', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        clearPreviews();
                        loadSkins(); // Reload skins list
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi upload skins!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi upload skins!');
                })
                .finally(() => {
                    // Restore button state
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = originalText;
                });
        });

        function loadSkins() {
            showLoading();

            fetch(`/skins/${heroId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentSkins = data.data;
                        displaySkins(currentSkins);
                        updateStats();
                    } else {
                        showEmpty();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showEmpty();
                })
                .finally(() => {
                    hideLoading();
                });
        }

        function displaySkins(skins) {
            const container = document.getElementById('skinsGrid');

            if (skins.length === 0) {
                showEmpty();
                return;
            }

            hideEmpty();

            container.innerHTML = skins.map(skin => `
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="skin-card">
                        <img src="${skin.image_url}" alt="${skin.name}" class="skin-image" 
                             onclick="viewSkin('${skin.id}', '${skin.image_url}', '${skin.name}')">
                        <div class="skin-actions">
                            <h6 class="mb-2 text-truncate" title="${skin.name}">${skin.name}</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    ${new Date(skin.created_at).toLocaleDateString('vi-VN')}
                                </small>
                                <button class="btn btn-outline-danger btn-sm" 
                                        onclick="deleteSkin('${skin.id}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function viewSkin(skinId, imageUrl, skinName) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('imageModalLabel').textContent = `Xem Skin: ${skinName}`;
            document.getElementById('deleteFromModal').onclick = () => deleteSkin(skinId);

            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        }

        function deleteSkin(skinId) {
            if (!confirm('Bạn có chắc chắn muốn xóa skin này?')) {
                return;
            }

            fetch(`/skins/${heroId}/${skinId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadSkins(); // Reload skins list

                        // Close modal if open
                        const modal = bootstrap.Modal.getInstance(document.getElementById('imageModal'));
                        if (modal) modal.hide();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi xóa skin!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa skin!');
                });
        }

        function updateStats() {
            document.getElementById('totalSkins').textContent = currentSkins.length;

            const today = new Date().toDateString();
            const uploadedToday = currentSkins.filter(skin =>
                new Date(skin.created_at).toDateString() === today
            ).length;
            document.getElementById('uploadedToday').textContent = uploadedToday;

            // Estimate total size (placeholder)
            const estimatedSize = currentSkins.length * 0.5; // Assume 0.5MB per image
            document.getElementById('totalSize').textContent = estimatedSize.toFixed(1) + ' MB';
        }

        function showLoading() {
            document.getElementById('loadingState').style.display = 'block';
            document.getElementById('skinsGrid').style.display = 'none';
            document.getElementById('emptyState').style.display = 'none';
        }

        function hideLoading() {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('skinsGrid').style.display = 'block';
        }

        function showEmpty() {
            document.getElementById('skinsGrid').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
        }

        function hideEmpty() {
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('skinsGrid').style.display = 'block';
        }
    </script>
</body>

</html>

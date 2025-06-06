<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Chỉnh Sửa Hero - {{ $hero['name'] }}</title>
    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }

        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: #007bff;
            background: #e3f2fd;
        }

        .upload-area.dragover {
            border-color: #28a745;
            background: #d4edda;
        }

        .preview-container {
            position: relative;
            display: inline-block;
        }

        .preview-image {
            max-width: 300px;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .remove-preview {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 14px;
            cursor: pointer;
        }

        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .upload-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .character-count {
            font-size: 12px;
            color: #6c757d;
            text-align: right;
        }

        .current-image {
            max-width: 200px;
            border-radius: 8px;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0"><i class="fas fa-edit"></i> Chỉnh Sửa Hero</h1>
                    <p class="mb-0 mt-2">Cập nhật thông tin cho "{{ $hero['name'] }}"</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="/heroes" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="form-container">
                    <form id="heroForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="heroId" value="{{ $hero['id'] }}">

                        <!-- Hero Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label">
                                <i class="fas fa-mask"></i> Tên Hero *
                            </label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name"
                                required maxlength="255" value="{{ $hero['name'] }}" placeholder="Nhập tên hero...">
                            <div class="character-count">
                                <span id="nameCount">{{ strlen($hero['name']) }}</span>/255 ký tự
                            </div>
                        </div>

                        <!-- Current Avatar Display -->
                        @if (isset($hero['avatar_url']) && $hero['avatar_url'])
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user-circle"></i> Avatar hiện tại
                                </label>
                                <div class="text-center">
                                    <img src="{{ $hero['avatar_url'] }}" alt="{{ $hero['name'] }}"
                                        class="current-image">
                                </div>
                            </div>
                        @endif

                        <!-- Image Upload -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-upload"></i> Thay đổi Avatar Hero
                                <small class="text-muted">(để trống nếu không muốn thay đổi)</small>
                            </label>

                            <div class="upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                                <div id="uploadContent">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <h5>Click để chọn ảnh mới hoặc kéo thả ảnh vào đây</h5>
                                    <p class="text-muted">Hỗ trợ: JPG, PNG, GIF (tối đa 2MB)</p>
                                </div>

                                <div id="previewContainer" style="display: none;">
                                    <div class="preview-container">
                                        <img id="previewImage" class="preview-image" src="" alt="Preview">
                                        <button type="button" class="remove-preview" onclick="removePreview()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <p class="mt-3 mb-0">Click để thay đổi ảnh khác</p>
                                </div>
                            </div>

                            <input type="file" class="form-control d-none" id="image" name="avatar"
                                accept="image/*">
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/heroes" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-save"></i> Cập nhật Hero
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">
                        <i class="fas fa-check-circle"></i> Cập nhật thành công!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Hero đã được cập nhật thành công!</h4>
                    <p id="successMessage" class="text-muted"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    <a href="/heroes" class="btn btn-success">Xem danh sách</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedFile = null;

        // Character counting
        document.getElementById('name').addEventListener('input', function() {
            document.getElementById('nameCount').textContent = this.value.length;
        });

        // File input change handler
        document.getElementById('image').addEventListener('change', function(e) {
            handleFileSelect(e.target.files[0]);
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

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    document.getElementById('image').files = files;
                    handleFileSelect(file);
                } else {
                    alert('Vui lòng chọn file ảnh!');
                }
            }
        });

        function handleFileSelect(file) {
            if (!file) return;

            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('File ảnh quá lớn! Vui lòng chọn file nhỏ hơn 2MB.');
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Vui lòng chọn file ảnh!');
                return;
            }

            selectedFile = file;

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('uploadContent').style.display = 'none';
                document.getElementById('previewContainer').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        function removePreview() {
            document.getElementById('uploadContent').style.display = 'block';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('image').value = '';
            selectedFile = null;
        }

        // Form submission
        document.getElementById('heroForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            const heroId = document.getElementById('heroId').value;

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';

            const formData = new FormData();
            formData.append('name', document.getElementById('name').value);

            if (selectedFile) {
                formData.append('avatar', selectedFile);
            }

            fetch(`/heroes/${heroId}`, {
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
                        // Show success modal
                        document.getElementById('successMessage').textContent =
                            `Hero "${data.hero.name}" đã được cập nhật.`;

                        const modal = new bootstrap.Modal(document.getElementById('successModal'));
                        modal.show();

                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi cập nhật hero!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật hero!');
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        });

        // Auto-focus on name field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('name').focus();
        });
    </script>
</body>

</html>

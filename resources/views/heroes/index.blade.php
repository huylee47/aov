<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Quản Lý Heroes</title>
    <style>
        .hero-card {
            transition: transform 0.2s;
            border: 1px solid #dee2e6;
        }

        .hero-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .hero-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .hero-placeholder {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
            color: #6c757d;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
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

        .navigation-links {
            margin-bottom: 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0"><i class="fas fa-mask"></i> Quản Lý Heroes</h1>
                    <p class="mb-0 mt-2">Thêm, chỉnh sửa và quản lý tất cả heroes</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="/heroes/create" class="btn btn-light btn-lg">
                        <i class="fas fa-plus"></i> Thêm Hero Mới
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Navigation Links -->
        <div class="navigation-links">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Heroes</li>
                </ol>
            </nav>
        </div>

        <!-- Action Bar -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm heroes...">
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-outline-primary" onclick="loadHeroes()">
                    <i class="fas fa-sync-alt"></i> Làm mới
                </button>
                <button class="btn btn-outline-success" onclick="exportHeroes()">
                    <i class="fas fa-download"></i> Xuất danh sách
                </button>
            </div>
        </div>

        <!-- Heroes Grid -->
        <div id="heroesContainer">
            <div class="row" id="heroesGrid">
                <!-- Heroes will be loaded here -->
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="mt-3">Đang tải heroes...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="empty-state" style="display: none;">
            <i class="fas fa-mask"></i>
            <h3>Chưa có hero nào</h3>
            <p>Bắt đầu bằng cách thêm hero đầu tiên của bạn!</p>
            <a href="/heroes/create" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i> Thêm Hero Đầu Tiên
            </a>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Chỉnh Sửa Hero</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editHeroId">

                        <div class="mb-3">
                            <label for="editName" class="form-label">Tên Hero</label>
                            <input type="text" class="form-control" id="editName" required>
                        </div>



                        <div class="mb-3">
                            <label for="editImage" class="form-label">Avatar Hero (để trống nếu không muốn thay
                                đổi)</label>
                            <input type="file" class="form-control" id="editImage" name="avatar" accept="image/*">
                        </div>

                        <div id="currentImageContainer" class="mb-3" style="display: none;">
                            <label class="form-label">Ảnh hiện tại:</label>
                            <img id="currentImage" src="" alt="Current image" class="img-thumbnail"
                                style="max-width: 200px;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let allHeroes = [];

        // Load heroes on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadHeroes();
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterHeroes(searchTerm);
        });

        function loadHeroes() {
            showLoading();

            fetch('/heroes/list')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allHeroes = data.data;
                        displayHeroes(allHeroes);
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

        function displayHeroes(heroes) {
            const container = document.getElementById('heroesGrid');

            if (heroes.length === 0) {
                showEmpty();
                return;
            }

            hideEmpty();

            container.innerHTML = heroes.map(hero => `
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card hero-card h-100">
                        <div class="card-body">
                                                        ${hero.avatar_url ? 
                                `<img src="${hero.avatar_url}" alt="${hero.name}" class="hero-image mb-3">` :
                                `<div class="hero-placeholder mb-3">
                                                                    <i class="fas fa-user-circle fa-2x"></i>
                                                                </div>`
                            }
                            
                            <h5 class="card-title">${hero.name}</h5>
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> 
                                ${new Date(hero.created_at).toLocaleDateString('vi-VN')}
                            </small>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-success" onclick="manageSkins('${hero.id}')">
                                    <i class="fas fa-images"></i> Skins
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="editHero('${hero.id}')">
                                    <i class="fas fa-edit"></i> Sửa
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteHero('${hero.id}', '${hero.name}')">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function filterHeroes(searchTerm) {
            const filtered = allHeroes.filter(hero =>
                hero.name.toLowerCase().includes(searchTerm)
            );
            displayHeroes(filtered);
        }

        function manageSkins(heroId) {
            window.location.href = `/heroes/${heroId}/skins`;
        }

        function editHero(heroId) {
            const hero = allHeroes.find(h => h.id === heroId);
            if (!hero) return;

            document.getElementById('editHeroId').value = hero.id;
            document.getElementById('editName').value = hero.name;
            document.getElementById('editDescription').value = hero.description || '';

            const currentImageContainer = document.getElementById('currentImageContainer');
            const currentImage = document.getElementById('currentImage');

            if (hero.avatar_url) {
                currentImage.src = hero.avatar_url;
                currentImageContainer.style.display = 'block';
            } else {
                currentImageContainer.style.display = 'none';
            }

            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }

        function deleteHero(heroId, heroName) {
            if (!confirm(`Bạn có chắc chắn muốn xóa hero "${heroName}"?`)) {
                return;
            }

            fetch(`/heroes/${heroId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadHeroes(); // Reload the list
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi xóa hero!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa hero!');
                });
        }

        // Edit form submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const heroId = document.getElementById('editHeroId').value;
            const formData = new FormData();
            formData.append('name', document.getElementById('editName').value);

            const avatarFile = document.getElementById('editImage').files[0];
            if (avatarFile) {
                formData.append('avatar', avatarFile);
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
                        alert(data.message);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                        modal.hide();
                        loadHeroes(); // Reload the list
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi cập nhật hero!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật hero!');
                });
        });

        function exportHeroes() {
            const csvContent = "data:text/csv;charset=utf-8," +
                "Tên,Mô tả,Ngày tạo\n" +
                allHeroes.map(hero =>
                    `"${hero.name}","${hero.description || ''}","${new Date(hero.created_at).toLocaleString('vi-VN')}"`
                ).join('\n');

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', `heroes_${Date.now()}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function showLoading() {
            document.getElementById('loadingState').style.display = 'block';
            document.getElementById('heroesContainer').style.display = 'none';
            document.getElementById('emptyState').style.display = 'none';
        }

        function hideLoading() {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('heroesContainer').style.display = 'block';
        }

        function showEmpty() {
            document.getElementById('heroesContainer').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
        }

        function hideEmpty() {
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('heroesContainer').style.display = 'block';
        }
    </script>
</body>

</html>

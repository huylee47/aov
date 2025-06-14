<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <title>Tạo Form Ảnh</title>
    <style>
        .photo-grid {
            display: grid;
            row-gap: 10px;
            /* Khoảng cách giữa các hàng */
            column-gap: 0px;
            /* Không có khoảng cách giữa các cột */
            max-width: 100%;
            margin: 20px 0;
            /* Bỏ border tổng thể */
        }

        .photo-cell {
            aspect-ratio: 1/1.4;
            border: none !important;
            /* Bỏ hết border mặc định */
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .photo-cell:hover:not(.has-image) {
            border-color: #007bff;
            background-color: #e3f2fd;
        }

        .photo-cell.has-image {
            /* Giữ nguyên border khi có ảnh */
            background-color: transparent !important;
        }

        .photo-cell img {
            width: 100%;
            /* Fill toàn bộ cell */
            height: 100%;
            /* Fill toàn bộ cell */
            object-fit: cover;
            border: none;
            /* Bỏ border của ảnh */
            display: block !important;
        }

        .photo-cell .placeholder-text {
            color: #6c757d;
            font-size: 12px;
            text-align: center;
        }

        .large-cell {
            grid-column: span 4;
            /* Chiếm 4 ô grid */
            /* Nhưng chỉ chiếm 90% width = 3.6 ô */
            aspect-ratio: 3.9/1.4;
            margin-right: 10px;
            border: none !important;
            /* Border sẽ được apply bằng JavaScript */
            margin-bottom: 25px;
            /* Khoảng cách lớn hơn dưới ảnh lớn */
        }

        .large-cell img {
            width: 100%;
            /* Fill toàn bộ large cell */
            height: 100%;
            /* Fill toàn bộ large cell */
            object-fit: cover;
            /* Cover toàn bộ ảnh lớn */
            border: none;
            /* Bỏ border ảnh để không bị xung đột */
            display: block !important;
        }

        /* Wrapper cho mỗi hàng để tạo border */
        .photo-row {
            display: contents;
        }

        /* Border sẽ được apply bằng JavaScript, không dùng CSS */
        .photo-row:not(.large-row) .photo-cell {
            border: none !important;
            /* Border sẽ được apply bằng JavaScript */
        }

        /* Border liên tục cho các cell trong cùng hàng */
        .photo-row:not(.large-row) .photo-cell:not(:first-child) {
            border-left: none;
            /* Bỏ border trái để tạo liên tục */
        }

        .remove-btn {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            display: none;
            z-index: 10;
            /* Đảm bảo nút luôn hiện trên ảnh */
        }

        .photo-cell.has-image:hover .remove-btn {
            display: block;
        }

        .grid-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }

        .form-control {
            margin-bottom: 15px;
        }

        .btn-group {
            margin: 10px 0;
        }

        /* Export styles */
        .export-container {
            background: white;
            padding: 20px;
            margin: 20px 0;
        }

        .export-grid {
            background: white;
            padding: 0;
            box-shadow: none;
            row-gap: 10px !important;
            /* Giảm khoảng cách giữa các hàng */
            column-gap: 0px !important;
            /* Không có khoảng cách giữa các cột */
        }

        .export-grid .large-cell {
            margin-bottom: 0px !important;
            /* Bỏ margin-bottom để không bị cách quá xa hàng 2 */
            grid-column: span 4 !important;
            /* Đảm bảo span 4 trong export */
            aspect-ratio: 3.9/1.4 !important;
            /* Đảm bảo aspect ratio nhất quán với main grid */
            margin-right: 10px !important;
        }

        .export-grid .photo-cell {
            border: none !important;
            /* Bỏ hết border cell */
            background: white !important;
        }

        .export-grid .photo-cell img {
            border: none !important;
            /* Bỏ hết border ảnh */
            box-shadow: none !important;
            width: 100% !important;
            height: 100% !important;
        }

        .export-grid .large-cell img {
            width: 100% !important;
            /* Fill toàn bộ large cell trong export */
            height: 100% !important;
            /* Fill toàn bộ large cell trong export */
            object-fit: cover !important;
            /* Cover toàn bộ ảnh lớn trong export */
            border: none !important;
            /* Bỏ border ảnh trong export */
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Tạo Form Ảnh</h2>
                    <div>
                        <a href="/heroes" class="btn btn-outline-primary">
                            <i class="fas fa-mask"></i> Quản lý Heroes
                        </a>
                    </div>
                </div>

                <!-- Form chọn kích thước -->
                <div class="card">
                    <div class="card-header">
                        <h5>Chọn kích thước grid</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="cols" class="form-label">Số cột (chiều ngang):</label>
                                <input type="number" id="cols" class="form-control" min="2" max="20"
                                    value="10">
                            </div>
                            <div class="col-md-4">
                                <label for="rows" class="form-label">Số hàng (chiều dọc):</label>
                                <input type="number" id="rows" class="form-control" min="2" max="20"
                                    value="6">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-primary w-100" onclick="generateGrid()">
                                    Tạo Grid
                                </button>
                            </div>
                        </div>

                        <!-- Preset buttons -->
                        <div class="btn-group" role="group" aria-label="Preset sizes">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="setGridSize(4, 6)">4x6</button>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="setGridSize(5, 7)">5x7</button>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="setGridSize(6, 8)">6x8</button>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="setGridSize(8, 10)">8x10</button>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="setGridSize(10, 5)">10x5</button>
                        </div>
                    </div>
                </div>

                <!-- Grid preview -->
                <div id="gridContainer" class="grid-preview" style="display: none;">
                    <h5>Grid ảnh (Click vào ô để chọn ảnh)</h5>
                    <div id="photoGrid" class="photo-grid"></div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-success" onclick="saveGrid()">
                            Lưu Form
                        </button>
                        <button type="button" class="btn btn-warning" onclick="clearAllImages()">
                            Xóa tất cả ảnh
                        </button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" onclick="exportToPNG()">
                                Xuất PNG (Client)
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="exportHighQuality()">
                                Xuất HD (Client)
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportToServerSide()">
                                Xuất PNG (Server)
                            </button>
                        </div>
                        <button type="button" class="btn btn-info" onclick="debugGrid(); applyRowBorders();">
                            Debug & Apply Borders
                        </button>
                    </div>
                </div>

                <!-- Saved Grids Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Grid đã lưu</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-info" onclick="loadSavedGrids()">
                                    Tải danh sách Grid
                                </button>
                            </div>
                        </div>
                        <div id="savedGridsList" class="mt-3" style="display: none;">
                            <!-- Saved grids will be displayed here -->
                        </div>
                    </div>
                </div>

                <!-- Hidden file input -->
                <input type="file" id="imageInput" accept="image/*" style="display: none;"
                    onchange="handleImageSelect(event)">
            </div>
        </div>
    </div>

    <!-- Skin Selection Modal -->
    <div class="modal fade" id="skinSelectionModal" tabindex="-1" aria-labelledby="skinSelectionLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="skinSelectionLabel">Chọn Skin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="skinSearchInput" class="form-control"
                                placeholder="Tìm kiếm skin theo tên...">
                        </div>
                        <div class="col-md-6">
                            <select id="heroFilterSelect" class="form-select">
                                <option value="">Tất cả heroes</option>
                            </select>
                        </div>
                    </div>

                    <div id="skinGridContainer" class="row" style="max-height: 400px; overflow-y: auto;">
                        <!-- Skins will be loaded here -->
                    </div>

                    <div id="loadingSkins" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <p class="mt-3">Đang tải skins...</p>
                    </div>

                    <div id="noSkinsFound" class="text-center py-5" style="display: none;">
                        <i class="fas fa-images fa-3x text-muted"></i>
                        <h5 class="mt-3">Không tìm thấy skin nào</h5>
                        <p class="text-muted">Hãy thêm skin mới hoặc thay đổi bộ lọc</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary"
                        onclick="document.getElementById('imageInput').click(); bootstrap.Modal.getInstance(document.getElementById('skinSelectionModal')).hide();">
                        <i class="fas fa-upload"></i> Upload ảnh mới
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Preview Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Preview Grid Export</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="exportPreview" class="text-center"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="confirmExport()">Xuất PNG</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentCellIndex = null;
        let gridData = {
            rows: 0,
            cols: 0,
            images: {}
        };

        function setGridSize(cols, rows) {
            document.getElementById('cols').value = cols;
            document.getElementById('rows').value = rows;
            generateGrid();
        }

        function generateGrid() {
            const cols = parseInt(document.getElementById('cols').value);
            const rows = parseInt(document.getElementById('rows').value);

            console.log('Generating grid with cols:', cols, 'rows:', rows);

            if (cols < 2 || rows < 2) {
                alert('Số cột và số hàng phải lớn hơn hoặc bằng 2');
                return;
            }

            gridData.rows = rows;
            gridData.cols = cols;
            gridData.images = {};

            const gridContainer = document.getElementById('gridContainer');
            const photoGrid = document.getElementById('photoGrid');

            // Set grid template
            photoGrid.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;

            // Clear existing grid
            photoGrid.innerHTML = '';

            let cellIndex = 0;

            for (let row = 0; row < rows; row++) {
                // Tạo wrapper cho từng hàng
                const isLargeRow = (row === 0); // Hàng đầu tiên có ảnh lớn

                for (let col = 0; col < cols; col++) {
                    // Skip cells 1, 2, 3 of first row (will be covered by large cell that spans 4 columns)
                    if (row === 0 && (col === 1 || col === 2 || col === 3)) {
                        continue;
                    }

                    // Row 1 và các row khác sẽ có cells bình thường
                    // (Không skip row 1 nữa để có đủ số hàng như user chọn)

                    const cell = document.createElement('div');
                    cell.className = 'photo-cell';
                    cell.setAttribute('data-index', cellIndex);
                    cell.setAttribute('data-row', row);
                    cell.setAttribute('data-col', col);

                    // Create a closure to capture the current cellIndex value
                    cell.onclick = ((index) => () => selectImage(index))(cellIndex);

                    // Make first cell large (spans 2 columns)
                    if (row === 0 && col === 0) {
                        cell.classList.add('large-cell');
                        cell.innerHTML =
                            '<div class="placeholder-text">Click để upload ảnh<br>(Avatar)</div>';
                    } else {
                        cell.innerHTML = '<div class="placeholder-text">Click để chọn skin<br>(Chọn từ hệ thống)</div>';
                    }

                    // Add remove button
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-btn';
                    removeBtn.innerHTML = '×';
                    removeBtn.onclick = ((index) => (e) => {
                        e.stopPropagation();
                        removeImage(index);
                    })(cellIndex);
                    cell.appendChild(removeBtn);

                    photoGrid.appendChild(cell);
                    console.log('Created cell with index:', cellIndex, 'at row:', row, 'col:', col);
                    cellIndex++;
                }
            }

            // Áp dụng border cho từng hàng bằng CSS
            console.log('🎯 CALLING applyRowBorders() after grid generation...');
            applyRowBorders();
            console.log('🎯 FINISHED applyRowBorders() call');

            gridContainer.style.display = 'block';

            // Debug: Log all created cells
            const allCells = document.querySelectorAll('.photo-cell');
            console.log('Total cells created:', allCells.length);
            // Calculate expected cells correctly
            let expectedCells = 0;
            for (let r = 0; r < rows; r++) {
                if (r === 0) {
                    expectedCells += (cols - 3); // Row 0: total cols minus 3 skipped (large cell spans 4)
                } else {
                    expectedCells += cols; // All other rows: full cols
                }
            }
            console.log('Expected cells for', rows, 'x', cols, '=', expectedCells, '(Row 0:', cols - 3,
                ', Rows 1+:', cols, 'each)');

            const cellsByRow = {};
            allCells.forEach(cell => {
                const row = cell.getAttribute('data-row');
                if (!cellsByRow[row]) cellsByRow[row] = 0;
                cellsByRow[row]++;
                console.log('Cell index:', cell.getAttribute('data-index'), 'Row:', row, 'Col:', cell.getAttribute(
                    'data-col'));
            });

            Object.keys(cellsByRow).forEach(row => {
                console.log('Row', row, 'has', cellsByRow[row], 'cells');
            });
        }

        function applyRowBorders() {
            console.log('🎯 applyRowBorders() called');
            applyRowBordersToElement(document);
            console.log('🎯 applyRowBorders() finished');
        }

        function applyRowBordersToElement(container) {
            console.log('🎯 applyRowBordersToElement() started');
            const cells = container.querySelectorAll('.photo-cell');
            console.log('🎯 Found', cells.length, 'cells to process');
            const rows = {};

            // Nhóm cells theo hàng
            cells.forEach(cell => {
                const row = cell.getAttribute('data-row');
                if (!rows[row]) {
                    rows[row] = [];
                }
                rows[row].push(cell);
            });

            // Áp dụng border cho từng hàng
            Object.keys(rows).forEach(rowNum => {
                const rowCells = rows[rowNum];
                console.log('Processing row:', rowNum, 'cells:', rowCells.length);

                // Xử lý large cell (nếu có)
                const largeCell = rowCells.find(cell => cell.classList.contains('large-cell'));
                if (largeCell) {
                    largeCell.style.setProperty('border', 'none', 'important');
                    largeCell.style.setProperty('border-left', '3px solid #333', 'important');
                    largeCell.style.setProperty('border-right', '3px solid #333', 'important');
                    largeCell.style.setProperty('border-top', '3px solid #333', 'important');
                    largeCell.style.setProperty('border-bottom', '3px solid #333', 'important');
                    largeCell.style.setProperty('box-shadow', '0 4px 8px rgba(0, 0, 0, 0.1)', 'important');
                    console.log('✅ Applied LARGE CELL border in row:', rowNum, 'Element:', largeCell);
                    console.log('✅ Large cell computed style:', window.getComputedStyle(largeCell).border);
                }

                // Lọc ra các cell không phải large-cell
                const normalCells = rowCells.filter(cell => !cell.classList.contains('large-cell'));

                if (normalCells.length === 0) {
                    console.log('Row', rowNum, 'has no normal cells, skipping');
                    return;
                }

                console.log('Applying border to', normalCells.length, 'normal cells in row:', rowNum);

                normalCells.forEach((cell, index) => {
                    // Reset all borders completely first
                    cell.style.setProperty('border', 'none', 'important');
                    cell.style.setProperty('border-left', 'none', 'important');
                    cell.style.setProperty('border-right', 'none', 'important');
                    cell.style.setProperty('border-top', 'none', 'important');
                    cell.style.setProperty('border-bottom', 'none', 'important');

                    // Apply row borders (top/bottom for all cells)
                    cell.style.setProperty('border-top', '3px solid #333', 'important');
                    cell.style.setProperty('border-bottom', '3px solid #333', 'important');
                    cell.style.setProperty('box-shadow', '0 4px 8px rgba(0, 0, 0, 0.1)', 'important');

                    // Apply left/right borders to create box around the row
                    // First cell in row always has left border
                    if (index === 0) {
                        cell.style.setProperty('border-left', '3px solid #333', 'important');
                        console.log('✅ Applied LEFT border to first cell in row:', rowNum, 'cell:', cell
                            .getAttribute('data-index'));
                    }

                    // Last cell in row always has right border
                    if (index === normalCells.length - 1) {
                        cell.style.setProperty('border-right', '3px solid #333', 'important');
                        console.log('✅ Applied RIGHT border to last cell in row:', rowNum, 'cell:', cell
                            .getAttribute('data-index'));
                    }

                    console.log('✅ Applied border to cell index:', cell.getAttribute('data-index'),
                        'in row:',
                        rowNum, 'position:', index, 'of', normalCells.length);
                    console.log('✅ Cell computed style:', window.getComputedStyle(cell).border);
                });
            });
        }

        function selectImage(cellIndex) {
            console.log('🎯🎯🎯 selectImage() called with cellIndex:', cellIndex);
            currentCellIndex = cellIndex;
            const cell = document.querySelector(`[data-index="${cellIndex}"]`);

            console.log('🎯 Selected cell index:', cellIndex, 'Cell element:', cell);
            console.log('🎯 Cell classes:', cell ? cell.className : 'NO CELL');
            console.log('🎯 currentCellIndex set to:', currentCellIndex);

            if (!cell) {
                console.error('❌ Cell not found for index:', cellIndex);
                const allCells = document.querySelectorAll('.photo-cell');
                console.log('Available cell indices:', Array.from(allCells).map(c => c.getAttribute('data-index')));
                return;
            }

            // Check if this is the large cell (first cell)
            if (cell.classList.contains('large-cell')) {
                // For large cell, allow upload new image
                console.log('✅ LARGE CELL detected - Opening file selector for large cell');
                const fileInput = document.getElementById('imageInput');
                console.log('✅ File input element:', fileInput);
                console.log('✅ About to trigger click on file input...');
                fileInput.click();
                console.log('✅ File input click triggered');
            } else {
                // For small cells, show skin selection modal
                console.log('🔹 Normal cell - Opening skin selection modal for small cell');
                showSkinSelectionModal(cellIndex);
            }
        }

        function handleImageSelect(event) {
            console.log('🔥🔥🔥 handleImageSelect() called');
            const file = event.target.files[0];
            console.log('🔥 File selected:', file);
            console.log('🔥 Current cellIndex:', currentCellIndex);
            console.log('🔥 Event target:', event.target);
            console.log('🔥 Files array:', event.target.files);

            if (!file) {
                console.error('❌ No file selected');
                return;
            }

            if (currentCellIndex === null) {
                console.error('❌ currentCellIndex is null');
                return;
            }

            console.log('✅ Processing image select for cell:', currentCellIndex, 'File:', file.name, 'Size:', file.size);

            const reader = new FileReader();
            reader.onload = function(e) {
                console.log('🔥 FileReader onload triggered');
                const cell = document.querySelector(`[data-index="${currentCellIndex}"]`);
                console.log('🔥 Found cell for index', currentCellIndex, ':', cell);
                console.log('🔥 Cell classes:', cell ? cell.className : 'NO CELL');

                if (cell) {
                    console.log('✅ About to update cell HTML...');
                    cell.innerHTML = `
                        <img src="${e.target.result}" alt="Selected image">
                        <button class="remove-btn" onclick="event.stopPropagation(); removeImage(${currentCellIndex})">×</button>
                    `;
                    cell.classList.add('has-image');
                    console.log('✅ Updated cell HTML and added has-image class');

                    // Store image data
                    gridData.images[currentCellIndex] = {
                        name: file.name,
                        data: e.target.result
                    };
                    console.log('✅ Stored image data for cell:', currentCellIndex);

                    // Reapply borders after adding image
                    setTimeout(() => {
                        console.log('🔥 Reapplying borders after image upload...');
                        applyRowBorders();
                    }, 100);
                } else {
                    console.error('❌ Cell not found for index:', currentCellIndex);
                }

                // Reset currentCellIndex sau khi hoàn tất
                currentCellIndex = null;
                console.log('🔥 Reset currentCellIndex after successful upload');
            };

            reader.onerror = function(e) {
                console.error('❌ FileReader error:', e);
                // Reset currentCellIndex khi có lỗi
                currentCellIndex = null;
            };

            console.log('🔥 Starting FileReader.readAsDataURL...');
            reader.readAsDataURL(file);

            // Reset file input ngay lập tức (OK vì không ảnh hưởng đến logic)
            event.target.value = '';
            console.log('🔥 Reset file input value');
        }

        function removeImage(cellIndex) {
            const cell = document.querySelector(`[data-index="${cellIndex}"]`);
            if (!cell) {
                console.error('Cannot remove image - cell not found for index:', cellIndex);
                return;
            }

            cell.classList.remove('has-image');

            if (cell.classList.contains('large-cell')) {
                cell.innerHTML = '<div class="placeholder-text">Click để upload ảnh<br>(Ô lớn - chỉ upload mới)</div>';
            } else {
                cell.innerHTML = '<div class="placeholder-text">Click để chọn skin<br>(Chọn từ database)</div>';
            }

            // Add remove button back
            const removeBtn = document.createElement('button');
            removeBtn.className = 'remove-btn';
            removeBtn.innerHTML = '×';
            removeBtn.onclick = ((index) => (e) => {
                e.stopPropagation();
                removeImage(index);
            })(cellIndex);
            cell.appendChild(removeBtn);

            // Remove from data
            delete gridData.images[cellIndex];

            // Reapply borders after removing image
            setTimeout(() => applyRowBorders(), 100);
        }

        function clearAllImages() {
            if (confirm('Bạn có chắc chắn muốn xóa tất cả ảnh?')) {
                gridData.images = {};
                const cells = document.querySelectorAll('.photo-cell');
                cells.forEach((cell) => {
                    const cellIndex = cell.getAttribute('data-index'); // Sử dụng data-index thực tế
                    cell.classList.remove('has-image');

                    if (cell.classList.contains('large-cell')) {
                        cell.innerHTML =
                            '<div class="placeholder-text">Click để upload ảnh<br>(Ô lớn - chỉ upload mới)</div>';
                    } else {
                        cell.innerHTML =
                            '<div class="placeholder-text">Click để chọn skin<br>(Chọn từ database)</div>';
                    }

                    // Add remove button back
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-btn';
                    removeBtn.innerHTML = '×';
                    removeBtn.onclick = (e) => {
                        e.stopPropagation();
                        removeImage(cellIndex); // Sử dụng cellIndex thực tế
                    };
                    cell.appendChild(removeBtn);
                });

                // Reapply borders after clearing all images
                setTimeout(() => applyRowBorders(), 100);
            }
        }

        function saveGrid() {
            // Check if there are any images to save
            if (Object.keys(gridData.images).length === 0) {
                alert('Vui lòng thêm ít nhất một ảnh trước khi lưu!');
                return;
            }

            // Show loading state
            const saveBtn = document.querySelector('.btn-success');
            const originalText = saveBtn.textContent;
            saveBtn.disabled = true;
            saveBtn.textContent = 'Đang lưu...';

            fetch('/save-photo-grid', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(gridData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Đã lưu thành công!');
                        console.log('Saved grid ID:', data.grid_id);
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi lưu!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi lưu!');
                })
                .finally(() => {
                    // Restore button state
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;
                });
        }

        function loadSavedGrids() {
            fetch('/photo-grids')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        displaySavedGrids(data.data);
                    } else {
                        document.getElementById('savedGridsList').innerHTML =
                            '<div class="alert alert-info">Chưa có grid nào được lưu.</div>';
                        document.getElementById('savedGridsList').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tải danh sách grid!');
                });
        }

        function displaySavedGrids(grids) {
            const container = document.getElementById('savedGridsList');
            let html = '<div class="row">';

            grids.forEach(grid => {
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Grid ${grid.cols}x${grid.rows}</h6>
                                <p class="card-text">
                                    Số ảnh: ${Object.keys(grid.images).length}<br>
                                    Tạo lúc: ${new Date(grid.created_at).toLocaleString('vi-VN')}
                                </p>
                                <button class="btn btn-primary btn-sm" onclick="loadGrid('${grid.id}')">
                                    Tải Grid
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteGrid('${grid.id}')">
                                    Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            container.innerHTML = html;
            container.style.display = 'block';
        }

        function loadGrid(gridId) {
            fetch(`/photo-grid/${gridId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const grid = data.data;

                        // Set grid dimensions
                        document.getElementById('cols').value = grid.cols;
                        document.getElementById('rows').value = grid.rows;

                        // Generate the grid structure
                        generateGrid();

                        // Load the images
                        setTimeout(() => {
                            Object.keys(grid.images).forEach(cellIndex => {
                                const imageData = grid.images[cellIndex];
                                const cell = document.querySelector(`[data-index="${cellIndex}"]`);

                                if (cell && imageData.url) {
                                    cell.innerHTML = `
                                    <img src="${imageData.url}" alt="Loaded image">
                                    <button class="remove-btn" onclick="event.stopPropagation(); removeImage(${cellIndex})">×</button>
                                `;
                                    cell.classList.add('has-image');

                                    // Store in current grid data
                                    gridData.images[cellIndex] = {
                                        name: imageData.original_name,
                                        data: imageData.url
                                    };
                                }
                            });
                        }, 100);

                        alert('Grid đã được tải thành công!');
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi tải grid!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tải grid!');
                });
        }

        function deleteGrid(gridId) {
            if (confirm('Bạn có chắc chắn muốn xóa grid này?')) {
                fetch(`/photo-grid/${gridId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message || 'Đã xóa grid thành công!');
                            loadSavedGrids(); // Reload the list
                        } else {
                            alert(data.message || 'Có lỗi xảy ra khi xóa grid!');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi xóa grid!');
                    });
            }
        }

        let exportCanvas = null;

        function exportToPNG() {
            // Check if there are images in the grid
            if (Object.keys(gridData.images).length === 0) {
                alert('Vui lòng thêm ít nhất một ảnh trước khi xuất!');
                return;
            }

            // Show preview modal
            showExportPreview();
        }

        function exportHighQuality() {
            console.log('🎯 Starting HIGH QUALITY export...');

            if (Object.keys(gridData.images).length === 0) {
                alert('Vui lòng thêm ít nhất một ảnh trước khi xuất!');
                return;
            }

            // Create export container
            const exportContainer = document.createElement('div');
            exportContainer.className = 'export-container';
            exportContainer.style.position = 'absolute';
            exportContainer.style.left = '-9999px';
            exportContainer.style.top = '0';

            // Clone the grid for ultra high quality
            const originalGrid = document.getElementById('photoGrid');
            const clonedGrid = originalGrid.cloneNode(true);
            clonedGrid.className = 'export-grid photo-grid';

            // Scale up the grid for higher resolution
            clonedGrid.style.transform = 'scale(2)';
            clonedGrid.style.transformOrigin = 'top left';

            // Remove interactive elements
            const removeButtons = clonedGrid.querySelectorAll('.remove-btn');
            removeButtons.forEach(btn => btn.remove());

            const cells = clonedGrid.querySelectorAll('.photo-cell');
            cells.forEach(cell => {
                cell.onclick = null;
                cell.style.cursor = 'default';
            });

            exportContainer.appendChild(clonedGrid);
            applyRowBordersToElement(clonedGrid);
            document.body.appendChild(exportContainer);

            // Wait for images to load
            const clonedImages = clonedGrid.querySelectorAll('img');
            console.log('🎯 Found', clonedImages.length, 'images for HD export');

            const imagePromises = Array.from(clonedImages).map((img, index) => {
                return new Promise((resolve) => {
                    if (img.complete && img.naturalWidth > 0) {
                        resolve();
                    } else {
                        img.onload = () => resolve();
                        img.onerror = () => resolve();
                    }
                });
            });

            Promise.all(imagePromises).then(() => {
                console.log('🎯 All images loaded for HD export, processing...');

                // Ultra high-quality options
                const options = {
                    backgroundColor: '#ffffff',
                    scale: 5, // Ultra high resolution
                    useCORS: true,
                    allowTaint: true,
                    logging: false,
                    width: clonedGrid.offsetWidth * 2, // Account for transform scale
                    height: clonedGrid.offsetHeight * 2,
                    dpi: 300,
                    pixelRatio: 4
                };

                console.log('🎯 Starting HD html2canvas - Final size will be:',
                    options.width * options.scale, 'x', options.height * options.scale);

                html2canvas(clonedGrid, options).then(canvas => {
                    console.log('✅ HD Export successful - Canvas size:', canvas.width, 'x', canvas.height);

                    // Create download link
                    const link = document.createElement('a');
                    link.download = `photo-grid-HD-${Date.now()}.png`;
                    link.href = canvas.toDataURL('image/png', 1.0);

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    document.body.removeChild(exportContainer);
                    alert('🎯 Xuất HD thành công! Kích thước: ' + canvas.width + 'x' + canvas.height);
                }).catch(error => {
                    console.error('❌ HD Export failed:', error);
                    document.body.removeChild(exportContainer);
                    alert('HD Export failed: ' + error.message);
                });
            });
        }

        function showExportPreview() {
            // Create export container
            const exportContainer = document.createElement('div');
            exportContainer.className = 'export-container';
            exportContainer.style.position = 'absolute';
            exportContainer.style.left = '-9999px';
            exportContainer.style.top = '0';

            // Clone the grid
            const originalGrid = document.getElementById('photoGrid');
            const clonedGrid = originalGrid.cloneNode(true);
            clonedGrid.className = 'export-grid photo-grid';
            clonedGrid.style.maxWidth = '500px'; // For preview

            // Remove interactive elements from cloned grid
            const removeButtons = clonedGrid.querySelectorAll('.remove-btn');
            removeButtons.forEach(btn => btn.remove());

            // Remove click handlers and style for export
            const cells = clonedGrid.querySelectorAll('.photo-cell');
            cells.forEach(cell => {
                cell.onclick = null;
                cell.style.cursor = 'default';
            });

            // Apply row borders for export
            applyRowBordersToElement(clonedGrid);

            exportContainer.appendChild(clonedGrid);
            document.body.appendChild(exportContainer);

            // Configure html2canvas options for preview
            const options = {
                backgroundColor: '#ffffff',
                scale: 1,
                useCORS: true,
                allowTaint: true,
                logging: false,
                width: clonedGrid.offsetWidth,
                height: clonedGrid.offsetHeight
            };

            // Generate preview
            html2canvas(clonedGrid, options).then(canvas => {
                // Store for final export
                exportCanvas = canvas;

                // Show preview in modal
                const previewContainer = document.getElementById('exportPreview');
                previewContainer.innerHTML = '';
                canvas.style.maxWidth = '100%';
                canvas.style.height = 'auto';
                canvas.style.border = '1px solid #ddd';
                previewContainer.appendChild(canvas);

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('exportModal'));
                modal.show();

                // Clean up
                document.body.removeChild(exportContainer);
            }).catch(error => {
                console.error('Lỗi khi tạo preview:', error);
                alert('Có lỗi xảy ra khi tạo preview!');
                document.body.removeChild(exportContainer);
            });
        }

        function confirmExport() {
            if (!exportCanvas) {
                alert('Có lỗi xảy ra, vui lòng thử lại!');
                return;
            }

            // Create high-quality export
            const exportContainer = document.createElement('div');
            exportContainer.className = 'export-container';
            exportContainer.style.position = 'absolute';
            exportContainer.style.left = '-9999px';
            exportContainer.style.top = '0';

            // Clone the grid for high-quality export
            const originalGrid = document.getElementById('photoGrid');
            const clonedGrid = originalGrid.cloneNode(true);
            clonedGrid.className = 'export-grid photo-grid';

            // Remove interactive elements
            const removeButtons = clonedGrid.querySelectorAll('.remove-btn');
            removeButtons.forEach(btn => btn.remove());

            const cells = clonedGrid.querySelectorAll('.photo-cell');
            cells.forEach(cell => {
                cell.onclick = null;
                cell.style.cursor = 'default';
            });

            exportContainer.appendChild(clonedGrid);

            // Apply row borders for export
            console.log('🔥 Applying borders for final export...');
            applyRowBordersToElement(clonedGrid);
            document.body.appendChild(exportContainer);

            // Debug: log width/height
            console.log('DEBUG clonedGrid.offsetWidth:', clonedGrid.offsetWidth, 'offsetHeight:', clonedGrid.offsetHeight);
            if (clonedGrid.offsetWidth === 0 || clonedGrid.offsetHeight === 0) {
                // Ép width/height giống photoGrid
                clonedGrid.style.width = originalGrid.offsetWidth + 'px';
                clonedGrid.style.height = originalGrid.offsetHeight + 'px';
                console.log('DEBUG forced width/height:', clonedGrid.style.width, clonedGrid.style.height);
            }

            // Wait for images to load
            const clonedImages = clonedGrid.querySelectorAll('img');
            console.log('🔥 Found', clonedImages.length, 'images in cloned grid');
            clonedImages.forEach((img, idx) => {
                console.log(`Image ${idx} src:`, img.src);
            });

            const imagePromises = Array.from(clonedImages).map((img, index) => {
                return new Promise((resolve) => {
                    if (img.complete && img.naturalWidth > 0) {
                        console.log(`✅ Image ${index} already loaded`);
                        resolve();
                    } else {
                        console.log(`⚠️ Waiting for image ${index} to load...`);
                        img.onload = () => {
                            console.log(`✅ Image ${index} loaded`);
                            resolve();
                        };
                        img.onerror = () => {
                            console.error(`❌ Image ${index} failed to load`);
                            resolve(); // Continue anyway
                        };
                    }
                });
            });

            Promise.all(imagePromises).then(() => {
                console.log('✅ All images loaded, starting export...');

                // High-quality export options
                const options = {
                    backgroundColor: '#ffffff',
                    scale: 3, // High resolution for quality
                    useCORS: true,
                    allowTaint: true,
                    logging: false, // Disable logging for production
                    width: clonedGrid.offsetWidth,
                    height: clonedGrid.offsetHeight,
                    dpi: 300, // High DPI for print quality
                    pixelRatio: 3 // High pixel ratio
                };

                // Generate final image
                console.log('🔥 Starting html2canvas for final export...');
                console.log('🔥 Export options:', options);
                console.log('🔥 Cloned grid dimensions:', clonedGrid.offsetWidth, 'x', clonedGrid.offsetHeight);

                html2canvas(clonedGrid, options).then(canvas => {
                    console.log('✅ html2canvas successful - Canvas size:', canvas.width, 'x', canvas
                    .height);

                    // Debug canvas data
                    const dataURL = canvas.toDataURL('image/png', 1.0);
                    console.log('🔍 Canvas dataURL length:', dataURL.length);
                    console.log('🔍 Canvas dataURL starts with:', dataURL.substring(0, 50));

                    if (dataURL.length < 100) {
                        console.error(
                            '❌ Dataurl ngắn - có vẻ chưa có dữ liệu, thử dùng exportCanvas của preview');
                        // Thử dùng exportCanvas (canvas của preview)
                        try {
                            const previewDataURL = exportCanvas.toDataURL('image/png', 1.0);
                            if (previewDataURL.length > 100) {
                                const link = document.createElement('a');
                                link.download = `photo-grid-preview-${Date.now()}.png`;
                                link.href = previewDataURL;
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                                document.body.removeChild(exportContainer);
                                alert('Đã xuất ảnh từ preview canvas!');
                                return;
                            }
                        } catch (e) {
                            console.error('❌ Xuất từ preview canvas cũng lỗi:', e);
                        }
                        alert('Export failed: Canvas data is empty. Check if images loaded properly.');
                        document.body.removeChild(exportContainer);
                        return;
                    }

                    // Create download link
                    const link = document.createElement('a');
                    link.download = `photo-grid-${Date.now()}.png`;
                    link.href = dataURL;

                    // Trigger download
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Clean up
                    document.body.removeChild(exportContainer);

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
                    modal.hide();

                    console.log('✅ Export completed successfully');
                    alert('Đã xuất ảnh thành công!');
                }).catch(error => {
                    console.error('❌ Lỗi khi xuất ảnh:', error);
                    console.error('❌ Error details:', error.message, error.stack);

                    // Try server-side export as fallback
                    console.log('🚀 Trying server-side export as fallback...');
                    document.body.removeChild(exportContainer);
                    exportToServerSide();
                });
            }).catch(error => {
                console.error('❌ Image loading error:', error);
                document.body.removeChild(exportContainer);
                exportToServerSide();
            });
        }


        function exportToServerSide() {
            console.log('🚀 Starting server-side export...');

            if (Object.keys(gridData.images).length === 0) {
                alert('Vui lòng thêm ít nhất một ảnh trước khi xuất!');
                return;
            }

            // Create a clean version of the grid for export
            const originalGrid = document.getElementById('photoGrid');
            const clonedGrid = originalGrid.cloneNode(true);

            // Remove interactive elements
            const removeButtons = clonedGrid.querySelectorAll('.remove-btn');
            removeButtons.forEach(btn => btn.remove());

            // Remove click handlers
            const cells = clonedGrid.querySelectorAll('.photo-cell');
            cells.forEach(cell => {
                cell.onclick = null;
                cell.style.cursor = 'default';
            });

            // Apply borders
            applyRowBordersToElement(clonedGrid);

            // Convert images to inline base64 for server export
            const images = clonedGrid.querySelectorAll('img');
            console.log('🚀 Converting', images.length, 'images to base64...');

            images.forEach((img, index) => {
                if (img.src.startsWith('data:image/')) {
                    console.log(`✅ Image ${index} already base64`);
                } else {
                    console.log(`⚠️ Image ${index} is URL, may cause server timeout`);
                }
            });

            // Get HTML content
            const gridHtml = clonedGrid.outerHTML;

            console.log('🚀 Sending to server for export...');

            // Send to server
            fetch('/export-photo-grid', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        gridHtml: gridHtml,
                        width: originalGrid.offsetWidth,
                        height: originalGrid.offsetHeight
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('✅ Server-side export successful');

                        // Create download link
                        const link = document.createElement('a');
                        link.href = data.download_url;
                        link.download = data.filename;
                        link.target = '_blank';

                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        alert('Export thành công! File đã được tải xuống.');
                    } else {
                        console.error('❌Xuất ảnh thất bại', data.message);
                        alert('Xuất ảnh thất bại: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('❌ Lỗi yêu cầu , vui lòng liên hệ quản trị viên:', error);

                    // Final fallback - screenshot using modern API
                    if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {
                        console.log('🚀 Trying screen capture API as final fallback...');
                        alert('Lỗi yêu cầu , thử lại.');
                    } else {
                        alert('All export methods failed. Error: ' + error.message);
                    }
                });
        }

        // Skin selection functionality
        let allSkins = [];
        let filteredSkins = [];

        function showSkinSelectionModal(cellIndex) {
            currentCellIndex = cellIndex;
            loadAllSkins();
            const modal = new bootstrap.Modal(document.getElementById('skinSelectionModal'));

            // Handle focus management to avoid aria-hidden errors
            const modalElement = document.getElementById('skinSelectionModal');
            modalElement.addEventListener('shown.bs.modal', function() {
                // Remove any focused elements that might cause aria-hidden conflicts
                document.activeElement.blur();
            });

            modal.show();
        }

        function loadAllSkins() {
            document.getElementById('loadingSkins').style.display = 'block';
            document.getElementById('skinGridContainer').style.display = 'none';
            document.getElementById('noSkinsFound').style.display = 'none';

            Promise.all([
                    fetch('/all-skins').then(r => r.json()),
                    fetch('/heroes/list').then(r => r.json())
                ])
                .then(([skinsData, heroesData]) => {
                    if (skinsData.success) {
                        allSkins = skinsData.data;
                        filteredSkins = [...allSkins];

                        // Populate hero filter
                        const heroSelect = document.getElementById('heroFilterSelect');
                        heroSelect.innerHTML = '<option value="">Tất cả heroes</option>';

                        if (heroesData.success) {
                            heroesData.data.forEach(hero => {
                                heroSelect.innerHTML +=
                                    `<option value="${hero.id}">${hero.name}</option>`;
                            });
                        }

                        displaySkins();
                        setupSkinFilters();
                    } else {
                        showNoSkinsFound();
                    }
                })
                .catch(error => {
                    console.error('Error loading skins:', error);
                    showNoSkinsFound();
                })
                .finally(() => {
                    document.getElementById('loadingSkins').style.display = 'none';
                });
        }

        function displaySkins() {
            const container = document.getElementById('skinGridContainer');

            if (filteredSkins.length === 0) {
                showNoSkinsFound();
                return;
            }

            document.getElementById('noSkinsFound').style.display = 'none';
            document.getElementById('skinGridContainer').style.display = 'flex';

            container.innerHTML = filteredSkins.map(skin => `
                <div class="col-md-3 col-lg-2 mb-3">
                    <div class="card h-100" style="cursor: pointer;" onclick="selectSkinFromModal('${skin.id}', '${skin.image_url}', '${skin.name}')">
                        <img src="${skin.image_url}" class="card-img-top" alt="${skin.name}" style="height: 120px; object-fit: cover;">
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1 text-truncate" style="font-size: 0.8rem;" title="${skin.name}">${skin.name}</h6>
                            <small class="text-muted">${skin.hero_name}</small>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function setupSkinFilters() {
            // Search input
            document.getElementById('skinSearchInput').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                filterSkins(searchTerm, document.getElementById('heroFilterSelect').value);
            });

            // Hero filter
            document.getElementById('heroFilterSelect').addEventListener('change', function(e) {
                const heroId = e.target.value;
                const searchTerm = document.getElementById('skinSearchInput').value.toLowerCase();
                filterSkins(searchTerm, heroId);
            });
        }

        function filterSkins(searchTerm, heroId) {
            filteredSkins = allSkins.filter(skin => {
                const matchesSearch = !searchTerm || skin.name.toLowerCase().includes(searchTerm) ||
                    skin.hero_name
                    .toLowerCase().includes(searchTerm);
                const matchesHero = !heroId || skin.hero_id.toString() === heroId;
                return matchesSearch && matchesHero;
            });

            displaySkins();
        }

        function selectSkinFromModal(skinId, imageUrl, skinName) {
            if (currentCellIndex === null) return;

            console.log('Selecting skin for cell:', currentCellIndex, 'Image URL:', imageUrl);

            const cell = document.querySelector(`[data-index="${currentCellIndex}"]`);
            console.log('Found cell:', cell);

            if (cell) {
                cell.innerHTML = `
                    <img src="${imageUrl}" alt="${skinName}">
                    <button class="remove-btn" onclick="event.stopPropagation(); removeImage(${currentCellIndex})">×</button>
                `;
                cell.classList.add('has-image');
                console.log('Added has-image class to cell:', currentCellIndex);

                // Store image data
                gridData.images[currentCellIndex] = {
                    name: skinName,
                    data: imageUrl,
                    skin_id: skinId
                };
                console.log('Stored skin data:', gridData.images[currentCellIndex]);

                // Reapply borders after adding skin
                setTimeout(() => applyRowBorders(), 100);
            } else {
                console.error('Cell not found for index:', currentCellIndex);
            }

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('skinSelectionModal'));
            modal.hide();
            currentCellIndex = null;
        }

        function showNoSkinsFound() {
            document.getElementById('skinGridContainer').style.display = 'none';
            document.getElementById('noSkinsFound').style.display = 'block';
        }

        // Debug function to check grid state
        function debugGrid() {
            console.log('=== GRID DEBUG ===');
            console.log('GridData:', gridData);
            console.log('Current cells in DOM:');
            const cells = document.querySelectorAll('.photo-cell');
            cells.forEach(cell => {
                const index = cell.getAttribute('data-index');
                const hasImage = cell.classList.contains('has-image');
                console.log(`Cell ${index}: hasImage=${hasImage}`);
            });
            console.log('=== END DEBUG ===');
        }

        // Initialize with default grid
        window.onload = function() {
            generateGrid();
        };
    </script>
</body>

</html>

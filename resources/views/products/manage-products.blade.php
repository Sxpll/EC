@extends('layouts.app')

@section('content')
<div class="container-admin manage-products-container">
    <div class="card-admin">
        <div class="card-header">
        <a href="{{ url()->previous() }}" class="btn btn-link text-decoration-none" style="margin-right: 725px";>
            <i class="fas fa-arrow-left" style="font-size: 24px;"></i>
            </a>
            <h1>Manage Products</h1>
            <button id="openModalBtn" class="btn btn-success">Add Product</button>
            <input type="text" id="search" placeholder="Search Products" class="form-control" style="display: inline-block; width: auto; margin-left: 20px;">
        </div>
        <div class="card-body">
            <div id="alert-container"></div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="products-table">
                        @foreach ($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category ? $product->category->name : 'No Category' }}</td>
                                <td>{{ $product->description }}</td>
                                <td>
                                    <button class="btn btn-primary btn-view" data-id="{{ $product->id }}">View</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add Product</h2>
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select name="category_id" id="category_id" class="form-control">
                    <option value="">No Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="images">Upload Images:</label>
                <input type="file" name="images[]" id="images" class="form-control-file" multiple>
            </div>
            <div class="form-group">
                <label for="attachments">Upload Attachments:</label>
                <input type="file" name="attachments[]" id="attachments" class="form-control-file" multiple>
            </div>
            <button type="submit" class="btn btn-success">Add Product</button>
        </form>

    </div>
</div>

<!-- View Product Modal -->
<div id="viewProductModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Product</h2>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-link active" data-tab="Info">Info</button>
            <button class="tab-link" data-tab="Images">Images</button>
            <button class="tab-link" data-tab="Attachments">Attachments</button>
            <button class="tab-link" data-tab="History">History</button>
        </div>

        <!-- Info tab -->
        <div id="Info" class="tab-content active">
            <form id="viewProductForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="viewProductId" name="id">
                <div class="form-group">
                    <label for="viewName">Name:</label>
                    <input type="text" id="viewName" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="viewCategoryId">Category:</label>
                    <select id="viewCategoryId" name="category_id" class="form-control">
                        <option value="">No Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="viewDescription">Description:</label>
                    <textarea id="viewDescription" name="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="form-group d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary mx-2">Update Product</button>
                    <button type="button" id="deleteProductBtn" class="btn btn-danger mx-2">Delete Product</button>
                </div>
            </form>
        </div>

        <!-- Images tab -->
        <div id="Images" class="tab-content">
            <div id="productImages" class="d-flex flex-wrap gallery-container"></div>
            <form id="addImageForm" enctype="multipart/form-data">
                <div class="form-group mt-4">
                    <label for="newImages">Add New Images:</label>
                    <input type="file" name="images[]" id="newImages" class="form-control-file" multiple>
                </div>
                <button type="button" class="btn btn-success mt-2" id="saveNewImagesBtn">Upload Images</button>
            </form>
        </div>

        <!-- Attachments tab -->
        <div id="Attachments" class="tab-content">
            <div id="productAttachments" class="d-flex flex-wrap"></div>
            <form id="addAttachmentForm" enctype="multipart/form-data">
                <div class="form-group mt-4">
                    <label for="newAttachments">Add New Attachments:</label>
                    <input type="file" name="attachments[]" id="newAttachments" class="form-control-file" multiple>
                </div>
                <button type="button" class="btn btn-success mt-2" id="saveNewAttachmentsBtn">Upload Attachments</button>
            </form>
        </div>

        <!-- History tab -->
        <div id="History" class="tab-content">
            <table id="historyTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Admin Name</th>
                        <th>Action</th>
                        <th>Field</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

        </div>
    </div>


</div>


<!-- Modal for image preview -->
<div id="imagePreviewModal">
    <span class="close-custom" id="closePreviewModal">&times;</span>
    <img id="previewImage" src="">
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var addProductModal = document.getElementById("addProductModal");
        var viewProductModal = document.getElementById("viewProductModal");
        var addProductBtn = document.getElementById("openModalBtn");
        var closeBtns = document.getElementsByClassName("close");
        var viewBtns = document.getElementsByClassName("btn-view");
        var deleteProductBtn = document.getElementById("deleteProductBtn");

        // Tab Switching
        const tabs = document.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const target = document.querySelector(`#${this.dataset.tab}`);

                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));

                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                target.classList.add('active');
            });
        });

        // Open modal to add product
        addProductBtn.onclick = function() {
            addProductModal.style.display = "block";
            console.log("Add Product Modal Opened");
        };

        // Close modals
        Array.from(closeBtns).forEach(function(btn) {
            btn.onclick = function() {
                addProductModal.style.display = "none";
                viewProductModal.style.display = "none";
            }
        });

        // Close modal if clicked outside
        window.onclick = function(event) {
            if (event.target == addProductModal) {
                addProductModal.style.display = "none";
            }
            if (event.target == viewProductModal) {
                viewProductModal.style.display = "none";
            }
        };

        // View product and history
        Array.from(viewBtns).forEach(function(btn) {
            btn.onclick = function() {
                var productId = this.getAttribute("data-id");
                console.log("Fetching product with ID:", productId);

                fetch(`/products/${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log("Product data received:", data);
                        document.getElementById("viewProductId").value = data.product.id;
                        document.getElementById("viewName").value = data.product.name;
                        document.getElementById("viewCategoryId").value = data.product.category_id;
                        document.getElementById("viewDescription").value = data.product.description;

                        fetchProductImages(productId);
                        fetchProductAttachments(productId);

                        // Fetch product history
                        fetch(`/products/${productId}/history`)
                            .then(response => response.json())
                            .then(histories => {
                                console.log("Product history received:", histories);
                                const historyTableBody = document.querySelector('#historyTable tbody');
                                historyTableBody.innerHTML = '';
                                histories.forEach(history => {
                                    const row = document.createElement('tr');
                                    row.innerHTML = `
                                        <td>${history.admin_name}</td>
                                        <td>${history.action}</td>
                                        <td>${history.field}</td>
                                        <td>${history.old_value ? history.old_value : 'N/A'}</td>
                                        <td>${history.new_value ? history.new_value : 'N/A'}</td>
                                        <td>${new Date(history.created_at).toLocaleString()}</td>
                                    `;
                                    historyTableBody.appendChild(row);
                                });
                            });

                        var viewProductForm = document.getElementById("viewProductForm");
                        viewProductForm.onsubmit = function(event) {
                            event.preventDefault();

                            var updatedProduct = {
                                name: document.getElementById("viewName").value,
                                category_id: document.getElementById("viewCategoryId").value || null,
                                description: document.getElementById("viewDescription").value,
                                _method: 'PUT',
                                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            };

                            console.log("Updating product with ID:", productId, updatedProduct);

                            axios.post(`/products/${productId}`, updatedProduct)
                                .then(response => {
                                    console.log("Product update response:", response.data);
                                    if (response.data.success) {
                                        sessionStorage.setItem('message', 'Product updated successfully');
                                        sessionStorage.setItem('messageType', 'success');
                                        location.reload();
                                    } else {
                                        alert('Error updating product');
                                    }
                                })
                                .catch(error => {
                                    console.error("Error updating product:", error.response ? error.response.data : error);
                                    alert('Error updating product');
                                });
                        };

                        deleteProductBtn.onclick = function() {
                            if (confirm('Are you sure you want to delete this product?')) {
                                console.log("Deleting product with ID:", productId);

                                axios.delete(`/products/${productId}`, {
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                }).then(response => {
                                    console.log("Product delete response:", response.data);
                                    if (response.data.success) {
                                        sessionStorage.setItem('message', 'Product deleted successfully');
                                        sessionStorage.setItem('messageType', 'success');
                                        location.reload();
                                    } else {
                                        alert('Error deleting product');
                                    }
                                }).catch(error => {
                                    console.error("Error deleting product:", error.response ? error.response.data : error);
                                    alert('Error deleting product');
                                });
                            }
                        };

                        viewProductModal.style.display = "block";
                    })
                    .catch(error => {
                        console.error("Error fetching product:", error);
                    });
            }
        });

        function fetchProductImages(productId) {
            console.log("Fetching images for product ID:", productId);
            axios.get(`/products/${productId}/images`)
                .then(response => {
                    console.log("Images data received:", response.data);
                    const imageContainer = document.getElementById('productImages');
                    imageContainer.innerHTML = '';
                    response.data.forEach(image => {
                        const imgElement = document.createElement('img');
                        imgElement.src = `data:${image.mime_type};base64,${image.file_data}`;
                        imgElement.classList.add('gallery-image');

                        const removeBtn = document.createElement('button');
                        removeBtn.textContent = 'Remove';
                        removeBtn.classList.add('btn', 'btn-danger', 'mt-2');
                        removeBtn.onclick = function() {
                            console.log("Removing image with ID:", image.id);
                            axios.delete(`/products/${productId}/images/${image.id}`)
                                .then(() => {
                                    fetchProductImages(productId);
                                })
                                .catch(error => {
                                    console.error('Error removing image:', error);
                                });
                        };

                        const imageWrapper = document.createElement('div');
                        imageWrapper.classList.add('gallery-item');
                        imageWrapper.appendChild(imgElement);
                        imageWrapper.appendChild(removeBtn);

                        imageContainer.appendChild(imageWrapper);
                    });
                })
                .catch(error => {
                    console.error('Error fetching images:', error);
                });
        }

        function fetchProductAttachments(productId) {
            console.log("Fetching attachments for product ID:", productId);
            axios.get(`/products/${productId}/attachments`)
                .then(response => {
                    console.log("Attachments data received:", response.data);
                    const attachmentContainer = document.getElementById('productAttachments');
                    attachmentContainer.innerHTML = '';

                    response.data.forEach(attachment => {
                        const linkElement = document.createElement('a');
                        linkElement.href = `data:${attachment.mime_type};base64,${attachment.file}`;
                        linkElement.textContent = attachment.file_name;
                        linkElement.download = attachment.file_name;
                        linkElement.classList.add('btn', 'btn-outline-info', 'mr-2', 'mt-2');

                        const removeBtn = document.createElement('button');
                        removeBtn.textContent = 'Remove';
                        removeBtn.classList.add('btn', 'btn-danger', 'mt-2');

                        removeBtn.onclick = function () {
                            console.log("Removing attachment with ID:", attachment.id);
                            if (confirm('Are you sure you want to delete this attachment?')) {
                                axios.delete(`/products/${productId}/attachments/${attachment.id}`)
                                    .then(response => {
                                        if (response.data.success) {
                                            fetchProductAttachments(productId);
                                        } else {
                                            alert('Error deleting attachment');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error deleting attachment:', error);
                                        alert('Error deleting attachment');
                                    });
                            }
                        };

                        const attachmentWrapper = document.createElement('div');
                        attachmentWrapper.classList.add('d-flex', 'flex-column', 'align-items-center', 'mr-2', 'mt-2');
                        attachmentWrapper.appendChild(linkElement);
                        attachmentWrapper.appendChild(removeBtn);

                        attachmentContainer.appendChild(attachmentWrapper);
                    });
                })
                .catch(error => {
                    console.error('Error fetching attachments:', error);
                });
        }

        document.getElementById('saveNewImagesBtn').onclick = function () {
            const productId = document.getElementById('viewProductId').value;
            const formData = new FormData();
            const images = document.getElementById('newImages').files;

            for (let i = 0; i < images.length; i++) {
                formData.append('images[]', images[i]);
            }

            console.log("Uploading images for product ID:", productId);

            axios.post(`/products/${productId}/images`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                if (response.data.success) {
                    fetchProductImages(productId);
                } else {
                    alert('Error uploading images');
                }
            }).catch(error => {
                console.error('Error uploading images:', error);
            });
        };

        document.getElementById('saveNewAttachmentsBtn').onclick = function () {
            const productId = document.getElementById('viewProductId').value;
            const formData = new FormData();
            const attachments = document.getElementById('newAttachments').files;

            for (let i = 0; i < attachments.length; i++) {
                formData.append('attachments[]', attachments[i]);
            }

            console.log("Uploading attachments for product ID:", productId);

            axios.post(`/products/${productId}/attachments`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                if (response.data.success) {
                    fetchProductAttachments(productId);
                } else {
                    alert('Error uploading attachments');
                }
            }).catch(error => {
                console.error('Error uploading attachments:', error);
            });
        };
    });
</script>
@endsection

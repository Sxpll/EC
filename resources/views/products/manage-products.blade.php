@extends('layouts.app')

@section('content')
<div class="container-admin manage-products-container">
    <div class="card-admin">
        <div class="card-header">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-link text-decoration-none" style="margin-right: 725px;">
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
                        <tr class="{{ !$product->isActive ? 'table-danger' : '' }}">
                            <td>{{ $product->name }}</td>
                            <td>
                                @foreach ($product->categories as $category)
                                {{ $category->name }}@if (!$loop->last), @endif
                                @endforeach
                            </td>
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
                <label for="categories">Categories:</label>
                <div id="category-tree"></div> <!-- Kontener dla jsTree -->
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
                    <label for="categories">Categories:</label>
                    <div id="category-tree-view"></div> <!-- Kontener dla jsTree w trybie edycji -->
                </div>
                <div class="form-group">
                    <label for="viewDescription">Description:</label>
                    <textarea id="viewDescription" name="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="form-group d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary mx-2">Update Product</button>
                    <button type="button" id="deleteProductBtn" class="btn btn-danger mx-2">Delete Product</button>
                    <button type="button" id="activateProductBtn" class="btn btn-success mx-2">Activate Product</button>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var addProductModal = document.getElementById("addProductModal");
        var viewProductModal = document.getElementById("viewProductModal");
        var addProductBtn = document.getElementById("openModalBtn");
        var closeBtns = document.getElementsByClassName("close");
        var viewBtns = document.getElementsByClassName("btn-view");
        var deleteProductBtn = document.getElementById("deleteProductBtn");

        // Initialize jsTree for adding product
        $('#category-tree').jstree({
            'core': {
                'data': {
                    "url": "{{ route('categories.getTree') }}",
                    "dataType": "json"
                },
                "check_callback": true,
                "themes": {
                    "variant": "large"
                }
            },
            "plugins": ["checkbox", "wholerow"]
        });

        // Initialize jsTree for viewing product
        $('#category-tree-view').jstree({
            'core': {
                'data': {
                    "url": "{{ route('categories.getTree') }}",
                    "dataType": "json"
                },
                "check_callback": true,
                "themes": {
                    "variant": "large"
                }
            },
            "plugins": ["checkbox", "wholerow"]
        });

        // Open Add Product Modal
        addProductBtn.onclick = function() {
            addProductModal.style.display = "block";
        };

        // Close modals
        for (var i = 0; i < closeBtns.length; i++) {
            closeBtns[i].onclick = function() {
                addProductModal.style.display = "none";
                viewProductModal.style.display = "none";
            };
        }

        // View Product Button Click
        for (var i = 0; i < viewBtns.length; i++) {
            viewBtns[i].onclick = function() {
                var productId = $(this).data('id');
                // Fetch product details and populate modal fields
                axios.get(`/products/${productId}`)
                    .then(function(response) {
                        console.log(response.data); // Debugging line
                        var product = response.data;
                        $('#viewProductId').val(product.id);
                        $('#viewName').val(product.name);
                        $('#viewDescription').val(product.description);
                        // Populate categories
                        $('#category-tree-view').jstree(true).deselect_all(true);
                        product.categories.forEach(function(categoryId) {
                            $('#category-tree-view').jstree(true).select_node(categoryId);
                        });
                        viewProductModal.style.display = "block";
                    })
                    .catch(function(error) {
                        console.error('Error fetching product details:', error);
                    });
            };
        }

        // Delete Product
        deleteProductBtn.onclick = function() {
            var productId = $('#viewProductId').val();
            axios.delete(`/products/${productId}`, {
                    data: {
                        _token: '{{ csrf_token() }}'
                    }
                })
                .then(function(response) {
                    alert('Product deleted successfully');
                    viewProductModal.style.display = "none";
                    location.reload();
                })
                .catch(function(error) {
                    console.error('Error deleting product:', error);
                });
        };

        // Activate Product
        document.getElementById("activateProductBtn").onclick = function() {
            var productId = $('#viewProductId').val();
            axios.post(`/products/${productId}/activate`, {
                    _token: '{{ csrf_token() }}'
                })
                .then(function(response) {
                    alert('Product activated successfully');
                    viewProductModal.style.display = "none";
                    location.reload();
                })
                .catch(function(error) {
                    console.error('Error activating product:', error);
                });
        };

        // Modal image preview
        $('#productImages').on('click', '.gallery-item img', function() {
            var src = $(this).attr('src');
            $('#previewImage').attr('src', src);
            $('#imagePreviewModal').show();
        });

        $('#closePreviewModal').click(function() {
            $('#imagePreviewModal').hide();
        });
    });
</script>
@endsection

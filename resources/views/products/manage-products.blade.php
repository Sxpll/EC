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
                        <tr class="{{ $product->isActive == 0 ? 'text-danger' : '' }}">
                            <td>{{ $product->name }}</td>
                            <td>
                                @foreach ($product->categories as $category)
                                @if ($category->isActive == 0)
                                <span style="color: gray;">{{ $category->name }}</span>
                                @else
                                {{ $category->name }}
                                @endif
                                @if (!$loop->last), @endif
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
                <div id="category-tree"></div>
                <input type="hidden" name="categories[]" id="selectedCategories">
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
            <button class="tab-link" data-tab="ArchivedCategories">Archived Categories</button>
        </div>

        <!-- Info tab -->
        <div id="Info" class="tab-content active">
            <form id="viewProductForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" id="viewProductId" name="id">
                <div class="form-group">
                    <label for="viewName">Name:</label>
                    <input type="text" id="viewName" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="categories">Categories:</label>
                    <div id="category-tree-view"></div>
                    <input type="hidden" name="categories[]" id="selectedCategoriesView">
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
                <tbody id="historyTableBody">
                    <!-- Dane historii zostaną wstawione tutaj dynamicznie przez JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Archived Categories tab -->
        <div id="ArchivedCategories" class="tab-content">
            <div id="archivedCategoriesContainer">
                <h4>Archived Categories</h4>
                <ul id="archivedCategoriesList"></ul>
            </div>
        </div>

        <!-- Modal for image preview -->
        <div id="imagePreviewModal">
            <span class="close-custom" id="closePreviewModal">&times;</span>
            <img id="previewImage" src="">
        </div>

        @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>

        <style>
            .assigned-category {
                color: gray;
                font-weight: bold;
            }
        </style>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var addProductModal = document.getElementById("addProductModal");
                var viewProductModal = document.getElementById("viewProductModal");
                var addProductBtn = document.getElementById("openModalBtn");
                var closeBtns = document.getElementsByClassName("close");
                var viewBtns = document.getElementsByClassName("btn-view");
                var deleteProductBtn = document.getElementById("deleteProductBtn");
                var activateProductBtn = document.getElementById("activateProductBtn");

                // Inicjalizacja drzewa kategorii do wyboru
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
                    "plugins": ["checkbox", "wholerow"],
                    "checkbox": {
                        "three_state": false,
                        "cascade": ""
                    }
                });

                $('#category-tree-view').jstree({
                    'core': {
                        'data': {
                            "url": "{{ route('categories.getTree') }}",
                            "dataType": "json",
                            "data": function(node) {
                                return {
                                    "assignedCategories": $('#selectedCategoriesView').val()
                                };
                            }
                        },
                        "check_callback": true,
                        "themes": {
                            "variant": "large"
                        }
                    },
                    "plugins": ["checkbox", "wholerow"],
                    "checkbox": {
                        "three_state": false,
                        "cascade": ""
                    }
                });

                $('#category-tree').on("changed.jstree", function(e, data) {
                    var selectedCategories = data.selected;
                    $('#selectedCategories').val(selectedCategories.join(','));
                });

                $('#category-tree-view').on("changed.jstree", function(e, data) {
                    var selectedCategoriesView = data.selected;
                    $('#selectedCategoriesView').val(selectedCategoriesView.join(','));
                });

                addProductBtn.onclick = function() {
                    addProductModal.style.display = "block";
                };

                for (var i = 0; i < closeBtns.length; i++) {
                    closeBtns[i].onclick = function() {
                        addProductModal.style.display = "none";
                        viewProductModal.style.display = "none";
                    };
                }

                activateProductBtn.onclick = function() {
                    var productId = $('#viewProductId').val();
                    axios.post(`/products/${productId}/activate`, {
                            _token: '{{ csrf_token() }}'
                        })
                        .then(function(response) {
                            alert('Product activated successfully');
                            location.reload();
                        })
                        .catch(function(error) {
                            console.error('Error activating product:', error);
                            alert('Failed to activate product');
                        });
                };

                function loadArchivedCategories() {
                    var productId = $('#viewProductId').val();
                    axios.get(`/products/${productId}/archived-categories`)
                        .then(function(response) {
                            var archivedCategories = response.data.archivedCategories || [];
                            var container = $('#archivedCategoriesList');
                            container.empty();

                            archivedCategories.forEach(function(category) {
                                var item = `<li>${category.path}</li>`;
                                container.append(item);
                            });
                        })
                        .catch(function(error) {
                            console.error('Error fetching archived categories:', error);
                        });
                }

                for (var i = 0; i < viewBtns.length; i++) {
                    viewBtns[i].onclick = function() {
                        var productId = $(this).data('id');
                        axios.get(`/products/${productId}`)
                            .then(function(response) {
                                var product = response.data.product;
                                $('#viewProductId').val(product.id);
                                $('#viewName').val(product.name);
                                $('#viewDescription').val(product.description);
                                $('#category-tree-view').jstree(true).deselect_all(true);

                                if (product.categories) {
                                    product.categories.forEach(function(category) {
                                        $('#category-tree-view').jstree(true).select_node(category.id);
                                    });
                                }

                                viewProductModal.style.display = "block";

                                var histories = response.data.histories || [];
                                var historyTableBody = $('#historyTableBody');
                                historyTableBody.empty();

                                histories.sort(function(a, b) {
                                    return new Date(b.created_at) - new Date(a.created_at);
                                });

                                histories.forEach(function(history) {
                                    var row = `<tr>
                                        <td>${history.admin_name}</td>
                                        <td>${history.action}</td>
                                        <td>${history.field}</td>
                                        <td>${history.old_value}</td>
                                        <td>${history.new_value}</td>
                                        <td>${new Date(history.created_at).toLocaleString()}</td>
                                    </tr>`;
                                    historyTableBody.append(row);
                                });

                                var productImages = response.data.product.images || [];
                                var imagesContainer = $('#productImages');
                                imagesContainer.empty();
                                productImages.forEach(function(image) {
                                    var imgElement = `<div class="gallery-item">
                                        <img src="data:${image.mime_type};base64,${image.file_data}" class="img-thumbnail" />
                                        <button class="btn btn-danger btn-sm delete-image" data-id="${image.id}">Delete</button>
                                    </div>`;
                                    imagesContainer.append(imgElement);
                                });

                                var productAttachments = response.data.product.attachments || [];
                                var attachmentsContainer = $('#productAttachments');
                                attachmentsContainer.empty();
                                productAttachments.forEach(function(attachment) {
                                    var attachmentElement = `<div class="attachment-item">
                                        <a href="data:${attachment.mime_type};base64,${attachment.file_data}" download="${attachment.file_name}">${attachment.file_name}</a>
                                        <button class="btn btn-danger btn-sm delete-attachment" data-id="${attachment.id}">Delete</button>
                                    </div>`;
                                    attachmentsContainer.append(attachmentElement);
                                });

                                loadArchivedCategories();
                            })
                            .catch(function(error) {
                                console.error('Error fetching product details:', error);
                            });
                    };
                }

                $('.tab-link').click(function() {
                    var tab = $(this).data('tab');
                    $('.tab-link').removeClass('active');
                    $(this).addClass('active');
                    $('.tab-content').removeClass('active');
                    $('#' + tab).addClass('active');

                    if (tab === 'ArchivedCategories') {
                        loadArchivedCategories();
                    }
                });

                $('#saveNewImagesBtn').click(function() {
                    var formData = new FormData($('#addImageForm')[0]);
                    var productId = $('#viewProductId').val();
                    axios.post(`/products/${productId}/images`, formData)
                        .then(function(response) {
                            alert('Images uploaded successfully');
                            location.reload();
                        })
                        .catch(function(error) {
                            if (error.response && error.response.status === 422) {
                                const errors = error.response.data.errors;
                                let errorMessages = [];
                                for (const field in errors) {
                                    if (errors.hasOwnProperty(field)) {
                                        errorMessages.push(errors[field].join(' '));
                                    }
                                }
                                alert('Validation errors: \n' + errorMessages.join('\n'));
                            } else {
                                console.error('Error uploading images:', error.message);
                                alert('Error uploading images. Please try again.');
                            }
                        });
                });

                $('#saveNewAttachmentsBtn').click(function() {
                    var formData = new FormData($('#addAttachmentForm')[0]);
                    var productId = $('#viewProductId').val();
                    axios.post(`/products/${productId}/attachments`, formData)
                        .then(function(response) {
                            alert('Attachments uploaded successfully');
                            location.reload();
                        })
                        .catch(function(error) {
                            if (error.response && error.response.status === 422) {
                                const errors = error.response.data.errors;
                                let errorMessages = [];
                                for (const field in errors) {
                                    if (errors.hasOwnProperty(field)) {
                                        errorMessages.push(errors[field].join(' '));
                                    }
                                }
                                alert('Validation errors: \n' + errorMessages.join('\n'));
                            } else {
                                console.error('Error uploading attachments:', error.message);
                                alert('Error uploading attachments. Please try again.');
                            }
                        });
                });

                $('#deleteProductBtn').click(function() {
                    var productId = $('#viewProductId').val();
                    axios.delete(`/products/${productId}`, {
                            data: {
                                _token: '{{ csrf_token() }}'
                            }
                        })
                        .then(function(response) {
                            alert('Product deleted successfully');
                            location.reload();
                        })
                        .catch(function(error) {
                            console.error('Error deleting product:', error);
                            alert('Failed to delete product');
                        });
                });

                $('#productImages').on('click', '.delete-image', function() {
                    var imageId = $(this).data('id');
                    var productId = $('#viewProductId').val();
                    axios.delete(`/products/${productId}/images/${imageId}`, {
                            data: {
                                _token: '{{ csrf_token() }}'
                            }
                        })
                        .then(function(response) {
                            alert('Image deleted successfully');
                            location.reload();
                        })
                        .catch(function(error) {
                            console.error('Error deleting image:', error);
                        });
                });

                $('#productAttachments').on('click', '.delete-attachment', function() {
                    var attachmentId = $(this).data('id');
                    var productId = $('#viewProductId').val();
                    axios.delete(`/products/${productId}/attachments/${attachmentId}`, {
                            data: {
                                _token: '{{ csrf_token() }}'
                            }
                        })
                        .then(function(response) {
                            alert('Attachment deleted successfully');
                            location.reload();
                        })
                        .catch(function(error) {
                            console.error('Error deleting attachment:', error);
                        });
                });

                $('#viewProductForm').submit(function(event) {
                    event.preventDefault();
                    var productId = $('#viewProductId').val();
                    var formData = new FormData(this);
                    axios.post(`/products/${productId}`, formData)
                        .then(function(response) {
                            alert('Product updated successfully');
                            location.reload();
                        })
                        .catch(function(error) {
                            if (error.response && error.response.status === 422) {
                                const errors = error.response.data.errors;
                                let errorMessages = [];
                                for (const field in errors) {
                                    if (errors.hasOwnProperty(field)) {
                                        errorMessages.push(errors[field].join(' '));
                                    }
                                }
                                alert('Validation errors: \n' + errorMessages.join('\n'));
                            } else {
                                console.error('Error updating product:', error.message);
                                alert('Error updating product. Please try again.');
                            }
                        });
                });
            });
        </script>
        @endsection

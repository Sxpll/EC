@extends('layouts.app')

@section('content')

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="sidebar" class="sidebar">
    <button id="close-sidebar" class="close-sidebar">&times;</button>
    <nav class=" sidebar-nav">
        <a href="{{ url('/home') }}">Home</a>
        <a href="{{ route('products.publicIndex') }}">Products</a>
        @if(auth()->check() && auth()->user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
        @endif
        <a href="{{ route('chat.index') }}">Chat</a>
        @if(auth()->check())
        <a href="{{ route('account.edit') }}">My Account</a>
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
        @endif
    </nav>
</div>

<main class="content-wrapper">
    <div class="container-admin manage-products-container">
        <div class="card-admin">
            <div class="card-header">
                <a href="{{ route('admin.dashboard') }}" class="back-arrow" style="margin-right:auto;">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <h1>Manage Products</h1>
                <button id="openModalBtn" class="btn btn-success">Add Product</button>
                <input type="text" id="search" placeholder="Search Products" class="form-control" style="display: inline-block; width: auto; margin-left: 20px;">
            </div>

            <div class="card-body">
                <div id="alert-container"></div>
                <div class="table-responsive scrollable-table" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Price (PLN)</th>
                                <th>Availability</th>
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
                                <td>{{ $product->price }}</td>
                                <td>
                                    @switch($product->availability)
                                    @case('available')
                                    In Stock
                                    @break
                                    @case('available_in_7_days')
                                    Available within 7 days
                                    @break
                                    @case('available_in_14_days')
                                    Available within 14 days
                                    @break
                                    @default
                                    Unavailable
                                    @endswitch
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-view" data-id="{{ $product->id }}">Edit</button>
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
                    <label for="price">Price (PLN):</label>
                    <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="availability">Availability:</label>
                    <select name="availability" id="availability" class="form-control">
                        <option value="available">Available</option>
                        <option value="available_in_7_days">Available within 7 days</option>
                        <option value="available_in_14_days">Available within 14 days</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="images">Upload Images:</label>
                    <input type="file" name="images[]" id="images" class="form-control-file" multiple>
                </div>
                <button type="submit" class="btn btn-success">Add Product</button>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
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

            <!-- Info Tab -->
            <div id="Info" class="tab-content active">
                <form id="viewProductForm" method="POST" enctype="multipart/form-data">
                    @csrf
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
                    <div class="form-group">
                        <label for="viewPrice">Price (PLN):</label>
                        <input type="number" step="0.01" id="viewPrice" name="price" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="viewAvailability">Availability:</label>
                        <select name="availability" id="viewAvailability" class="form-control">
                            <option value="available">Available</option>
                            <option value="available_in_7_days">Available within 7 days</option>
                            <option value="available_in_14_days">Available within 14 days</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="form-group d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary mx-2">Update Product</button>
                        <button type="button" id="deleteProductBtn" class="btn btn-danger mx-2">Deactivate Product</button>
                        <button type="button" id="activateProductBtn" class="btn btn-success mx-2">Activate Product</button>
                    </div>
                </form>
            </div>

            <!-- Images Tab -->
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

            <!-- Attachments Tab -->
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

            <!-- History Tab -->
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
                        <!-- History data will be inserted here dynamically by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Archived Categories Tab -->
            <div id="ArchivedCategories" class="tab-content">
                <div id="archivedCategoriesContainer">
                    <h4>Archived Categories</h4>
                    <ul id="archivedCategoriesList"></ul>
                </div>
            </div>

        </div>
    </div>
</main>

<footer class="footer-bar">
    <div class="footer-bar-content">
        <a href="#">About Us</a>
        <a href="#">Privacy</a>
        <a href="#">FAQ</a>
        <a href="#">Careers</a>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
<link href="{{ asset('css/style.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

<!-- Define categoriesGetTreeUrl before including product.js -->
<script>
    var categoriesGetTreeUrl = "{{ route('categories.getTree') }}";
</script>
<!-- Your JavaScript file -->
<script src="{{ asset('js/product.js') }}"></script>

@endsection

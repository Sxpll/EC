@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add New Category</h1>
    <form id="categoryForm" action="{{ route('categories.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="parent_id">Parent Category</label>
            <select name="parent_id" id="parent_id" class="form-control">
                <option value="">No Parent</option>
                @foreach($categories as $category)
                @include('categories.category-option', ['category' => $category, 'level' => 0])
                @endforeach
            </select>
        </div>

        <button type="button" class="btn btn-success" onclick="handleFormSubmit()">Save and Check Parent Products</button>
        <div class="row mt-3">
            <div class="col text-center">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-back">Back</a>
            </div>
        </div>
    </form>
</div>

<div id="moveProductsModal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h5>Move Products to New Subcategory</h5>
        <form id="moveProductsForm">
            @csrf
            <input type="hidden" name="parent_category_id" id="parent_category_id" value="">
            <input type="hidden" name="new_category_id" id="new_category_id" value="">

            <div id="product-list" style="margin-top: 15px; margin-bottom: 15px;">
            </div>

            <button type="button" class="btn btn-primary" onclick="submitMoveProducts()">Move Products</button>
        </form>
    </div>
</div>

<style>
    .custom-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
    }

    .custom-modal-content {
        background-color: #333;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        position: relative;
        text-align: center;
        color: #fefefe;
    }

    .close {
        color: #fefefe;
        position: absolute;
        right: 10px;
        top: 5px;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: #ddd;
        text-decoration: none;
    }

    #product-list {
        max-height: 200px;
        overflow-y: auto;
        text-align: left;
        margin-top: 10px;
    }

    #product-list div {
        padding: 5px;
        color: #fefefe;
    }

    #moveProductsModal h5 {
        margin-bottom: 20px;
        color: #fefefe;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log("Document loaded, ensuring modal does not auto open.");
    });

    function handleFormSubmit() {
        console.log("handleFormSubmit called");

        const parentCategoryId = document.getElementById('parent_id').value;
        console.log("Selected parent category ID:", parentCategoryId);

        if (!parentCategoryId) {
            console.log("No parent category selected, submitting form");
            submitFormWithAjax();
            return;
        }

        axios.get('/categories/' + parentCategoryId + '/products')
            .then(response => {
                console.log("Products response:", response.data);

                if (response.data.products.length > 0) {
                    console.log("Parent category has products");
                    if (confirm('This category has products. Do you want to move them to the new subcategory?')) {
                        submitFormWithAjax(true);
                    } else {
                        console.log("User chose not to move products, submitting form");
                        submitFormWithAjax(false);
                    }
                } else {
                    console.log("Parent category has no products, submitting form");
                    submitFormWithAjax(false);
                }
            })
            .catch(error => {
                console.error('Error checking parent products:', error);
                alert('Error checking parent products.');
            });
    }

    function submitFormWithAjax(moveProducts = false) {
        const formData = new FormData(document.getElementById('categoryForm'));
        fetch('{{ route("categories.store") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (response.headers.get('Content-Type').includes('application/json')) {
                    return response.json();
                } else {
                    console.error('Expected JSON but got:', response.headers.get('Content-Type'));
                    return response.text();
                }
            })
            .then(data => {
                console.log('Category created response:', data);
                if (typeof data === 'string' && data.includes('<!DOCTYPE html>')) {
                    alert('An unexpected error occurred. Please check the server logs.');
                } else if (data.success) {
                    document.getElementById('new_category_id').value = data.category_id;

                    // Automatyczne otwarcie modalu po utworzeniu kategorii
                    if (moveProducts) {
                        openMoveProductsModal(document.getElementById('parent_id').value, data.category_id);
                    } else {
                        window.location.href = "{{ route('categories.index') }}";
                    }
                } else {
                    alert(data.error || 'Error creating category.');
                }
            })
            .catch(error => {
                console.error('Error creating category:', error);
                alert('Error creating category.');
            });
    }

    function openMoveProductsModal(parentCategoryId, newCategoryId) {
        console.log("Opening modal for parent category:", parentCategoryId);

        document.getElementById('parent_category_id').value = parentCategoryId;
        document.getElementById('new_category_id').value = newCategoryId || '';

        axios.get('/categories/' + parentCategoryId + '/products')
            .then(response => {
                console.log("Fetched products for modal:", response.data);

                var productList = document.getElementById('product-list');
                productList.innerHTML = '';

                response.data.products.forEach(function(product) {
                    var productItem = document.createElement('div');
                    productItem.innerHTML = '<input type="checkbox" name="product_ids[]" value="' + product.id + '"> ' + product.name;
                    productList.appendChild(productItem);
                });

                document.getElementById('moveProductsModal').style.display = 'flex';
            })
            .catch(error => {
                console.error('Error fetching products:', error);
            });
    }

    function closeModal() {
        console.log("Closing modal");
        document.getElementById('moveProductsModal').style.display = 'none';
    }

    function submitMoveProducts() {
        console.log("Submitting move products form");

        var formData = new FormData(document.getElementById('moveProductsForm'));

        axios.post('/categories/move-products', formData)
            .then(response => {
                console.log('Move products response:', response.data);

                if (response.data.success) {
                    closeModal();
                    alert(response.data.success);
                    window.location.href = "{{ route('categories.index') }}";
                } else {
                    alert(response.data.error || 'Error moving products.');
                }
            })
            .catch(error => {
                console.error('Error moving products:', error);
                alert('Error moving products.');
            });
    }
</script>
@endsection

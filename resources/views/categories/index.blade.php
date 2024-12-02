@extends('layouts.app')

@section('content')
<div class="container">
    <a href="{{ route('admin.dashboard') }}" class="back-arrow" style="margin-right: 725px;">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1>Manage Categories</h1>


    <a href="{{ route('categories.create') }}" class="btn btn-success mb-3">Add New Category</a>


    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- Przewijalne drzewo kategorii -->
    <div id="scrollable-category-tree" class="card p-3" style="max-height: 500px; overflow-y: auto;">
        <div id="category-tree"></div>
    </div>

    <!-- Przycisk do zapisywania zmian -->
    <button id="saveHierarchy" class="btn btn-primary mt-3">Save Hierarchy</button>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>

<script>
    $(document).ready(function() {
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
            "plugins": ["checkbox", "dnd", "contextmenu", "wholerow"],
            "checkbox": {
                "three_state": false,
                "cascade": "down"

            },
            "contextmenu": {
                "items": function(node) {
                    return {
                        "Create": {
                            "label": "Create",
                            "action": function(obj) {
                                window.location.href = '{{ route("categories.create") }}';
                            }
                        },
                        "Rename": {
                            "label": "Rename",
                            "action": function(obj) {
                                $('#category-tree').jstree(true).edit(node);
                            }
                        },
                        "Delete": {
                            "label": "Delete",
                            "action": function(obj) {
                                $.ajax({
                                    url: '{{ route("categories.destroy", ":id") }}'.replace(':id', node.id),
                                    method: 'DELETE',
                                    data: {
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        $('#category-tree').jstree('refresh');
                                        alert('Category deleted successfully');
                                    },
                                    error: function(error) {
                                        console.error('Error deleting category:', error);
                                        alert('Failed to delete category');
                                    }
                                });
                            }
                        }
                    };
                }
            }
        });

        $('#category-tree').on('move_node.jstree', function(e, data) {
            var parentCategoryId = data.parent;
            var nodeId = data.node.id;

            $.ajax({
                url: '/categories/' + parentCategoryId + '/products',
                method: 'GET',
                success: function(response) {
                    if (response.products.length > 0) {
                        if (confirm('The parent category has products. Do you want to move them to the new subcategory?')) {
                            openMoveProductsModal(parentCategoryId, nodeId);
                        } else {
                            $('#category-tree').jstree('refresh');
                        }
                    } else {
                        updateCategoryHierarchy(nodeId, parentCategoryId);
                    }
                }
            });
        });

        function updateCategoryHierarchy(nodeId, parentId) {
            $.ajax({
                url: '{{ route("categories.updateHierarchy") }}',
                method: 'POST',
                data: {
                    category_id: nodeId,
                    new_parent_id: parentId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert(response.message || 'Hierarchy updated successfully');
                },
                error: function(error) {
                    console.error('Error updating hierarchy:', error);
                    alert(error.responseJSON.error || 'Failed to update hierarchy');
                }
            });
        }


        $('#category-tree').on('rename_node.jstree', function(e, data) {
            $.ajax({
                url: '{{ route("categories.update", ":id") }}'.replace(':id', data.node.id),
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: data.text
                },
                success: function(response) {
                    alert('Category renamed successfully');
                },
                error: function(error) {
                    console.error('Error renaming category:', error);
                    alert('Failed to rename category');
                    $('#category-tree').jstree('refresh');
                }
            });
        });
    });

    function openMoveProductsModal(parentCategoryId, newCategoryId) {
        $('#parent_category_id').val(parentCategoryId);
        $('#new_category_id').val(newCategoryId);

        $.ajax({
            url: '/categories/' + parentCategoryId + '/products',
            method: 'GET',
            success: function(response) {
                var productList = $('#product-list');
                productList.empty();

                response.products.forEach(function(product) {
                    productList.append('<div><input type="checkbox" name="product_ids[]" value="' + product.id + '"> ' + product.name + '</div>');
                });

                $('#moveProductsModal').modal('show');
            }
        });
    }

    function submitMoveProducts() {
        $.ajax({
            url: '/categories/move-products',
            method: 'POST',
            data: $('#moveProductsForm').serialize(),
            success: function(response) {
                $('#moveProductsModal').modal('hide');
                alert(response.success);
                $('#category-tree').jstree('refresh');
            },
            error: function(response) {
                alert('Error moving products.');
            }
        });
    }
</script>
@endsection

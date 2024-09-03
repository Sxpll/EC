@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Categories</h1>
    <a href="{{ route('categories.create') }}" class="btn btn-success mb-3">Add New Category</a>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- Przewijalne drzewo kategorii -->
    <div id="scrollable-category-tree" class="card p-3" style="max-height: 500px; overflow-y: auto;">
        <div id="category-tree"></div> <!-- Kontener dla jsTree -->
    </div>

    <!-- Przycisk do zapisywania zmian -->
    <button id="saveHierarchy" class="btn btn-primary mt-3">Save Hierarchy</button>
</div>
@endsection

@section('scripts')
<!-- Dodanie jsTree -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>

<script>
    $(document).ready(function() {
        $('#category-tree').jstree({
            'core': {
                'data': {
                    "url": "{{ route('categories.getTree') }}", // Trasa zwracająca JSON drzewa
                    "dataType": "json"
                },
                "check_callback": true, // Umożliwia edytowanie struktury drzewa
                "themes": {
                    "variant": "large"
                }
            },
            "plugins": ["dnd", "contextmenu", "wholerow"], // Wtyczki jsTree
            "contextmenu": {
                "items": function(node) {
                    return {
                        "Create": {
                            "label": "Create",
                            "action": function(obj) {
                                // Obsługa tworzenia kategorii
                                alert('Create new category functionality not yet implemented.');
                            }
                        },
                        "Rename": {
                            "label": "Rename",
                            "action": function(obj) {
                                $('#category-tree').jstree(true).edit(node); // Edytuj węzeł
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

        // Obsługa zmiany nazwy
        $('#category-tree').on('rename_node.jstree', function(e, data) {
            $.ajax({
                url: '{{ route("categories.update", ":id") }}'.replace(':id', data.node.id),
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: data.text // Nowa nazwa węzła
                },
                success: function(response) {
                    alert('Category renamed successfully');
                },
                error: function(error) {
                    console.error('Error renaming category:', error);
                    alert('Failed to rename category');
                    $('#category-tree').jstree('refresh'); // Odśwież drzewo, jeśli wystąpił błąd
                }
            });
        });

        // Obsługa przenoszenia węzłów
        $('#category-tree').on('move_node.jstree', function(e, data) {
            $.ajax({
                url: '{{ route("categories.updateHierarchy") }}',
                method: 'POST',
                data: {
                    hierarchy: $('#category-tree').jstree("get_json"),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert('Hierarchy updated successfully');
                },
                error: function(error) {
                    console.error('Error updating hierarchy:', error);
                    alert('Failed to update hierarchy');
                }
            });
        });
    });
</script>
@endsection

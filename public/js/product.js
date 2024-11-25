document.addEventListener("DOMContentLoaded", function () {
    // Get CSRF token from meta tag
    axios.defaults.headers.common["X-CSRF-TOKEN"] = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    // Handle deactivating product
    document
        .getElementById("deleteProductBtn")
        .addEventListener("click", function () {
            const productId = document.getElementById("viewProductId").value;
            const formData = new FormData();
            formData.append("_method", "DELETE");
            formData.append(
                "_token",
                axios.defaults.headers.common["X-CSRF-TOKEN"]
            );

            axios
                .post(`/admin/products/${productId}`, formData)
                .then((response) => {
                    alert("Product has been deactivated successfully");
                    location.reload();
                })
                .catch((error) => {
                    console.error("Error deactivating product:", error);
                    alert("Failed to deactivate product");
                });
        });

    // Handle updating product
    document
        .getElementById("viewProductForm")
        .addEventListener("submit", function (event) {
            event.preventDefault();
            const productId = document.getElementById("viewProductId").value;
            const formData = new FormData(this);
            formData.append("_method", "PUT");

            axios
                .post(`/admin/products/${productId}`, formData)
                .then((response) => {
                    alert("Product has been updated successfully");
                    location.reload();
                })
                .catch((error) => {
                    console.error("Error updating product:", error);
                    alert("Failed to update product");
                });
        });

    // Initialize category tree in add product modal
    $("#category-tree").jstree({
        core: {
            data: {
                url: categoriesGetTreeUrl,
                dataType: "json",
            },
            check_callback: true,
            themes: {
                variant: "large",
            },
        },
        plugins: ["checkbox", "wholerow"],
        checkbox: {
            three_state: false,
            cascade: "",
        },
    });

    $("#category-tree").on("changed.jstree", function (e, data) {
        var selectedCategories = data.selected;
        $("#selectedCategories").val(selectedCategories.join(","));
    });

    var addProductModal = document.getElementById("addProductModal");
    var viewProductModal = document.getElementById("viewProductModal");
    var addProductBtn = document.getElementById("openModalBtn");
    var closeBtns = document.getElementsByClassName("close");
    var viewBtns = document.getElementsByClassName("btn-view");
    var activateProductBtn = document.getElementById("activateProductBtn");

    addProductBtn.onclick = function () {
        addProductModal.style.display = "block";
    };

    for (var i = 0; i < closeBtns.length; i++) {
        closeBtns[i].onclick = function () {
            addProductModal.style.display = "none";
            viewProductModal.style.display = "none";
        };
    }

    activateProductBtn.onclick = function () {
        var productId = $("#viewProductId").val();
        axios
            .post(`/products/${productId}/activate`, {
                _token: axios.defaults.headers.common["X-CSRF-TOKEN"],
            })
            .then(function (response) {
                alert("Product has been activated successfully");
                location.reload();
            })
            .catch(function (error) {
                console.error("Error activating product:", error);
                alert("Failed to activate product");
            });
    };

    for (var i = 0; i < viewBtns.length; i++) {
        viewBtns[i].onclick = function () {
            var productId = $(this).data("id");
            axios
                .get(`/products/${productId}`)
                .then(function (response) {
                    var product = response.data.product;
                    $("#viewProductId").val(product.id);
                    $("#viewName").val(product.name);
                    $("#viewDescription").val(product.description);
                    $("#viewPrice").val(product.price);
                    $("#viewAvailability").val(product.availability);

                    // Get product category IDs
                    var categoryIds = product.categories.map(function (
                        category
                    ) {
                        return category.id;
                    });

                    // Update hidden field with category IDs
                    $("#selectedCategoriesView").val(categoryIds.join(","));

                    // Destroy existing tree and reinitialize
                    $("#category-tree-view").jstree("destroy");

                    $("#category-tree-view").jstree({
                        core: {
                            data: {
                                url: categoriesGetTreeUrl,
                                dataType: "json",
                            },
                            check_callback: true,
                            themes: {
                                variant: "large",
                            },
                        },
                        plugins: ["checkbox", "wholerow"],
                        checkbox: {
                            three_state: false,
                            cascade: "",
                        },
                    });

                    // After tree is fully loaded, select categories
                    $("#category-tree-view").on(
                        "ready.jstree",
                        function (e, data) {
                            $("#category-tree-view")
                                .jstree(true)
                                .deselect_all(true);
                            categoryIds.forEach(function (id) {
                                $("#category-tree-view")
                                    .jstree(true)
                                    .select_node(id);
                            });
                        }
                    );

                    // Update hidden field when tree selection changes
                    $("#category-tree-view").on(
                        "changed.jstree",
                        function (e, data) {
                            var selectedCategoriesView = data.selected;
                            $("#selectedCategoriesView").val(
                                selectedCategoriesView.join(",")
                            );
                        }
                    );

                    viewProductModal.style.display = "block";

                    // Handle other tabs (Images, Attachments, History, Archived Categories)

                    // Load product images
                    var productImages = response.data.product.images || [];
                    var imagesContainer = $("#productImages");
                    imagesContainer.empty();
                    productImages.forEach(function (image) {
                        var imgElement = `<div class="gallery-item">
                                    <img src="data:${image.mime_type};base64,${image.file_data}" class="img-thumbnail" />
                                    <button class="btn btn-danger btn-sm delete-image" data-id="${image.id}">Delete</button>
                                </div>`;
                        imagesContainer.append(imgElement);
                    });

                    // Load product attachments
                    var productAttachments =
                        response.data.product.attachments || [];
                    var attachmentsContainer = $("#productAttachments");
                    attachmentsContainer.empty();
                    productAttachments.forEach(function (attachment) {
                        var attachmentElement = `<div class="attachment-item">
                                    <a href="data:${attachment.mime_type};base64,${attachment.file_data}" download="${attachment.file_name}">${attachment.file_name}</a>
                                    <button class="btn btn-danger btn-sm delete-attachment" data-id="${attachment.id}">Delete</button>
                                </div>`;
                        attachmentsContainer.append(attachmentElement);
                    });

                    // Load product history
                    var histories = response.data.histories || [];
                    var historyTableBody = $("#historyTableBody");
                    historyTableBody.empty();

                    histories.sort(function (a, b) {
                        return new Date(b.created_at) - new Date(a.created_at);
                    });

                    histories.forEach(function (history) {
                        var row = `<tr>
                                    <td>${history.admin_name}</td>
                                    <td>${history.action}</td>
                                    <td>${history.field}</td>
                                    <td>${history.old_value}</td>
                                    <td>${history.new_value}</td>
                                    <td>${new Date(
                                        history.created_at
                                    ).toLocaleString()}</td>
                                </tr>`;
                        historyTableBody.append(row);
                    });

                    // Load archived categories
                    loadArchivedCategories();
                })
                .catch(function (error) {
                    console.error("Error fetching product details:", error);
                });
        };
    }

    $(".tab-link").click(function () {
        var tab = $(this).data("tab");
        $(".tab-link").removeClass("active");
        $(this).addClass("active");
        $(".tab-content").removeClass("active");
        $("#" + tab).addClass("active");

        if (tab === "ArchivedCategories") {
            loadArchivedCategories();
        }
    });

    $("#saveNewImagesBtn").click(function () {
        var formData = new FormData($("#addImageForm")[0]);
        var productId = $("#viewProductId").val();
        axios
            .post(`/products/${productId}/images`, formData)
            .then(function (response) {
                alert("Images uploaded successfully");
                location.reload();
            })
            .catch(function (error) {
                console.error("Error uploading images:", error);
                alert("Failed to upload images");
            });
    });

    $("#saveNewAttachmentsBtn").click(function () {
        var formData = new FormData($("#addAttachmentForm")[0]);
        var productId = $("#viewProductId").val();
        axios
            .post(`/products/${productId}/attachments`, formData)
            .then(function (response) {
                alert("Attachments uploaded successfully");
                location.reload();
            })
            .catch(function (error) {
                console.error("Error uploading attachments:", error);
                alert("Failed to upload attachments");
            });
    });

    $("#productImages").on("click", ".delete-image", function () {
        var imageId = $(this).data("id");
        var productId = $("#viewProductId").val();
        axios
            .delete(`/products/${productId}/images/${imageId}`, {
                data: {
                    _token: axios.defaults.headers.common["X-CSRF-TOKEN"],
                },
            })
            .then(function (response) {
                alert("Image has been deleted successfully");
                location.reload();
            })
            .catch(function (error) {
                console.error("Error deleting image:", error);
            });
    });

    $("#productAttachments").on("click", ".delete-attachment", function () {
        var attachmentId = $(this).data("id");
        var productId = $("#viewProductId").val();
        axios
            .delete(`/products/${productId}/attachments/${attachmentId}`, {
                data: {
                    _token: axios.defaults.headers.common["X-CSRF-TOKEN"],
                },
            })
            .then(function (response) {
                alert("Attachment has been deleted successfully");
                location.reload();
            })
            .catch(function (error) {
                console.error("Error deleting attachment:", error);
            });
    });

    function loadArchivedCategories() {
        var productId = $("#viewProductId").val();
        axios
            .get(`/products/${productId}/archived-categories`)
            .then(function (response) {
                var archivedCategories = response.data.archivedCategories || [];
                var container = $("#archivedCategoriesList");
                container.empty();

                archivedCategories.forEach(function (category) {
                    var item = `<li>${category.path}</li>`;
                    container.append(item);
                });
            })
            .catch(function (error) {
                console.error("Error fetching archived categories:", error);
            });
    }
});

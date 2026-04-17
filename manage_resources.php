<?php
require_once __DIR__ . '/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: Log-in (Admin).php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "career_counseling");
$message = '';
$message_type = '';

// Initialize default categories if they don't exist
$check_categories = $mysqli->query("SELECT COUNT(*) as count FROM resource_categories");
$count_result = $check_categories->fetch_assoc();

if ($count_result['count'] == 0) {
    $default_categories = [
        'Education Courses',
        'Exams & Assessments',
        'Careers & Opportunities',
        'Industry Insights',
        'Skills Development'
    ];
    
    foreach ($default_categories as $cat) {
        $cat_escaped = $mysqli->real_escape_string($cat);
        $mysqli->query("INSERT INTO resource_categories (name, created_at) VALUES ('$cat_escaped', NOW())");
    }
}

// Handle Add New Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = $mysqli->real_escape_string($_POST['name']);
    
    if (!empty($name)) {
        $insert_stmt = $mysqli->prepare("INSERT INTO resource_categories (name, created_at) VALUES (?, NOW())");
        $insert_stmt->bind_param("s", $name);
        
        if ($insert_stmt->execute()) {
            $message = "Category added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding category!";
            $message_type = "error";
        }
        $insert_stmt->close();
    }
}

// Handle Update Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_category') {
    $category_id = intval($_POST['category_id']);
    $name = $mysqli->real_escape_string($_POST['name']);
    
    $update_stmt = $mysqli->prepare("UPDATE resource_categories SET name = ? WHERE id = ?");
    $update_stmt->bind_param("si", $name, $category_id);
    
    if ($update_stmt->execute()) {
        $message = "Category updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating category!";
        $message_type = "error";
    }
    $update_stmt->close();
}

// Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $category_id = intval($_POST['category_id']);
    
    // Delete all items in this category first
    $mysqli->query("DELETE FROM resource_items WHERE category_id = $category_id");
    
    $delete_stmt = $mysqli->prepare("DELETE FROM resource_categories WHERE id = ?");
    $delete_stmt->bind_param("i", $category_id);
    
    if ($delete_stmt->execute()) {
        $message = "Category deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting category!";
        $message_type = "error";
    }
    $delete_stmt->close();
}

// Handle Add Item to Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_item') {
    $category_id = intval($_POST['category_id']);
    $title = $mysqli->real_escape_string($_POST['title']);
    $description = $mysqli->real_escape_string($_POST['description']);
    $url = $mysqli->real_escape_string($_POST['url']);
    
    $insert_stmt = $mysqli->prepare("INSERT INTO resource_items (category_id, title, description, url, created_at) VALUES (?, ?, ?, ?, NOW())");
    $insert_stmt->bind_param("isss", $category_id, $title, $description, $url);
    
    if ($insert_stmt->execute()) {
        $message = "Item added successfully!";
        $message_type = "success";
    } else {
        $message = "Error adding item!";
        $message_type = "error";
    }
    $insert_stmt->close();
}

// Handle Update Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_item') {
    $item_id = intval($_POST['item_id']);
    $title = $mysqli->real_escape_string($_POST['title']);
    $description = $mysqli->real_escape_string($_POST['description']);
    $url = $mysqli->real_escape_string($_POST['url']);
    
    $update_stmt = $mysqli->prepare("UPDATE resource_items SET title = ?, description = ?, url = ? WHERE id = ?");
    $update_stmt->bind_param("sssi", $title, $description, $url, $item_id);
    
    if ($update_stmt->execute()) {
        $message = "Item updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating item!";
        $message_type = "error";
    }
    $update_stmt->close();
}

// Handle Delete Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_item') {
    $item_id = intval($_POST['item_id']);
    
    $delete_stmt = $mysqli->prepare("DELETE FROM resource_items WHERE id = ?");
    $delete_stmt->bind_param("i", $item_id);
    
    if ($delete_stmt->execute()) {
        $message = "Item deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting item!";
        $message_type = "error";
    }
    $delete_stmt->close();
}

// Get all categories with their items
$categories_result = $mysqli->query("SELECT * FROM resource_categories ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #000, #4b0082);
            color: white;
            font-family: 'Arial', sans-serif;
            padding-bottom: 40px;
        }

        .navbar {
            background-color: black;
            padding: 20px 40px;
            margin-bottom: 40px;
        }

        .back-link {
            color: #ff6b6b;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #ff5252;
        }

        .back-link i {
            font-size: 1.1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 0 20px;
        }

        .page-header h2 {
            margin: 0;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: #ff6b6b;
            border-color: #ff6b6b;
            font-size: 0.85rem;
            padding: 5px 10px;
            margin: 3px;
        }

        .btn-danger:hover {
            background-color: #ff5252;
        }

        .btn-primary {
            background-color: #800080;
            border-color: #800080;
            font-size: 0.85rem;
            padding: 5px 10px;
            margin: 3px;
        }

        .btn-primary:hover {
            background-color: #6b0080;
        }

        .alert {
            margin: 20px;
        }

        /* Category Cards */
        .categories-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            padding: 0 20px;
            margin-bottom: 30px;
        }

        .category-card {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.2), rgba(128, 0, 128, 0.1));
            border: 2px solid #ff6b6b;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
            border-color: #ff5252;
        }

        .category-card h5 {
            color: #ff6b6b;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .category-card .item-count {
            font-size: 0.9rem;
            color: #aaa;
            margin-bottom: 10px;
        }

        .category-card-actions {
            display: flex;
            gap: 5px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ff6b6b;
        }

        .category-card-actions button {
            flex: 1;
            font-size: 0.8rem;
            padding: 6px !important;
            margin: 0 !important;
        }

        .add-card {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.2), rgba(40, 167, 69, 0.1));
            border: 2px dashed #28a745;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 180px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-card:hover {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.3), rgba(40, 167, 69, 0.2));
            border-color: #20c997;
        }

        .add-card-content {
            text-align: center;
        }

        .add-card-content i {
            font-size: 2.5rem;
            color: #28a745;
            margin-bottom: 10px;
        }

        .add-card-content p {
            color: #28a745;
            margin: 0;
            font-weight: 600;
        }

        /* Items Expansion */
        .items-section {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding: 0 20px;
        }

        .items-section.show {
            max-height: 1000px;
        }

        .items-container {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #ff6b6b;
        }

        .items-container h5 {
            color: #ff6b6b;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ff6b6b;
        }

        .item-row {
            background: rgba(255, 107, 107, 0.05);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 3px solid #800080;
        }

        .item-row h6 {
            color: #ff6b6b;
            margin-bottom: 5px;
        }

        .item-row small {
            color: #aaa;
            display: block;
            margin: 5px 0;
        }

        .item-row-description {
            color: #ddd;
            font-size: 0.95rem;
            margin: 10px 0;
        }

        .item-actions {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }

        .item-actions button {
            font-size: 0.8rem;
        }

        .add-item-btn {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .add-item-btn:hover {
            background-color: #218838;
            text-decoration: none;
            color: white;
        }

        /* Modal Styling */
        .modal-content {
            background-color: rgba(0, 0, 0, 0.95);
            border: 1px solid #ff6b6b;
            color: white;
        }

        .modal-header {
            border-bottom: 1px solid #ff6b6b;
        }

        .modal-footer {
            border-top: 1px solid #ff6b6b;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid #666;
            color: white;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: #ff6b6b;
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
        }

        .form-control::placeholder {
            color: #999;
        }

        .form-group label {
            color: #ff6b6b;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="Admin_Dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </nav>

    <div class="page-header">
        <h2><i class="fas fa-book"></i> Resource Management</h2>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="categories-container">
        <?php while ($category = $categories_result->fetch_assoc()): ?>
            <?php 
                $cat_id = $category['id'];
                $items = $mysqli->query("SELECT * FROM resource_items WHERE category_id = $cat_id ORDER BY created_at DESC");
                $item_count = $items->num_rows;
            ?>
            <div class="category-card" onclick="toggleItems(<?php echo $cat_id; ?>)">
                <h5><i class="fas fa-folder"></i> <?php echo htmlspecialchars($category['name']); ?></h5>
                <div class="item-count"><i class="fas fa-list"></i> <?php echo $item_count; ?> items</div>
                <small style="color: #999;">Click to expand</small>
                <div class="category-card-actions">
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCategoryModal<?php echo $cat_id; ?>" onclick="event.stopPropagation();">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <form method="POST" style="display: inline; flex: 1;" onsubmit="event.stopPropagation(); return confirm('Delete this category and all items?');">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" value="<?php echo $cat_id; ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; margin: 0;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>

            <!-- Edit Category Modal -->
            <div class="modal fade" id="editCategoryModal<?php echo $cat_id; ?>" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Category</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="update_category">
                                <input type="hidden" name="category_id" value="<?php echo $cat_id; ?>">
                                <div class="form-group">
                                    <label>Category Name</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Items Expansion -->
            <div class="items-section" id="items-<?php echo $cat_id; ?>">
                <div class="items-container">
                    <h5><i class="fas fa-tasks"></i> Items in <?php echo htmlspecialchars($category['name']); ?></h5>
                    
                    <?php if ($item_count > 0): ?>
                        <?php $items->data_seek(0); while ($item = $items->fetch_assoc()): ?>
                            <div class="item-row">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <h6><?php echo htmlspecialchars($item['title']); ?></h6>
                                        <div class="item-row-description"><?php echo htmlspecialchars($item['description']); ?></div>
                                        <?php if (!empty($item['url'])): ?>
                                            <small><strong>Link:</strong> <a href="<?php echo htmlspecialchars($item['url']); ?>" style="color: #ff6b6b;" target="_blank">Open Resource</a></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-actions">
                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editItemModal<?php echo $item['id']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this item?');">
                                            <input type="hidden" name="action" value="delete_item">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Item Modal -->
                            <div class="modal fade" id="editItemModal<?php echo $item['id']; ?>" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Item</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="update_item">
                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                <div class="form-group">
                                                    <label>Item Title</label>
                                                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Description</label>
                                                    <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($item['description']); ?></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>URL (Optional)</label>
                                                    <input type="url" class="form-control" name="url" placeholder="https://..." value="<?php echo htmlspecialchars($item['url'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: #aaa;">No items in this category yet.</p>
                    <?php endif; ?>

                    <button type="button" class="add-item-btn" data-toggle="modal" data-target="#addItemModal<?php echo $cat_id; ?>">
                        <i class="fas fa-plus"></i> Add New Item
                    </button>
                </div>
            </div>

            <!-- Add Item Modal -->
            <div class="modal fade" id="addItemModal<?php echo $cat_id; ?>" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Item to <?php echo htmlspecialchars($category['name']); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add_item">
                                <input type="hidden" name="category_id" value="<?php echo $cat_id; ?>">
                                <div class="form-group">
                                    <label>Item Title</label>
                                    <input type="text" class="form-control" name="title" placeholder="e.g., University Information" required>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Item details..." required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>URL (Optional)</label>
                                    <input type="url" class="form-control" name="url" placeholder="https://example.com">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Add Item</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- Add New Category Card -->
        <div class="add-card" data-toggle="modal" data-target="#addCategoryModal">
            <div class="add-card-content">
                <i class="fas fa-plus-circle"></i>
                <p>Add New Category</p>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_category">
                        <div class="form-group">
                            <label>Category Name</label>
                            <input type="text" class="form-control" name="name" placeholder="e.g., Advanced Certifications" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
    <script>
        function toggleItems(categoryId) {
            const itemsSection = document.getElementById('items-' + categoryId);
            itemsSection.classList.toggle('show');
        }
    </script>
</body>
</html>

<?php
// Database connection closed automatically by PHP
?>


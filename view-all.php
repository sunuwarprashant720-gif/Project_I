<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("connect.php");

/* 🔐 PROTECT PAGE */
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session email
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT user_id AS id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user_id = $user['id'];
$username = $user['username'];

// Handle AJAX composition deletion (same as home.php)
if (isset($_POST['ajax_delete']) && isset($_POST['composition_id'])) {
    $composition_id = intval($_POST['composition_id']);
    
    $delete_stmt = $conn->prepare("DELETE FROM compositions WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $composition_id, $user_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Composition deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting composition']);
    }
    exit();
}

// Pagination setup
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Fetch ALL compositions count
$total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM compositions WHERE user_id = ?");
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_compositions = $total_result->fetch_assoc()['total'];

// Calculate total pages
$total_pages = ceil($total_compositions / $limit);

// Fetch paginated compositions
$compositions_stmt = $conn->prepare("
    SELECT id, title, composer, created_at, updated_at 
    FROM compositions 
    WHERE user_id = ? 
    ORDER BY updated_at DESC 
    LIMIT ? OFFSET ?
");
$compositions_stmt->bind_param("iii", $user_id, $limit, $offset);
$compositions_stmt->execute();

$compositions_result = $compositions_stmt->get_result();
$all_compositions = [];
while ($row = $compositions_result->fetch_assoc()) {
    $all_compositions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuneCraft - All Compositions</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(90deg, #faf5ff 0%, #fff1f8 40%, #eef4ff 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
        }

        /* Composition cards (same as home.php) */
        .project-card {
            width: 200px;
            padding: 20px;
            background: #ffffff;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            margin-bottom: 20px;
            position: relative;
            border: 2px solid transparent;
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: inherit;
            border-color: #8b5cf6;
        }

        .project-card:hover .delete-btn {
            opacity: 1;
        }

        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            opacity: 0;
            transition: all 0.2s;
            z-index: 10;
        }

        .delete-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        .empty-state {
            width: 100%;
            text-align: center;
            padding: 60px 40px;
            background: #f8fafc;
            border-radius: 16px;
            border: 2px dashed #cbd5e1;
            margin-top: 20px;
            grid-column: 1 / -1;
        }

        .empty-state img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.6;
        }

        .empty-state p {
            color: #64748b;
            font-size: 16px;
            margin: 0;
        }

        .empty-state .empty-title {
            font-size: 20px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 10px;
        }

        .composition-date {
            font-size: 12px;
            color: #64748b;
            margin-top: 8px;
            font-style: italic;
        }

        .composition-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
            font-size: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 40px;
        }

        .composition-composer {
            font-size: 14px;
            color: #8b5cf6;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .projects {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
            margin-top: 30px;
            margin-bottom: 40px;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            align-items: center;
            flex-wrap: wrap;
        }

        .new-composition-btn {
            cursor: pointer;
            padding: 14px 28px;
            font-size: 16px;
            border: none;
            color: white;
            background: linear-gradient(to right, #8b5cf6, #ec4899);
            border-radius: 14px;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(236, 72, 153, 0.3);
            transition: 0.2s;
            font-weight: 600;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0;
        }

        .new-composition-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(236, 72, 153, 0.4);
        }

        .back-to-dashboard-btn {
            padding: 14px 28px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.2s;
            font-weight: 600;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-to-dashboard-btn:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(5, 150, 105, 0.3);
        }

        /* Page header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h1 {
            margin: 0;
            color: #1e293b;
        }

        .total-count {
            font-size: 18px;
            color: #64748b;
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 500;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .page-btn {
            padding: 8px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
            min-width: 40px;
            text-align: center;
        }

        .page-btn:hover {
            background: #f3f4f6;
        }

        .page-btn.active {
            background: #8b5cf6;
            color: white;
            border-color: #8b5cf6;
        }

        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Success message and modal (same as home.php) */
        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
            display: none;
            align-items: center;
            gap: 10px;
            z-index: 1001;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal {
            background: white;
            padding: 30px;
            border-radius: 16px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
<div class="container">

<!-- SIDEBAR (same as home.php) -->
<aside class="sidebar">
    <div class="logo-wrapper">
        <img src="Assets/music-2.svg" class="logo">
        <p class="logo-title">TuneCraft</p>
    </div>
    <p class="logo-description">Make music, have fun!</p>

    <nav class="menu-nav">
        <ul class="menu">
            <div class="menu-item-container">
                <li>
                    <a href="home.php">
                        <img src="./Assets/house.svg"> Dashboard
                    </a>
                </li>
            </div>
            <div class="menu-item-container">
                <li>
                    <a href="music-note-editor.html">
                        <img src="./Assets/music-2-black.svg"> Music Editor
                    </a>
                </li>
            </div>
            <div class="menu-item-container">
                <li>
                    <a href="learn.html">
                        <img src="./Assets/book.svg"> Learn
                    </a>
                </li>
            </div>
        </ul>
    </nav>

    <!-- LOGOUT BUTTON -->
    <form action="logout.php" method="POST">
        <button type="submit" name="logout" class="logout-btn">
            <img src="./Assets/log-out.svg" alt="">
            Logout
        </button>
    </form>
</aside>

<!-- MAIN CONTENT -->
<main class="main-content">
    <div class="page-header">
        <h1>All Your Compositions</h1>
        <div class="total-count">Total: <?php echo $total_compositions; ?> compositions</div>
    </div>

    <div class="action-buttons">
        <button class="new-composition-btn" onclick="window.location.href='music-note-editor.html'">
            + Create New Composition
        </button>
        
        <button class="back-to-dashboard-btn" onclick="window.location.href='home.php'">
            ← Back to Dashboard
        </button>
    </div>

    <div class="projects">
        <?php if (empty($all_compositions)): ?>
            <div class="empty-state">
                <img src="./Assets/music.svg" alt="No compositions">
                <p class="empty-title">No compositions yet</p>
                <p>Create your first masterpiece! Click the button above to get started.</p>
            </div>
        <?php else: ?>
            <?php foreach ($all_compositions as $composition): ?>
                <div class="project-card" data-id="<?php echo $composition['id']; ?>">
                    <button class="delete-btn" onclick="showDeleteModal(<?php echo $composition['id']; ?>, '<?php echo htmlspecialchars(addslashes($composition['title'])); ?>')">×</button>
                    
                    <a href="music-note-editor.html?id=<?php echo $composition['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                        <div class="icon-box">
                            <img src="Assets/music.svg" style="width: 40px; height: 40px;">
                        </div>
                        <div class="composition-info">
                            <p class="composition-title"><?php echo htmlspecialchars($composition['title']); ?></p>
                            <p class="composition-composer">by <?php echo htmlspecialchars($composition['composer']); ?></p>
                            <p class="composition-date">
                                Updated: <?php echo date('M d, Y', strtotime($composition['updated_at'])); ?>
                            </p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($total_compositions > 0): ?>
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <button class="page-btn" onclick="window.location.href='view-all.php?page=<?php echo $page - 1; ?>'">← Previous</button>
                <?php else: ?>
                    <button class="page-btn disabled">← Previous</button>
                <?php endif; ?>
                
                <?php 
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): 
                ?>
                    <button class="page-btn <?php echo $i == $page ? 'active' : ''; ?>" 
                            onclick="window.location.href='view-all.php?page=<?php echo $i; ?>'">
                        <?php echo $i; ?>
                    </button>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <button class="page-btn" onclick="window.location.href='view-all.php?page=<?php echo $page + 1; ?>'">Next →</button>
                <?php else: ?>
                    <button class="page-btn disabled">Next →</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<!-- Delete Confirmation Modal (same as home.php) -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <h3>Delete Composition</h3>
        <p id="deleteMessage">Are you sure you want to delete this composition?</p>
        <div class="modal-buttons">
            <button class="modal-btn cancel" onclick="hideDeleteModal()">Cancel</button>
            <button class="modal-btn delete" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<!-- Success Message (same as home.php) -->
<div class="success-message" id="successMessage" style="display: none;">
    <span>✓</span>
    <span id="successText"></span>
</div>

<div class="bg-illustration-container">
    <img src="./Assets/bgimg2updated.jpg" class="bg-illustration">
</div>

</div>

<script>
// Delete confirmation modal (same as home.php with slight modification)
let compositionToDelete = null;
let compositionToDeleteTitle = '';

function showDeleteModal(id, title) {
    event.stopPropagation();
    event.preventDefault();
    
    compositionToDelete = id;
    compositionToDeleteTitle = title;
    
    document.getElementById('deleteMessage').textContent = 
        `Are you sure you want to delete "${title}"? This action cannot be undone.`;
    
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
    compositionToDelete = null;
    compositionToDeleteTitle = '';
    document.body.style.overflow = 'auto';
}

// Handle delete confirmation
function confirmDelete() {
    if (!compositionToDelete) return;
    
    const deleteBtn = document.querySelector('.modal-btn.delete');
    const originalText = deleteBtn.textContent;
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    console.log('Deleting composition ID:', compositionToDelete);
    
    // Send AJAX request to delete composition
    fetch('view-all.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax_delete=1&composition_id=${compositionToDelete}`
    })
    .then(response => response.text())
    .then(data => {
        console.log('Delete response:', data);
        
        try {
            const result = JSON.parse(data);
            
            if (result.success) {
                // Remove the composition card from DOM
                const card = document.querySelector(`.project-card[data-id="${compositionToDelete}"]`);
                if (card) {
                    card.style.opacity = '0.5';
                    card.style.transform = 'scale(0.95)';
                    
                    setTimeout(() => {
                        card.remove();
                        
                        // Update total count in header
                        const totalCountElement = document.querySelector('.total-count');
                        if (totalCountElement) {
                            const currentText = totalCountElement.textContent;
                            const match = currentText.match(/Total: (\d+) compositions/);
                            if (match) {
                                const currentCount = parseInt(match[1]);
                                if (!isNaN(currentCount) && currentCount > 0) {
                                    totalCountElement.textContent = `Total: ${currentCount - 1} compositions`;
                                }
                            }
                        }
                        
                        // Check if we need to show empty state
                        const remainingCards = document.querySelectorAll('.project-card').length;
                        const projectsContainer = document.querySelector('.projects');
                        
                        if (remainingCards === 0) {
                            projectsContainer.innerHTML = `
                                <div class="empty-state">
                                    <img src="./Assets/music.svg" alt="No compositions">
                                    <p class="empty-title">No compositions yet</p>
                                    <p>Create your first masterpiece! Click the button above to get started.</p>
                                </div>
                            `;
                            // Hide pagination if no compositions left
                            const pagination = document.querySelector('.pagination');
                            if (pagination) pagination.style.display = 'none';
                        }
                        
                        // Show success message
                        showSuccessMessage('Composition deleted successfully!');
                    }, 300);
                }
            } else {
                alert('Error deleting composition: ' + (result.message || 'Unknown error'));
            }
        } catch (e) {
            console.error('Error parsing response:', e);
            alert('Error processing delete request. Please try again.');
        }
        
        hideDeleteModal();
        deleteBtn.disabled = false;
        deleteBtn.textContent = originalText;
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Network error deleting composition: ' + error.message);
        deleteBtn.disabled = false;
        deleteBtn.textContent = originalText;
        hideDeleteModal();
    });
}

function showSuccessMessage(message) {
    const successMsg = document.getElementById('successMessage');
    const successText = document.getElementById('successText');
    
    successText.textContent = message;
    successMsg.style.display = 'flex';
    
    setTimeout(() => {
        successMsg.style.opacity = '0';
        setTimeout(() => {
            successMsg.style.display = 'none';
            successMsg.style.opacity = '1';
        }, 300);
    }, 3000);
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteModal();
    }
});

// Add keyboard support for modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && compositionToDelete) {
        hideDeleteModal();
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Show delete button on card hover
    document.querySelectorAll('.project-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('selected');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('selected');
        });
    });
    
    // Prevent card navigation when delete button is clicked
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
        });
    });
    
    // Handle card clicks - navigate to editor
    document.querySelectorAll('.project-card a').forEach(link => {
        link.addEventListener('click', function(e) {
            // Only navigate if not clicking the delete button
            if (!e.target.closest('.delete-btn')) {
                window.location.href = this.getAttribute('href');
            }
        });
    });
});
</script>
</body>
</html>
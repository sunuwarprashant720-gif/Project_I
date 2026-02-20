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
$stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user_id = $user['user_id'];
$username = $user['username'];

// Handle AJAX composition deletion
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

// Check if we're viewing all compositions
$view_all = isset($_GET['view']) && $_GET['view'] === 'all';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Fetch ALL compositions count for stats
$total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM compositions WHERE user_id = ?");
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_compositions = $total_result->fetch_assoc()['total'];

// Fetch compositions for this user
if ($view_all) {
    // Get total count for pagination
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
} else {
    // Fetch only 3 recent compositions (LIMIT 3)
    $compositions_stmt = $conn->prepare("
        SELECT id, title, composer, created_at, updated_at 
        FROM compositions 
        WHERE user_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 3
    ");
    $compositions_stmt->bind_param("i", $user_id);
    $compositions_stmt->execute();
}

$compositions_result = $compositions_stmt->get_result();
$recent_compositions = [];
while ($row = $compositions_result->fetch_assoc()) {
    $recent_compositions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuneCraft Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(90deg, #faf5ff 0%, #fff1f8 40%, #eef4ff 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
        }

        /* Updated CSS for composition cards */
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

      

        /* Stats cards */
        .stats-container {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .stat-card {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(to right, #8b5cf6, #ec4899);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 24px;
            color: #1e293b;
        }

        .stat-info p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        /* Action buttons */
        
        /* Action buttons */
.action-buttons {
    display: flex;
    gap: 15px;
    margin: 20px 0;
    align-items: center;
    justify-content: flex-start; /* align buttons with stats container */
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
    height: 50px; /* Fixed height */
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 0;
    margin-top: 0;
}

.new-composition-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 22px rgba(236, 72, 153, 0.4);
}

.view-all-btn {
    padding: 14px 28px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 14px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.2s;
    font-weight: 600;
    height: 50px; /* Same height as new composition button */
    display: flex;
    align-items: center;
    justify-content: center;
}

.view-all-btn:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.3);
}

.view-recent-btn {
    padding: 14px 28px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 14px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.2s;
    font-weight: 600;
    height: 50px; /* Same height as other buttons */
    display: flex;
    align-items: center;
    justify-content: center;
}

.view-recent-btn:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(5, 150, 105, 0.3);
}
     /* Recent compositions heading */
        .recent-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .recent-title:before {
            content: "🎵";
            font-size: 20px;
        }

        /* Delete confirmation modal */
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

        .modal h3 {
            margin-top: 0;
            color: #1e293b;
        }

        .modal p {
            color: #64748b;
            margin-bottom: 25px;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .modal-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.2s;
            font-weight: 500;
        }

        .modal-btn.cancel {
            background: #f3f4f6;
            color: #374151;
        }

        .modal-btn.cancel:hover {
            background: #e5e7eb;
        }

        .modal-btn.delete {
            background: #ef4444;
            color: white;
        }

        .modal-btn.delete:hover {
            background: #dc2626;
        }

        /* Success message */
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

        /* Show delete button on card hover or when selected */
        .project-card.selected .delete-btn {
            opacity: 1;
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
    </style>
</head>

<body>
<div class="container">

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="logo-wrapper">
        <img src="Assets/music-2.svg" class="logo">
        <p class="logo-title">TuneCraft</p>
    </div>
    <p class="logo-description">Make music, have fun!</p>

    <nav class="menu-nav">
        <ul class="menu">
            <div class="menu-item-container">
                <li class="active">
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
<!-- MAIN CONTENT -->
<main class="main-content">
    <h1>Welcome <?php echo htmlspecialchars($username); ?>! 👋</h1>
    <h2>Your creative journey begins here!</h2>

    <!-- Stats Section -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">🎵</div>
            <div class="stat-info">
                <h3><?php echo $total_compositions; ?></h3>
                <p>Total Compositions</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-info">
                <h3>
                    <?php 
                    if (!empty($recent_compositions)) {
                        $latest = reset($recent_compositions);
                        echo date('M j', strtotime($latest['updated_at']));
                    } else {
                        echo 'None';
                    }
                    ?>
                </h3>
                <p>Last Updated</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-info">
                <h3><?php echo count($recent_compositions); ?> / 3</h3>
                <p>Recent Compositions</p>
            </div>
        </div>
    </div>

   <div class="action-buttons">
    <button class="new-composition-btn" onclick="window.location.href='music-note-editor.html'">
        + Create New Composition
    </button>
    
    <?php if (!$view_all && $total_compositions > 3): ?>
        <button class="view-all-btn" onclick="window.location.href='view-all.php'">
            View All Compositions (<?php echo $total_compositions; ?>)
        </button>
    <?php elseif ($view_all): ?>
        <button class="view-recent-btn" onclick="window.location.href='home.php'">
            ← Back to Dashboard
        </button>
    <?php endif; ?>
</div>

    <h3 class="recent-title">
        <?php 
        if ($view_all) {
            echo "All Your Compositions";
        } else {
            echo "Recent Compositions (Showing " . count($recent_compositions) . " of 3)";
            if ($total_compositions > 3) {
                echo " <small style='color:#6b7280; font-weight:normal; margin-left:10px;'>Click 'View All' to see more</small>";
            }
        }
        ?>
    </h3>

    <div class="projects">
        <?php if (empty($recent_compositions)): ?>
            <div class="empty-state">
                <img src="./Assets/music.svg" alt="No compositions">
                <p class="empty-title">No compositions yet</p>
                <p>Create your first masterpiece! Click the button above to get started.</p>
            </div>
        <?php else: ?>
            <?php foreach ($recent_compositions as $composition): ?>
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

    <?php if ($view_all && $total_compositions > 0): 
        $total_pages = ceil($total_compositions / $limit);
    ?>
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <button class="page-btn" onclick="window.location.href='home.php?view=all&page=<?php echo $page - 1; ?>'">← Previous</button>
                <?php else: ?>
                    <button class="page-btn disabled">← Previous</button>
                <?php endif; ?>
                
                <?php 
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): 
                ?>
                    <button class="page-btn <?php echo $i == $page ? 'active' : ''; ?>" 
                            onclick="window.location.href='home.php?view=all&page=<?php echo $i; ?>'">
                        <?php echo $i; ?>
                    </button>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <button class="page-btn" onclick="window.location.href='home.php?view=all&page=<?php echo $page + 1; ?>'">Next →</button>
                <?php else: ?>
                    <button class="page-btn disabled">Next →</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<!-- Delete Confirmation Modal -->
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

<!-- Success Message -->
<div class="success-message" id="successMessage" style="display: none;">
    <span>✓</span>
    <span id="successText"></span>
</div>

<div class="bg-illustration-container">
    <img src="./Assets/bgimg2updated.jpg" class="bg-illustration">
</div>

</div>

<script>
// Delete confirmation modal
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
    fetch('home.php', {
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
                        
                        // Update total count
                        const totalCountElement = document.querySelector('.stat-info h3:first-child');
                        if (totalCountElement) {
                            const currentCount = parseInt(totalCountElement.textContent);
                            if (!isNaN(currentCount) && currentCount > 0) {
                                totalCountElement.textContent = currentCount - 1;
                            }
                        }
                        
                        // Update recent count
                        const recentCountElement = document.querySelectorAll('.stat-info h3')[2];
                        if (recentCountElement) {
                            const recentText = recentCountElement.textContent;
                            const match = recentText.match(/(\d+)\s*\/\s*3/);
                            if (match) {
                                const currentRecent = parseInt(match[1]);
                                if (!isNaN(currentRecent) && currentRecent > 0) {
                                    recentCountElement.textContent = `${currentRecent - 1} / 3`;
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
    // Check if we should show a welcome message for new users
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('newuser') === 'true') {
        setTimeout(() => {
            alert('Welcome to TuneCraft! 🎵\n\nStart by creating your first composition using the "Create New Composition" button.');
        }, 500);
    }
    
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

// Handle window refresh after delete if needed
if (window.location.search.includes('deleted=true')) {
    showSuccessMessage('Composition deleted successfully!');
    // Clean URL
    const url = new URL(window.location);
    url.searchParams.delete('deleted');
    window.history.replaceState({}, '', url);
}
</script>
</body>
</html>
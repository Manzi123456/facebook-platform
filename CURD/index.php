<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require_login();

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook - Home</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-welcome {
            color: #1c1e21;
            font-weight: 600;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <header class="top-nav">
        <div class="nav-container">
            <div class="nav-left">
                <div class="logo">
                    <i class="fab fa-facebook"></i>
                </div>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search on Facebook">
                </div>
            </div>
            
            <div class="nav-center">
                <a href="index.php" class="nav-icon active">
                    <i class="fas fa-home"></i>
                </a>
                <a href="#" class="nav-icon">
                    <i class="fas fa-play"></i>
                </a>
                <a href="#" class="nav-icon">
                    <i class="fas fa-store"></i>
                </a>
                <a href="#" class="nav-icon">
                    <i class="fas fa-gamepad"></i>
                </a>
            </div>
            
            <div class="nav-right">
                <a href="#" class="nav-icon">
                    <i class="fas fa-th"></i>
                </a>
                <a href="#" class="nav-icon notification">
                    <i class="fab fa-facebook-messenger"></i>
                    <span class="badge">8</span>
                </a>
                <a href="#" class="nav-icon notification">
                    <i class="fas fa-bell"></i>
                    <span class="badge">20+</span>
                </a>
                <a href="logout.php" class="profile-dropdown" title="Logout">
                    <div class="profile-icon-small">
                        <i class="fas fa-user"></i>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!-- Left Sidebar -->
        <aside class="left-sidebar">
            <div class="user-profile">
                <div class="profile-icon">
                    <i class="fas fa-user"></i>
                </div>
                <span class="user-welcome"><?php echo e($user['name'] ?? 'User'); ?></span>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="menu-item" style="background: #e7f3ff; color: #1877f2; border-radius: 8px;">
                    <i class="fas fa-database menu-icon"></i>
                    <span>Employee CRUD</span>
                </a>
                <a href="#" class="menu-item">
                    <div class="menu-icon meta-ai">
                        <i class="fas fa-circle"></i>
                    </div>
                    <span>Meta AI</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-user-friends menu-icon"></i>
                    <span>Friends</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-clock menu-icon"></i>
                    <span>Memories</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-bookmark menu-icon"></i>
                    <span>Saved</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-users menu-icon"></i>
                    <span>Groups</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fab fa-instagram menu-icon reels-icon"></i>
                    <span>Reels</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-store menu-icon"></i>
                    <span>Marketplace</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-calendar menu-icon"></i>
                    <span>Events</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- What's on your mind section -->
            <div class="post-creator">
                <div class="post-input">
                    <div class="profile-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" placeholder="What's on your mind, <?php echo e($user['name'] ?? 'User'); ?>?">
                </div>
                <div class="post-options">
                    <button class="post-option">
                        <i class="fas fa-video" style="color: #f02849;"></i>
                        <span>Video</span>
                    </button>
                    <button class="post-option">
                        <i class="fas fa-images" style="color: #45bd62;"></i>
                        <span>Photos</span>
                    </button>
                    <button class="post-option">
                        <i class="far fa-smile" style="color: #f7b928;"></i>
                        <span>Feeling</span>
                    </button>
                </div>
            </div>

            <!-- Stories Section -->
            <div class="stories-section">
                <div class="story-card add-story">
                    <div class="story-image story-icon-bg">
                        <i class="fas fa-image story-bg-icon"></i>
                        <div class="add-story-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <p>Create story</p>
                </div>
                
                <div class="story-card">
                    <div class="story-image story-icon-bg">
                        <i class="fas fa-image story-bg-icon"></i>
                        <div class="story-profile">
                            <div class="profile-icon-story">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                    <p>John Doe</p>
                    <span class="story-label">Friend</span>
                </div>
                
                <div class="story-card">
                    <div class="story-image story-icon-bg">
                        <i class="fas fa-image story-bg-icon"></i>
                        <div class="story-profile">
                            <div class="profile-icon-story">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                    <p>Jane Smith</p>
                </div>
                
                <div class="story-card">
                    <div class="story-image story-icon-bg">
                        <i class="fas fa-image story-bg-icon"></i>
                        <div class="story-profile">
                            <div class="profile-icon-story">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                    <p>Mike Johnson</p>
                    <button class="story-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <!-- People you may know section -->
            <div class="people-section">
                <h3>People you may know</h3>
                <div class="people-scroll">
                    <div class="person-card">
                        <button class="dismiss-btn">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="person-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <p>Person Name</p>
                    </div>
                    <div class="person-card">
                        <button class="dismiss-btn">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="person-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <p>Person Name</p>
                    </div>
                    <div class="person-card">
                        <button class="dismiss-btn">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="person-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <p>Person Name</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Right Sidebar -->
        <aside class="right-sidebar">
            <!-- Advertisements -->
            <div class="ads-section">
                <h3>Sponsored</h3>
                
                <div class="ad-card">
                    <div class="ad-header">
                        <span class="ad-label">Ad</span>
                    </div>
                    <div class="ad-content">
                        <h4>Be a Better Writer, Starting Today</h4>
                        <p class="ad-link">grammarly.com</p>
                        <div class="ad-creative">
                            <div class="ad-creative-content">
                                <h5>Project Proposal</h5>
                                <p>This document contains unprofessional, unconvincing writing which some coworkers may find questionable.</p>
                            </div>
                        </div>
                        <button class="ad-button">Do better work with Grammarly</button>
                    </div>
                </div>
            </div>

            <!-- Friend Requests -->
            <div class="friends-section">
                <div class="section-header">
                    <h3>Friend Requests</h3>
                    <a href="#" class="see-all">See all</a>
                </div>
                
                <div class="friend-request">
                    <div class="friend-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="friend-info">
                        <p class="friend-name">John Doe</p>
                        <p class="friend-mutual">13 mutual friends</p>
                        <p class="friend-time">1w</p>
                    </div>
                    <div class="friend-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</body>
</html>

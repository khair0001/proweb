<?php
include_once '../include/koneksi.php'; // Pastikan path sudah benar

$profile_pic = '../assets/image/user.png'; // Default
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $result = mysqli_query($conn, "SELECT profile_image FROM user WHERE id = $user_id LIMIT 1");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        if (!empty($row['profile_image']) && file_exists($row['profile_image'])) {
            $profile_pic = $row['profile_image'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reshina Header</title>
    <link rel="stylesheet" href="/ProwebReshina2/assets/css/header.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="header-wrapper">
                <!-- Logo -->
                <div class="logo">
                    <a href="/ProwebReshina2/pages/dashboard.php">
                        <span>Reshina</span>
                    </a>
                </div>

                <!-- Search Form -->
                <div class="search-form">
                    <form action="/ProwebReshina2/pages/search-results.php" method="GET" class="search-form">
                        <div class="search-input">
                            <input type="text" name="search" placeholder="cari product...">
                            <button type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="user-menu">
                    <a href="/ProwebReshina2/pages/product-form.php" class="upload-btn"><i class="fas fa-plus"></i> <span>barang</span></a>
                    <div class="notification-wrapper">
                        <a href="/ProwebReshina2/pages/notifications.php" class="notification-btn"><i class="fas fa-bell"></i></a>
                        <?php 
                        $unread_notification_count = 0;
                        if (isset($_SESSION['user_id']) && isset($conn)) {
                            $current_user_id_for_notif = $_SESSION['user_id'];
                            $sql_unread_count = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
                            $stmt_unread_count = $conn->prepare($sql_unread_count);
                            if ($stmt_unread_count) {
                                $stmt_unread_count->bind_param("i", $current_user_id_for_notif);
                                $stmt_unread_count->execute();
                                $result_unread_count = $stmt_unread_count->get_result();
                                $row_unread_count = $result_unread_count->fetch_assoc();
                                $unread_notification_count = $row_unread_count['unread_count'];
                                $stmt_unread_count->close();
                            }
                        }
                        if ($unread_notification_count > 0): 
                        ?>
                        <span class="notification-badge"><?php echo $unread_notification_count; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    
                    <div class="notification-wrapper">
                        <a href="/ProwebReshina2/pages/beli-barang.php" class="notification-btn"><i class="fas fa-shopping-cart"></i></a>
                        <?php 
                        $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                        if ($cart_count > 0): 
                        ?>
                        <span class="notification-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <div class="user-menu">
                        <a href="/ProwebReshina2/pages/admin-panel.php" class="admin-btn"><i class="fas fa-gear"></i></a>
                    </div>
                    <?php endif; ?>
                    <div class="user-dropdown">
                        <div class="user-dropdown">
                            <a href="/ProwebReshina2/pages/profile.php" class="user-info" style="text-decoration: none;">
                                <img src="<?php echo $profile_pic; ?>" alt="User Avatar" class="user-avatar">
                                <span class="user-name">
                                    <?php echo ($_SESSION['username']); ?>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</body>

</html>
<?php
session_start();
include_once '../include/koneksi.php'; // Ensure correct path to koneksi.php
include_once '../include/header.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mark notification as read (basic implementation)
if (isset($_GET['mark_read']) && isset($_GET['notif_id'])) {
    $notif_id_to_mark = $_GET['notif_id'];
    $update_stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("ii", $notif_id_to_mark, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
    // Redirect to remove GET params from URL and show updated state
    header("Location: notifications.php");
    exit();
}

// Fetch notifications for the user
$query = "SELECT 
            id, 
            title, 
            message, 
            product_id, 
            is_read, 
            created_at, 
            sender_id
          FROM notifications 
          WHERE user_id = ? 
          ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - SecondHand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <style>
        body {
            background-color: #f8f9fa;
        }
        .notification-page-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1.5rem;
        }
        .notification-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .notification-item {
            display: flex;
            align-items: flex-start; /* Align icon to the top of text */
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            text-decoration: none;
            color: #343a40;
            transition: background-color 0.2s ease;
            background-color: #fff;
            border-radius: 0.5rem; /* Rounded corners for items */
            margin-bottom: 0.75rem; /* Space between items */
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .notification-item.unread {
            background-color: #eef6ff; /* Slightly different background for unread */
            font-weight: bold;
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item:hover {
            background-color: #f1f3f5;
        }
        .notification-icon-container {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.25rem;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .notification-icon-purchase {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196f3; /* Blue for purchases */
        }
        .notification-icon-sale {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4caf50; /* Green for sales */
        }
        .notification-icon-general {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d; /* Grey for general */
        }
        .notification-details {
            flex-grow: 1;
            min-width: 0;
        }
        .notification-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-size: 1.05rem;
            color: #212529;
        }
        .notification-message {
            font-size: 0.9rem;
            color: #495057;
            margin-bottom: 0.35rem;
            line-height: 1.5;
        }
        .notification-meta {
            font-size: 0.8rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .notification-meta .time {
            display: flex;
            align-items: center;
        }
        .notification-meta .time i {
            margin-right: 0.3rem;
        }
        .notification-actions a {
            font-size: 0.8rem;
            text-decoration: none;
        }
        .empty-notifications {
            text-align: center;
            padding: 3rem 1.5rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .empty-notifications i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #adb5bd;
        }
        .empty-notifications p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: #495057;
        }
        .card-header {
            border-bottom: 1px solid #dee2e6;
            background-color: #fff;
        }
    </style>
</head>
<body>

<div class="container notification-page-container">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h1 class="h4 mb-0 d-flex align-items-center">
                <i class="fas fa-bell text-primary me-2"></i>
                Notifikasi Anda
            </h1>
        </div>
        <div class="card-body p-lg-4 p-md-3 p-2">
            <?php if ($result->num_rows > 0): ?>
                <div class="notification-list">
                    <?php while ($row = $result->fetch_assoc()): 
                        $is_unread_class = $row['is_read'] == 0 ? 'unread' : '';
                        $notification_link = '#!'; // Default link
                        $icon_class = 'notification-icon-general';
                        $fa_icon = 'fa-info-circle';

                        // Refined logic to determine icon based on notification title for the current user
                        if (stripos($row['title'], 'Pembelian Diproses') !== false) { // Notification for the buyer
                            $icon_class = 'notification-icon-purchase';
                            $fa_icon = 'fa-shopping-bag';
                        } elseif (stripos($row['title'], 'Produk Anda Dibeli') !== false) { // Notification for the seller
                            $icon_class = 'notification-icon-sale';
                            $fa_icon = 'fa-store';
                        } else { // Fallback for other types of notifications
                            $icon_class = 'notification-icon-general';
                            $fa_icon = 'fa-info-circle';
                        }
                        
                        // If product_id is available, create a link to product-detail.php
                        // This is a generic link, specific logic for sales/purchases might differ
                        if ($row['product_id']) {
                            $notification_link = "product-detail.php?id=" . $row['product_id'];
                        } else {
                            // If there's no product_id, maybe it's a general notification
                            // or points to a transaction. For now, a generic link.
                            // $notification_link = "#!"; 
                        }

                    ?>
                        <a href="<?php echo $notification_link; ?>" class="notification-item <?php echo $is_unread_class; ?> d-block text-decoration-none">
                            <div class="notification-icon-container <?php echo $icon_class; ?>">
                                <i class="fas <?php echo $fa_icon; ?>"></i>
                            </div>
                            <div class="notification-details">
                                <div class="notification-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                <div class="notification-message"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
                                <div class="notification-meta">
                                    <span class="time"><i class="far fa-clock me-1"></i><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></span>
                                    <?php if ($row['is_read'] == 0): ?>
                                        <span class="notification-actions">
                                            <a href="notifications.php?mark_read=true&notif_id=<?php echo $row['id']; ?>" class="text-primary">Tandai sudah dibaca</a>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>Belum ada notifikasi untuk Anda.</p>
                    <a href="../index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Kembali ke Beranda
                    </a>
                </div>
            <?php endif; ?>
            <?php $stmt->close(); ?>
        </div>
    </div>
</div>

<?php include_once '../include/footer.php'; // Assuming you have a footer include ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

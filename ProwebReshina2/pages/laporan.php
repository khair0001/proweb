<?php
session_start();
include '../include/koneksi.php';
include '../include/header.php';
?>

<style>
    /* Main Container */
    .laporan-container {
        background: #fff;
        border-radius: 12px;
        padding: 2rem;
        margin: 2rem 0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* Card Styles */
    .detail-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
        padding: 1.5rem;
        border-bottom: none;
    }

    /* Table Styles */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .data-table thead th {
        background-color: #f1f5f9;
        color: #374151;
        font-weight: 600;
        padding: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .data-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .data-table tbody tr:hover {
        background-color: #f9fafb;
    }

    /* Badge Styles */
    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
        border-radius: 6px;
        font-size: 0.85em;
    }

    .badge-success {
        background-color: #10b981;
        color: white;
    }

    .badge-info {
        background-color: #3b82f6;
        color: white;
    }

    /* Button Styles */
    .btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        transition: all 0.2s;
        text-decoration: none;
        color: white;
        display: inline-block;
    }

    .btn-detail {
        background-color: #3b82f6;
    }

    .btn-detail:hover {
        background-color: #2563eb;
    }

    .btn-back {
        background-color: #6b7280;
    }

    .btn-back:hover {
        background-color: #4b5563;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-item {
        background-color: #f9fafb;
        padding: 1rem;
        border-radius: 8px;
        border-left: 1px solid #3b82f6;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-weight: 500;
        color: #1f2937;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .data-table thead {
            display: none;
        }

        .data-table tr {
            display: block;
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: right;
            padding: 0.75rem 1rem;
        }

        .data-table td:before {
            content: attr(data-label);
            font-weight: 600;
            color: #4b5563;
            margin-right: 1rem;
        }

        .data-table td:last-child {
            justify-content: center;
        }
    }
</style>

<div class="container py-4">
    <div class="laporan-container">
        <?php
        if (isset($_GET['id_laporan'])) {
            $id_laporan = intval($_GET['id_laporan']);
            $query = "SELECT lt.*, 
                             pembeli.username AS nama_pelanggan, pembeli.email, pembeli.nomor_hp,
                             produk.judul AS nama_produk, produk.harga, penjual.username AS nama_penjual
                      FROM laporan_transaksi lt
                      JOIN user pembeli ON lt.id_pelanggan = pembeli.id
                      JOIN user penjual ON lt.id_penjual = penjual.id
                      JOIN payment ON lt.id_pembayaran = payment.id_pembayaran
                      JOIN produk ON payment.id_produk = produk.id
                      WHERE lt.id_laporan = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id_laporan);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($laporan = $result->fetch_assoc()) {
        ?>
                <div class="card detail-card mb-4">
                    <div class="card-header">
                        <h3 class="h5 mb-0">Detail Transaksi</h3>
                        <small class="text-white-50">ID: #<?php echo htmlspecialchars($laporan['id_pembayaran']); ?></small>
                    </div>
                    <div class="card-body">
                        <div class="info-item mb-3">
                            <div class="info-label">Tanggal Transaksi</div>
                            <div class="info-value"><?php echo date('d M Y H:i', strtotime($laporan['tanggal_transaksi'])); ?></div>
                        </div>
                        <div class="info-item mb-3">
                            <div class="info-label">Pelanggan</div>
                            <div class="info-value"><?php echo htmlspecialchars($laporan['nama_pelanggan']); ?></div>
                        </div>
                        <div class="info-item mb-3">
                            <div class="info-label">Penjual</div>
                            <div class="info-value"><?php echo htmlspecialchars($laporan['nama_penjual']); ?></div>
                        </div>
                        <div class="info-item mb-3">
                            <div class="info-label">Produk</div>
                            <div class="info-value"><?php echo htmlspecialchars($laporan['nama_produk']); ?></div>
                        </div>
                        <div class="info-item mb-3">
                            <div class="info-label">Harga</div>
                            <div class="info-value">Rp <?php echo number_format($laporan['harga'], 0, ',', '.'); ?></div>
                        </div>
                        <div class="info-item mb-3">
                            <div class="info-label">Pembayaran</div>
                            <div class="info-value">
                                <span class="badge badge-info"><?php echo htmlspecialchars($laporan['metode_pembayaran']); ?></span>
                            </div>
                        </div>
                        <a href="laporan.php" class="btn btn-back">Kembali ke Daftar Laporan</a>
                    </div>
                </div>

            <?php
            } else {
                echo '<div class="alert alert-danger">Data laporan tidak ditemukan.</div>';
            }
            $stmt->close();
        } else {
            $query = "SELECT lt.*, 
                             pembeli.username AS nama_pelanggan, produk.judul AS nama_produk, penjual.username AS nama_penjual
                      FROM laporan_transaksi lt
                      JOIN user pembeli ON lt.id_pelanggan = pembeli.id
                      JOIN user penjual ON lt.id_penjual = penjual.id
                      JOIN payment ON lt.id_pembayaran = payment.id_pembayaran
                      JOIN produk ON payment.id_produk = produk.id
                      ORDER BY lt.tanggal_transaksi DESC";
            $result = $conn->query($query);
            ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>ID Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Produk</th>
                            <th>Penjual</th>
                            <th>Harga</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr data-href="laporan.php?id_laporan=' . $row['id_laporan'] . '">';
                                echo '<td data-label="Tanggal">' . date('d M Y, H:i', strtotime($row['tanggal_transaksi'])) . '</td>';
                                echo '<td data-label="ID Transaksi">' . htmlspecialchars($row['id_pembayaran']) . '</td>';
                                echo '<td data-label="Pelanggan">' . htmlspecialchars($row['nama_pelanggan']) . '</td>';
                                echo '<td data-label="Produk">' . htmlspecialchars($row['nama_produk']) . '</td>';
                                echo '<td data-label="Penjual">' . htmlspecialchars($row['nama_penjual']) . '</td>';
                                echo '<td data-label="Harga"><span class="badge badge-success">Rp ' . number_format($row['harga'], 0, ',', '.') . '</span></td>';
                                echo '<td data-label="Pembayaran"><span class="badge badge-info">' . htmlspecialchars($row['metode_pembayaran']) . '</span></td>';
                                echo '<td><a href="laporan.php?id_laporan=' . $row['id_laporan'] . '" class="btn btn-detail">Lihat Detail</a></td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="8" class="text-center py-4">Belum ada laporan transaksi</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php
        }
        ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('tr[data-href]').forEach(row => {
            row.addEventListener('click', function(e) {
                if (!e.target.closest('a, button')) {
                    window.location.href = this.dataset.href;
                }
            });

            row.style.transition = 'background-color 0.2s ease';
            row.addEventListener('mouseenter', () => {
                row.style.backgroundColor = '#f9fafb';
            });
            row.addEventListener('mouseleave', () => {
                row.style.backgroundColor = '';
            });
        });
    });
</script>

<?php include '../include/footer.php'; ?>
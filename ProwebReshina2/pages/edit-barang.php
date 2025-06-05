<?php
session_start();
include '../include/header.php';
include '../include/koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if no id
if ($product_id <= 0) {
  header('Location: dashboard.php');
  exit();
}

// Get product data
$product = null;
$images = [];
$query = "SELECT * FROM produk WHERE id = ? AND status = 'active'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
  $product = $row;
  if ($product['id_user'] != $user_id) {
    // Not the owner
    header('Location: dashboard.php');
    exit();
  }
} else {
  // Not found
  header('Location: dashboard.php');
  exit();
}

// Get product images
$img_query = "SELECT * FROM image_produk WHERE id_produk = ? ORDER BY is_primary DESC, id ASC";
$img_stmt = mysqli_prepare($conn, $img_query);
mysqli_stmt_bind_param($img_stmt, "i", $product_id);
mysqli_stmt_execute($img_stmt);
$img_result = mysqli_stmt_get_result($img_stmt);
while ($img = mysqli_fetch_assoc($img_result)) {
  $images[] = $img;
}

// Count current images
$current_count = count($images);

// Reference data
$jenis_produk_result = mysqli_query($conn, "SELECT * FROM jenis_produk");
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori");
$kondisi_result = mysqli_query($conn, "SELECT * FROM kondisi ORDER BY nama");

// Handle submit
$success_message = '';
$error_message = '';

// Ambil nilai lelang_end_time jika produk lelang
$lelang_end_time = isset($product['lelang_end_time']) ? $product['lelang_end_time'] : '';
$is_lelang = false;
if (isset($product['id_kategori'])) {
  $kat_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kategori WHERE id = '" . $product['id_kategori'] . "'"));
  if ($kat_row && strtolower($kat_row['nama']) === 'lelang') {
    $is_lelang = true;
  }
}

if (isset($_GET['updated']) && $_GET['updated'] == '1') {
  $success_message = "Produk berhasil diperbarui!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $productName = mysqli_real_escape_string($conn, $_POST['productName']);
  $jenis_produk = mysqli_real_escape_string($conn, $_POST['jenis_produk']);
  $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
  $condition = mysqli_real_escape_string($conn, $_POST['condition']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $price = mysqli_real_escape_string($conn, $_POST['price']);
  $address = mysqli_real_escape_string($conn, $_POST['address']);
  $city = mysqli_real_escape_string($conn, $_POST['city']);
  $lelang_end_time = isset($_POST['lelang_end_time']) ? mysqli_real_escape_string($conn, $_POST['lelang_end_time']) : null;

  // Cek apakah kategori "lelang"
  $is_lelang = false;
  $kat_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kategori WHERE id = '" . $kategori . "'"));
  if ($kat_row && strtolower($kat_row['nama']) === 'lelang') {
    $is_lelang = true;
  }

  // Validasi field wajib
  if (empty($productName) || empty($jenis_produk) || empty($kategori) || empty($condition) || empty($description) || empty($address) || empty($city) || ($is_lelang && empty($lelang_end_time))) {
    $error_message = "Semua field yang bertanda * wajib diisi. (Kategori lelang wajib isi tanggal berakhir)";
  } else {
    // Update produk
    if ($is_lelang) {
      $update_query = "UPDATE produk SET judul=?, deskripsi=?, harga=?, alamat=?, id_kategori=?, id_jenis_produk=?, id_kondisi=?, city=?, lelang_end_time=? WHERE id=? AND id_user=?";
      $update_stmt = mysqli_prepare($conn, $update_query);
      mysqli_stmt_bind_param($update_stmt, "ssdsiiissii", $productName, $description, $price, $address, $kategori, $jenis_produk, $condition, $city, $lelang_end_time, $product_id, $user_id);
    } else {
      $update_query = "UPDATE produk SET judul=?, deskripsi=?, harga=?, alamat=?, id_kategori=?, id_jenis_produk=?, id_kondisi=?, city=?, lelang_end_time=NULL WHERE id=? AND id_user=?";
      $update_stmt = mysqli_prepare($conn, $update_query);
      mysqli_stmt_bind_param($update_stmt, "ssdsiiisii", $productName, $description, $price, $address, $kategori, $jenis_produk, $condition, $city, $product_id, $user_id);
    }
    if (mysqli_stmt_execute($update_stmt)) {
      // Handle images
      $upload_dir = "../uploads/products/";
      $upload_success = true;
      // DEBUG: cek apakah data hapus gambar diterima
      if (isset($_POST['delete_image'])) {
        error_log('DEBUG delete_image[]: ' . print_r($_POST['delete_image'], true));
      } else {
        error_log('DEBUG delete_image[]: TIDAK ADA DATA');
      }
      // Delete images if requested
      if (isset($_POST['delete_image']) && is_array($_POST['delete_image'])) {
        foreach ($_POST['delete_image'] as $img_id) {
          $img_id = intval($img_id);
          $del_query = "SELECT file_name FROM image_produk WHERE id = ? AND id_produk = ?";
          $del_stmt = mysqli_prepare($conn, $del_query);
          mysqli_stmt_bind_param($del_stmt, "ii", $img_id, $product_id);
          mysqli_stmt_execute($del_stmt);
          $del_res = mysqli_stmt_get_result($del_stmt);
          if ($del_row = mysqli_fetch_assoc($del_res)) {
            $file_path = $upload_dir . $del_row['file_name'];
            if (file_exists($file_path)) unlink($file_path);
            mysqli_query($conn, "DELETE FROM image_produk WHERE id = $img_id");
          }
        }
      }

      // Upload new images
      if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $files = $_FILES['images'];
        $file_count = count($files['name']);

        // Count total images after upload
        $img_count_query = "SELECT COUNT(*) as count FROM image_produk WHERE id_produk = ?";
        $img_count_stmt = mysqli_prepare($conn, $img_count_query);
        mysqli_stmt_bind_param($img_count_stmt, "i", $product_id);
        mysqli_stmt_execute($img_count_stmt);
        $img_count_result = mysqli_stmt_get_result($img_count_stmt);
        $img_count_row = mysqli_fetch_assoc($img_count_result);
        $current_count = $img_count_row['count'];

        if ($file_count + $current_count > 5) {
          $upload_success = false;
          $error_message = "Total gambar tidak boleh lebih dari 5.";
        } else {
          for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === 0) {
              $file_name = $files['name'][$i];
              $file_tmp = $files['tmp_name'][$i];
              $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
              // Generate unique filename
              $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
              $destination = $upload_dir . $new_file_name;

              // Move uploaded file
              if (move_uploaded_file($file_tmp, $destination)) {
                // Set as primary if no images exist
                $is_primary = ($current_count == 0 && $i == 0) ? 1 : 0;

                // Insert image record
                $image_query = "INSERT INTO image_produk (id_produk, file_name, is_primary) VALUES (?, ?, ?)";
                $image_stmt = mysqli_prepare($conn, $image_query);
                mysqli_stmt_bind_param($image_stmt, "isi", $product_id, $new_file_name, $is_primary);
                mysqli_stmt_execute($image_stmt);
                mysqli_stmt_close($image_stmt);
              } else {
                $upload_success = false;
                $error_message = "Gagal upload gambar.";
                break;
              }
            }
          }
        }
      }

      if ($upload_success) {
        $success_message = "Produk berhasil diperbarui!";
        // Refresh data
        header("Location: edit-barang.php?id=$product_id&updated=1");
        exit();
      }
    } else {
      $error_message = "Gagal update produk: " . mysqli_error($conn);
    }
  }
}

// Refresh image data after update/delete
$images = [];
$img_query = "SELECT * FROM image_produk WHERE id_produk = ? ORDER BY is_primary DESC, id ASC";
$img_stmt = mysqli_prepare($conn, $img_query);
mysqli_stmt_bind_param($img_stmt, "i", $product_id);
mysqli_stmt_execute($img_stmt);
$img_result = mysqli_stmt_get_result($img_stmt);
while ($img = mysqli_fetch_assoc($img_result)) {
  $images[] = $img;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Barang | ProwebReshina</title>
  <link rel="stylesheet" href="../assets/css/product-form.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>

<body>
  <main class="main-content">
    <div class="container">
      <div class="form-container">
        <h1>Edit Barang</h1>
        <p class="form-description">Perbarui data barang Anda di bawah ini.</p>
        <?php if (!empty($success_message)): ?>
          <div class="alert success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
          <div class="alert error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form id="editProductForm" method="POST" action="" enctype="multipart/form-data" class="product-form">
          <div class="form-section">
            <h2>Informasi Barang</h2>
            <div class="form-group">
              <label for="productName">Nama Barang <span class="required">*</span></label>
              <input type="text" id="productName" name="productName" required value="<?php echo htmlspecialchars($product['judul']); ?>" />
            </div>
            <div class="form-group">
              <label for="jenis_produk">Jenis Produk <span class="required">*</span></label>
              <select id="jenis_produk" name="jenis_produk" required>
                <option value="" disabled>-- Pilih Jenis Produk --</option>
                <?php mysqli_data_seek($jenis_produk_result, 0);
                while ($jenis = mysqli_fetch_assoc($jenis_produk_result)) { ?>
                  <option value="<?php echo $jenis['id']; ?>" <?php if ($jenis['id'] == $product['id_jenis_produk']) echo 'selected'; ?>><?php echo $jenis['nama']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label for="condition">Kondisi <span class="required">*</span></label>
              <select id="condition" name="condition" required>
                <option value="" disabled>-- Pilih Kondisi --</option>
                <?php mysqli_data_seek($kondisi_result, 0);
                while ($kond = mysqli_fetch_assoc($kondisi_result)) { ?>
                  <option value="<?php echo $kond['id']; ?>" <?php if ($kond['id'] == $product['id_kondisi']) echo 'selected'; ?>><?php echo $kond['nama']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label for="description">Deskripsi <span class="required">*</span></label>
              <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
              <p class="form-hint">
                Berikan deskripsi lengkap tentang barang, termasuk
                spesifikasi, kelengkapan, dan alasan menjual/mendonasikan.
              </p>
            </div>
          </div>

          <div class="form-section">
            <h2>Jenis Transaksi</h2>
            <div class="form-group">
              <label for="kategori">Pilih Jenis Transaksi <span class="required">*</span></label>
              <select id="kategori" name="kategori" class="form-control" required onchange="toggleLelangField()">
                <option value="" disabled>-- Pilih Jenis Transaksi --</option>
                <?php mysqli_data_seek($kategori_result, 0);
                while ($kat = mysqli_fetch_assoc($kategori_result)) { ?>
                  <option value="<?php echo $kat['id']; ?>" <?php if ($kat['id'] == $product['id_kategori']) echo 'selected'; ?>><?php echo $kat['nama']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div id="priceSection" class="form-group">
              <label for="price">Harga (Rp) <span class="required">*</span></label>
              <input
                type="number"
                id="price"
                name="price"
                min="0"
                value="<?php echo htmlspecialchars($product['harga']); ?>"
                required />
            </div>
            <div id="lelangSection" class="form-group" style="display: <?php echo $is_lelang ? 'block' : 'none'; ?>;">
              <label for="lelang_end_time">Tanggal Berakhir Lelang <span class="required">*</span></label>
              <input type="datetime-local" id="lelang_end_time" name="lelang_end_time" value="<?php echo $is_lelang && $lelang_end_time ? date('Y-m-d\TH:i', strtotime($lelang_end_time)) : ''; ?>" />
              <small class="form-hint">Isi jika transaksi berupa lelang.</small>
            </div>
          </div>
          <script>
            function toggleLelangField() {
              var kategori = document.getElementById('kategori');
              var lelangSection = document.getElementById('lelangSection');
              var selected = kategori.options[kategori.selectedIndex].text.toLowerCase();
              if (selected === 'lelang') {
                lelangSection.style.display = 'block';
              } else {
                lelangSection.style.display = 'none';
              }
            }
            document.addEventListener('DOMContentLoaded', function() {
              toggleLelangField();
            });
          </script>

          <div class="form-section">
            <style>
              /* Image Upload Styles */
              .image-upload-container {
                margin-top: 10px;
              }

              .image-preview-container {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
              }

              .image-upload-placeholder {
                width: 120px;
                height: 120px;
                border: 2px dashed #bdc3c7;
                border-radius: 4px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s;
                color: #7f8c8d;
              }

              .image-upload-placeholder:hover {
                border-color: #3498db;
                color: #3498db;
              }

              .image-upload-placeholder i {
                font-size: 24px;
                margin-bottom: 5px;
              }

              .image-upload-placeholder span {
                font-size: 13px;
                text-align: center;
              }

              .image-preview {
                width: 120px;
                height: 120px;
                position: relative;
                border-radius: 4px;
                overflow: hidden;
              }

              .image-preview img {
                width: 100%;
                height: 100%;
                object-fit: cover;
              }

              .remove-image {
                position: absolute;
                top: 5px;
                right: 5px;
                width: 25px;
                height: 25px;
                border: none;
                border-radius: 50%;
                background-color: rgba(231, 76, 60, 0.8);
                color: white;
                font-size: 12px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
              }

              .remove-image:hover {
                background-color: #e74c3c;
              }

              .current-images-container {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                margin-bottom: 20px;
              }

              .delete-checkbox {
                background-color: rgba(231, 76, 60, 0.8);
                border-radius: 3px;
                padding: 2px 5px;
                cursor: pointer;
              }

              @media (max-width: 480px) {

                .image-preview,
                .image-upload-placeholder {
                  width: 100px;
                  height: 100px;
                }
              }
            </style>
            <div class="form-group">
              <label>Foto Barang <span class="required">*</span></label>
              <div class="image-upload-container">
                <p style="margin-bottom: 10px;">Foto Saat Ini:</p>
                <div class="current-images-container">
                  <?php if (count($images) > 0): ?>
                    <?php foreach ($images as $img): ?>
                      <div class="image-preview" style="position: relative;" id="img-<?php echo $img['id']; ?>">
                        <img src="../uploads/products/<?php echo htmlspecialchars($img['file_name']); ?>" alt="Product Image">
                        <button type="button" class="remove-image-btn" data-img-id="<?php echo $img['id']; ?>" style="position: absolute; top: 5px; right: 5px; background: rgba(231,76,60,0.8); border: none; border-radius: 50%; width: 28px; height: 28px; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p>Tidak ada foto tersedia.</p>
                  <?php endif; ?>
                </div>
                <script>
                  document.querySelectorAll('.remove-image-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                      if (!confirm('Hapus gambar ini?')) return;
                      const imgId = this.getAttribute('data-img-id');
                      const imgDiv = document.getElementById('img-' + imgId);
                      fetch('../pages/delete-image.php', {
                          method: 'POST',
                          headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                          },
                          body: 'img_id=' + encodeURIComponent(imgId)
                        })
                        .then(res => res.json())
                        .then(data => {
                          if (data.success) {
                            imgDiv.remove();
                          } else {
                            alert('Gagal menghapus gambar: ' + (data.error || 'Unknown error'));
                          }
                        })
                        .catch(() => alert('Gagal menghapus gambar.'));
                    });
                  });
                </script>

                <p style="margin-bottom: 10px;">Tambah Foto Baru:</p>
                <div
                  class="image-preview-container"
                  id="image-preview-container">
                  <div
                    class="image-upload-placeholder"
                    onclick="document.getElementById('images').click()">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Foto</span>
                  </div>
                </div>
                <input
                  type="file"
                  id="images"
                  name="images[]"
                  multiple
                  accept="image/*"
                  onchange="previewImages(this)"
                  style="display: none"
                  <?php if (count($images) == 0): ?>required<?php endif; ?> />
              </div>
              <div class="form-hint">
                Total maksimal 5 foto. Klik ikon sampah untuk menghapus foto yang ada secara langsung.
                <?php if (count($images) == 0): ?>Minimal 1 foto harus diunggah.<?php endif; ?>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="address">Alamat Lengkap <span class="required">*</span></label>
            <textarea
              id="address"
              name="address"
              rows="3"
              placeholder="Contoh: Jl. Gajah Mada No. 123, RT 01/RW 05"
              required><?php echo htmlspecialchars($product['alamat']); ?></textarea>
          </div>

          <div class="form-group">
            <label for="city">Kota/Kabupaten <span class="required">*</span></label>
            <input
              type="text"
              id="city"
              name="city"
              placeholder="Contoh: Mataram"
              value="<?php echo htmlspecialchars($product['city']); ?>"
              required />
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="product-detail.php?id=<?php echo $product_id; ?>" class="btn btn-secondary" style="text-decoration: none;">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </main>

  <?php include '../include/footer.php'; ?>

  <script>
    // Preview images before upload
    function previewImages(input) {
      const container = document.getElementById('image-preview-container');
      const maxFiles = 5;
      const currentImagesCount = <?php echo count($images); ?>;
      const remainingSlots = maxFiles - currentImagesCount;

      // Clear existing previews except the placeholder
      const existingPreviews = container.querySelectorAll('.image-preview');
      existingPreviews.forEach(preview => {
        container.removeChild(preview);
      });

      // Add new previews
      if (input.files) {
        const filesAmount = Math.min(input.files.length, remainingSlots);

        for (let i = 0; i < filesAmount; i++) {
          const reader = new FileReader();

          reader.onload = function(e) {
            const preview = document.createElement('div');
            preview.className = 'image-preview';

            const img = document.createElement('img');
            img.src = e.target.result;

            const removeBtn = document.createElement('button');
            removeBtn.className = 'remove-image';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = function(event) {
              event.preventDefault();
              container.removeChild(preview);
            };

            preview.appendChild(img);
            preview.appendChild(removeBtn);

            // Insert before placeholder
            const placeholder = container.querySelector('.image-upload-placeholder');
            container.insertBefore(preview, placeholder);
          }

          reader.readAsDataURL(input.files[i]);
        }

        // Hide placeholder if max files reached
        const placeholder = container.querySelector('.image-upload-placeholder');
        if (input.files.length >= remainingSlots) {
          placeholder.style.display = 'none';
        } else {
          placeholder.style.display = 'flex';
        }
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const kategori = document.getElementById('kategori');
      const priceSection = document.getElementById('priceSection');
      const priceInput = document.getElementById('price');

      function togglePriceSection() {
        // Get the text of the selected option and convert to lowercase
        const selectedText = kategori.options[kategori.selectedIndex].text.toLowerCase();

        if (selectedText.includes('donasi')) {
          // If "Donasi" is selected
          priceSection.style.display = 'none';
          priceInput.value = '0'; // Set value to 0
          priceInput.removeAttribute('required');
        } else {
          // For other options
          priceSection.style.display = 'block';
          priceInput.setAttribute('required', 'required');

          // If the current value is 0 (from previous donation selection), clear it
          if (priceInput.value === '0') {
            priceInput.value = '';
          }
        }
      }

      // Run on page load
      if (kategori) {
        togglePriceSection();
        // Run when selection changes
        kategori.addEventListener('change', togglePriceSection);
      }

      // Toggle checkbox untuk hapus gambar
      const deleteCheckboxes = document.querySelectorAll('.delete-checkbox');
      deleteCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function() {
          const input = this.querySelector('input');
          input.checked = !input.checked;
          this.style.backgroundColor = input.checked ? 'rgba(231, 76, 60, 1)' : 'rgba(231, 76, 60, 0.8)';
        });
      });
    });

    // Batasi maksimal 5 file di sisi frontend
    const imagesInput = document.getElementById('images');
    if (imagesInput) {
      imagesInput.addEventListener('change', function() {
        const currentImagesCount = <?php echo count($images); ?>;
        const remainingSlots = 5 - currentImagesCount;

        if (this.files.length > remainingSlots) {
          alert('Maksimal 5 gambar total yang dapat diunggah! Anda dapat mengunggah ' + remainingSlots + ' gambar lagi.');
          this.value = '';
        }
      });
    }
  </script>
</body>

</html>
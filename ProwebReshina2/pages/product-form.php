<?php
session_start();
include '../include/header.php';
include '../include/koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

// Initialize variables for messages
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $productName = mysqli_real_escape_string($conn, $_POST['productName']);
  $jenis_produk = mysqli_real_escape_string($conn, $_POST['jenis_produk']);
  $condition = mysqli_real_escape_string($conn, $_POST['condition']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
  $price = mysqli_real_escape_string($conn, $_POST['price']);
  $address = mysqli_real_escape_string($conn, $_POST['address']);
  $city = mysqli_real_escape_string($conn, $_POST['city']);
  $lelang_end_time = isset($_POST['lelang_end_time']) ? mysqli_real_escape_string($conn, $_POST['lelang_end_time']) : null;

  // Validate form data
  // Validasi tambahan untuk lelang
  $is_lelang = false;
  $harga_awal_lelang = null;
  if (!empty($kategori)) {
    // Cek nama kategori dari DB
    $kategori_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kategori WHERE id = '".$kategori."'"));
    if ($kategori_row && strtolower($kategori_row['nama']) === 'lelang') {
      $is_lelang = true;
      $harga_awal_lelang = $price;
      if (empty($lelang_end_time)) {
        $error_message = "Tanggal berakhir lelang wajib diisi.";
      }
    }
  }

  if (empty($productName) || empty($jenis_produk) || empty($condition) || empty($description) || empty($kategori) || empty($address) || empty($city) || ($is_lelang && empty($lelang_end_time))) {
    $error_message = "Semua field yang bertanda * wajib diisi. (Lelang wajib isi tanggal berakhir)";
  } else {
    if ($is_lelang) {
      $product_query = "INSERT INTO produk (id_user, judul, deskripsi, harga, alamat, id_kategori, id_jenis_produk, id_kondisi, status, city, lelang_end_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?)";
      $product_stmt = mysqli_prepare($conn, $product_query);
      // id_user (i), judul (s), deskripsi (s), harga (d), alamat (s), id_kategori (i), id_jenis_produk (i), id_kondisi (i), city (s), lelang_end_time (s)
      mysqli_stmt_bind_param($product_stmt, "issdsiiiss", $_SESSION['user_id'], $productName, $description, $price, $address, $kategori, $jenis_produk, $condition, $city, $lelang_end_time);
    } else {
      $product_query = "INSERT INTO produk (id_user, judul, deskripsi, harga, alamat, id_kategori, id_jenis_produk, id_kondisi, status, city) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)";
      $product_stmt = mysqli_prepare($conn, $product_query);
      mysqli_stmt_bind_param($product_stmt, "issdsiiis", $_SESSION['user_id'], $productName, $description, $price, $address, $kategori, $jenis_produk, $condition, $city);
    }

    if (mysqli_stmt_execute($product_stmt)) {
      $product_id = mysqli_insert_id($conn);
      mysqli_stmt_close($product_stmt);

      // Handle image uploads
      $upload_success = true;
      $upload_dir = "../uploads/products/";

      // Create directory if it doesn't exist
      if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
      }

      // Process uploaded images
      if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $files = $_FILES['images'];
        $file_count = count($files['name']);
        if ($file_count > 5) {
          $upload_success = false;
          $error_message = "Maksimal 5 gambar yang dapat diunggah.";
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
                // Set first image as primary
                $is_primary = ($i === 0) ? 1 : 0;
                // Insert image record
                $image_query = "INSERT INTO image_produk (id_produk, file_name, is_primary) VALUES (?, ?, ?)";
                $image_stmt = mysqli_prepare($conn, $image_query);
                mysqli_stmt_bind_param($image_stmt, "isi", $product_id, $new_file_name, $is_primary);
                mysqli_stmt_execute($image_stmt);
                mysqli_stmt_close($image_stmt);
              } else {
                $upload_success = false;
                $error_message = "Gagal mengunggah gambar.";
              }
            } else {
              $upload_success = false;
              $error_message = "Terjadi kesalahan saat mengunggah gambar.";
            }
          }
        }
      } else {
        $upload_success = false;
        $error_message = "Minimal satu gambar harus diunggah.";
      }

      if ($upload_success) {
        $success_message = "Produk berhasil ditambahkan!";
        // Redirect to product page or listing
        header("Location: product-detail.php?id=" . $product_id);
        exit();
      }
    } else {
      $error_message = "Gagal menambahkan produk: " . mysqli_error($conn);
    }
  }
}

// kondisi
$kondisi_barang = "SELECT * FROM kondisi ORDER BY nama";
$kondisi_result = mysqli_query($conn, $kondisi_barang);

//katgori misalkan elektronik
$jenis_produk = "SELECT * FROM jenis_produk";
$jenis_produk_result = mysqli_query($conn, $jenis_produk);

//
$kategori_query = "SELECT * FROM kategori";
$kategori_result = mysqli_query($conn, $kategori_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jual/Donasi Barang | ProwebReshina</title>
  <link rel="stylesheet" href="../assets/css/product-form.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>

<body>
  <main class="main-content">
    <div class="container">
      <div class="form-container">
        <h1>Jual / Donasi / Lelang Barang</h1>
        <p class="form-description">
          Isi formulir di bawah ini untuk menjual, mendonasikan, atau melelang
          barang Anda
        </p>

        <?php if (!empty($success_message)): ?>
          <div class="alert success">
            <?php echo $success_message; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
          <div class="alert error">
            <?php echo $error_message; ?>
          </div>
        <?php endif; ?>

        <form id="productForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" class="product-form">
          <div class="form-section">
            <h2>Informasi Barang</h2>

            <div class="form-group">
              <label for="productName">Nama Barang <span class="required">*</span></label>
              <input
                type="text"
                id="productName"
                name="productName"
                required />
            </div>

            <div class="form-group">
              <label for="jenis_produk">Kategori <span class="required">*</span></label>
              <select id="jenis_produk" name="jenis_produk" required>
                <option value="" selected disabled>-- Pilih Kategori --</option>
                <?php
                // Reset pointer to beginning
                mysqli_data_seek($jenis_produk_result, 0);
                while ($jenis_produk = mysqli_fetch_assoc($jenis_produk_result)) {
                ?>
                  <option value="<?php echo $jenis_produk['id']; ?>"><?php echo $jenis_produk['nama']; ?></option>
                <?php } ?>
              </select>
            </div>

            <div class="form-group">
              <label for="condition">Kondisi <span class="required">*</span></label>
              <select id="condition" name="condition" required>
                <option value="" selected disabled>-- Pilih Kondisi --</option>
                <?php while ($condition = mysqli_fetch_assoc($kondisi_result)) { ?>
                  <option value="<?php echo $condition['id']; ?>"><?php echo $condition['nama']; ?></option>
                <?php } ?>
              </select>
            </div>

            <div class="form-group">
              <label for="description">Deskripsi <span class="required">*</span></label>
              <textarea
                id="description"
                name="description"
                rows="5"
                required></textarea>
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
              <select id="kategori" name="kategori" class="form-control" required>
                <option value="" selected disabled>-- Pilih Jenis Transaksi --</option>
                <?php
                // Reset pointer to beginning
                mysqli_data_seek($kategori_result, 0);
                while ($kategori = mysqli_fetch_assoc($kategori_result)) {
                ?>
                  <option value="<?php echo $kategori['id']; ?>"><?php echo $kategori['nama']; ?></option>
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
                value="0"
                placeholder="Contoh: 100000"
                required />
            </div>

            <div id="auctionSection" class="form-group hidden">
              <label for="auctionDuration">Durasi Lelang <span class="required">*</span></label>
              <select id="auctionDuration" class="form-control">
                <option value="">-- Pilih Durasi --</option>
                <option value="1">1 Hari</option>
                <option value="3">3 Hari</option>
                <option value="7">7 Hari</option>
                <option value="custom">Custom</option>
              </select>
              <div id="customAuctionDuration" style="display:none; margin-top:10px;">
                <input type="number" id="customDurationValue" min="1" placeholder="Jumlah hari" style="width: 120px; display: inline-block;" /> hari
              </div>
              <label for="auctionEndDate" style="margin-top:10px;">Tanggal Berakhir Lelang <span class="required">*</span></label>
              <input
                type="datetime-local"
                id="auctionEndDate"
                name="lelang_end_time" />
            </div>

            <script>
              // Helper: get kategori id untuk "Lelang" (harus sesuai DB)
              const kategoriSelect = document.getElementById('kategori');
              const auctionSection = document.getElementById('auctionSection');
              const auctionDuration = document.getElementById('auctionDuration');
              const customAuctionDuration = document.getElementById('customAuctionDuration');
              const customDurationValue = document.getElementById('customDurationValue');
              const auctionEndDate = document.getElementById('auctionEndDate');
              const priceSection = document.getElementById('priceSection');

              // Ganti sesuai value id kategori "Lelang" di database
              const LELANG_ID = Array.from(kategoriSelect.options).find(opt => opt.textContent.toLowerCase().includes('lelang'))?.value;

              function setAuctionSection() {
                if (kategoriSelect.value === LELANG_ID) {
                  auctionSection.classList.remove('hidden');
                  priceSection.querySelector('input').required = false;
                  auctionEndDate.required = true;
                } else {
                  auctionSection.classList.add('hidden');
                  priceSection.querySelector('input').required = true;
                  auctionEndDate.required = false;
                }
              }
              kategoriSelect.addEventListener('change', setAuctionSection);

              auctionDuration.addEventListener('change', function() {
                if (this.value === 'custom') {
                  customAuctionDuration.style.display = 'inline-block';
                } else {
                  customAuctionDuration.style.display = 'none';
                  if (this.value) {
                    // Set tanggal berakhir otomatis
                    const now = new Date();
                    now.setSeconds(0, 0);
                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                    const durationDays = parseInt(this.value);
                    if (!isNaN(durationDays)) {
                      const endDate = new Date(now.getTime() + durationDays * 24 * 60 * 60 * 1000);
                      auctionEndDate.value = endDate.toISOString().slice(0, 16);
                    }
                  }
                }
              });
              customDurationValue.addEventListener('input', function() {
                const days = parseInt(this.value);
                if (!isNaN(days) && days > 0) {
                  const now = new Date();
                  now.setSeconds(0, 0);
                  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                  const endDate = new Date(now.getTime() + days * 24 * 60 * 60 * 1000);
                  auctionEndDate.value = endDate.toISOString().slice(0, 16);
                }
              });
            </script>
          </div>

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

              @media (max-width: 480px) {

                .image-preview,
                .image-upload-placeholder {
                  width: 100px;
                  height: 100px;
                }
              }
            </style>
            <h2>Foto Barang</h2>

            <div class="form-group">
              <label>Unggah Foto <span class="required">*</span></label>
              <div class="image-upload-container">
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
                  required />
              </div>
              <div class="form-hint">
                Unggah minimal 1 dan maksimal 5 foto. Foto pertama akan
                menjadi foto utama.
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
              required></textarea>
          </div>

          <div class="form-group">
            <label for="city">Kota/Kabupaten <span class="required">*</span></label>
            <input
              type="text"
              id="city"
              name="city"
              placeholder="Contoh: Mataram"
              required />
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Kirim</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <?php include '../include/footer.php'; ?>

  <!-- OpenStreetMap with Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tX/miZyoHS5obTRR9BMY="
    crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>

  <style>
    .btn-location {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 15px;
      background-color: #f8f9fa;
      border: 1px solid #ddd;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      color: #333;
      transition: all 0.3s;
      margin-bottom: 10px;
    }

    .btn-location:hover {
      background-color: #e9ecef;
      border-color: #adb5bd;
    }

    .btn-location i {
      color: #e74c3c;
    }

    #map {
      height: 300px;
      width: 100%;
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-bottom: 10px;
    }

    .gm-style-iw {
      padding: 10px;
    }

    .gm-style-iw h3 {
      margin: 0 0 5px;
      font-size: 16px;
    }

    .gm-style-iw p {
      margin: 0;
      font-size: 14px;
    }
  </style>

  <script>
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
      togglePriceSection();

      // Run when selection changes
      kategori.addEventListener('change', togglePriceSection);
    });
  </script>
  <script>
    // Preview images before upload
    function previewImages(input) {
      const container = document.getElementById('image-preview-container');
      const maxFiles = 5;

      // Clear existing previews except the placeholder
      const existingPreviews = container.querySelectorAll('.image-preview');
      existingPreviews.forEach(preview => {
        container.removeChild(preview);
      });

      // Add new previews
      if (input.files) {
        const filesAmount = Math.min(input.files.length, maxFiles);

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
        if (input.files.length >= maxFiles) {
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
    });
    // Batasi maksimal 5 file di sisi frontend
    const imagesInput = document.getElementById('images');
    if (imagesInput) {
      imagesInput.addEventListener('change', function() {
        if (this.files.length > 5) {
          alert('Maksimal 5 gambar yang dapat diunggah!');
          this.value = '';
        }
      });
    }
  </script>
</body>

</html>
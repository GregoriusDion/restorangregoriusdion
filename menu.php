<?php
// File: menu.php  ← Halaman Daftar Menu
require_once 'includes/koneksi.php';

$pesan = '';

// ── TAMBAH MENU ──
if (isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama_menu']));
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga    = (int) $_POST['harga'];
    $stok     = (int) $_POST['stok'];
    $deskripsi= mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));
    $status   = $_POST['status'];

    if ($nama && $harga > 0) {
        $sql = "INSERT INTO daftar_menu (nama_menu, kategori, harga, stok, deskripsi, status) 
                VALUES ('$nama', '$kategori', $harga, $stok, '$deskripsi', '$status')";
        if (mysqli_query($koneksi, $sql)) {
            $pesan = ['type' => 'success', 'teks' => '✅ Menu berhasil ditambahkan!'];
        } else {
            $pesan = ['type' => 'danger', 'teks' => '❌ Gagal: ' . mysqli_error($koneksi)];
        }
    } else {
        $pesan = ['type' => 'danger', 'teks' => '⚠️ Nama menu dan harga wajib diisi!'];
    }
}

// ── EDIT MENU ──
if (isset($_POST['aksi']) && $_POST['aksi'] == 'edit') {
    $id       = (int) $_POST['id_menu'];
    $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama_menu']));
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga    = (int) $_POST['harga'];
    $stok     = (int) $_POST['stok'];
    $deskripsi= mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));
    $status   = $_POST['status'];

    $sql = "UPDATE daftar_menu SET nama_menu='$nama', kategori='$kategori', harga=$harga, 
            stok=$stok, deskripsi='$deskripsi', status='$status' WHERE id_menu=$id";
    if (mysqli_query($koneksi, $sql)) {
        $pesan = ['type' => 'success', 'teks' => '✅ Menu berhasil diperbarui!'];
    }
}

// ── HAPUS MENU ──
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    // Cek apakah menu dipakai di pesanan
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE id_menu=$id"));
    if ($cek['total'] > 0) {
        $pesan = ['type' => 'danger', 'teks' => '❌ Menu tidak bisa dihapus karena ada di data pesanan!'];
    } else {
        mysqli_query($koneksi, "DELETE FROM daftar_menu WHERE id_menu=$id");
        $pesan = ['type' => 'success', 'teks' => '✅ Menu berhasil dihapus!'];
    }
}

// ── DATA UNTUK EDIT ──
$data_edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $data_edit = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM daftar_menu WHERE id_menu=$id"));
}

// ── AMBIL SEMUA MENU ──
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$sql_menu = "SELECT * FROM daftar_menu";
if ($filter_kategori) {
    $filter_kategori = mysqli_real_escape_string($koneksi, $filter_kategori);
    $sql_menu .= " WHERE kategori='$filter_kategori'";
}
$sql_menu .= " ORDER BY kategori, nama_menu";
$result_menu = mysqli_query($koneksi, $sql_menu);

require_once 'includes/header.php';
?>

<div class="container">
    <div class="page-title">
        <h1>🍜 Manajemen Menu</h1>
        <p>Tambah, edit, dan hapus menu restoran.</p>
    </div>

    <?php if ($pesan): ?>
        <div class="alert alert-<?= $pesan['type'] ?>"><?= $pesan['teks'] ?></div>
    <?php endif; ?>

    <!-- Form Tambah/Edit -->
    <div class="card">
        <h2><?= $data_edit ? '✏️ Edit Menu' : '➕ Tambah Menu Baru' ?></h2>
        <form method="POST">
            <input type="hidden" name="aksi" value="<?= $data_edit ? 'edit' : 'tambah' ?>">
            <?php if ($data_edit): ?>
                <input type="hidden" name="id_menu" value="<?= $data_edit['id_menu'] ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Menu *</label>
                    <input type="text" name="nama_menu" placeholder="Contoh: Nasi Goreng Spesial" 
                           value="<?= $data_edit ? htmlspecialchars($data_edit['nama_menu']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                        <?php
                        $kategori_list = ['Makanan', 'Minuman', 'Snack', 'Dessert', 'Lainnya'];
                        foreach ($kategori_list as $k): ?>
                            <option value="<?= $k ?>" <?= ($data_edit && $data_edit['kategori'] == $k) ? 'selected' : '' ?>><?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Harga (Rp) *</label>
                    <input type="number" name="harga" placeholder="Contoh: 25000" min="0"
                           value="<?= $data_edit ? $data_edit['harga'] : '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" placeholder="Jumlah stok" min="0"
                           value="<?= $data_edit ? $data_edit['stok'] : '10' ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Deskripsi</label>
                    <input type="text" name="deskripsi" placeholder="Deskripsi singkat menu..."
                           value="<?= $data_edit ? htmlspecialchars($data_edit['deskripsi']) : '' ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="tersedia" <?= ($data_edit && $data_edit['status'] == 'tersedia') ? 'selected' : '' ?>>✅ Tersedia</option>
                        <option value="habis"    <?= ($data_edit && $data_edit['status'] == 'habis')    ? 'selected' : '' ?>>❌ Habis</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><?= $data_edit ? '💾 Simpan Perubahan' : '➕ Tambah Menu' ?></button>
            <?php if ($data_edit): ?>
                <a href="menu.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabel Menu -->
    <div class="card">
        <h2>📋 Daftar Menu (<?= mysqli_num_rows($result_menu) ?> item)</h2>
        
        <!-- Filter -->
        <div style="margin-bottom:15px; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="menu.php" class="btn btn-sm <?= !$filter_kategori ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
            <?php foreach (['Makanan','Minuman','Snack','Dessert','Lainnya'] as $k): ?>
                <a href="menu.php?kategori=<?= $k ?>" class="btn btn-sm <?= $filter_kategori == $k ? 'btn-primary' : 'btn-secondary' ?>"><?= $k ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (mysqli_num_rows($result_menu) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result_menu)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['nama_menu']) ?></strong>
                        <?php if ($row['deskripsi']): ?>
                            <br><small style="color:#888"><?= htmlspecialchars($row['deskripsi']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['kategori'] ?></td>
                    <td class="harga">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td><?= $row['stok'] ?></td>
                    <td>
                        <span class="badge badge-<?= $row['status'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="menu.php?edit=<?= $row['id_menu'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                        <a href="menu.php?hapus=<?= $row['id_menu'] ?>" class="btn btn-danger btn-sm" 
                           onclick="return confirm('Hapus menu ini?')">🗑️ Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">🍽️</div>
                <p>Belum ada menu. Tambah menu pertama kamu!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'includes/koneksi.php';

$pesan = '';
if (isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $id_menu       = (int) $_POST['id_menu'];
    $nama_pelanggan= mysqli_real_escape_string($koneksi, trim($_POST['nama_pelanggan']));
    $meja          = (int) $_POST['nomor_meja'];
    $jumlah        = (int) $_POST['jumlah'];
    $catatan       = mysqli_real_escape_string($koneksi, trim($_POST['catatan']));

    $menu = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM daftar_menu WHERE id_menu=$id_menu AND status='tersedia'"));
    
    if (!$menu) {
        $pesan = ['type' => 'danger', 'teks' => '❌ Menu tidak ditemukan atau sudah habis!'];
    } elseif ($menu['stok'] < $jumlah) {
        $pesan = ['type' => 'danger', 'teks' => "❌ Stok tidak cukup! Stok tersisa: {$menu['stok']}"];
    } else {
        $subtotal = $menu['harga'] * $jumlah;
        $sql = "INSERT INTO pesanan (id_menu, nama_pelanggan, nomor_meja, jumlah, subtotal, catatan, status) 
                VALUES ($id_menu, '$nama_pelanggan', $meja, $jumlah, $subtotal, '$catatan', 'menunggu')";
        if (mysqli_query($koneksi, $sql)) {
            mysqli_query($koneksi, "UPDATE daftar_menu SET stok = stok - $jumlah WHERE id_menu=$id_menu");
            $pesan = ['type' => 'success', 'teks' => "✅ Pesanan berhasil ditambahkan! Subtotal: Rp " . number_format($subtotal, 0, ',', '.')];
        }
    }
}

if (isset($_GET['status']) && isset($_GET['id'])) {
    $id     = (int) $_GET['id'];
    $status = mysqli_real_escape_string($koneksi, $_GET['status']);
    $valid  = ['menunggu', 'diproses', 'selesai', 'batal'];
    if (in_array($status, $valid)) {
        mysqli_query($koneksi, "UPDATE pesanan SET status='$status' WHERE id_pesanan=$id");
        
        if ($status == 'batal') {
            $p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id_pesanan=$id"));
            mysqli_query($koneksi, "UPDATE daftar_menu SET stok = stok + {$p['jumlah']} WHERE id_menu={$p['id_menu']}");
        }
        $pesan = ['type' => 'success', 'teks' => "✅ Status pesanan diperbarui ke: $status"];
    }
}

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id_pesanan=$id"));
    if ($p && $p['status'] != 'batal') {
        mysqli_query($koneksi, "UPDATE daftar_menu SET stok = stok + {$p['jumlah']} WHERE id_menu={$p['id_menu']}");
    }
    mysqli_query($koneksi, "DELETE FROM pesanan WHERE id_pesanan=$id");
    $pesan = ['type' => 'success', 'teks' => '✅ Pesanan dihapus!'];
}

if (isset($_GET['bayar'])) {
    $id = (int) $_GET['bayar'];
    $p  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id_pesanan=$id AND status='selesai'"));
    if ($p) {
        $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_transaksi FROM transaksi WHERE id_pesanan=$id"));
        if (!$cek) {
            $total = $p['subtotal'];
            mysqli_query($koneksi, "INSERT INTO transaksi (id_pesanan, total_bayar, metode_bayar, status_bayar) 
                                    VALUES ($id, $total, 'tunai', 'belumlunas')");
            $pesan = ['type' => 'info', 'teks' => '💳 Transaksi dibuat! Silakan selesaikan pembayaran di halaman Transaksi.'];
        } else {
            $pesan = ['type' => 'danger', 'teks' => '⚠️ Transaksi untuk pesanan ini sudah ada!'];
        }
    }
}

$filter_status = isset($_GET['filter']) ? $_GET['filter'] : '';
$sql_pesanan = "SELECT p.*, m.nama_menu, m.harga FROM pesanan p 
                JOIN daftar_menu m ON p.id_menu = m.id_menu";
if ($filter_status) {
    $f = mysqli_real_escape_string($koneksi, $filter_status);
    $sql_pesanan .= " WHERE p.status='$f'";
}
$sql_pesanan .= " ORDER BY p.waktu_pesan DESC";
$result_pesanan = mysqli_query($koneksi, $sql_pesanan);

$result_menu = mysqli_query($koneksi, "SELECT * FROM daftar_menu WHERE status='tersedia' ORDER BY kategori, nama_menu");

require_once 'includes/header.php';
?>

<div class="container">
    <div class="page-title">
        <h1>📋 Manajemen Pesanan</h1>
        <p>Catat dan kelola pesanan pelanggan.</p>
    </div>

    <?php if ($pesan): ?>
        <div class="alert alert-<?= $pesan['type'] ?>"><?= $pesan['teks'] ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>➕ Tambah Pesanan Baru</h2>
        <form method="POST">
            <input type="hidden" name="aksi" value="tambah">
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" placeholder="Nama pelanggan..." required>
                </div>
                <div class="form-group">
                    <label>Nomor Meja</label>
                    <input type="number" name="nomor_meja" placeholder="Nomor meja" min="1" max="50" value="1" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Pilih Menu</label>
                    <select name="id_menu" required>
                        <option value="">-- Pilih Menu --</option>
                        <?php while ($m = mysqli_fetch_assoc($result_menu)): ?>
                            <option value="<?= $m['id_menu'] ?>">
                                <?= htmlspecialchars($m['nama_menu']) ?> — Rp <?= number_format($m['harga'], 0, ',', '.') ?> (stok: <?= $m['stok'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Porsi</label>
                    <input type="number" name="jumlah" value="1" min="1" required>
                </div>
            </div>
            <div class="form-group">
                <label>Catatan (opsional)</label>
                <input type="text" name="catatan" placeholder="Contoh: tidak pedas, tambah nasi...">
            </div>
            <button type="submit" class="btn btn-primary">📝 Tambah Pesanan</button>
        </form>
    </div>

    <div class="card">
        <h2>📑 Daftar Pesanan</h2>

        <div style="margin-bottom:15px; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="pesanan.php" class="btn btn-sm <?= !$filter_status ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
            <?php foreach (['menunggu','diproses','selesai','batal'] as $s): ?>
                <a href="pesanan.php?filter=<?= $s ?>" class="btn btn-sm <?= $filter_status == $s ? 'btn-primary' : 'btn-secondary' ?>">
                    <?= ucfirst($s) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (mysqli_num_rows($result_pesanan) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pelanggan</th>
                    <th>Menu</th>
                    <th>Meja</th>
                    <th>Jml</th>
                    <th>Subtotal</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result_pesanan)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong>
                        <?php if ($row['catatan']): ?>
                            <br><small style="color:#888">📝 <?= htmlspecialchars($row['catatan']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['nama_menu']) ?></td>
                    <td style="text-align:center">🪑 <?= $row['nomor_meja'] ?></td>
                    <td style="text-align:center"><?= $row['jumlah'] ?>x</td>
                    <td class="harga">Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                    <td><?= date('d/m H:i', strtotime($row['waktu_pesan'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                    </td>
                    <td>
                        <?php if ($row['status'] == 'menunggu'): ?>
                            <a href="pesanan.php?status=diproses&id=<?= $row['id_pesanan'] ?>" class="btn btn-warning btn-sm">🍳 Proses</a>
                        <?php endif; ?>
                        <?php if ($row['status'] == 'diproses'): ?>
                            <a href="pesanan.php?status=selesai&id=<?= $row['id_pesanan'] ?>" class="btn btn-success btn-sm">✅ Selesai</a>
                        <?php endif; ?>
                        <?php if ($row['status'] == 'selesai'): ?>
                            <a href="pesanan.php?bayar=<?= $row['id_pesanan'] ?>" class="btn btn-primary btn-sm">💳 Bayar</a>
                        <?php endif; ?>
                        <?php if (in_array($row['status'], ['menunggu','diproses'])): ?>
                            <a href="pesanan.php?status=batal&id=<?= $row['id_pesanan'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Batalkan pesanan ini?')">❌ Batal</a>
                        <?php endif; ?>
                        <a href="pesanan.php?hapus=<?= $row['id_pesanan'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Hapus pesanan ini?')">🗑️</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">📭</div>
                <p>Belum ada pesanan.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'includes/koneksi.php';

$pesan = '';

if (isset($_POST['aksi']) && $_POST['aksi'] == 'bayar') {
    $id_transaksi = (int) $_POST['id_transaksi'];
    $metode       = mysqli_real_escape_string($koneksi, $_POST['metode_bayar']);
    $dibayar      = (int) $_POST['jumlah_dibayar'];

    $t = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi=$id_transaksi"));
    
    if ($dibayar >= $t['total_bayar']) {
        $kembalian = $dibayar - $t['total_bayar'];
        $sql = "UPDATE transaksi SET metode_bayar='$metode', jumlah_dibayar=$dibayar, 
                kembalian=$kembalian, status_bayar='lunas', waktu_bayar=NOW() 
                WHERE id_transaksi=$id_transaksi";
        if (mysqli_query($koneksi, $sql)) {
            $pesan = ['type' => 'success', 'teks' => "✅ Pembayaran berhasil! Kembalian: Rp " . number_format($kembalian, 0, ',', '.')];
        }
    } else {
        $kurang = $t['total_bayar'] - $dibayar;
        $pesan = ['type' => 'danger', 'teks' => "❌ Uang kurang Rp " . number_format($kurang, 0, ',', '.') . "!"];
    }
}
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM transaksi WHERE id_transaksi=$id");
    $pesan = ['type' => 'success', 'teks' => '✅ Transaksi dihapus!'];
}

$data_bayar = null;
if (isset($_GET['bayar'])) {
    $id = (int) $_GET['bayar'];
    $data_bayar = mysqli_fetch_assoc(mysqli_query($koneksi, "
        SELECT t.*, p.nama_pelanggan, p.nomor_meja, m.nama_menu, p.jumlah
        FROM transaksi t
        JOIN pesanan p ON t.id_pesanan = p.id_pesanan
        JOIN daftar_menu m ON p.id_menu = m.id_menu
        WHERE t.id_transaksi=$id AND t.status_bayar='belumlunas'
    "));
}

$stats = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT 
        COUNT(*) as total_transaksi,
        SUM(CASE WHEN status_bayar='lunas' THEN total_bayar ELSE 0 END) as total_pendapatan,
        SUM(CASE WHEN status_bayar='belumlunas' THEN total_bayar ELSE 0 END) as total_pending,
        COUNT(CASE WHEN status_bayar='lunas' THEN 1 END) as jumlah_lunas
    FROM transaksi
"));

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$sql_trans = "SELECT t.*, p.nama_pelanggan, p.nomor_meja, m.nama_menu 
              FROM transaksi t
              JOIN pesanan p ON t.id_pesanan = p.id_pesanan
              JOIN daftar_menu m ON p.id_menu = m.id_menu";
if ($filter) {
    $f = mysqli_real_escape_string($koneksi, $filter);
    $sql_trans .= " WHERE t.status_bayar='$f'";
}
$sql_trans .= " ORDER BY t.waktu_transaksi DESC";
$result_trans = mysqli_query($koneksi, $sql_trans);

require_once 'includes/header.php';
?>

<div class="container">
    <div class="page-title">
        <h1>💰 Transaksi & Pembayaran</h1>
        <p>Kelola pembayaran dari pesanan yang sudah selesai.</p>
    </div>

    <?php if ($pesan): ?>
        <div class="alert alert-<?= $pesan['type'] ?>"><?= $pesan['teks'] ?></div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat-box">
            <div class="icon">🧾</div>
            <div class="label">Total Transaksi</div>
            <div class="value"><?= $stats['total_transaksi'] ?></div>
        </div>
        <div class="stat-box">
            <div class="icon">✅</div>
            <div class="label">Sudah Lunas</div>
            <div class="value hijau"><?= $stats['jumlah_lunas'] ?></div>
        </div>
        <div class="stat-box">
            <div class="icon">💵</div>
            <div class="label">Total Pendapatan</div>
            <div class="value hijau">Rp <?= number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.') ?></div>
        </div>
        <div class="stat-box">
            <div class="icon">⏳</div>
            <div class="label">Menunggu Bayar</div>
            <div class="value merah">Rp <?= number_format($stats['total_pending'] ?? 0, 0, ',', '.') ?></div>
        </div>
    </div>

    <?php if ($data_bayar): ?>
    <div class="card" style="border: 2px solid #e94560;">
        <h2>💳 Proses Pembayaran</h2>
        <div style="background:#f9f9f9; border-radius:8px; padding:15px; margin-bottom:15px;">
            <table style="width:auto; font-size:14px;">
                <tr><td style="padding:4px 15px 4px 0; color:#888">Pelanggan:</td><td><strong><?= htmlspecialchars($data_bayar['nama_pelanggan']) ?></strong></td></tr>
                <tr><td style="padding:4px 15px 4px 0; color:#888">Menu:</td><td><?= htmlspecialchars($data_bayar['nama_menu']) ?> × <?= $data_bayar['jumlah'] ?></td></tr>
                <tr><td style="padding:4px 15px 4px 0; color:#888">Meja:</td><td>🪑 <?= $data_bayar['nomor_meja'] ?></td></tr>
                <tr><td style="padding:4px 15px 4px 0; color:#888">Total Tagihan:</td><td><span class="harga" style="font-size:18px">Rp <?= number_format($data_bayar['total_bayar'], 0, ',', '.') ?></span></td></tr>
            </table>
        </div>
        <form method="POST">
            <input type="hidden" name="aksi" value="bayar">
            <input type="hidden" name="id_transaksi" value="<?= $data_bayar['id_transaksi'] ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="metode_bayar">
                        <option value="tunai">💵 Tunai</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                        <option value="qris">📱 QRIS</option>
                        <option value="kartu">💳 Kartu Debit/Kredit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Dibayar (Rp)</label>
                    <input type="number" name="jumlah_dibayar" 
                           value="<?= $data_bayar['total_bayar'] ?>" 
                           min="<?= $data_bayar['total_bayar'] ?>" required>
                </div>
            </div>
            <button type="submit" class="btn btn-success">✅ Konfirmasi Bayar</button>
            <a href="transaksi.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>🧾 Riwayat Transaksi</h2>

        <div style="margin-bottom:15px; display:flex; gap:10px;">
            <a href="transaksi.php" class="btn btn-sm <?= !$filter ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
            <a href="transaksi.php?filter=lunas" class="btn btn-sm <?= $filter=='lunas' ? 'btn-primary' : 'btn-secondary' ?>">✅ Lunas</a>
            <a href="transaksi.php?filter=belumlunas" class="btn btn-sm <?= $filter=='belumlunas' ? 'btn-primary' : 'btn-secondary' ?>">⏳ Belum Lunas</a>
        </div>

        <?php if (mysqli_num_rows($result_trans) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pelanggan</th>
                    <th>Menu</th>
                    <th>Meja</th>
                    <th>Total</th>
                    <th>Dibayar</th>
                    <th>Kembalian</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result_trans)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                    <td><?= htmlspecialchars($row['nama_menu']) ?></td>
                    <td style="text-align:center">🪑 <?= $row['nomor_meja'] ?></td>
                    <td class="harga">Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                    <td><?= $row['jumlah_dibayar'] ? 'Rp '.number_format($row['jumlah_dibayar'], 0, ',', '.') : '-' ?></td>
                    <td><?= $row['kembalian'] ? 'Rp '.number_format($row['kembalian'], 0, ',', '.') : '-' ?></td>
                    <td><?= $row['metode_bayar'] ?? '-' ?></td>
                    <td>
                        <span class="badge badge-<?= $row['status_bayar'] == 'lunas' ? 'lunas' : 'belumlunas' ?>">
                            <?= $row['status_bayar'] == 'lunas' ? '✅ Lunas' : '⏳ Belum Lunas' ?>
                        </span>
                    </td>
                    <td><?= $row['waktu_bayar'] ? date('d/m H:i', strtotime($row['waktu_bayar'])) : date('d/m H:i', strtotime($row['waktu_transaksi'])) ?></td>
                    <td>
                        <?php if ($row['status_bayar'] == 'belumlunas'): ?>
                            <a href="transaksi.php?bayar=<?= $row['id_transaksi'] ?>" class="btn btn-success btn-sm">💳 Bayar</a>
                        <?php endif; ?>
                        <a href="transaksi.php?hapus=<?= $row['id_transaksi'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Hapus transaksi ini?')">🗑️</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">🧾</div>
                <p>Belum ada transaksi.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

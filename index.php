<?php
require_once 'includes/koneksi.php';
require_once 'includes/header.php';

$total_menu     = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM daftar_menu"))['total'];
$total_pesanan  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan"))['total'];
$pesanan_aktif  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE status IN ('menunggu','diproses')"))['total'];
$pendapatan     = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_bayar) as total FROM transaksi WHERE status_bayar = 'lunas'"))['total'];
$pendapatan     = $pendapatan ?? 0;

$pesanan_baru = mysqli_query($koneksi, "
    SELECT p.*, m.nama_menu 
    FROM pesanan p 
    JOIN daftar_menu m ON p.id_menu = m.id_menu 
    ORDER BY p.waktu_pesan DESC 
    LIMIT 5
");
?>

<div class="container">
    <div class="page-title">
        <h1>📊 Dashboard</h1>
        <p>Selamat datang! Berikut ringkasan aktivitas restoran hari ini.</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="icon">🍜</div>
            <div class="label">Total Menu</div>
            <div class="value biru"><?= $total_menu ?></div>
        </div>
        <div class="stat-box">
            <div class="icon">📋</div>
            <div class="label">Total Pesanan</div>
            <div class="value"><?= $total_pesanan ?></div>
        </div>
        <div class="stat-box">
            <div class="icon">🔥</div>
            <div class="label">Pesanan Aktif</div>
            <div class="value merah"><?= $pesanan_aktif ?></div>
        </div>
        <div class="stat-box">
            <div class="icon">💰</div>
            <div class="label">Total Pendapatan</div>
            <div class="value hijau">Rp <?= number_format($pendapatan, 0, ',', '.') ?></div>
        </div>
    </div>

    <div class="card">
        <h2>🕐 5 Pesanan Terbaru</h2>
        <?php if (mysqli_num_rows($pesanan_baru) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Menu</th>
                    <th>Nama Pelanggan</th>
                    <th>Jumlah</th>
                    <th>Waktu</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($pesanan_baru)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_menu']) ?></td>
                    <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                    <td><?= $row['jumlah'] ?> porsi</td>
                    <td><?= date('d/m/Y H:i', strtotime($row['waktu_pesan'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $row['status'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
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
        <div style="margin-top:15px;">
            <a href="pesanan.php" class="btn btn-primary">Lihat Semua Pesanan →</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

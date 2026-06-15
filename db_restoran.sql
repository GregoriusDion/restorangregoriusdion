-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 15 Jun 2026 pada 04.08
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_restoran`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `daftar_menu`
--

CREATE TABLE `daftar_menu` (
  `id_menu` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `kategori` enum('Makanan','Minuman','Snack','Dessert','Lainnya') DEFAULT 'Makanan',
  `harga` int(11) NOT NULL DEFAULT 0,
  `stok` int(11) NOT NULL DEFAULT 0,
  `deskripsi` varchar(255) DEFAULT '',
  `status` enum('tersedia','habis') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `daftar_menu`
--

INSERT INTO `daftar_menu` (`id_menu`, `nama_menu`, `kategori`, `harga`, `stok`, `deskripsi`, `status`, `created_at`) VALUES
(1, 'Nasi Goreng Spesial', 'Makanan', 20000, 19, 'Nasi goreng dengan telur dan ayam', 'tersedia', '2026-06-08 09:16:53'),
(2, 'Mie Ayam Bakso', 'Makanan', 15000, 15, 'Mie ayam dengan bakso kenyal', 'tersedia', '2026-06-08 09:16:53'),
(3, 'Ayam Bakar', 'Makanan', 22000, 9, 'Ayam bakar bumbu kecap', 'tersedia', '2026-06-08 09:16:53'),
(4, 'Gado-Gado', 'Makanan', 13000, 12, 'Sayuran segar dengan bumbu kacang', 'tersedia', '2026-06-08 09:16:53'),
(5, 'Es Teh Manis', 'Minuman', 5000, 50, 'Teh manis dingin segar', 'tersedia', '2026-06-08 09:16:53'),
(6, 'Es Jeruk', 'Minuman', 7000, 40, 'Jeruk peras segar', 'tersedia', '2026-06-08 09:16:53'),
(7, 'Jus Alpukat', 'Minuman', 12000, 19, 'Jus alpukat dengan susu kental manis', 'tersedia', '2026-06-08 09:16:53'),
(8, 'Tempe Goreng', 'Snack', 7000, 30, 'Tempe goreng crispy', 'tersedia', '2026-06-08 09:16:53'),
(9, 'Pisang Goreng', 'Snack', 10000, 25, 'Pisang goreng crispy dengan madu', 'tersedia', '2026-06-08 09:16:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `nomor_meja` int(11) NOT NULL DEFAULT 1,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `subtotal` int(11) NOT NULL DEFAULT 0,
  `catatan` varchar(255) DEFAULT '',
  `status` enum('menunggu','diproses','selesai','batal') DEFAULT 'menunggu',
  `waktu_pesan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_menu`, `nama_pelanggan`, `nomor_meja`, `jumlah`, `subtotal`, `catatan`, `status`, `waktu_pesan`) VALUES
(1, 3, 'Robby', 1, 1, 22000, 'tidak ada', 'selesai', '2026-06-08 11:15:27'),
(2, 7, 'Budi', 1, 1, 12000, '', 'selesai', '2026-06-08 11:19:14'),
(3, 1, 'sadasda', 1, 1, 18000, '', 'selesai', '2026-06-08 11:20:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `total_bayar` int(11) NOT NULL DEFAULT 0,
  `jumlah_dibayar` int(11) DEFAULT NULL,
  `kembalian` int(11) DEFAULT 0,
  `metode_bayar` enum('tunai','transfer','qris','kartu') DEFAULT 'tunai',
  `status_bayar` enum('belumlunas','lunas') DEFAULT 'belumlunas',
  `waktu_transaksi` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu_bayar` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_pesanan`, `total_bayar`, `jumlah_dibayar`, `kembalian`, `metode_bayar`, `status_bayar`, `waktu_transaksi`, `waktu_bayar`) VALUES
(1, 1, 22000, 22000, 0, 'qris', 'lunas', '2026-06-08 11:16:03', '2026-06-08 11:16:12'),
(2, 2, 12000, 12000, 0, 'tunai', 'lunas', '2026-06-08 11:19:43', '2026-06-08 11:19:52'),
(3, 3, 18000, 18000, 0, 'qris', 'lunas', '2026-06-08 11:20:28', '2026-06-08 11:20:37');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `daftar_menu`
--
ALTER TABLE `daftar_menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_pesanan` (`id_pesanan`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `daftar_menu`
--
ALTER TABLE `daftar_menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `daftar_menu` (`id_menu`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

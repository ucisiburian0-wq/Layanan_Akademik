<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('Asia/Jayapura');

// Sesuaikan dengan pengaturan XAMPP/hosting Anda
const DB_HOST = 'localhost';
const DB_NAME = 'portal_akademik';
const DB_USER = 'root';
const DB_PASS = '';

const DAFTAR_STATUS = ['menunggu' => 'Menunggu', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'];

set_exception_handler(function (Throwable $t): void {
    http_response_code(500);
    error_log($t->getMessage());
    echo '<!doctype html><html lang="id"><meta charset="UTF-8"><title>Kesalahan</title>'
       . '<body style="font-family:sans-serif;padding:1.5rem"><h1>Layanan belum dapat dipakai</h1>'
       . '<p>Database tidak dapat dihubungi. Periksa MySQL aktif, database sudah diimpor, dan isi config.php.</p></body></html>';
});

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrf_input(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_cek(): bool { return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']); }
function kode_tiket(): string { return 'LA-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3))); }
function tgl(string $s): string { return date('d-m-Y H:i', strtotime($s)) . ' WIT'; }
function lencana(string $st): string {
    return '<span class="lencana lencana--' . e($st) . '">' . e(DAFTAR_STATUS[$st] ?? $st) . '</span>';
}
function wajib_admin(): void {
    if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
}

<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$app = require dirname(__DIR__) . '/config/app.php';
$menu = require dirname(__DIR__) . '/data/menu.php';
date_default_timezone_set($app['timezone']);

$raw = file_get_contents('php://input');
if ($raw === false || strlen($raw) > 100000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payload pesanan tidak valid.']);
    exit;
}

$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format JSON tidak valid.']);
    exit;
}

function clean_text(mixed $value, int $maxLength): string
{
    $text = trim(strip_tags((string) $value));
    return mb_substr($text, 0, $maxLength);
}

$customer = is_array($payload['customer'] ?? null) ? $payload['customer'] : [];
$name = clean_text($customer['name'] ?? '', 80);
$phone = clean_text($customer['phone'] ?? '', 30);
$address = clean_text($customer['address'] ?? '', 500);
$note = clean_text($customer['note'] ?? '', 500);
$orderType = in_array($payload['orderType'] ?? '', ['Pickup', 'Delivery'], true) ? $payload['orderType'] : 'Pickup';
$payment = in_array($payload['payment'] ?? '', ['Cash', 'QRIS'], true) ? $payload['payment'] : 'Cash';

if ($name === '' || $phone === '' || ($orderType === 'Delivery' && $address === '')) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Data pemesan belum lengkap.']);
    exit;
}

$menuIndex = [];
foreach ($menu as $product) $menuIndex[$product['id']] = $product;
$requestedItems = is_array($payload['items'] ?? null) ? $payload['items'] : [];
$orderItems = [];
$subtotal = 0;

foreach ($requestedItems as $requested) {
    if (!is_array($requested)) continue;
    $id = (string) ($requested['id'] ?? '');
    if (!isset($menuIndex[$id])) continue;
    $product = $menuIndex[$id];
    $size = ($requested['size'] ?? 'regular') === 'large' && $product['large'] ? 'large' : 'regular';
    $qty = max(1, min(50, (int) ($requested['qty'] ?? 1)));
    $price = $size === 'large' ? (int) $product['large'] : (int) $product['regular'];
    $lineSubtotal = $price * $qty;
    $subtotal += $lineSubtotal;
    $orderItems[] = [
        'id' => $id,
        'name' => $product['name'],
        'size' => $size,
        'sizeLabel' => $product['large'] ? ucfirst($size) : $product['category'],
        'qty' => $qty,
        'price' => $price,
        'subtotal' => $lineSubtotal,
        'note' => clean_text($requested['note'] ?? '', 200),
    ];
}

if (!$orderItems) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Keranjang pesanan kosong.']);
    exit;
}

$deliveryFee = $orderType === 'Delivery' ? (int) $app['delivery_fee'] : 0;
$order = [
    'code' => 'HRM-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2))),
    'createdAt' => date(DATE_ATOM),
    'customerName' => $name,
    'customerPhone' => $phone,
    'address' => $address,
    'note' => $note,
    'orderType' => $orderType,
    'payment' => $payment,
    'items' => $orderItems,
    'subtotal' => $subtotal,
    'deliveryFee' => $deliveryFee,
    'total' => $subtotal + $deliveryFee,
    'status' => 'new',
];

$storageDirectory = dirname(__DIR__) . '/storage';
if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0775, true) && !is_dir($storageDirectory)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Direktori penyimpanan tidak dapat dibuat.']);
    exit;
}

$storageFile = $storageDirectory . '/orders.json';
$handle = fopen($storageFile, 'c+');
if ($handle === false || !flock($handle, LOCK_EX)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Pesanan tidak dapat disimpan.']);
    exit;
}

$existing = stream_get_contents($handle);
$orders = $existing ? json_decode($existing, true) : [];
if (!is_array($orders)) $orders = [];
$orders[] = $order;
rewind($handle);
ftruncate($handle, 0);
fwrite($handle, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
fflush($handle);
flock($handle, LOCK_UN);
fclose($handle);

echo json_encode(['success' => true, 'order' => $order], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

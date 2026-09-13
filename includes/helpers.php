<?php

// แปลงข้อความให้ปลอดภัยก่อนแสดงผล HTML
function h(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// แปลงวันที่ ค.ศ. (จาก DB) เป็น พ.ศ. — ตามมาตรฐานข้อ 13 ของ plan.md
const THAI_MONTHS = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
];

function thai_date(?string $dateStr, string $format = 'long'): string
{
    if (!$dateStr) return '-';
    $ts = strtotime($dateStr);
    if ($ts === false) return '-';
    $d = (int)date('j', $ts);
    $m = (int)date('n', $ts);
    $y = (int)date('Y', $ts) + 543;
    if ($format === 'short') {
        return sprintf('%02d/%02d/%04d', $d, $m, $y);
    }
    return "{$d} " . THAI_MONTHS[$m] . " {$y}";
}

function thai_datetime(?string $dateStr): string
{
    if (!$dateStr) return '-';
    $ts = strtotime($dateStr);
    $time = date('H:i', $ts);
    return thai_date($dateStr) . ' เวลา ' . $time . ' น.';
}

function money($n): string
{
    return number_format((float)$n, 2);
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

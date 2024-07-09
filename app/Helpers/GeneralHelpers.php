<?php

use Carbon\Carbon;

// Set locale untuk Carbon agar menggunakan bahasa Indonesia
Carbon::setLocale('id');

// Format Tanggal
if (! function_exists('format_date')) {
    function format_date($date, $format = 'j F Y')
    {
        return Carbon::parse($date)->isoFormat($format);
    }
}

// Format Nama Bulan dalam Bahasa Indonesia
if (! function_exists('format_month_name')) {
    function format_month_name($monthNumber)
    {
        return Carbon::parse('2023-' . $monthNumber . '-01')->translatedFormat('F');
    }
}

// Format Nama Hari dalam Bahasa Indonesia
if (! function_exists('format_day_name')) {
    function format_day_name($dayNumber)
    {
        return Carbon::parse('next Monday')->addDays($dayNumber - 1)->translatedFormat('l');
    }
}

// Format Waktu Ago dalam Bahasa Indonesia
if (! function_exists('time_ago')) {
    function time_ago($datetime)
    {
        return Carbon::parse($datetime)->locale('id')->diffForHumans();
    }
}

// Format Mata Uang
if (! function_exists('format_rupiah')) {
    function format_rupiah($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

// Format Persentase
if (! function_exists('format_percentage')) {
    function format_percentage($number, $decimals = 2)
    {
        return number_format($number, $decimals) . '%';
    }
}

// Format Bytes
if (! function_exists('format_bytes')) {
    function format_bytes($bytes, $decimals = 2)
    {
        $size = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}

// tambahkan fungsi helper lainnya di sini sesuai kebutuhan


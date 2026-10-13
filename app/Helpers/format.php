<?php

if (!function_exists('rupiah')) {
    function rupiah(int|float|string $amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}

if (!function_exists('short_rupiah')) {
    function short_rupiah(int|float $amount): string
    {
        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 1, ',', '.') . 'jt';
        }
        if ($amount >= 1_000) {
            return number_format($amount / 1_000, 0, ',', '.') . 'rb';
        }
        return (string) (int) $amount;
    }
}

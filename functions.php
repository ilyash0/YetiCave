<?php

function format_price(int $amount): string
{
    $formatted = number_format($amount, 0, ",", " ");

    return $formatted . " ₽";
}

function sanitize_recursive(&$value): void
{
    if (is_array($value)) {
        foreach ($value as &$v) {
            sanitize_recursive($v);
        }
        unset($v);
    } elseif (is_scalar($value) || is_null($value)) {
        $value = htmlspecialchars((string)$value);
    }
}

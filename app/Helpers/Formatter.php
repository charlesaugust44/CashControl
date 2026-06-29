<?php

namespace App\Helpers;

class Formatter
{
    private string $locale;
    private string $currency;

    public function __construct(?string $locale = null, ?string $currency = null)
    {
        $this->locale = $locale ?? config('app.locale', 'en');
        $this->currency = $currency ?? config('app.currency', 'USD');
    }

    public function date($date): string
    {
        if (!$date) return '';

        if ($date instanceof \Carbon\Carbon) {
            return $date->translatedFormat('M j, Y');
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format('M j, Y');
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) return '';
        return date('M j, Y', $timestamp);
    }

    public function dateTime($date): string
    {
        if (!$date) return '';

        if ($date instanceof \Carbon\Carbon || $date instanceof \DateTimeInterface) {
            return $date->format('M j, Y g:i A');
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) return '';
        return date('M j, Y g:i A', $timestamp);
    }

    public function currency($amount): string
    {
        $amount = $amount ?? 0;
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'BRL' => 'R$',
        ];
        $symbol = $symbols[$this->currency] ?? $this->currency . ' ';
        return $symbol . number_format($amount, 2);
    }

    public function signal($value): string
    {
        if ($value > 0) return 'positive';
        if ($value < 0) return 'negative';
        return 'zero';
    }
}

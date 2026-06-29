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
        $symbol = $this->currencySymbol();
        return $symbol . $this->number($amount, 2);
    }

    public function currencySymbol(): string
    {
        $localeSymbols = [
            'pt_BR' => 'R$',
            'en' => '$',
        ];
        
        return $localeSymbols[$this->locale] ?? $this->currency;
    }

    public function number($value, int $decimals = 2): string
    {
        $value = $value ?? 0;
        $decimalSep = $this->decimalSeparator();
        $thousandsSep = $this->thousandsSeparator();
        return number_format($value, $decimals, $decimalSep, $thousandsSep);
    }

    public function decimalSeparator(): string
    {
        $separators = [
            'pt_BR' => ',',
            'en' => '.',
        ];
        
        return $separators[$this->locale] ?? '.';
    }

    public function thousandsSeparator(): string
    {
        $separators = [
            'pt_BR' => '.',
            'en' => ',',
        ];
        
        return $separators[$this->locale] ?? ',';
    }

    public function signal($value): string
    {
        if ($value > 0) return 'positive';
        if ($value < 0) return 'negative';
        return 'zero';
    }
}

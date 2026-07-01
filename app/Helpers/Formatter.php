<?php

namespace App\Helpers;

class Formatter
{
    private string $currency;

    public function __construct(?string $currency = null)
    {
        $this->currency = $currency ?? config('app.currency', 'USD');
    }

    private function locale(): string
    {
        return app()->getLocale();
    }

    private function dateFormat(): string
    {
        return match ($this->locale()) {
            'pt_BR' => 'j M Y',
            default => 'M j, Y',
        };
    }

    private function dateTimeFormat(): string
    {
        return match ($this->locale()) {
            'pt_BR' => 'j M Y H:i',
            default => 'M j, Y g:i A',
        };
    }

    private function monthFormat(): string
    {
        return 'M Y';
    }

    public function month($date): string
    {
        if (!$date) return '';

        if ($date instanceof \Carbon\Carbon) {
            return $date->translatedFormat($this->monthFormat());
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format($this->monthFormat());
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) return '';
        return date($this->monthFormat(), $timestamp);
    }

    public function date($date): string
    {
        if (!$date) return '';

        if ($date instanceof \Carbon\Carbon) {
            return $date->translatedFormat($this->dateFormat());
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format($this->dateFormat());
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) return '';
        return date($this->dateFormat(), $timestamp);
    }

    public function dateTime($date): string
    {
        if (!$date) return '';

        if ($date instanceof \Carbon\Carbon) {
            return $date->translatedFormat($this->dateTimeFormat());
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format($this->dateTimeFormat());
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) return '';
        return date($this->dateTimeFormat(), $timestamp);
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
        
        return $localeSymbols[$this->locale()] ?? $this->currency;
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
        
        return $separators[$this->locale()] ?? '.';
    }

    public function thousandsSeparator(): string
    {
        $separators = [
            'pt_BR' => '.',
            'en' => ',',
        ];
        
        return $separators[$this->locale()] ?? ',';
    }

    public function signal($value): string
    {
        if ($value > 0) return 'positive';
        if ($value < 0) return 'negative';
        return 'zero';
    }
}

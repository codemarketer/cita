<?php

namespace App\Traits;

trait MasksData
{
    protected function maskName(string $name): string
    {
        if (!$name) return '';
        if (strlen($name) <= 2) return $name;
        
        $words = explode(' ', $name);
        return implode(' ', array_map(function($word) {
            return $word[0] . str_repeat('*', max(1, strlen($word) - 2)) . $word[-1];
        }, $words));
    }

    protected function maskEmail(string $email): string
    {
        if (!$email) return '';
        [$localPart, $domain] = explode('@', $email);
        $maskedLocal = substr($localPart, 0, 2) . 
                      str_repeat('*', max(1, strlen($localPart) - 3)) . 
                      substr($localPart, -1);
        return $maskedLocal . '@' . $domain;
    }

    protected function maskPhone(string $phone): string
    {
        if (!$phone) return '';
        return str_repeat('*', strlen($phone) - 4) . substr($phone, -4);
    }
}

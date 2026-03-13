<?php

namespace App\Services;

class Base62Encoder
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const ID_OFFSET = 1746;

    public function encode(int $number): string
    {
        if ($number < 0) {
            throw new \InvalidArgumentException('Only positive integers can be encoded.');
        }

        $number += self::ID_OFFSET;

        $encoded = '';
        $base = strlen(self::ALPHABET);

        while ($number > 0) {
            $remainder = $number % $base;
            $encoded = self::ALPHABET[$remainder].$encoded;
            $number = intdiv($number, $base);
        }

        return $encoded;
    }
}

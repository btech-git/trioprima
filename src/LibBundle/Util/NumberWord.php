<?php

namespace LibBundle\Util;

class NumberWord
{
    public static function name($number)
    {
        if (!is_numeric($number)) {
            return '';
        }

        $parts = explode('.', strval($number));
        $integerPart = isset($parts[0]) ? $parts[0] : '0';
        $fractionPart = isset($parts[1]) ? $parts[1] : '0';

        $signName = self::signName($integerPart, $fractionPart);
        $wholeNumberName = self::wholeNumberName($integerPart);
        $decimalName = self::decimalName($fractionPart);

        return rtrim($signName . $wholeNumberName . $decimalName);
    }

    private static function signName($integerPart, $fractionPart)
    {
        if ($integerPart[0] === '-' && ((int) $integerPart !== 0 || (int) $fractionPart !== 0)) {
            $signName = 'minus ';
        } else {
            $signName = '';
        }

        return $signName;
    }

    private static function wholeNumberName($integerPart)
    {
        if ((int) $integerPart === 0) {
            $wholeNumberName = 'nol ';
        } else {
            $wholeNumberName = '';
            $wholeNumber = abs($integerPart);
            $counter = 0;
            do {
                $quotient = intval($wholeNumber / 1000);
                $remainder = intval($wholeNumber) % 1000;

                $prefix = ($counter === 1 && $remainder === 1) ? 'se' : self::hundredsName($remainder);
                $wholeNumberName = $prefix . $wholeNumberName;
                if (intval($quotient) % 1000 > 0) {
                    $wholeNumberName = self::thousandSeparatorName($counter) . $wholeNumberName;
                }

                $wholeNumber = $quotient;
                $counter++;
            } while ($wholeNumber > 0);
        }

        return $wholeNumberName;
    }

    private static function decimalName($fractionPart)
    {
        $decimalName = '';
        if ((int) $fractionPart !== 0) {
            $decimal = rtrim($fractionPart, '0');
            $digits = array('nol ', 'satu ');
            foreach (str_split($decimal) as $digit) {
                $unitName = isset($digits[$digit]) ? $digits[$digit] : self::unitName($digit);
                $decimalName .= $unitName;
            }
            $decimalName = 'koma ' . $decimalName;
        }

        return $decimalName;
    }

    private static function hundredsName($hundreds)
    {
        $hundred = intval($hundreds / 100);
        $tens = intval($hundreds) % 100;
        $ten = intval($tens / 10);
        $unit = intval($hundreds) % 10;

        $name = self::unitName($hundred) . ($hundred !== 0 ? 'ratus ' : '');
        if ($tens > 10 && $tens < 20) {
            $name .= self::unitName($unit) . 'belas ';
        } else {
            $name .= self::unitName($ten) . ($ten !== 0 ? 'puluh ' : '');
            $name .= ($unit === 1) ? 'satu ' : self::unitName($unit);
        }

        return $name;
    }

    private static function thousandSeparatorName($ordinal)
    {
        switch ($ordinal) {
            case 0: return 'ribu ';
            case 1: return 'juta ';
            case 2: return 'miliar ';
            case 3: return 'triliun ';
            case 4: return 'kuadriliun ';
            case 5: return 'kuintiliun ';
            default: return '';
        }
    }

    private static function unitName($unit)
    {
        switch ($unit) {
            case 1: return 'se';
            case 2: return 'dua ';
            case 3: return 'tiga ';
            case 4: return 'empat ';
            case 5: return 'lima ';
            case 6: return 'enam ';
            case 7: return 'tujuh ';
            case 8: return 'delapan ';
            case 9: return 'sembilan ';
            default: return '';
        }
    }
}

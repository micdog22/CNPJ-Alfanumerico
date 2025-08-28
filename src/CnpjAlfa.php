<?php
declare(strict_types=1);

namespace MicDog\CnpjAlfa;

/**
 * CNPJ alfanumérico (SEFAZ/Serpro): 12 caracteres alfanuméricos + 2 dígitos verificadores numéricos.
 * - Valor do caractere: ord(ch) - 48 (0..9 => 0..9; A..Z => 17..42)
 * - DV1: módulo 11 com pesos 2..9 aplicados da direita p/ esquerda nos 12 primeiros.
 * - DV2: repete o processo nos 13 caracteres (12 + DV1).
 *
 * Métodos principais:
 *  - normalize(string): string
 *  - computeDv(string $cnpj12): array{0:int,1:int}
 *  - validate(string): bool
 *  - format(string): string
 *
 * @author  Michael
 * @date    2025-08-28
 */
final class CnpjAlfa
{
    /**
     * Remove tudo que não é [A-Z0-9] e converte para maiúsculas.
     */
    public static function normalize(string $input): string
    {
        $u = strtoupper($input);
        return preg_replace('/[^A-Z0-9]/', '', $u) ?? '';
    }

    /**
     * Converte um caractere [0-9A-Z] no valor (ord - 48). Retorna null se inválido.
     */
    private static function charValue(string $ch): ?int
    {
        $ord = ord($ch);
        if ($ord >= 48 && $ord <= 57) return $ord - 48; // '0'..'9'
        if ($ord >= 65 && $ord <= 90) return $ord - 48; // 'A'..'Z'
        return null;
    }

    /**
     * Pesos 2..9 aplicados da direita para a esquerda (cíclico).
     */
    private static function rightWeights(int $length): array
    {
        $w = [];
        $p = 2;
        for ($i = 0; $i < $length; $i++) {
            $w[] = $p;
            $p = ($p === 9) ? 2 : $p + 1;
        }
        return array_reverse($w);
    }

    /**
     * Computa um DV (módulo 11) para a string base já normalizada.
     * @param string $body  12 chars (DV1) ou 13 chars (DV2)
     */
    private static function computeSingleDv(string $body): int
    {
        $len = strlen($body);
        $weights = self::rightWeights($len);

        $sum = 0;
        for ($i = 0; $i < $len; $i++) {
            $v = self::charValue($body[$i]);
            if ($v === null) {
                throw new \RuntimeException("Caractere inválido na posição $i.");
            }
            $sum += $v * $weights[$i];
        }

        $r = $sum % 11;
        return ($r === 0 || $r === 1) ? 0 : (11 - $r);
    }

    /**
     * Calcula os dois dígitos verificadores para os 12 primeiros caracteres alfanuméricos.
     * @return array{0:int,1:int} [DV1, DV2]
     */
    public static function computeDv(string $cnpj12): array
    {
        $n = self::normalize($cnpj12);
        if (strlen($n) !== 12) {
            throw new \InvalidArgumentException("Corpo do CNPJ deve ter 12 caracteres alfanuméricos.");
        }
        $dv1 = self::computeSingleDv($n);
        $dv2 = self::computeSingleDv($n . (string)$dv1);
        return [$dv1, $dv2];
    }

    /**
     * Valida um CNPJ alfanumérico completo (12 alfanuméricos + 2 dígitos).
     */
    public static function validate(string $input): bool
    {
        $n = self::normalize($input);
        if (strlen($n) !== 14) return false;

        $body = substr($n, 0, 12);
        $dv   = substr($n, 12, 2);

        if (!ctype_digit($dv)) return false;

        try {
            [$d1, $d2] = self::computeDv($body);
        } catch (\Throwable) {
            return false;
        }
        return $dv === ((string)$d1 . (string)$d2);
    }

    /**
     * Formata como XX.XXX.XXX/XXXX-YY (preserva letras no corpo).
     * Se não tiver 14 após normalize, retorna a normalizada.
     */
    public static function format(string $input): string
    {
        $n = self::normalize($input);
        if (strlen($n) !== 14) return $n;

        $p = [
            substr($n, 0, 2),
            substr($n, 2, 3),
            substr($n, 5, 3),
            substr($n, 8, 4),
            substr($n, 12, 2),
        ];
        return "{$p[0]}.{$p[1]}.{$p[2]}/{$p[3]}-{$p[4]}";
    }
}

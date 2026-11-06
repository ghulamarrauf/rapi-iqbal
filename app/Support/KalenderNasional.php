<?php

namespace App\Support;

/**
 * Pembaca data config/hari_libur.php untuk Kalender agenda.
 * Setiap item: ['name' => string, 'type' => 'Libur nasional'|'Cuti bersama'|'Hari besar nasional', 'red' => bool]
 * 'red' = tanggal merah (libur nasional / cuti bersama).
 */
class KalenderNasional
{
    /** @return array<int, array{name:string,type:string,red:bool}> item pada satu tanggal (Y-m-d), tanggal merah lebih dulu */
    public static function on(string $date): array
    {
        $items = [];

        if ($hit = config('hari_libur.libur.'.$date)) {
            $items[] = ['name' => $hit[0], 'type' => $hit[1], 'red' => true];
        }

        if ($name = config('hari_libur.besar.'.substr($date, 5))) {
            $items[] = ['name' => $name, 'type' => 'Hari besar nasional', 'red' => false];
        }

        return $items;
    }
}

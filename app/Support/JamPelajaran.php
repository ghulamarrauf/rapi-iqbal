<?php

namespace App\Support;

/**
 * Jam pelajaran ke-1 s/d ke-10.
 *
 * Database tetap menyimpan kolom TIME (start_time & end_time), jadi struktur
 * tabel events tidak berubah. Kelas ini yang menerjemahkan jam pelajaran
 * <-> jam di dinding. Ubah tabel JAM di bawah bila jadwal sekolah berbeda.
 */
class JamPelajaran
{
    /** Zona waktu sekolah (WIB). Dipakai untuk menentukan jam yang sudah lewat. */
    public const TIMEZONE = 'Asia/Jakarta';

    /** nomor jam ke => [mulai, selesai] */
    public const JAM = [
        1  => ['07:00', '07:45'],
        2  => ['07:45', '08:30'],
        3  => ['08:30', '09:15'],
        4  => ['09:15', '10:00'],
        // istirahat 10:00 - 10:15
        5  => ['10:15', '11:00'],
        6  => ['11:00', '11:45'],
        7  => ['11:45', '12:30'],
        // istirahat 12:30 - 13:00
        8  => ['13:00', '13:45'],
        9  => ['13:45', '14:30'],
        10 => ['14:30', '15:15'],
    ];

    public static function all(): array
    {
        return self::JAM;
    }

    public static function numbers(): array
    {
        return array_keys(self::JAM);
    }

    /** Jam mulai (HH:MM:SS) dari jam pelajaran ke-$no. */
    public static function startTime(int $no): string
    {
        return self::JAM[$no][0].':00';
    }

    /** Jam selesai (HH:MM:SS) dari jam pelajaran ke-$no. */
    public static function endTime(int $no): string
    {
        return self::JAM[$no][1].':00';
    }

    /** Label pilihan, mis. "Jam ke-3 (08:30 - 09:15)". */
    public static function label(int $no): string
    {
        return 'Jam ke-'.$no.' ('.self::JAM[$no][0].' - '.self::JAM[$no][1].')';
    }

    /**
     * Jam pelajaran tempat sebuah jam MULAI berada. Jika jatuh di waktu istirahat
     * dipakai jam pelajaran berikutnya (data lama yang belum sesuai jam pelajaran).
     */
    public static function fromStart(?string $time): ?int
    {
        if (! $time) {
            return null;
        }
        $t = substr($time, 0, 5);

        foreach (self::JAM as $no => [$s, $e]) {
            if ($t < $e) {
                return $no;
            }
        }

        return array_key_last(self::JAM);
    }

    /**
     * Jam pelajaran tempat sebuah jam SELESAI berada. Jika jatuh di waktu istirahat
     * dipakai jam pelajaran sebelumnya.
     */
    public static function fromEnd(?string $time): ?int
    {
        if (! $time) {
            return null;
        }
        $t = substr($time, 0, 5);
        $result = array_key_first(self::JAM);

        foreach (self::JAM as $no => [$s, $e]) {
            if ($t > $s) {
                $result = $no;
            }
        }

        return $result;
    }

    /** Tanggal hari ini menurut zona waktu sekolah (Y-m-d). */
    public static function today(): string
    {
        return now(self::TIMEZONE)->toDateString();
    }

    /**
     * Apakah jam pelajaran ke-$no pada tanggal tertentu sudah berakhir (sudah lewat)?
     * Jam yang sedang berlangsung masih boleh dipilih.
     */
    public static function hasEnded(int $no, string $date): bool
    {
        $today = self::today();

        if ($date < $today) {
            return true;
        }
        if ($date > $today) {
            return false;
        }

        return now(self::TIMEZONE)->format('H:i') >= self::JAM[$no][1];
    }

    /** Teks rentang, mis. "Jam ke-2 s/d ke-4" atau "Jam ke-3". */
    public static function range(int $from, int $to): string
    {
        return $from === $to ? 'Jam ke-'.$from : 'Jam ke-'.$from.' s/d ke-'.$to;
    }
}

<?php

/**
 * Data kalender nasional untuk Kalender agenda.
 *
 * 'libur'  : hari libur nasional & cuti bersama (SKB 3 Menteri). Tanggal merah.
 * 'besar'  : hari besar nasional yang berulang tiap tahun (bukan tanggal merah).
 *
 * Sumber 2026: SKB Menag 1497/2025, Menaker 2/2025, MenPANRB 5/2025.
 * Sumber 2027: SKB Menag 1205/2026, Menaker 3/2026, MenPANRB 2/2026 (ditetapkan 15 September 2026).
 * Untuk tahun berikutnya, tambahkan baris baru di 'libur' setelah SKB resmi terbit.
 */
return [
    'libur' => [
        // ---- 2026 ----
        '2026-01-01' => ['Tahun Baru 2026 Masehi', 'Libur nasional'],
        '2026-01-16' => ['Isra Mikraj Nabi Muhammad saw.', 'Libur nasional'],
        '2026-02-16' => ['Cuti bersama Tahun Baru Imlek', 'Cuti bersama'],
        '2026-02-17' => ['Tahun Baru Imlek 2577 Kongzili', 'Libur nasional'],
        '2026-03-18' => ['Cuti bersama Hari Suci Nyepi', 'Cuti bersama'],
        '2026-03-19' => ['Hari Suci Nyepi (Tahun Baru Saka 1948)', 'Libur nasional'],
        '2026-03-20' => ['Cuti bersama Idulfitri', 'Cuti bersama'],
        '2026-03-21' => ['Idulfitri 1447 H', 'Libur nasional'],
        '2026-03-22' => ['Idulfitri 1447 H', 'Libur nasional'],
        '2026-03-23' => ['Cuti bersama Idulfitri', 'Cuti bersama'],
        '2026-03-24' => ['Cuti bersama Idulfitri', 'Cuti bersama'],
        '2026-04-03' => ['Wafat Yesus Kristus', 'Libur nasional'],
        '2026-04-05' => ['Kebangkitan Yesus Kristus (Paskah)', 'Libur nasional'],
        '2026-05-01' => ['Hari Buruh Internasional', 'Libur nasional'],
        '2026-05-14' => ['Kenaikan Yesus Kristus', 'Libur nasional'],
        '2026-05-15' => ['Cuti bersama Kenaikan Yesus Kristus', 'Cuti bersama'],
        '2026-05-27' => ['Iduladha 1447 H', 'Libur nasional'],
        '2026-05-28' => ['Cuti bersama Iduladha', 'Cuti bersama'],
        '2026-05-31' => ['Hari Raya Waisak 2570 BE', 'Libur nasional'],
        '2026-06-01' => ['Hari Lahir Pancasila', 'Libur nasional'],
        '2026-06-16' => ['Tahun Baru Islam 1448 H', 'Libur nasional'],
        '2026-08-17' => ['Proklamasi Kemerdekaan RI', 'Libur nasional'],
        '2026-08-25' => ['Maulid Nabi Muhammad saw.', 'Libur nasional'],
        '2026-12-24' => ['Cuti bersama Natal', 'Cuti bersama'],
        '2026-12-25' => ['Kelahiran Yesus Kristus (Natal)', 'Libur nasional'],

        // ---- 2027 ----
        '2027-01-01' => ['Tahun Baru 2027 Masehi', 'Libur nasional'],
        '2027-01-05' => ['Isra Mikraj Nabi Muhammad saw.', 'Libur nasional'],
        '2027-02-05' => ['Cuti bersama Tahun Baru Imlek', 'Cuti bersama'],
        '2027-02-06' => ['Tahun Baru Imlek 2578 Kongzili', 'Libur nasional'],
        '2027-03-08' => ['Hari Suci Nyepi (Tahun Baru Saka 1949)', 'Libur nasional'],
        '2027-03-09' => ['Cuti bersama Idulfitri', 'Cuti bersama'],
        '2027-03-10' => ['Idulfitri 1448 H', 'Libur nasional'],
        '2027-03-11' => ['Idulfitri 1448 H', 'Libur nasional'],
        '2027-03-12' => ['Cuti bersama Idulfitri', 'Cuti bersama'],
        '2027-03-15' => ['Cuti bersama Idulfitri', 'Cuti bersama'],
        '2027-03-25' => ['Cuti bersama Wafat Yesus Kristus', 'Cuti bersama'],
        '2027-03-26' => ['Wafat Yesus Kristus', 'Libur nasional'],
        '2027-03-28' => ['Kebangkitan Yesus Kristus (Paskah)', 'Libur nasional'],
        '2027-05-01' => ['Hari Buruh Internasional', 'Libur nasional'],
        '2027-05-06' => ['Kenaikan Yesus Kristus', 'Libur nasional'],
        '2027-05-17' => ['Iduladha 1448 H', 'Libur nasional'],
        '2027-05-18' => ['Cuti bersama Iduladha', 'Cuti bersama'],
        '2027-05-19' => ['Cuti bersama Waisak', 'Cuti bersama'],
        '2027-05-20' => ['Hari Raya Waisak 2571 BE', 'Libur nasional'],
        '2027-06-01' => ['Hari Lahir Pancasila', 'Libur nasional'],
        '2027-06-06' => ['Tahun Baru Islam 1449 H', 'Libur nasional'],
        '2027-08-15' => ['Maulid Nabi Muhammad saw.', 'Libur nasional'],
        '2027-08-17' => ['Proklamasi Kemerdekaan RI', 'Libur nasional'],
        '2027-12-24' => ['Cuti bersama Natal', 'Cuti bersama'],
        '2027-12-25' => ['Kelahiran Yesus Kristus (Natal)', 'Libur nasional'],
        '2027-12-26' => ['Isra Mikraj Nabi Muhammad saw.', 'Libur nasional'],
    ],

    // 'bulan-hari' => nama (berulang tiap tahun)
    'besar' => [
        '04-21' => 'Hari Kartini',
        '05-02' => 'Hari Pendidikan Nasional',
        '05-20' => 'Hari Kebangkitan Nasional',
        '10-02' => 'Hari Batik Nasional',
        '10-28' => 'Hari Sumpah Pemuda',
        '11-10' => 'Hari Pahlawan',
        '11-25' => 'Hari Guru Nasional',
        '12-22' => 'Hari Ibu',
    ],
];

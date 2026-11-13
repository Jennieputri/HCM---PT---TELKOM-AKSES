<?php

return [
    'nik_manager'        => env('ABSEN_NIK_MANAGER'),
    'kota_surat'         => env('ABSEN_KOTA_SURAT', 'Palangkaraya'),
    'jatah_cuti_tahunan' => (int) env('ABSEN_JATAH_CUTI_TAHUNAN', 12),
];

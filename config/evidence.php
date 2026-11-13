<?php

// Aturan unggah barang bukti. Ubah lewat .env tanpa menyentuh kode.
return [
    // Batas ukuran berkas dalam KB. Ingat: upload_max_filesize dan post_max_size di php.ini juga harus cukup besar.
    'max_kb' => (int) env('EVIDENCE_MAX_KB', 10240),

    // Ekstensi yang diizinkan. Tipe asli berkas (isi) juga dicek lewat aturan "mimes".
    'file_types' => ['pdf', 'jpg', 'jpeg', 'png', 'log', 'txt', 'pcap'],
    'photo_types' => ['jpg', 'jpeg', 'png'],
];

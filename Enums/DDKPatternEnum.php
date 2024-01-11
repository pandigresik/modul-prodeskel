<?php

namespace Modules\Prodeskel\Enums;

defined('BASEPATH') || exit('No direct script access allowed');

// NOTE : Digunakan untuk replace data pilihan di RTF
class DDKPatternEnum
{
    const KODE_TEXT  = 't[*]';
    const KODE_VALUE = 'v[*]';

    const SUMBER_AIR_MINUM_BAIK     = 'v[*]1';
    const SUMBER_AIR_MINUM_BERASA   = 'v[*]2';
    const SUMBER_AIR_MINUM_BERWARNA = 'v[*]3';
    const SUMBER_AIR_MINUM_BERBAU   = 'v[*]4';

    const KEPEMILIKAN_LAHAN_KURANG_05      = 'v[*]1';
    const KEPEMILIKAN_LAHAN_ANTARA_05_1    = 'v[*]2';
    const KEPEMILIKAN_LAHAN_LEBIH_1        = 'v[*]3';
    const KEPEMILIKAN_LAHAN_TIDAK_MEMILIKI = 'v[*]4';

    const PRODUKSI_TAHUN_INI_JUMLAH_POHON    = 'prod_j[*]';
    const PRODUKSI_TAHUN_INI_LUAS_PANEN      = 'prod_l[*]';
    const PRODUKSI_TAHUN_INI_PRODUKSI        = 'prod_n[*]';
    const PRODUKSI_TAHUN_INI_SATUAN          = 'prod_s[*]';
    const PRODUKSI_TAHUN_INI_PEMASARAN_HASIL = 'prod_p[*]';

    const PRODUKSI_BAHAN_GALIAN_PRODUKSI        = 'bg_n[*]';
    const PRODUKSI_BAHAN_GALIAN_MILIK_ADAT      = 'bg_m[*]';
    const PRODUKSI_BAHAN_GALIAN_PERORANGAN      = 'bg_o[*]';
    const PRODUKSI_BAHAN_GALIAN_PEMASARAN_HASIL = 'bg_p[*]';
}
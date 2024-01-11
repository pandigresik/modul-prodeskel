<?php

namespace Modules\Prodeskel\Services;

use Modules\Prodeskel\Enums\DDKEnum;
use Illuminate\Support\Collection;
use Modules\Prodeskel\Models\ProdeskelCustomValue;
use Modules\Prodeskel\Enums\DDKPatternEnum;
use Modules\Prodeskel\Enums\DDKMaxCustomDataPilihanEnum;
use Modules\Prodeskel\Enums\DDKPilihanProduksiTahunIniEnum;

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * NOTE : Template HTML digunakan untuk membantu dalam menyesuaikan dokumen asli menjadi template RTF + kode (tinggal copas kode)
 */
class ProdeskelDDKTemplateHtmlServices
{
    // NOTE : [Sumber Air Minum, Kepemilikan Lahan, Produksi Tahun Ini,
    // Aset Perumahan, Kualitas Persalinan dalam Keluarga,
    // Cacat Menurut Jenis, Produksi Bahan Galian Anggota Keluarga] berbeda struktur tabel

    public static function buatTabelDatanyaDibagi(int $bagi = 2, string $kode_unik, array $values, array $key_data, array $header_3_col = []) : string
    {
        $table = '<table class="text-8" style="border-collapse: collapse; width: 100%;" border="1">';
        if ($header_3_col !== []) {
            $table .= '<tr>';
            for ($i = 0; $i < $bagi; $i++) {
                foreach ($header_3_col as $item) {
                    $table .= '<td><strong>' . $item . '</strong></td>';
                }
            }
            $table .= '</tr>';
        }
        $values = array_values($values);
        for ($i = 0; $i < ceil(count($values) / $bagi); $i++) {
            $table .= '<tr>';

            for ($j = 0; $j < $bagi; $j++) {
                $urutan = $i + count($values) / $bagi * $j;
                if(is_array($values[ceil($urutan)])){
                    $text = $values[ceil($urutan)]['text'];
                }else{
                    $text = $values[ceil($urutan)];
                }
                $key = $kode_unik . '_' . $key_data[ceil($urutan)];
                $kode_nama = str_replace('*', $key, DDKPatternEnum::KODE_TEXT);
                $key = $kode_unik . '_' . $key_data[ceil($urutan)];
                $kode_value = str_replace('*', $key, DDKPatternEnum::KODE_VALUE);
                if (isset($values[$urutan])) {
                    $table .= '<td style="text-align: center;">' . ceil($urutan + 1) . '</td>';
                    $table .= '<td>' . ((isset($text) && $text != '') ? $text : $kode_nama) . '</td>';
                    $table .= '<td>&nbsp;' . $kode_value . '&nbsp;</td>';
                } else {
                    $table .= '<td></td><td></td><td></td>';
                }
            }

            $table .= '</tr>';
        }
        return $table .= '</table>';
    }

    private static function appendTDTextAndKodeToTable(&$table, array $array, $key)
    {
        $kode_nama = str_replace('*', $key, DDKPatternEnum::KODE_TEXT);
        $kode_value = str_replace('*', $key, DDKPatternEnum::KODE_VALUE);
        $text = current(array_slice($array, 0, 1));
        if(is_array($text)){
            $text = $text['text'];
        }
        $text = ((isset($text) && $text != '') ? $text : $kode_nama);
        $table .=  '<td>' . (count($array) > 0 ? $text : $kode_nama) . '</td>';
        $table .=  '<td>' . (count($array) > 0 ? $kode_value : $key) . '</td>';
    }

    public static function templateKeluarga() : string
    {
        $page1 = '<p style="text-align: center; "><span style="font-size: 18pt; font-family: arial, helvetica, sans-serif;"><strong>DAFTAR ISIAN <br />DATA DASAR KELUARGA</strong></span></p>
            <hr />
            <table style="border-collapse: collapse; width: 100%;" border="0">
            <tbody>
            <tr style="">
            <td style="">Nomor Kartu Keluarga</td>
            <td style="">: [no_kk]</td>
            </tr>
            <tr style="">
            <td style="">Nama Kepala Keluarga</td>
            <td style="">: [NaMa_kk]</td>
            </tr>
            <tr style="">
            <td style="">Alamat</td>
            <td style="">: [Alamat_kk]</td>
            </tr>
            <tr style="">
            <td style="">RT/RW</td>
            <td style="">: [Rt_rw_kk]</td>
            </tr>
            <tr style="">
            <td style="">Dusun/Lingkungan</td>
            <td style="">: [Dusun]</td>
            </tr>
            <tr style="">
            <td style="">Desa/Kelurahan</td>
            <td style="">: [Desa_kel]</td>
            </tr>
            <tr style="">
            <td style="">Kecamatan</td>
            <td style="">: [Kec]</td>
            </tr>
            <tr style="">
            <td style="">Kabupaten/Kota</td>
            <td style="">: [Kab_kota]</td>
            </tr>
            <tr style="">
            <td style="">Provinsi</td>
            <td style="">: [Prov]</td>
            </tr>
            <tr style="">
            <td style="">Bulan</td>
            <td style="">: [Bulan]</td>
            </tr>
            <tr style="">
            <td style="">Tahun</td>
            <td style="">: [Tahun]</td>
            </tr>
            <tr style="">
            <td style=""></td>
            <td style=""></td>
            </tr>
            <tr style="">
            <td style="">Nama pengisi</td>
            <td style="">: [NaMa_pengisi]</td>
            </tr>
            <tr style="">
            <td style="">Pekerjaan</td>
            <td style="">: [Pekerjaan_pengisi]</td>
            </tr>
            <tr style="">
            <td style="">Jabatan</td>
            <td style="">: [Jabatan_pengisi]</td>
            </tr>
            </tbody>
            </table>
            <p><strong><span style="font-size: 10pt;">SUMBER DATA UNTUK MENGISI DATA DASAR KELUARGA</span></strong><span style="font-size: 10pt;"><br /></span></p>
            <ol>
            <li style="">[Sumber_data_1]</li>
            <li>[Sumber_data_2]</li>
            <li>[Sumber_data_3]</li>
            <li>[Sumber_data_4]</li>
            </ol>
            <table style="border-collapse: collapse; width: 27.2074%; height: 137.922px;" border="0">
            <tbody>
            <tr style="height: 21.875px;">
            <td style="width: 100%; text-align: left; height: 21.875px;">Kepala Keluarga</td>
            </tr>
            <tr style="height: 23px;">
            <td style="width: 100%; text-align: left; height: 23px;"><em>Nama &amp; Tanda Tangan</em></td>
            </tr>
            <tr style="height: 64.0469px;">
            <td style="width: 100%; text-align: left; height: 64.0469px;"></td>
            </tr>
            <tr style="height: 29px;">
            <td style="width: 100%; text-align: left; height: 29px;">[NaMa_kk]</td>
            </tr>
            </tbody>
            </table>';

        return  $page1 . self::dataKeluarga();
    }

    public static function dataKeluarga() : string
    {
        $semua_kode = array_keys(DDKMaxCustomDataPilihanEnum::semua());
        $custom_value = ProdeskelCustomValue::where('kategori', DDKEnum::KATEGORI)
            ->whereIn('kode_value', $semua_kode)
            ->get();

        $html = //'<style>p.list{margin:3px 0 0px 0;font-size:8pt;}.text-8{font-size:8pt;}.text-center{text-align:center}</style>
            '<!-- pagebreak -->
            <p class="list">1. DATA KELUARGA</p>
            <p class="list">1.1 Jumlah Penghasilan Perbulan: '. str_replace('*', DDKPatternEnum::KODE_VALUE, DDKEnum::KODE_JUMLAH_PENGHASILAN_PERBULAN) .'</p>
            <p class="list">1.2 Jumlah Pengeluaran Perbulan : '. str_replace('*', DDKPatternEnum::KODE_VALUE, DDKEnum::KODE_JUMLAH_PENGELUARAN_PERBULAN) .'</p>'
            . '';
        $values = ProdeskelDDKPilihanServices::statusKepemilikanRumah($custom_value, false);
        $html  .= '<p class="list">1.3 Status Kepemilikan Rumah</p>'
            . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_STATUS_KEPEMILIKAN_RUMAH, $values, array_keys($values));

        $values = ProdeskelDDKPilihanServices::sumberAirMinum($custom_value, false);
        $html .= '<p class="list">1.4 Sumber Air Minum yang digunakan anggota keluarga</p>'
            . self::sumberAirMinum($values);

        $values = ProdeskelDDKPilihanServices::kepemilikanLahan($custom_value, false);
        $html .=  '<p class="list">1.5 Kepemilikan Lahan</p>'
            . self::kepemilikanLahan($values);

        $values = ProdeskelDDKPilihanServices::produksiTahunIni($custom_value, false);
        $html .=  '<p class="list">1.6 Produksi tahun ini</p>'
            . self::produksiTahunIni($values);

        $values = ProdeskelDDKPilihanServices::kepemilikanJenisTernak($custom_value, false);
        $html .=  '<p class="list">1.7 Kepemilikan Jenis Ternak Keluarga Tahun ini</p>'
            . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI, $values, array_keys($values), ['No', 'Jenis Binatang Ternak', 'Jenis Binatang Ternak']);

        $values = ProdeskelDDKPilihanServices::alatProduksiBudidayaIkan($custom_value, false);
        $html .=  '<p class="list">1.8 Alat produksi budidaya ikan</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN, $values, array_keys($values), ['No', 'Nama Alat', 'Jumlah']);

        $values = ProdeskelDDKPilihanServices::pemanfaatanDanauSungaiWadukSituMataAir($custom_value, false);
        $html .=  '<p class="list">1.9 Pemanfaatan Danau/Sungai/Waduk/situ/Mata Air oleh Keluarga</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA, $values, array_keys($values));

        $values = ProdeskelDDKPilihanServices::lembagaPendidikan($custom_value, false);
        $html .=  '<p class="list">1.10 Lembaga Pendidikan Yang Dimiliki Keluarga/Komunitas</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::penguasaanAsetTanah($custom_value, false);
        $html .=  '<p class="list">1.11 Penguasaan Aset Tanah oleh Keluarga</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::asetSaranaTransportasiUmum($custom_value, false);
        $html .=  '<p class="list">1.12 Aset Sarana Transportasi Umum</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_ASET_SARANA_TRANSPORTASI_UMUM, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::asetSaranaProduksi($custom_value, false);
        $html .=  '<p class="list">1.13 Aset Sarana Produksi</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_ASET_SARANA_PRODUKSI, $values, array_keys($values));
        $html .=  '<p class="list">1.14 Aset Perumahan</p>'
        . self::asetPerumahan($custom_value, false);
        $values = ProdeskelDDKPilihanServices::asetLainnya($custom_value, false);
        $html .=  '<p class="list">1.15 Aset Lainnya dalam Keluarga</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_ASET_LAINNYA, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::kualitasIbuHamil($custom_value, false);
        $html .=  '<p class="list">1.16 Kualitas Ibu Hamil dalam Keluarga (jika ada/pernah ada ibu hamil/nifas)</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_KUALITAS_IBU_HAMIL, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::kualitasBayi($custom_value, false);
        $html .=  '<p class="list">1.17	Kualitas Bayi dalam Keluarga (jika ada/pernah ada bayi)</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_KUALITAS_BAYI, $values, array_keys($values));
        $html .=  '<p class="list">1.18 Kualitas Persalinan dalam Keluarga (jika ada/pernah ada)</p>'
        . self::kualitasPersalinan($custom_value);
        $values = ProdeskelDDKPilihanServices::cakupanImunisasi($custom_value, false);
        $html .=  '<p class="list">1.19 Cakupan Imunisasi</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_CAKUPAN_IMUNISASI, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::penderitaSakitKelainan($custom_value, false);
        $html .=  '<p class="list">1.20 Penderita Sakit dan Kelainan dalam Keluarga (jika ada/pernah)</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_PENDERITA_SAKIT_DAN_KELAINAN, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::perilakuHidupBersihSehat($custom_value, false);
        $html .=  '<p class="list">1.21 Perilaku hidup bersih dan sehat dalam Keluarga</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_PERILAKU_HIDUP_BERSIH_SEHAT, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::polaMakanKeluarga($custom_value, false);
        $html .=  '<p class="list">1.22 Pola makan Keluarga</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_POLA_MAKAN_KELUARGA, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::kebiasaanBerobatBilaSakit($custom_value, false);
        $html .=  '<p class="list">1.23 Kebiasaan berobat bila sakit dalam keluarga</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_KEBIASAAN_BEROBAT_BILA_SAKIT, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::statusGiziBalita($custom_value, false);
        $html .=  '<p class="list">1.24 Status Gizi Balita dalam Keluarga</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_STATUS_GIZI_BALITA, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::jenisPenyakitAnggotaKeluaga($custom_value, false);
        $html .=  '<p class="list">1.25 Jenis Penyakit yang diderita Anggota Keluarga</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::kerukunan($custom_value, false);
        $html .=  '<p class="list">1.26 Kerukunan</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_KERUKUNAN, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::perkelahian($custom_value, false);
        $html .=  '<p class="list">1.27 Perkelahian</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_PERKELAHIAN, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::pencurian($custom_value, false);
        $html .=  '<p class="list">1.28 Pencurian</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_PENCURIAN, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::penjarahan($custom_value, false);
        $html .=  '<p class="list">1.29 Penjarahan</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_PENJARAHAN, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::perjudian($custom_value, false);
        $html .=  '<p class="list">1.30 Perjudian</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_PERJUDIAN, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::pemakaianMirasDanNarkoba($custom_value, false);
        $html .=  '<p class="list">1.31 Pemakaian Miras dan Narkoba</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_PEMAKAIAN_MIRAS_DAN_NARKOBA, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::pembunuhan($custom_value, false);
        $html .=  '<p class="list">1.32 Pembunuhan</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_PEMBUNUHAN, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::penculikan($custom_value, false);
        $html .=  '<p class="list">1.33 Penculikan</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_PENCULIKAN, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::kejahatanSeksual($custom_value, false);
        $html .=  '<p class="list">1.34 Kejahatan seksual</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_KEJAHATAN_SEKSUAL, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::kekerasanDalamKeluargaAtauRumahTangga($custom_value, false);
        $html .=  '<p class="list">1.35 Kekerasan Dalam Keluarga/Rumah Tangga</p>'
        . self::buatTabelDatanyaDibagi(1, DDKEnum::KODE_KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::masalahKesejahteraanKeluarga($custom_value, false);
        $html .=  '<p class="list">1.36 Masalah Kesejahteraan Keluarga</p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA, $values, array_keys($values));
        return $html;
    }

    public static function templateAnggota() : string
    {
        $semua_kode = array_keys(DDKMaxCustomDataPilihanEnum::semua());
        $custom_value = ProdeskelCustomValue::where('kategori', DDKEnum::KATEGORI)
            ->whereIn('kode_value', $semua_kode)
            ->get();

        $html = '<!-- pagebreak -->
            <p class="list">2. DATA ANGGOTA KELUARGA</p>
            <p class="list">2.1 Biodata</p>
            <table style="border-collapse: collapse; width: 100%;" border="0">
                <tr>
                    <td>2.1.1</td>
                    <td>Nomor Urut	:</td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_NO_URUT, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.2</td>
                    <td>Nomor Induk Kependudukan (NIK)	:</td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_NIK, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.3</td>
                    <td>Nama Lengkap 	:</td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_NAMA_LENGKAP, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.4</td>
                    <td>Nomor Akte Kelahiran	:</td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_AKTE_KELAHIRAN, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.5</td>
                    <td>Jenis Kelamin 	:  </td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_JENIS_KELAMIN, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.6</td>
                    <td>Hubungan dengan Kepala Keluarga	: </td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_HUB_KK, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.7</td>
                    <td>Tempat Lahir	:</td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_TEMPAT_LAHIR, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.8</td>
                    <td>Tanggal Lahir	:</td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_TANGGAL_LAHIR, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.9</td>
                    <td>Tanggal Pencatatan	:</td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_TANGGAL_PENCATATAN_LAHIR, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.10</td>
                    <td>Status Perkawinan	:  </td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_STATUS_PERKAWINAN, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.11</td>
                    <td>Agama dan Aliran Kepercayaan	:  </td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_AGAMA, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.12</td>
                    <td>Golongan Darah	:  </td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_GOL_DARAH, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.13</td>
                    <td>Kewarganegaraan/Etnis/Suku	:</td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_KEWARGANEGARAAN, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.14</td>
                    <td>Pendidikan Umum Terakhir	:  </td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_PENDIDIKAN, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.15</td>
                    <td>Mata Pencaharian Pokok/Pekerjaan	:</td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_PEKERJAAN, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.16</td>
                    <td>Nama Bapak/Ibu Kandung	:</td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_NAMA_BAPAK_IBU_KANDUNG, DDKPatternEnum::KODE_VALUE).'</td>
                <tr>
                    <td>2.1.17</td>
                    <td>Akseptor KB	:  </td>
                    <td>:</td>
                    <td>'.str_replace('*', DDKEnum::KODE_AKSEPTOR_KB, DDKPatternEnum::KODE_VALUE).'</td>
                </tr>
            </table>';
        $html .=  '<p class="list">2.2 Cacat Menurut Jenis </p>'
            .self::cacatMenurutJenis($custom_value);
        $values = ProdeskelDDKPilihanServices::kedudukanAgtWajibPajakRetribusi($custom_value, false);
        $html .=  '<p class="list">2.3 Kedudukan Anggota Keluarga sebagai Wajib Pajak dan Retribusi </p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::lembagaPemerintahanAgt($custom_value, false);
        $html .=  '<p class="list">2.4 Lembaga Pemerintahan Yang Diikuti Anggota Keluarga  </p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::lembagaKemasyarakatanAgt($custom_value, false);
        $html .=  '<p class="list">2.5 Lembaga Kemasyarakatan Yang Diikuti Anggota Keluarga  </p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::lembagaEkonomiAgt($custom_value, false);
        $html .=  '<p class="list">2.6 Lembaga Ekonomi Yang Dimiliki Anggota Keluarga  </p>'
        . self::buatTabelDatanyaDibagi(2, DDKEnum::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA, $values, array_keys($values));
        $values = ProdeskelDDKPilihanServices::produksiBahanGalianAgt($custom_value, false);
        $html .=  '<p class="list">2.7 Produksi bahan galian yang dimiliki anggota keluarga </p>'
            . self::produksiBahanGalianAgt($values);
        return $html;
    }

    private static function sumberAirMinum($values)
    {
        $tr = '<table class="text-8" style="border-collapse: collapse;" border="1">
            <tr><th>No</th><th>Sumber Air Minum</th><th>Baik</th><th>Berasa</th><th>Berwarna</th><th>Berbau</th></tr>
        <tbody>';
        $no = 1;
        $kode_unik = DDKEnum::KODE_SUMBER_AIR_MINUM;
        foreach ($values as $index => $text) {
            $key = $kode_unik . '_' . $index;
            if(is_array($text)){
                $text = $text['text'];
            }
            $kode_nama = str_replace('*', $key, DDKPatternEnum::KODE_TEXT);
            $tr .= '<tr>
                <td>' . $no . '</td>
                <td>' . ((isset($text) && $text != '') ? $text : $kode_nama) . '</td>
                <td class="text-center">&nbsp;'. str_replace('*', $key, DDKPatternEnum::SUMBER_AIR_MINUM_BAIK) .'&nbsp;</td>
                <td class="text-center">&nbsp;'. str_replace('*', $key, DDKPatternEnum::SUMBER_AIR_MINUM_BERASA) .'&nbsp;</td>
                <td class="text-center">&nbsp;'. str_replace('*', $key, DDKPatternEnum::SUMBER_AIR_MINUM_BERWARNA) .'&nbsp;</td>
                <td class="text-center">&nbsp;'. str_replace('*', $key, DDKPatternEnum::SUMBER_AIR_MINUM_BERBAU) .'&nbsp;</td>
            </tr>';
            $no++;
        }
        // end
        return $tr .= '</tbody></table>';
    }

    private static function kepemilikanLahan($values)
    {
        $tr = '<table class="text-8" style="border-collapse: collapse;" border="1">
        <tr>
            <th>No</th>
            <th>Jenis Lahan</th>
            <th>Memiliki kurang 0,5 ha</th>
            <th>Memiliki 0,5 - 1,0 ha</th>
            <th>Memiliki lebih dari1,0 ha</th>
            <th>Tidak memiliki</th>
        </tr>
        <tbody>
        ';
        $kode_unik = DDKEnum::KODE_KEPEMILIKAN_LAHAN;

        $no = 1;
        foreach ($values as $index => $text) {
            $key = $kode_unik . '_' . $index;
            if(is_array($text)){
                $text = $text['text'];
            }
            $kode_nama = str_replace('*', $key, DDKPatternEnum::KODE_TEXT);
            $tr .= '<tr>
                <td>' . $no . '</td>
                <td>' . ((isset($text) && $text != '') ? $text : $kode_nama) . '</td>
                <td class="text-center">&nbsp;'. str_replace('*', $key, DDKPatternEnum::KEPEMILIKAN_LAHAN_KURANG_05) .'&nbsp;</td>
                <td class="text-center">&nbsp;'. str_replace('*', $key, DDKPatternEnum::KEPEMILIKAN_LAHAN_ANTARA_05_1) .'&nbsp;</td>
                <td class="text-center">&nbsp;'. str_replace('*', $key, DDKPatternEnum::KEPEMILIKAN_LAHAN_LEBIH_1) .'&nbsp;</td>
                <td class="text-center">&nbsp;'. str_replace('*', $key, DDKPatternEnum::KEPEMILIKAN_LAHAN_TIDAK_MEMILIKI) .'&nbsp;</td>
            </tr>';
            $no++;
        }
        // end
        $tr .= '
        </tbody>
        </table>';

        return $tr;
    }

    private static function produksiTahunIni($values)
    {
        $tr = '<table class="text-8" style="border-collapse: collapse; width: 100%;" border="1" width="100%">
            <thead>
                <tr>
                    <td style="width:1%"><strong>No</strong></td>
                    <td style=" text-align: center;"><strong>Komoditas</strong></td>
                    <td style=" text-align: center;"><strong>Jumlah Pohon</strong></td>
                    <td style=" text-align: center;"><strong>Luas Panen (M<sup>2</sup>)</strong></td>
                    <td style=" text-align: center;"><strong>Produksi</strong></td>
                    <td style=" text-align: center;"><strong>Satuan</strong></td>
                    <td style="width:20%; text-align: center;"><strong>Pemasaran Hasil</strong></td>
                </tr>
            </thead>
            <tbody>
        ';
        $az_range = range('A', 'Z');
        foreach (DDKPilihanProduksiTahunIniEnum::dataWithKode($values) as $kategori_komoditas => $komoditas_per_grup) {
            $char = array_shift($az_range);
            // head_row
            $tr .= '
                <tr style="background-color: #ced4d9;">
                <td><strong>' . $char . '</strong></td>
                <td colspan="6"><strong>' . $kategori_komoditas . '</strong></td>
            </tr>';
            // list_row
            $no = 1;
            foreach ($komoditas_per_grup as $item) {
                $kode_nama = str_replace('*', $item['kode'], DDKPatternEnum::KODE_TEXT);
                $kode_jumlah = str_replace('*', $item['kode'], DDKPatternEnum::PRODUKSI_TAHUN_INI_JUMLAH_POHON);
                $kode_panen = str_replace('*', $item['kode'], DDKPatternEnum::PRODUKSI_TAHUN_INI_LUAS_PANEN);
                $kode_produksi = str_replace('*', $item['kode'], DDKPatternEnum::PRODUKSI_TAHUN_INI_PRODUKSI);
                $kode_satuan = str_replace('*', $item['kode'], DDKPatternEnum::PRODUKSI_TAHUN_INI_SATUAN);
                $kode_pemasaran = str_replace('*', $item['kode'], DDKPatternEnum::PRODUKSI_TAHUN_INI_PEMASARAN_HASIL);

                $tr .= '
                    <tr>
                    <td>' . ($no++) . '</td>
                    <td>' . ($item['komoditas'] == '' ? $kode_nama : $item['komoditas']) . '</td>
                    ' . (DDKPilihanProduksiTahunIniEnum::PENGATURAN[$kategori_komoditas]['jumlah_pohon']
                    ? '<td >' . $kode_jumlah . '</td>'
                    : '<td style="background-color: #ced4d9;"></td>'
                )
                    . (DDKPilihanProduksiTahunIniEnum::PENGATURAN[$kategori_komoditas]['luas_panen']
                        ? '<td>' . $kode_panen . '</td>'
                        : '<td style="background-color: #ced4d9;"></td>'
                    )
                    . '<td>' . $kode_produksi . '</td>
                    <td>' . ($item['satuan'] == '' ? $kode_satuan : $item['satuan']) . '</td>
                    <td>' . $kode_pemasaran . '</td>
                </tr>';
            }
        }
        // end
        $tr .= '
        </tbody>
        </table>';

        return $tr;
    }

    private static function asetPerumahan(Collection $custom_value)
    {
        $dinding = ProdeskelDDKPilihanServices::asetPerumahanDinding($custom_value, false);
        $lantai = ProdeskelDDKPilihanServices::asetPerumahanLantai($custom_value, false);
        $atap = ProdeskelDDKPilihanServices::asetPerumahanAtap($custom_value, false);

        $table = '<table style="border-collapse: collapse; width: 100%;" border="1">
                    <tr>
                        <td>No</td>
                        <td><strong>Dinding</strong></td>
                        <td></td>
                        <td><strong>Lantai</strong></td>
                        <td></td>
                        <td><strong>Atap</strong></td>
                        <td></td>
                    </tr>';
        $no = 1;
        while (count($dinding) > 0 || count($lantai) > 0 || count($atap) > 0) {

            $table .=  '<tr>';
            $table .=  '<td>' . $no . '</td>';

            $key = DDKEnum::KODE_ASET_PERUMAHAN_DINDING . '_' . key($dinding);
            self::appendTDTextAndKodeToTable($table, $dinding, $key);

            $key = DDKEnum::KODE_ASET_PERUMAHAN_LANTAI . '_' . key($lantai);
            self::appendTDTextAndKodeToTable($table, $lantai, $key);

            $key = DDKEnum::KODE_ASET_PERUMAHAN_ATAP . '_' . key($atap);
            self::appendTDTextAndKodeToTable($table, $atap, $key);

            $dinding = count($dinding) > 1 ? array_slice($dinding, 1, null, $preserve_keys = true) : [];
            $lantai = count($lantai) > 1 ? array_slice($lantai, 1, null, $preserve_keys = true) : [];
            $atap = count($atap) > 1 ? array_slice($atap, 1, null, $preserve_keys = true) : [];
            $no++;
        }

        return $table .= '</table>';
    }

    private static function kualitasPersalinan(Collection $custom_value)
    {
        $tempat = ProdeskelDDKPilihanServices::pilihanKualitasTempatPersalinan($custom_value, false);
        $pertolongan = ProdeskelDDKPilihanServices::pilihanKualitasPertolonganPersalinan($custom_value, false);

        $table = '<table style="border-collapse: collapse; width: 100%;" border="1">
                    <tr>
                        <td>No</td>
                        <td><strong>Tempat Persalinan</strong></td>
                        <td></td>
                        <td>No</td>
                        <td><strong>Pertolongan Persalinan</strong></td>
                        <td></td>
                    </tr>';
        $no = 1;
        while (count($tempat) > 0 || count($pertolongan) > 0) {
            $table .=  '<tr>';
            $table .=  '<td>' . $no . '</td>';

            $key = DDKEnum::KODE_KUALITAS_TEMPAT_PERSALINAN . '_' . key($tempat);
            self::appendTDTextAndKodeToTable($table, $tempat, $key);

            $table .=  '<td>' . $no . '</td>';
            $key = DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN . '_' . key($pertolongan);
            self::appendTDTextAndKodeToTable($table, $pertolongan, $key);
            $table .=  '</tr>';

            $tempat = count($tempat) > 1 ? array_slice($tempat, 1, null, $preserve_keys = true) : [];
            $pertolongan = count($pertolongan) > 1 ? array_slice($pertolongan, 1, null, $preserve_keys = true) : [];
            $no++;
        }

        return $table .= '</table>';
    }

    private static function cacatMenurutJenis(Collection $custom_value)
    {
        $fisik =  ProdeskelDDKPilihanServices::cacatFisik($custom_value, false);
        $mental = ProdeskelDDKPilihanServices::cacatMental($custom_value, false);

        $table = '<table style="border-collapse: collapse; width: 100%;" border="1">
                    <tr>
                        <td>No</td>
                        <td><strong>Cacat Fisik</strong></td>
                        <td></td>
                        <td><strong>Cacat Mental</strong></td>
                        <td></td>
                    </tr>';
        $no = 1;
        while (count($fisik) > 0 || count($mental) > 0) {
            $table .=  '<tr>';
            $table .=  '<td>' . $no . '</td>';

            $key = DDKEnum::KODE_CACAT_FISIK . '_' . key($fisik);
            self::appendTDTextAndKodeToTable($table, $fisik, $key);

            $key = DDKEnum::KODE_CACAT_MENTAL . '_' . key($mental);
            self::appendTDTextAndKodeToTable($table, $mental, $key);
            $table .=  '</tr>';

            $fisik = count($fisik) > 1 ? array_slice($fisik, 1, null, $preserve_keys = true) : [];
            $mental = count($mental) > 1 ? array_slice($mental, 1, null, $preserve_keys = true) : [];
            $no++;
        }

        return $table .= '</table>';
    }

    /**
     * Tidak menggunakan kode self::addValue($values, $seach->first()->value_long);
     */
    public static function produksiBahanGalianAgt($values)
    {
        $tr = '<table class="text-8" style="border-collapse: collapse; width: 100%;" border="1" width="100%">
            <thead>
                <tr>
                    <td style="width:1%" rowspan="2"><strong>No</strong></td>
                    <td style=" text-align: center;" rowspan="2"><strong>Jenis Bahan Galian</strong></td>
                    <td style=" text-align: center;" colspan="3"><strong>Pemilik dan Produksi Bahan Galian (Ton/Tahun)</strong></td>
                    <td style=" text-align: center;" rowspan="2"><strong>Pemasaran Hasil</strong></td>
                </tr>
                <tr>
                    <td style=" text-align: center;"><strong>Produksi</strong></td>
                    <td style=" text-align: center;"><strong>Milik Adat</strong></td>
                    <td style=" text-align: center;"><strong>Perorangan</strong></td>
                </tr>
            </thead>
            <tbody>
        ';
        $no = 1;
        foreach ($values as $index => $text) {
            $key = DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA . '_' . $index;
            if(is_array($text)){
                $text = $text['text'];
            }
            $kode_nama = str_replace('*', $key, DDKPatternEnum::KODE_TEXT);
            $kode_produksi = str_replace('*', $key, DDKPatternEnum::PRODUKSI_BAHAN_GALIAN_PRODUKSI);
            $kode_milik_adat = str_replace('*', $key, DDKPatternEnum::PRODUKSI_BAHAN_GALIAN_MILIK_ADAT);
            $kode_perorangan = str_replace('*', $key, DDKPatternEnum::PRODUKSI_BAHAN_GALIAN_PERORANGAN);
            $kode_pemasaran = str_replace('*', $key, DDKPatternEnum::PRODUKSI_BAHAN_GALIAN_PEMASARAN_HASIL);

            $tr .= '
                <tr>
                <td>' . ($no++) . '</td>
                <td>' . (isset($text) && $text != '' ? $text : $kode_nama) . '</td>
                <td>' . $kode_milik_adat . '</td>
                <td>' . $kode_produksi . '</td>
                <td>' . $kode_perorangan . '</td>
                <td>' . $kode_pemasaran . '</td>
            </tr>';
        }
        // end
        $tr .= '
        </tbody>
        </table>';

        return $tr;
    }
}

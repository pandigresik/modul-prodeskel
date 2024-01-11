<?php

namespace Modules\Prodeskel\Services;

use Collator;
use Modules\Prodeskel\Enums\DDKEnum;
use Illuminate\Support\Collection;
use Modules\Prodeskel\Enums\DDKPilihanCheckboxEnum;
use Modules\Prodeskel\Enums\DDKMaxCustomDataPilihanEnum;
use Modules\Prodeskel\Enums\DDKPilihanMultipleSelectEnum;
use Modules\Prodeskel\Enums\DDKPilihanProduksiTahunIniEnum;
use Modules\Prodeskel\Enums\DDKPilihanBahanGalianAnggotaEnum;

defined('BASEPATH') || exit('No direct script access allowed');

class ProdeskelDDKPilihanServices
{
    private static function addValue(&$values, $json_data)
    {
        foreach(json_decode($json_data, true) as $key => $item){
            // digunakan untuk menandai pengkodean rtf
            $values[$key] = [
                'kode' => $key,
                'text' => $item,
            ];
        }
    }

    private static function removeEmptyCustom(&$values, $json_data_or_kode)
    {
        if($json_data_or_kode == DDKEnum::KODE_PRODUKSI_TAHUN_INI){
            foreach($values as $key_kategori => $komoditas){
                foreach($komoditas as $key => $item){
                    if($item['komoditas'] === ''){
                        unset($values[$key_kategori][$key]);
                    }
                }
            }

            return $values;
        }
        foreach(json_decode($json_data_or_kode, true) as $key => $item){
            if($item === ''){
                unset($values[$key]);
            }else{
                $values[$key] = $item;
            }
        }

        return $values;
    }

    public static function select(Collection $custom_value, $kode, $is_for_select_in_blade = true) : array
    {
        switch ($kode) {
            case DDKEnum::KODE_STATUS_KEPEMILIKAN_RUMAH: return self::statusKepemilikanRumah($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA: return self::penguasaanAsetTanah($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_PERILAKU_HIDUP_BERSIH_SEHAT: return self::perilakuHidupBersihSehat($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_POLA_MAKAN_KELUARGA: return self::polaMakanKeluarga($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_KEBIASAAN_BEROBAT_BILA_SAKIT: return self::kebiasaanBerobatBilaSakit($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_STATUS_GIZI_BALITA: return self::statusGiziBalita($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA: return self::lembagaPemerintahanAgt($custom_value, $is_for_select_in_blade);
        }

        return [];
    }

    public static function multipleSelect(Collection $custom_value, $kode, $is_for_select_in_blade = true) : array
    {
        switch ($kode) {
            case DDKEnum::KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA : return self::pemanfaatanDanauSungaiWadukSituMataAir($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS : return self::lembagaPendidikan($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_ASET_SARANA_TRANSPORTASI_UMUM : return self::asetSaranaTransportasiUmum($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_ASET_SARANA_PRODUKSI : return self::asetSaranaProduksi($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_ASET_PERUMAHAN_LANTAI : return self::asetPerumahanLantai($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_ASET_PERUMAHAN_DINDING : return self::asetPerumahanDinding($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_ASET_PERUMAHAN_ATAP : return self::asetPerumahanAtap($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_ASET_LAINNYA : return self::asetLainnya($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_KUALITAS_IBU_HAMIL : return self::kualitasIbuHamil($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_KUALITAS_BAYI : return self::kualitasBayi($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_KUALITAS_TEMPAT_PERSALINAN : return self::pilihanKualitasTempatPersalinan($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_CAKUPAN_IMUNISASI : return self::cakupanImunisasi($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_PENDERITA_SAKIT_DAN_KELAINAN : return self::penderitaSakitKelainan($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA : return self::jenisPenyakitAnggotaKeluaga($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_KERUKUNAN : return self::kerukunan($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_PERKELAHIAN : return self::perkelahian($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_PENCURIAN : return self::pencurian($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_PENJARAHAN : return self::penjarahan($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_PERJUDIAN : return self::perjudian($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_PEMAKAIAN_MIRAS_DAN_NARKOBA : return self::pemakaianMirasDanNarkoba($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_PEMBUNUHAN : return self::pembunuhan($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_PENCULIKAN : return self::penculikan($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_KEJAHATAN_SEKSUAL : return self::kejahatanSeksual($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA : return self::kekerasanDalamKeluargaAtauRumahTangga($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA : return self::masalahKesejahteraanKeluarga($custom_value, $is_for_select_in_blade);
            //  ANGGOTA,
            case DDKEnum::KODE_AKSEPTOR_KB : return self::akseptorKB($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_CACAT_FISIK : return self::cacatFisik($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_CACAT_MENTAL : return self::cacatMental($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI : return self::kedudukanAgtWajibPajakRetribusi($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA : return self::lembagaKemasyarakatanAgt($custom_value, $is_for_select_in_blade);
            case DDKEnum::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA : return self::lembagaEkonomiAgt($custom_value, $is_for_select_in_blade);
        }

        return [];
    }

    public static function multipleJumlah(Collection $custom_value, $kode, $is_for_select_in_blade = true) : array
    {
        switch ($kode) {
            case DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI: return self::kepemilikanJenisTernak($custom_value, $is_for_select_in_blade = true);
            case DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN: return self::alatProduksiBudidayaIkan($custom_value, $is_for_select_in_blade = true);
            case DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN: return self::pilihanKualitasPertolonganPersalinan($custom_value, $is_for_select_in_blade = true);
        }

        return [];
    }

    public static function semuaPilihanKeluargaKecualiProduksi($custom_value) : array
    {
        return [
            DDKEnum::KODE_STATUS_KEPEMILIKAN_RUMAH => self::statusKepemilikanRumah($custom_value, false),
            DDKEnum::KODE_SUMBER_AIR_MINUM => self::sumberAirMinum($custom_value, false),
            DDKEnum::KODE_KEPEMILIKAN_LAHAN => self::kepemilikanLahan($custom_value, false),
            DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI => self::kepemilikanJenisTernak($custom_value, false),
            DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN => self::alatProduksiBudidayaIkan($custom_value, false),
            DDKEnum::KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA => self::pemanfaatanDanauSungaiWadukSituMataAir($custom_value, false),
            DDKEnum::KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS => self::lembagaPendidikan($custom_value, false),
            DDKEnum::KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA => self::penguasaanAsetTanah($custom_value, false),
            DDKEnum::KODE_ASET_SARANA_TRANSPORTASI_UMUM => self::asetSaranaTransportasiUmum($custom_value, false),
            DDKEnum::KODE_ASET_SARANA_PRODUKSI => self::asetSaranaProduksi($custom_value, false),
            DDKEnum::KODE_ASET_PERUMAHAN_ATAP => self::asetPerumahanAtap($custom_value, false),
            DDKEnum::KODE_ASET_PERUMAHAN_DINDING => self::asetPerumahanDinding($custom_value, false),
            DDKEnum::KODE_ASET_PERUMAHAN_LANTAI => self::asetPerumahanLantai($custom_value, false),
            DDKEnum::KODE_ASET_LAINNYA => self::asetLainnya($custom_value, false),
            DDKEnum::KODE_KUALITAS_IBU_HAMIL => self::kualitasIbuHamil($custom_value, false),
            DDKEnum::KODE_KUALITAS_BAYI => self::kualitasBayi($custom_value, false),
            DDKEnum::KODE_KUALITAS_TEMPAT_PERSALINAN => self::pilihanKualitasTempatPersalinan($custom_value, false),
            DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN => self::pilihanKualitasPertolonganPersalinan($custom_value, false),
            DDKEnum::KODE_CAKUPAN_IMUNISASI => self::cakupanImunisasi($custom_value, false),
            DDKEnum::KODE_PENDERITA_SAKIT_DAN_KELAINAN => self::penderitaSakitKelainan($custom_value, false),
            DDKEnum::KODE_PERILAKU_HIDUP_BERSIH_SEHAT => self::perilakuHidupBersihSehat($custom_value, false),
            DDKEnum::KODE_POLA_MAKAN_KELUARGA => self::polaMakanKeluarga($custom_value, false),
            DDKEnum::KODE_KEBIASAAN_BEROBAT_BILA_SAKIT => self::kebiasaanBerobatBilaSakit($custom_value, false),
            DDKEnum::KODE_STATUS_GIZI_BALITA => self::statusGiziBalita($custom_value, false),
            DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA => self::jenisPenyakitAnggotaKeluaga($custom_value, false),
            DDKEnum::KODE_KERUKUNAN => self::kerukunan($custom_value, false),
            DDKEnum::KODE_PERKELAHIAN => self::perkelahian($custom_value, false),
            DDKEnum::KODE_PENCURIAN => self::pencurian($custom_value, false),
            DDKEnum::KODE_PENJARAHAN => self::penjarahan($custom_value, false),
            DDKEnum::KODE_PERJUDIAN => self::perjudian($custom_value, false),
            DDKEnum::KODE_PEMAKAIAN_MIRAS_DAN_NARKOBA => self::pemakaianMirasDanNarkoba($custom_value, false),
            DDKEnum::KODE_PEMBUNUHAN => self::pembunuhan($custom_value, false),
            DDKEnum::KODE_PENCULIKAN => self::penculikan($custom_value, false),
            DDKEnum::KODE_KEJAHATAN_SEKSUAL => self::kejahatanSeksual($custom_value, false),
            DDKEnum::KODE_KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA => self::kekerasanDalamKeluargaAtauRumahTangga($custom_value, false),
            DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA => self::masalahKesejahteraanKeluarga($custom_value, false),
        ];
    }

    public static function semuaPilihanAnggotaKecualiBahanGalian($custom_value) : array
    {
        return [
            DDKEnum::KODE_AKSEPTOR_KB => self::akseptorKB($custom_value, false),
            DDKEnum::KODE_CACAT_FISIK => self::cacatFisik($custom_value, false),
            DDKEnum::KODE_CACAT_MENTAL => self::cacatMental($custom_value, false),
            DDKEnum::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI => self::kedudukanAgtWajibPajakRetribusi($custom_value, false),
            DDKEnum::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA => self::lembagaPemerintahanAgt($custom_value, false),
            DDKEnum::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA => self::lembagaKemasyarakatanAgt($custom_value, false),
            DDKEnum::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA => self::lembagaEkonomiAgt($custom_value, false),
        ];
    }

    public static function semuaPilihanYangMemilikiCustomValue($custom_value)
    {
        $semua = array_filter(DDKMaxCustomDataPilihanEnum::semua(), function($item, $key){
            return $item > 0 && !array_key_exists($key, DDKMaxCustomDataPilihanEnum::KATEGORI_PRODUKSI_TAHUN_INI);
        }, ARRAY_FILTER_USE_BOTH);
        return array_intersect_key($semua,
            self::semuaPilihanKeluargaKecualiProduksi($custom_value),
            self::semuaPilihanAnggotaKecualiBahanGalian($custom_value)
        );
    }

    public static function statusKepemilikanRumah(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_STATUS_KEPEMILIKAN_RUMAH, $custom_value);
        $seach = $custom_value->where('kode_value', DDKEnum::KODE_STATUS_KEPEMILIKAN_RUMAH);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function sumberAirMinum(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_SUMBER_AIR_MINUM, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_SUMBER_AIR_MINUM);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function kepemilikanLahan(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KEPEMILIKAN_LAHAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KEPEMILIKAN_LAHAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    /**
     * Tidak menggunakan kode self::addValue($values, $seach->first()->value_long);
     */
    public static function produksiTahunIni(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKPilihanProduksiTahunIniEnum::DATA;
        foreach($values as $key_kategori => $komoditas){
            $prefix = array_search($key_kategori, DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI);
            $seach = $custom_value->where('kode_value',  $prefix);
            if($seach->count() > 0){
                foreach(json_decode($seach->first()->value_long, true) as $key => $item){
                    $values[$key_kategori][$key] = $item;
                }
            }
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom(DDKPilihanProduksiTahunIniEnum::dataWithKode($values), DDKEnum::KODE_PRODUKSI_TAHUN_INI);
        }

        return DDKPilihanProduksiTahunIniEnum::dataWithKode($values);
    }

    public static function kepemilikanJenisTernak(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function alatProduksiBudidayaIkan(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function pemanfaatanDanauSungaiWadukSituMataAir(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function lembagaPendidikan(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function penguasaanAsetTanah(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function asetSaranaTransportasiUmum(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_ASET_SARANA_TRANSPORTASI_UMUM, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_ASET_SARANA_TRANSPORTASI_UMUM);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function asetSaranaProduksi(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_ASET_SARANA_PRODUKSI, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_ASET_SARANA_PRODUKSI);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function asetPerumahanDinding(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_ASET_PERUMAHAN_DINDING, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_ASET_PERUMAHAN_DINDING);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function asetPerumahanLantai(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_ASET_PERUMAHAN_LANTAI, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_ASET_PERUMAHAN_LANTAI);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function asetPerumahanAtap(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_ASET_PERUMAHAN_ATAP, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_ASET_PERUMAHAN_ATAP);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function asetLainnya(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_ASET_LAINNYA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_ASET_LAINNYA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function kualitasIbuHamil(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KUALITAS_IBU_HAMIL, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KUALITAS_IBU_HAMIL);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function kualitasBayi(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KUALITAS_BAYI, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KUALITAS_BAYI);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function pilihanKualitasTempatPersalinan(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KUALITAS_TEMPAT_PERSALINAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KUALITAS_TEMPAT_PERSALINAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function pilihanKualitasPertolonganPersalinan(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function cakupanImunisasi(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_CAKUPAN_IMUNISASI, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_CAKUPAN_IMUNISASI);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function penderitaSakitKelainan(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PENDERITA_SAKIT_DAN_KELAINAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PENDERITA_SAKIT_DAN_KELAINAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function perilakuHidupBersihSehat(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PERILAKU_HIDUP_BERSIH_SEHAT, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PERILAKU_HIDUP_BERSIH_SEHAT);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function polaMakanKeluarga(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_POLA_MAKAN_KELUARGA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_POLA_MAKAN_KELUARGA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function kebiasaanBerobatBilaSakit(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KEBIASAAN_BEROBAT_BILA_SAKIT, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KEBIASAAN_BEROBAT_BILA_SAKIT);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function statusGiziBalita(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_STATUS_GIZI_BALITA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_STATUS_GIZI_BALITA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function jenisPenyakitAnggotaKeluaga(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function kerukunan(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KERUKUNAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KERUKUNAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function perkelahian(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PERKELAHIAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PERKELAHIAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function pencurian(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PENCURIAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PENCURIAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function penjarahan(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PENJARAHAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PENJARAHAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function perjudian(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PERJUDIAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PERJUDIAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function pemakaianMirasDanNarkoba(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PEMAKAIAN_MIRAS_DAN_NARKOBA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PEMAKAIAN_MIRAS_DAN_NARKOBA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function pembunuhan(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PEMBUNUHAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PEMBUNUHAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function penculikan(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_PENCULIKAN, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PENCULIKAN);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function kejahatanSeksual(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KEJAHATAN_SEKSUAL, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KEJAHATAN_SEKSUAL);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function kekerasanDalamKeluargaAtauRumahTangga(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function masalahKesejahteraanKeluarga(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    // ANGGOTA
    public static function akseptorKB(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_AKSEPTOR_KB, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_AKSEPTOR_KB);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function cacatFisik(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_CACAT_FISIK, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_CACAT_FISIK);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function cacatMental(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_CACAT_MENTAL, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_CACAT_MENTAL);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function kedudukanAgtWajibPajakRetribusi(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function lembagaPemerintahanAgt(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function lembagaKemasyarakatanAgt(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function lembagaEkonomiAgt(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKEnum::valuesOf(DDKEnum::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA, $custom_value);
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

    public static function produksiBahanGalianAgt(Collection $custom_value, $is_for_select_in_blade = true) : array
    {
        $values = DDKPilihanBahanGalianAnggotaEnum::DATA;
        $seach = $custom_value->where('kode_value',  DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA);
        if($seach->count() > 0){
            self::addValue($values, $seach->first()->value_long);
        }
        if($is_for_select_in_blade){
            return self::removeEmptyCustom($values, $seach->first()->value_long);
        }

        return $values;
    }

}

<?php

namespace Modules\Prodeskel\Services;

use Carbon\Carbon;
use App\Models\Config;
use App\Models\Wilayah;
use App\Models\Keluarga;
use App\Models\Penduduk;
use Modules\Prodeskel\Models\ProdeskelDDK;
use App\Enums\StatusDasarEnum;
use App\Enums\JenisKelaminEnum;
use Modules\Prodeskel\Enums\DDKEnum;
use Modules\Prodeskel\Models\ProdeskelDDKDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Prodeskel\Models\ProdeskelCustomValue;
use Modules\Prodeskel\Enums\DDKPatternEnum;
use Modules\Prodeskel\Enums\DDKPilihanSelectEnum;
use App\Services\ProdeskelDDKPilihanServices;
use Modules\Prodeskel\Enums\DDKPilihanCheckboxEnum;
use Modules\Prodeskel\Enums\DDKMaxCustomDataPilihanEnum;
use Modules\Prodeskel\Enums\DDKPilihanMultipleJumlahEnum;
use Modules\Prodeskel\Enums\DDKPilihanMultipleSelectEnum;
use Modules\Prodeskel\Enums\DDKPilihanProduksiTahunIniEnum;
use Modules\Prodeskel\Enums\DDKPilihanBahanGalianAnggotaEnum;

defined('BASEPATH') || exit('No direct script access allowed');

class ProdeskelDDKServices
{
    protected $custom_value = false;

    protected function semuaCustomValueDDK() : Collection
    {
        if($this->custom_value === false){
            $semua_kode = array_keys(DDKMaxCustomDataPilihanEnum::semua());
            $this->custom_value = ProdeskelCustomValue::where('kategori', DDKEnum::KATEGORI)
                ->whereIn('kode_value', $semua_kode)
                ->get();
        }

        return $this->custom_value;
    }

    public function datatables()
    {
        $keluarga = (new Keluarga())->getTable();
        $penduduk = (new Penduduk())->getTable();
        $wilayah  = (new Wilayah())->getTable();
        $ddk      = (new ProdeskelDDK())->getTable();
        $join = DB::table($keluarga)
            ->select(
                'tweb_keluarga.id', 'tweb_keluarga.no_kk', 'tweb_keluarga.nik_kepala',
                'kk.nama',
            )
            ->addSelect('wil_kk.dusun as dusun', 'wil_kk.rt as rt', 'wil_kk.rw as rw')
            ->addSelect(DB::raw("CONCAT(ddk.updated_at, '|', IFNULL(ddk.bulan, ''), '|', IFNULL(ddk.tahun, '')) as updated_at"))
            ->join($penduduk . ' AS kk', 'tweb_keluarga.nik_kepala', '=', 'kk.id')
            ->join($wilayah . ' AS wil_kk', 'kk.id_cluster', '=', 'wil_kk.id')
            ->join($ddk . ' AS ddk', 'ddk.keluarga_id', '=', 'tweb_keluarga.id', 'left')
            // KK Aktif
            ->where('kk.status_dasar', 1)
            ->where('kk.kk_level', 1)
            ;

        return datatables()->of($join)
            ->addColumn('ceklist', static function ($row) {
                return '<input type="checkbox" name="id_cb[]" value="' . $row->id . '"/>';
            })
            ->addColumn('aksi', static function ($row) {
                $aksi = '';
                if (can('u')) {
                    $aksi .= '&nbsp;<a href="' . ci_route("prodeskel/ddk/{$row->id}") . '" class="btn btn-warning btn-sm"  title="Lihat & Ubah Data"><i class="fa fa-edit"></i></a> ';
                }
                if($row->updated_at){
                    $aksi .= '&nbsp;<a href="' . ci_route("prodeskel/ddk/cetak/{$row->id}") . '" class="btn bg-purple btn-sm"  title="Cetak Data"><i class="fa fa-print"></i></a> ';
                }

                return $aksi;
            })
            // ->addColumn('no_kk', static function ($row) {
            //     return $row->no_kk;
            // })
            // ->addColumn('nama', static function ($row) {
            //     return $row->nama;
            // })
            ->filterColumn('nama', static function ($query, $keyword) {
                return $query->whereRaw('kk.nama LIKE ?', ['%' . $keyword . '%']);
            })
            // ->addColumn('dusun', static function ($row) {
            //     return $row->dusun;
            // })
            ->filterColumn('dusun', static function ($query, $keyword) {
                return $query->whereRaw('wil_kk.dusun LIKE ?', ['%' . $keyword . '%']);
            })
            ->addColumn('rt', static function ($row) {
                return $row->rt;
            })
            ->filterColumn('rt', static function ($query, $keyword) {
                return $query->whereRaw('wil_kk.rt LIKE ?', ['%' . $keyword . '%']);
            })
            ->addColumn('rw', static function ($row) {
                return $row->rw;
            })
            ->filterColumn('rw', static function ($query, $keyword) {
                return $query->whereRaw('wil_kk.rw LIKE ?', ['%' . $keyword . '%']);
            })
            ->rawColumns(['ceklist', 'aksi'])
            ->toJson();
    }

    public function form(int $keluarga_id)
    {
        $keluarga = Keluarga::status()->findOrFail($keluarga_id);
        $keluarga->load([
            'kepalaKeluarga' => function ($builder) {
                $builder->withOnly('wilayah');
                $builder->status(StatusDasarEnum::HIDUP);
            },
            'anggota' => static function ($builder) {
                $builder->withoutDefaultRelations();
                $builder->status(StatusDasarEnum::HIDUP);
            },
            'prodeskelDDK',
            'prodeskelDDK.produksi',
            'prodeskelDDK.detail',
            'prodeskelDDK.bahanGalianAnggota',
        ]);

        // log_message('error', str_replace('query', "\nquery", json_encode(DB::getQueryLog())));

        $data['keluarga'] = $keluarga;
        if($keluarga->prodeskelDDK){
            $data['ddk'] = $keluarga->prodeskelDDK;
        }else{
            $data['ddk'] = ProdeskelDDK::create(['keluarga_id' => $keluarga_id]);
            $data['ddk']->load([ 'produksi', 'detail', 'bahanGalianAnggota', ]);
        }
        // assign data keluarga yg sudah di load sebelumnya ke ddk
        // Note : jika ada data yg tidak terload perlu di cek ketika eager load data awal
        $data['ddk']->keluarga = $keluarga;

        $data['config']       = Config::first();
        $data['sebutan_desa'] = setting('sebutan_desa');
        $data['bulan']        = bulan();
        $tahun                = range(2020 - 2,date('Y'));
        $data['tahun']        = array_combine($tahun, $tahun);
        $data['custom_value'] = $this->semuaCustomValueDDK();

        $updated_kode = $this->singkronisasiOtomatis($data['ddk']);

        return view('ddk.form', $data);
    }

    /**
     * @return array of updated_code
     */
    private function singkronisasiOtomatis(ProdeskelDDK &$ddk) : array
    {
        $updated_kode = [];
        $reload_detail = false;
        try {
            $fun_sesuaikan_ddk_data = function($ddk_data, $kode_field, $data_otomatis) use(&$updated_kode){
                $tmp = $ddk_data->value;
                foreach($data_otomatis as $kode_data => $item){
                    if(in_array($kode_field, DDKPilihanMultipleSelectEnum::semuaKode())){
                        // masukkan data kalau belum ada
                        if($item == true && (
                            (is_array($ddk_data->value) && ! in_array($kode_data, $ddk_data->value))
                            || (!is_array($ddk_data->value) && $kode_data != $ddk_data->value)
                        )){
                            if(is_array($ddk_data->value)){
                                $tmp[] = $kode_data;
                            }else{
                                $tmp = [
                                    ...$tmp,
                                    $kode_data
                                ];
                            }
                        }
                        // hapus data kalau ada
                        elseif($item == false && (
                            (is_array($ddk_data->value) && in_array($kode_data, $ddk_data->value))
                            || (!is_array($ddk_data->value) && $kode_data == $ddk_data->value)
                        )){
                            if(is_array($ddk_data->value)){
                                $index = array_search($kode_data, $tmp);
                                unset($tmp[$index]);
                            }else{
                                $tmp = [];
                            }
                        }
                    }
                }
                $ddk_data->value = $this->sesuaikanValueSebelumDisimpan($tmp, $kode_field, true);
                // abaikan jika tidak berubah
                if($ddk_data->isDirty()){
                    $ddk_data->save();
                    $updated_kode[$kode_field] = $ddk_data->value;
                }
            };
            $this->otomatisKeluarga($ddk, $fun_sesuaikan_ddk_data, $reload_detail);

            foreach($ddk->keluarga->anggota as $no_urut => $anggota){
                $this->otomatisAgt($ddk, $anggota, $fun_sesuaikan_ddk_data, $reload_detail);
            }
            // jika diakses awal-awal ketika baru membuka form, load ulang
            if($reload_detail == true){
                $ddk->load(['detail']);
            }
            // TODO : sesuaikan anggota yang berbeda keluarga_id/prodeskel_ddk_id
        } catch (\Throwable $th) {
            log_message('error', 'Singkronisasi otomatis ProdeskelDDKServices ('. $ddk->id .') gagal'
                . "\n".$th->getMessage());

            return [];
        }

        return $updated_kode;
    }

    private function otomatisAgt(&$ddk, $anggota, callable $fun_sesuaikan_ddk_data, &$reload_detail)
    {
        $fun_new_detail = function($ddk, $anggota, $kode_field, $data, $timestamp) use(&$reload_detail){
            $reload_detail = true;
            $data = array_filter($data, function($item){return $item == true;});
            $ddk->detail()->create([
                'keluarga_id' => $ddk->keluarga_id,
                'penduduk_id' => $anggota->id,
                'kode_field'  => $kode_field,
                'value'       => json_encode(array_keys($data)),
                'created_at'  => $timestamp,
                'updated_at'  => $timestamp,
            ]);
        };
        $timestamp = Carbon::now();

        // \Modules\Prodeskel\Enums\DDKPilihanMultipleSelectEnum::CACAT_FISIK
        $cacat_fisik_otomatis = [
            // Tuna Rungu
            1 => $anggota->cacat_id == 3, //CACAT RUNGU/WICARA
            // Tuna Wicara
            2 => $anggota->cacat_id == 3, //CACAT RUNGU/WICARA
            // Tuna Netra
            3 => $anggota->cacat_id == 2, //CACAT NETRA/BUTA
        ];
        // \Modules\Prodeskel\Enums\DDKPilihanMultipleSelectEnum::CACAT_MENTAL
        $cacat_mental_otomatis = [

        ];
        $ddk_data = $ddk->detail->where('penduduk_id', $anggota->id)->where('kode_field', DDKEnum::KODE_CACAT_FISIK)->first();
        if(is_object($ddk_data) && $ddk_data instanceof ProdeskelDDKDetail){
            $fun_sesuaikan_ddk_data($ddk_data, DDKEnum::KODE_CACAT_FISIK, $cacat_fisik_otomatis);
        }else{
            $fun_new_detail($ddk, $anggota, DDKEnum::KODE_CACAT_FISIK, $cacat_fisik_otomatis, $timestamp);
        }
    }

    private function otomatisKeluarga(&$ddk, callable &$fun_sesuaikan_ddk_data, &$reload_detail)
    {
        $fun_new_detail = function($ddk, $kode_field, $data_otomatis, $timestamp) use(&$reload_detail){
            $reload_detail = true;
            $data = array_filter($data_otomatis, function($item){return $item == true;});
            $ddk->detail()->create([
                'keluarga_id' => $ddk->keluarga_id,
                'kode_field'  => $kode_field,
                'value'       => json_encode(array_keys($data)),
                'created_at'  => $timestamp,
                'updated_at'  => $timestamp,
            ]);
        };
        $timestamp = Carbon::now();
        // \Modules\Prodeskel\Enums\DDKPilihanMultipleSelectEnum::MASALAH_KESEJAHTERAAN_KELUARGA
        $kesejahteraan_otomatis = [
            // ada_anggota_gila_atau_stress
            6 => count(array_intersect(
                    $ddk->detail->where('kode_field', DDKEnum::KODE_CACAT_MENTAL)
                        ->pluck('value')
                        ->flatten()
                        ->toArray()
                    , [2, 3]  // \Modules\Prodeskel\Enums\DDKPilihanMultipleSelectEnum::CACAT_MENTAL
                )) > 0,
            // ada_anggota_cacat_fisik
            7 => $ddk->detail->where('kode_field', DDKEnum::KODE_CACAT_FISIK)
                ->pluck('value')->whereNotNull()
                ->flatten()
                ->count() > 0
                || $ddk->keluarga->anggota->whereIn('cacat_id', [1, 2, 3, 5])
                ->count() > 0,
            // ada_anggota_cacat_mental
            8 => $ddk->detail->where('kode_field', DDKEnum::KODE_CACAT_MENTAL)
                ->pluck('value')->whereNotNull()
                ->flatten()
                ->count() > 0,
            // keluarga janda, "3. CERAI HIDUP" atau "4. CERAI MATI"
            12 => $ddk->keluarga->kepalaKeluarga->whereIn('status_kawin', [3, 4])->count() > 0 && $ddk->jenis_kelamin_kepala_keluarga == JenisKelaminEnum::PEREMPUAN,
            // keluarga duda, "3. CERAI HIDUP" atau "4. CERAI MATI"
            13 => $ddk->keluarga->kepalaKeluarga->whereIn('status_kawin', [3, 4])->count() > 0 && $ddk->jenis_kelamin_kepala_keluarga == JenisKelaminEnum::LAKI_LAKI,
            // anggota keluarga menganggur
            19 => $ddk->keluarga->anggota->whereIn('pekerjaan_id', 1)->count() > 0,
            // kepala_keluarga_perempuan
            21 => $ddk->jenis_kelamin_kepala_keluarga == JenisKelaminEnum::PEREMPUAN,
        ];
        $ddk_data = $ddk->detail->firstWhere('kode_field', DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA);
        if(is_object($ddk_data) && $ddk_data instanceof ProdeskelDDKDetail){
            $fun_sesuaikan_ddk_data($ddk_data, DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA, $kesejahteraan_otomatis);
        }else{
            $fun_new_detail($ddk, DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA, $kesejahteraan_otomatis, $timestamp);
        }

        // \Modules\Prodeskel\Enums\DDKPilihanMultipleSelectEnum::JENIS_PENYAKIT_ANGGOTA_KELUARGA
        $penyakit_anggota_otomatis = [
            1 => $ddk->keluarga->anggota->where('sakit_menahun_id', 1)->count() > 0, //1. JANTUNG
            2 => $ddk->keluarga->anggota->where('sakit_menahun_id', 2)->count() > 0, //2. LEVER
            3 => $ddk->keluarga->anggota->where('sakit_menahun_id', 3)->count() > 0, //3. PARU-PARU
            4 => $ddk->keluarga->anggota->where('sakit_menahun_id', 4)->count() > 0, //4. KANKER
            5 => $ddk->keluarga->anggota->where('sakit_menahun_id', 5)->count() > 0, //5. STROKE
            6 => $ddk->keluarga->anggota->where('sakit_menahun_id', 6)->count() > 0, //6. DIABETES MELITUS
            7 => $ddk->keluarga->anggota->where('sakit_menahun_id', 7)->count() > 0, //7. GINJAL
            8 => $ddk->keluarga->anggota->where('sakit_menahun_id', 8)->count() > 0, //8. MALARIA
            9 => $ddk->keluarga->anggota->where('sakit_menahun_id', 9)->count() > 0, //9. LEPRA/KUSTA
            10 => $ddk->keluarga->anggota->where('sakit_menahun_id', 10)->count() > 0, //10. HIV/AIDS
            11 => $ddk->keluarga->anggota->where('sakit_menahun_id', 11)->count() > 0, //11. GILA/STRESS
            12 => $ddk->keluarga->anggota->where('sakit_menahun_id', 12)->count() > 0, //12. TBC
            13 => $ddk->keluarga->anggota->where('sakit_menahun_id', 13)->count() > 0, //13. ASMA
        ];
        $ddk_data = $ddk->detail->firstWhere('kode_field', DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA);
        // jika diakses awal-awal ketika baru membuka form, kemudian return
        if(is_object($ddk_data) && $ddk_data instanceof ProdeskelDDKDetail){
            $fun_sesuaikan_ddk_data($ddk_data, DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA, $penyakit_anggota_otomatis);
        }else{
            $fun_new_detail($ddk, DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA, $penyakit_anggota_otomatis, $timestamp);
        }

    }

    /**
     * @return json [message:'', otomatis:'']
     */
    public function save($keluarga_id, $request)
    {
        $keluarga = Keluarga::status()
            ->where('id', $keluarga_id)
            ->first();
        if(! $keluarga){
            $message[] = "Keluarga tidak ditemukan";
        }

        $keluarga->load([
            'kepalaKeluarga' => function ($builder) {
                $builder->withoutDefaultRelations();
                $builder->status(StatusDasarEnum::HIDUP);
            },
            'anggota' => function($builder){
                $builder->withoutDefaultRelations();
                $builder->status(StatusDasarEnum::HIDUP);
            }
        ]);
        $key_multiple_checkbox_with_pilihan = [
            'sumber_air_minum' => [
                'fields' => ProdeskelDDKPilihanServices::sumberAirMinum($this->semuaCustomValueDDK(), false),
                'checkboxs' => DDKPilihanCheckboxEnum::SUMBER_AIR_MINUM_CHECKBOX,
            ],
            'kepemilikan_lahan' => [
                'fields' => ProdeskelDDKPilihanServices::kepemilikanLahan($this->semuaCustomValueDDK(), false),
                'checkboxs' => DDKPilihanCheckboxEnum::KEPEMILIKAN_LAHAN_CHECKBOX,
            ],
        ];

        // validasi data
        $message = [];
        foreach ($request as $key => $item) {
            if(in_array($key, DDKEnum::semuaKodeAnggota())){
                foreach($item as $anggota_id => $item_anggota){
                    $no_urut_anggota = '(Anggota ke-' . ($keluarga->anggota->search(static function($item_no) use($anggota_id){
                        return $item_no->id == $anggota_id;
                    }) + 1) . ')';

                    if ($key == DDKEnum::KODE_TANGGAL_PENCATATAN_LAHIR) {
                        if($item_anggota != ''){
                            if(strtotime($item_anggota) === false){
                                $message[] = ucwords(str_replace('_', ' ', $key)) . " $no_urut_anggota : Tanggal tidak sesuai.";
                            }
                            // selalu perbaiki waktu jika tidak kosong
                            $request[$key][$anggota_id] = date('Y-m-d', strtotime($item_anggota));
                        }
                    } elseif($key == DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA){
                        foreach($item_anggota as $index => $item_produksi){
                            // abaikan dan hapus yg tidak ada dalam daftar kode, dan datanya kosong
                            if( ! in_array($index, array_keys(ProdeskelDDKPilihanServices::produksiBahanGalianAgt($this->semuaCustomValueDDK(), false)))
                                || (trim($item_produksi['milik_adat']) == ''
                                    && trim($item_produksi['milik_perorangan']) == ''
                                    && trim($item_produksi['nilai_produksi']) == ''
                                    && trim($item_produksi['pemasaran_hasil']) == ''
                                )){
                                unset($request[$key][$anggota_id][$index]);
                                continue;
                            }
                            if ($item_produksi['nilai_produksi'] != '' && ! is_numeric($item_produksi['nilai_produksi'])) {
                                $message[] = ucwords(str_replace('_', ' ', $key)) . " $no_urut_anggota Nilai Produksi $index : Nilai salah, pastikan tanda titik (.) yang menandakan desimal hanya ada 1";
                            }
                        }
                    } elseif($item_anggota != '' && array_key_exists($key, DDKEnum::semuaSelect()) && !array_key_exists($item_anggota, ProdeskelDDKPilihanServices::select($this->semuaCustomValueDDK(), $key))){
                        $message[] = ucwords(str_replace('_', ' ', $key)) . " $no_urut_anggota : Pilihan tidak ditemukan";
                    } elseif(array_key_exists($key, DDKEnum::semuaMultipleSelect())){
                        foreach($item_anggota as $index => $item_multiple_bolean){
                            // abaikan dan hapus yg tidak ada dalam daftar kode
                            if( ! in_array($item_multiple_bolean, array_keys(ProdeskelDDKPilihanServices::multipleSelect($this->semuaCustomValueDDK(), $key)))){
                                unset($request[$key][$anggota_id][$index]);
                                continue;
                            }
                        }
                    }

                }

                continue;
            }

            if ($item != '' && $key == 'bulan' && !array_key_exists($item, bulan())) {
                $message[] = ucwords(str_replace('_', ' ', $key)) . ": Bulan salah";
            } elseif ($item != '' && $key == 'tahun' && !in_array($item, range(date('Y') - 2,date('Y')))) {
                $message[] = ucwords(str_replace('_', ' ', $key)) . ": Tahun salah";
            } elseif ($item != '' && in_array($key, ['nama_pengisi', 'pekerjaan', 'jabatan']) && cekNama($item)) {
                $message[] = ucwords(str_replace('_', ' ', $key)) . ": Nama hanya boleh berisi karakter alpha, spasi, titik, koma, tanda petik dan strip";
            } elseif ($item != '' && in_array($key, ['jumlah_penghasilan_perbulan', 'jumlah_pengeluaran_perbulan']) && ! is_numeric($item)) {
                $message[] = ucwords(str_replace('_', ' ', $key)) . ": Nilai salah, pastikan tanda titik (.) yang menandakan desimal hanya ada 1";
            } elseif ($item != '' && in_array($key, array_keys(DDKEnum::semuaSelect())) && !array_key_exists($item, ProdeskelDDKPilihanServices::select($this->semuaCustomValueDDK(), $key))){
                $message[] = ucwords(str_replace('_', ' ', $key)) . ": Pilihan tidak ditemukan";
            } elseif (in_array($key, array_keys($key_multiple_checkbox_with_pilihan))){
                foreach($item as $key_found => $item_value){
                    if($item_value == ''){
                        unset($request[$key][$key_found]);
                        continue;
                    }
                    if(!in_array($key_found, array_keys($key_multiple_checkbox_with_pilihan[$key]['fields']))){
                        $message[] = ucwords(str_replace('_', ' ', $key)) . " ($key_found): Kode pilihan tidak ditemukan";
                    }
                    if(!in_array($item_value, array_keys($key_multiple_checkbox_with_pilihan[$key]['checkboxs']))){
                        $message[] = ucwords(str_replace('_', ' ', $key)) . " ($key_found): Detail pilihan tidak ditemukan";
                    }
                }
            } elseif($item != '' && in_array($key, array_keys(DDKEnum::semuaJumlah()))){
                foreach($item as $key_jumlah => $item_jumlah){
                    // abaikan dan hapus yg tidak ada dalam daftar kode
                    if($item_jumlah == '' || ! in_array($key_jumlah, array_keys(ProdeskelDDKPilihanServices::multipleJumlah($this->semuaCustomValueDDK(), $key)))){
                        unset($request[$key][$key_jumlah]);
                        continue;
                    }

                    if($item_jumlah != '' && ! is_numeric($item_jumlah)){
                        $message[] = ucwords(str_replace(['_', 'pp'], [' ', 'Pertolongan Persalinan'], $key)) . " ($key_jumlah): Tidak sesuai";
                    }
                }
            } elseif(in_array($key, array_keys(DDKEnum::semuaMultipleSelect()))){
                foreach($item as $index => $item_multiple_bolean){
                    // abaikan dan hapus yg tidak ada dalam daftar kode
                    if( ! in_array($item_multiple_bolean, array_keys(ProdeskelDDKPilihanServices::multipleSelect($this->semuaCustomValueDDK(), $key)))){
                        unset($request[$key][$index]);
                        continue;
                    }
                }
            } elseif($key == 'produksi_tahun_ini'){
                foreach($item as $index => $item_produksi){
                    // abaikan dan hapus yg tidak ada dalam daftar kode, atau ketiga datanya kosong
                    $data_gabungan = array_merge(...array_values(ProdeskelDDKPilihanServices::produksiTahunIni($this->semuaCustomValueDDK(), false)));
                    if( ! in_array($index, array_column($data_gabungan, 'kode'))
                        || (trim($item_produksi['luas_panen']) == ''
                            && trim($item_produksi['nilai_produksi_per_satuan']) == ''
                            && trim($item_produksi['pemasaran_hasil']) == ''
                        )){
                        unset($request[$key][$index]);
                        continue;
                    }
                    if ($item_produksi['luas_panen'] != '' && ! is_numeric($item_produksi['luas_panen'])) {
                        $message[] = ucwords(str_replace('_', ' ', $key)) . ' Luas Panen ' . $index . ": Nilai salah, pastikan tanda titik (.) yang menandakan desimal hanya ada 1";
                    }
                    if ($item_produksi['nilai_produksi_per_satuan'] != '' && ! is_numeric($item_produksi['nilai_produksi_per_satuan'])) {
                        $message[] = ucwords(str_replace('_', ' ', $key)) . ' Nilai Produksi ' . $index . ": Nilai salah, pastikan tanda titik (.) yang menandakan desimal hanya ada 1";
                    }
                }
            }
        }

        if (count($message) > 0) {
            return json(['message' => $message], 406);
        }

        // prepare data
        $new_data_ddk = [
            'keluarga_id'                 => $keluarga_id,
            'bulan'                       => empty_as_null_or_value($request['bulan']),
            'tahun'                       => empty_as_null_or_value($request['tahun']),
            'nama_pengisi'                => empty_as_null_or_value(nama($request['nama_pengisi'])),
            'pekerjaan'                   => empty_as_null_or_value(nama($request['pekerjaan'])),
            'jabatan'                     => empty_as_null_or_value(nama($request['jabatan'])),
            'sumber_data_1'               => empty_as_null_or_value(alamat($request['sumber_data_1'])),
            'sumber_data_2'               => empty_as_null_or_value(alamat($request['sumber_data_2'])),
            'sumber_data_3'               => empty_as_null_or_value(alamat($request['sumber_data_3'])),
            'sumber_data_4'               => empty_as_null_or_value(alamat($request['sumber_data_4'])),
            'jumlah_penghasilan_perbulan' => empty_as_null_or_value(bilangan_titik($request['jumlah_penghasilan_perbulan'])),
            'jumlah_pengeluaran_perbulan' => empty_as_null_or_value(bilangan_titik($request['jumlah_pengeluaran_perbulan'])),
        ];

        try {
            DB::beginTransaction();
            $ddk = ProdeskelDDK::where('keluarga_id', $keluarga_id)->first();
            // update or create ddk
            if( ! $ddk){
                $ddk = ProdeskelDDK::create($new_data_ddk);
            }else{
                $ddk->update($new_data_ddk);
            }
            // assign data keluarga yg sudah di load sebelumnya ke ddk
            // Note : jika ada data yg tidak terload perlu di cek ketika eager load data awal
            $ddk->keluarga = $keluarga;

            // ambil semua detail
            $ddk_details = $ddk->detail()->get();
            $new_details = [];
            $timestamp = Carbon::now();
            foreach(array_keys(DDKEnum::semuaTanpaProduksiDanGalianAgt()) as $kode_field){
                // abaikan yg bukan kode field
                if(str_contains($kode_field, '#')){
                    continue;
                }
                // anggota di cek terlebih dahulu karena memiliki kriteria khusus penduduk_id
                // jika untuk anggota dan selesai diproses maka lanjutkan data berikutnya (skip perintah untuk keluarga)
                if(in_array($kode_field, array_keys(DDKEnum::semuaKhususAnggotaTanpaGalian()))){
                    // If unused requests are found in the DB, set them to null. Otherwise, ignore
                    foreach($keluarga->anggota as $anggota){
                        if( ! $request[$kode_field]){
                            $exists = $ddk_details->where('kode_field', $kode_field);
                            if($exists->count() > 0){
                                $ddk->detail()->where('kode_field', $kode_field)
                                    ->update(['value' => json_encode(null)]);
                            }
                            break; // break foreach
                        }

                        if($request[$kode_field] && ! $request[$kode_field][$anggota->id]){
                            $exists = $ddk_details->where('kode_field', $kode_field)
                                ->where('penduduk_id', $anggota->id);
                            if($exists->count() > 0){
                                $ddk->detail()->where('kode_field', $kode_field)
                                    ->where('penduduk_id', $anggota->id)
                                    ->update(['value' => json_encode(null)]);
                            }
                        }
                    }
                    // update or new
                    foreach($request[$kode_field] as $anggota_id => $item){
                        $detailnya = $ddk_details->where('kode_field', $kode_field)
                            ->where('penduduk_id', $anggota_id)
                            ->first();
                        $value = $this->sesuaikanValueSebelumDisimpan($request[$kode_field][$anggota_id], $kode_field, true);
                        if($detailnya){
                            $detailnya->update([
                                'value' => $value
                            ]);
                        } else {
                            $new_details[] = [
                                'prodeskel_ddk_id'      => $ddk->id,
                                'keluarga_id' => $ddk->keluarga_id,
                                'penduduk_id' => $anggota_id,
                                'kode_field'  => $kode_field,
                                'value'       => $value,
                                'created_at'  => $timestamp,
                                'updated_at'  => $timestamp,
                            ];
                        }
                    }

                    continue;
                }

                // selain anggota
                $detailnya = $ddk_details->firstWhere('kode_field', $kode_field);
                $value = $this->sesuaikanValueSebelumDisimpan($request[$kode_field], $kode_field, true);
                if($detailnya){
                    $detailnya->update([
                        'value' => $value
                    ]);
                }else{
                    $new_details[] = [
                        'prodeskel_ddk_id'      => $ddk->id,
                        'keluarga_id' => $ddk->keluarga_id,
                        'penduduk_id' => null,
                        'kode_field'  => $kode_field,
                        'value'       => $value,
                        'created_at'  => $timestamp,
                        'updated_at'  => $timestamp,
                    ];
                }
            }
            ProdeskelDDKDetail::insert($new_details);

            if( ! $request['produksi_tahun_ini']){
                $ddk->produksi()->delete();
            }else{
                $all_ddk_produksi = $ddk->produksi;
                $produksi_tahun_ini = $request['produksi_tahun_ini'];
                $fun_exists = static function($item) use($produksi_tahun_ini){
                    return in_array($item->kode_komoditas, array_keys($produksi_tahun_ini));
                };
                // delete unused
                if($all_ddk_produksi->reject($fun_exists)->count() > 0){
                    $all_ddk_produksi->reject($fun_exists)->delete();
                }
                // update modified
                foreach($all_ddk_produksi->filter($fun_exists)->all() as $ddk_produksi){
                    $values = $request['produksi_tahun_ini'][$ddk_produksi->kode_komoditas];
                    $ddk_produksi->jumlah_pohon              = empty_as_null_or_value(bilangan($values['jumlah_pohon']));
                    $ddk_produksi->luas_panen                = empty_as_null_or_value(bilangan_titik($values['luas_panen']));
                    $ddk_produksi->nilai_produksi_per_satuan = empty_as_null_or_value(bilangan_titik($values['nilai_produksi_per_satuan']));
                    $ddk_produksi->pemasaran_hasil           = empty_as_null_or_value(alamat($values['pemasaran_hasil']));
                    $ddk_produksi->deleted_at = null;
                    $ddk_produksi->save();
                    // hapus dari daftar agak tidak ditambahkan ulang
                    unset($request['produksi_tahun_ini'][$ddk_produksi->kode_komoditas]);
                }
                // create new
                $new_produksi = [];
                foreach($request['produksi_tahun_ini'] as $key => $item){
                    $new_produksi[] = [
                        'kode_komoditas'            => $key,
                        'jumlah_pohon'              => empty_as_null_or_value(bilangan($item['jumlah_pohon'])),
                        'luas_panen'                => empty_as_null_or_value(bilangan_titik($item['luas_panen'])),
                        'nilai_produksi_per_satuan' => empty_as_null_or_value(bilangan_titik($item['nilai_produksi_per_satuan'])),
                        'pemasaran_hasil'           => empty_as_null_or_value(alamat($item['pemasaran_hasil'])),
                        'created_at'                => $timestamp,
                        'updated_at'                => $timestamp,
                    ];
                }
                $ddk->produksi()->createMany($new_produksi);
            }

            if( ! $request[DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA]){
                $ddk->bahanGalianAnggota()->delete();
            }else{
                $all_ddk_bahan_galian = $ddk->bahanGalianAnggota()->get();
                // delete and update
                foreach ($request[DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA] ?? [] as $anggota_id => $item_anggota) {
                    $bahan_galian_anggota = $request[DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA][$anggota_id];
                    $fun_exists = static function($item) use($bahan_galian_anggota){
                        return in_array($item->kode_komoditas, array_keys($bahan_galian_anggota));
                    };
                    // delete unused
                    if($all_ddk_bahan_galian->where('penduduk_id', $anggota_id)->reject($fun_exists)->count() > 0){
                        $all_ddk_bahan_galian->where('penduduk_id', $anggota_id)->reject($fun_exists)->delete();
                    }
                    // update modified
                    foreach($all_ddk_bahan_galian->where('penduduk_id', $anggota_id)->filter($fun_exists)->all() as $ddk_bahan_galian){
                        $values = $request[DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA][$anggota_id][$ddk_bahan_galian->kode_komoditas];
                        $ddk_bahan_galian->nilai_produksi   = empty_as_null_or_value(bilangan_titik($values['nilai_produksi']));
                        $ddk_bahan_galian->milik_adat       = empty_as_null_or_value(alamat($values['milik_adat']));
                        $ddk_bahan_galian->milik_perorangan = empty_as_null_or_value(alamat($values['milik_perorangan']));
                        $ddk_bahan_galian->pemasaran_hasil  = empty_as_null_or_value(alamat($values['pemasaran_hasil']));
                        $ddk_bahan_galian->save();
                        // hapus dari daftar agar tidak ditambahkan ulang
                        unset($request[DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA][$anggota_id][$ddk_bahan_galian->kode_komoditas]);
                    }
                }
                // create new
                $new_bahan_galian = [];
                foreach ($request[DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA] ?? [] as $anggota_id => $item_anggota) {
                    foreach($request[DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA][$anggota_id] as $key => $item){
                        $new_bahan_galian[] = [
                            'penduduk_id'       => $anggota_id,
                            'kode_komoditas'    => $key,
                            'nilai_produksi'    => empty_as_null_or_value(bilangan_titik($item['nilai_produksi'])),
                            'milik_adat'        => empty_as_null_or_value(alamat($item['milik_adat'])),
                            'milik_perorangan'  => empty_as_null_or_value(alamat($item['milik_perorangan'])),
                            'pemasaran_hasil'   => empty_as_null_or_value(alamat($item['pemasaran_hasil'])),
                            'created_at'        => $timestamp,
                            'updated_at'        => $timestamp,
                        ];
                    }
                }
                $ddk->bahanGalianAnggota()->createMany($new_bahan_galian);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            log_message('error', $th->getMessage());
            return json(['message' => 'Gagal Disimpan'], 406);
        }

        $otomatis = $this->singkronisasiOtomatis($ddk);
        return json(['message' => 'Berhasil Disimpan', 'otomatis' => $otomatis], 200);
    }

    /**
     * Jika $value bertipe string, hapus spasi dan koma diakir
     * Jika $value bagian dari "select", simpan data array tanpa index. Selainnya simpan nilai atau null
     * Jika $value bertipe array dan bagian dari "multiple select", simpan data array tanpa index. Selainnya simpan nilai atau null
     * @param mixed $value
     * @param string $kode_field prodeskel_ddk_detail
     * @param bool $has_been_validated = false (default false untuk impor)
     *  */
    protected function sesuaikanValueSebelumDisimpan($value, string $kode_field, bool $has_been_validated = false) : string
    {
        // value dari Excel bertipe string
        // jika pilihan dan bertipe string, hapus spasi dan koma di akhir.
        if(is_string($value) && in_array($kode_field, array_keys(DDKMaxCustomDataPilihanEnum::semua()))){
            $value = str_replace(' ', '', $value);
            $value = substr($value, -1) == ',' ? rtrim($value, ',') : $value;
        }

        if(in_array($kode_field, DDKPilihanSelectEnum::semuaKode())){
            // jika string memiliki koma, ambil nilai pertama sebelum koma
            if(is_string($value) && strpos($value, ',') !== false){
                $value =  trim(explode(',', $value)[0]);
                if( ! $has_been_validated){
                    $value = $this->filterValue($kode_field, $value);
                }
                return json_encode(empty_as_null_or_value($value));
            }elseif(is_array($value)){
                $value = array_shift($value);
                if( ! $has_been_validated){
                    $value = $this->filterValue($kode_field, $value);
                }
                return json_encode(empty_as_null_or_value($value));
            }
        }
        // jika string memiliki koma, convert ke array
        $value = (is_string($value) && strpos($value, ',') !== false) ? explode(',', $value) : $value;

        if(is_array($value) && in_array($kode_field, DDKPilihanMultipleSelectEnum::semuaKode())){
            if( ! $has_been_validated){
                $value = $this->filterValue($kode_field, $value);
            }
            return json_encode(array_values($value));
        }else{
            if( ! $has_been_validated){
                $value = $this->filterValue($kode_field, $value);
            }
            return json_encode(empty_as_null_or_value($value));
        }
    }

    private function filterValue($kode_field, $value)
    {
        if(in_array($kode_field, DDKPilihanMultipleSelectEnum::semuaKode())){
            if(is_array($value)){
                foreach($value as $index => $item){
                    if( ! array_key_exists($item, ProdeskelDDKPilihanServices::multipleSelect($this->semuaCustomValueDDK(), $kode_field, false))){
                        unset($value[$index]);
                    }
                }
            }else{
                if( ! array_key_exists($value, ProdeskelDDKPilihanServices::multipleSelect($this->semuaCustomValueDDK(), $kode_field, false))){
                    return null;
                }
            }

            return $value;
        }elseif(in_array($kode_field, DDKPilihanSelectEnum::semuaKode())){
            if(is_array($value)){
                $value = array_shift($value);
            }
            if( ! array_key_exists($value, ProdeskelDDKPilihanServices::select($this->semuaCustomValueDDK(), $kode_field, false))){
                return null;
            }

            return $value;
        }elseif(in_array($kode_field, DDKPilihanMultipleJumlahEnum::semuaKode())){
            if(is_array($value)){
                foreach($value as $k => $v){
                    if( ! array_key_exists($k, ProdeskelDDKPilihanServices::multipleJumlah($this->semuaCustomValueDDK(), $kode_field, false))){
                        return null;
                    }
                }
            }else{
                $value = array_map(function($item){ return bilangan($item);}, $value);
                if(in_array(null, $value)){
                    return null;
                }
            }
            return $value;
        }elseif(in_array($kode_field, DDKPilihanCheckboxEnum::semuaKode())){
            $key_with_pilihan = [
                DDKEnum::KODE_SUMBER_AIR_MINUM => [
                    'fields' => ProdeskelDDKPilihanServices::sumberAirMinum($this->semuaCustomValueDDK(), false),
                    'checkboxs' => DDKPilihanCheckboxEnum::SUMBER_AIR_MINUM_CHECKBOX,
                ],
                DDKEnum::KODE_KEPEMILIKAN_LAHAN => [
                    'fields' => ProdeskelDDKPilihanServices::kepemilikanLahan($this->semuaCustomValueDDK(), false),
                    'checkboxs' => DDKPilihanCheckboxEnum::KEPEMILIKAN_LAHAN_CHECKBOX,
                ],
            ];
            if(is_array($value)){
                foreach($value as $index_value => $tmp_value){
                    // check index (pilihan)
                    if( ! array_key_exists($index_value, $key_with_pilihan[$kode_field]['fields'])){
                        return null;
                    }

                    // check value (keterangan pilihan)
                    if(is_array($tmp_value) && ! array_key_exists(array_sum($tmp_value), $key_with_pilihan[$kode_field]['checkboxs'])){
                        return null;
                    }elseif(is_numeric($tmp_value) && ! array_key_exists($tmp_value, $key_with_pilihan[$kode_field]['checkboxs'])){
                        return null;
                    }
                }

                return $value;
            }else{
                log_message('error', 'Terdapat kode_field yang belum dicek. (B). '. $kode_field . ' : ' . json_encode($value));
                return null;
            }
        }else{
            log_message('error', 'Terdapat kode_field yang belum dicek. (C). '. $kode_field . ' : ' . json_encode($value));
            return null;
        }
    }

    /**
     * @return json
     */
    public function savePengaturan($request)
    {
        try {
            if($request['tipe'] == 'pilihan-tambahan'){
                // siapkan kode hingga jumlah maksimal
                $kode_kustom = [];
                foreach(DDKMaxCustomDataPilihanEnum::semua() as $kode_pilihan => $jumlah){
                    if($jumlah == 0){
                        continue;
                    }else if(array_key_exists($kode_pilihan, DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI)){
                        $kode_kustom[$kode_pilihan] = [];
                        for($i = 1; $i <= $jumlah; $i++){
                            $pilihan = DDKPilihanProduksiTahunIniEnum::DATA[DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI[$kode_pilihan]];
                            $kode_kustom[$kode_pilihan][count($pilihan) + $i] = ['komoditas' => '', 'satuan' => ''];
                        }
                    }else if($kode_pilihan == DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA){
                        $kode_kustom[$kode_pilihan] = [];
                        for($i = 1; $i <= $jumlah; $i++){
                            $pilihan = DDKPilihanBahanGalianAnggotaEnum::DATA;
                            $kode_kustom[$kode_pilihan][count($pilihan) + $i] = '';
                        }
                    }else{
                        $kode_kustom[$kode_pilihan] = [];
                        for($i = 1; $i <= $jumlah; $i++){
                            $pilihan = DDKEnum::valuesOf($kode_pilihan);
                            end($pilihan);
                            $kode_kustom[$kode_pilihan][key($pilihan) + $i] = '';
                        }
                    }
                }
                // tambahkan data hanya yg seseuai dengan kode_pilihan dan custom value indexnya sesuai
                // misal k1_3 memiliki index 6, k1_9 memiliki index 9 dan 10
                $to_be_insert = [];
                foreach($request as $kode_pilihan => $item_pilihan){
                    if(array_key_exists($kode_pilihan, $kode_kustom)){
                        // hapus yg tidak sesuai
                        foreach($item_pilihan as $key => $item){
                            if( ! array_key_exists($key, $kode_kustom[$kode_pilihan])){
                                unset($request[$kode_pilihan][$key]);
                            }
                        }
                        // tambahkan missing index
                        $request[$kode_pilihan] = $request[$kode_pilihan] + $kode_kustom[$kode_pilihan];
                        $to_be_insert[] = [
                            'kategori'   => DDKEnum::KATEGORI,
                            'kode_value' => $kode_pilihan,
                            'value'      => '',
                            'value_long' => json_encode($request[$kode_pilihan]),
                        ];
                    }
                }
                ProdeskelCustomValue::upsert(
                    $to_be_insert,
                    ['kategori', 'kode_value'],
                    ['value_long']
                );
            }elseif($request['tipe'] == 'set-semua-periode'){
                // DB::enableQueryLog();
                ProdeskelDDK::where('id', '<>', 0)
                    ->update([
                        'bulan' => empty_as_null_or_value($request['bulan']),
                        'tahun' => empty_as_null_or_value($request['tahun']),
                    ]);
                // log_message('error', str_replace('query', "\nquery", json_encode(DB::getQueryLog())));
            }elseif($request['tipe'] == 'set-semua-sumber-data'){
                if(!in_array($request['sumber_data_ke'], [1,2,3,4])){
                    return json(['message' => 'Gagal Disimpan. Sumber data ke - hanya bisa 1 s.d 4'], 406);
                }
                ProdeskelDDK::where('id', '<>', 0)
                ->update([
                    'sumber_data_' . $request['sumber_data_ke'] => empty_as_null_or_value($request['value'])
                ]);
            }
            return json(['message' => 'Berhasil Disimpan'], 200);

        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return json(['message' => 'Gagal Disimpan'], 406);
        }
    }

    public function rtf($keluarga_id)
    {
        try {
            $keluarga = Keluarga::status()->findOrFail($keluarga_id);
            $keluarga->load([
                'kepalaKeluarga' => static function ($builder) {
                    $builder->withOnly('wilayah');
                    $builder->status(StatusDasarEnum::HIDUP);
                },
                'anggota' => static function ($builder) {
                    $builder->withoutDefaultRelations();
                    $builder->status(StatusDasarEnum::HIDUP);
                },
                'prodeskelDDK',
                'prodeskelDDK.produksi',
                'prodeskelDDK.detail',
                'prodeskelDDK.bahanGalianAnggota',
            ]);

            self::validasiTemplate();

            // Segoe UI Symbol, size 9, bold
            $check_mark = '{\rtlch\fcs1 \ab\af53\afs18 \ltrch\fcs0 \b\f53\fs18\insrsid10583346\charrsid7613117 \u10003\\\'3f}';
            // tutup dulu tag asli dengan } kemudian buka tag strikethrough dan replace simbol * dengan kode, kemudian lanjutkan
            $strikethrough_star_pattern = '}{\rtlch\fcs1 \af1\afs16 \ltrch\fcs0 \b0\strike\f1\fs16\cf0\lang1057\langfe1033\langnp1057\insrsid11866146\charrsid11866146 \hich\af1\dbch\af31501\loch\f1 *}{\rtlch\fcs1 \af1\afs16 \ltrch\fcs0 \b0\f1\fs16\cf0\lang1057\langfe1033\langnp1057\insrsid11866146\charrsid11866146 \hich\af1\dbch\af31501\loch\f1 ';

            $handle = fopen(DDKEnum::PATH_TEMPLATE . DDKEnum::FILE_TEMPLATE_DDK, 'rb');
            $buffer_combine = stream_get_contents($handle);
            $this->replaceDataKeluarga($buffer_combine, $check_mark, $keluarga);
            fclose($handle);

            foreach($keluarga->anggota as $no_urut => $anggota){
                $handle = fopen(DDKEnum::PATH_TEMPLATE . DDKEnum::FILE_TEMPLATE_DDK_ANGGOTA, 'rb');
                $new_buffer = stream_get_contents($handle);
                $this->replaceDataAnggota($new_buffer, $check_mark, $strikethrough_star_pattern, $keluarga, $no_urut + 1, $anggota);
                // Combine RTF (remove the last character, add new page tag, remove open rtf tag)
                $buffer_combine = substr($buffer_combine, 0, -1) . '\page' . substr($new_buffer, 6);
                fclose($handle);
            }

            // Simpan dan download
            $nama_file     = nama_file('ddk_' . str_replace(' ', '', strtolower($keluarga->kepalaKeluarga->nama)) .'_'. date('YmdHis', strtotime($keluarga->prodeskelDDK->updated_at)). '.rtf');
            $berkas_arsip  = DDKEnum::PATH_TEMPLATE . $nama_file;
            $handle        = fopen($berkas_arsip, 'w+b');
            fwrite($handle, $buffer_combine);
            fclose($handle);
            // Register a function to be called on script termination
            $absolute_berkas_arsip = getcwd() . DIRECTORY_SEPARATOR . $berkas_arsip;
            register_shutdown_function(function () use ($absolute_berkas_arsip) {
                if (file_exists($absolute_berkas_arsip)) {
                    unlink($absolute_berkas_arsip);
                }
            });

            ambilBerkas($nama_file, $this->controller, null, DDKEnum::PATH_TEMPLATE);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            show_error($th->getMessage());
        }
    }

    public function validasiTemplate()
    {
        $file         = DDKEnum::PATH_TEMPLATE . DDKEnum::FILE_TEMPLATE_DDK;
        $file_anggota = DDKEnum::PATH_TEMPLATE . DDKEnum::FILE_TEMPLATE_DDK_ANGGOTA;
        if (!is_file($file)) {
            log_message('error', 'File template FormDDK.rtf tidak ditemukan');
            show_error('File template FormDDK.rtf tidak ditemukan');
        }
        if (!is_file($file_anggota)) {
            log_message('error', 'File template FormDDKAnggota.rtf tidak ditemukan');
            show_error('File template FormDDKAnggota.rtf tidak ditemukan');
        }
        $hash = ProdeskelCustomValue::where('kategori', DDKEnum::KATEGORI)
            ->whereIn('kode_value', [DDKEnum::HASH_TEMPLATE_DDK, DDKEnum::HASH_TEMPLATE_DDK_ANGGOTA])
            ->get()
            ->pluck('value', 'kode_value');
        if($hash->count() != 2){
            log_message('error', 'Data hash template pada database tidak ditemukan. Silahkan coba lakukan migrasi database ulang.');
            $arr = [
                DDKEnum::HASH_TEMPLATE_DDK         => DDKEnum::FILE_TEMPLATE_DDK,
                DDKEnum::HASH_TEMPLATE_DDK_ANGGOTA => DDKEnum::FILE_TEMPLATE_DDK_ANGGOTA,
            ];
            foreach($arr as $kode_value => $path_value){
                if( ! array_key_exists($kode_value, $hash->all())){
                    $hash_value = hash_file(DDKEnum::HASH_ALGO, DDKEnum::PATH_TEMPLATE . $path_value);
                    $hashNew = ProdeskelCustomValue::create([
                        'kategori' => DDKEnum::KATEGORI,
                        'kode_value' => $kode_value,
                        'value' => $hash_value,
                        'value_long' => '',
                    ]);
                    $hash = $hash->merge([$kode_value => $hash_value]);
                }
            }
        }
        $hash_ddk = hash_file(DDKEnum::HASH_ALGO, DDKEnum::PATH_TEMPLATE . DDKEnum::FILE_TEMPLATE_DDK);
        $hash_ddk_anggota = hash_file(DDKEnum::HASH_ALGO, DDKEnum::PATH_TEMPLATE . DDKEnum::FILE_TEMPLATE_DDK_ANGGOTA);
        if($hash[DDKEnum::HASH_TEMPLATE_DDK] != $hash_ddk){
            log_message('error', 'File template FormDDK.rtf telah berubah');
            show_error('File template FormDDK.rtf telah berubah');
        }
        if($hash[DDKEnum::HASH_TEMPLATE_DDK_ANGGOTA] != $hash_ddk_anggota){
            log_message('error', 'File template FormDDKAnggota.rtf telah berubah');
            show_error('File template FormDDKAnggota.rtf telah berubah');
        }
    }

    private function replaceDataKeluarga(&$buffer, &$check_mark, $keluarga)
    {
        $ci =& get_instance();
        $ci->load->helper('tglindo_helper');

        $config = Config::first();
        $ddk           = $keluarga->prodeskelDDK;
        $array_replace = [
            '[no_kk]'               => $keluarga->no_kk,
            '[NaMa_kk]'             => strtoupper($keluarga->kepalaKeluarga->nama),
            '[Alamat_kk]'           => ucwords(strtolower(trim($keluarga->kepalaKeluarga->alamat_wilayah))),
            '[Rt_rw_kk]'            => $keluarga->wilayah->rt . ' / ' . $keluarga->wilayah->rw,
            '[Dusun]'               => ucwords(strtolower($keluarga->wilayah->dusun)),
            '[Desa_kel]'            => ucwords(strtolower($config->nama_desa)),
            '[Kec]'                 => ucwords(strtolower($config->nama_kecamatan)),
            '[Kab_kota]'            => ucwords(strtolower($config->nama_kabupaten)),
            '[Prov]'                => ucwords(strtolower($config->nama_propinsi)),
            '[Bulan]'               => bulan2($ddk->bulan) ?? '',
            '[Tahun]'               => $ddk->tahun ?? '',
            '[NaMa_pengisi]'        => strtoupper($ddk->nama_pengisi) ?? '',
            '[Pekerjaan_pengisi]'   => ucwords(strtolower($ddk->pekerjaan)) ?? '',
            '[Jabatan_pengisi]'     => ucwords(strtolower($ddk->jabatan)) ?? '',
            '[Sumber_data_1]'       => ucwords(strtolower($ddk->sumber_data_1)) ?? '',
            '[Sumber_data_2]'       => ucwords(strtolower($ddk->sumber_data_2)) ?? '',
            '[Sumber_data_3]'       => ucwords(strtolower($ddk->sumber_data_3)) ?? '',
            '[Sumber_data_4]'       => ucwords(strtolower($ddk->sumber_data_4)) ?? '',
            str_replace('*', DDKEnum::KODE_JUMLAH_PENGHASILAN_PERBULAN, DDKPatternEnum::KODE_VALUE) => $ddk->jumlah_penghasilan_perbulan,
            str_replace('*', DDKEnum::KODE_JUMLAH_PENGELUARAN_PERBULAN, DDKPatternEnum::KODE_VALUE) => $ddk->jumlah_pengeluaran_perbulan,
        ];

        foreach(ProdeskelDDKPilihanServices::semuaPilihanKeluargaKecualiProduksi($this->semuaCustomValueDDK()) as $kode_field => $daftar_pilihan){
            $terpilih = $ddk->detailKeluarga[$kode_field]->value ?? null;
            $this->generateReplaceValue($daftar_pilihan, $kode_field, $terpilih, $check_mark, $array_replace, $ddk->detail);
        }
        $this->replaceBuffer($buffer, $array_replace);
        $array_replace = [];
        foreach(ProdeskelDDKPilihanServices::produksiTahunIni($this->semuaCustomValueDDK(), false) as $kategori => $komoditas_per_grup){
            foreach($komoditas_per_grup as $item){
                // TEXT
                $kode_nama = str_replace('*', $item['kode'], DDKPatternEnum::KODE_TEXT);
                $array_replace[$kode_nama] =  $item['komoditas'];
                // VALUE
                $ddk_produksi_data = $ddk->produksi
                    ? $ddk->produksi->firstWhere('kode_komoditas', $item['kode'])
                    : false;

                if(DDKPilihanProduksiTahunIniEnum::PENGATURAN[$kategori]['jumlah_pohon']){
                    $kode = str_replace('*', $item['kode'], DDKPatternEnum::PRODUKSI_TAHUN_INI_JUMLAH_POHON);
                    $array_replace[$kode] =  $ddk_produksi_data != false ? $ddk_produksi_data->jumlah_pohon : '';
                }
                if(DDKPilihanProduksiTahunIniEnum::PENGATURAN[$kategori]['luas_panen']){
                    $kode = str_replace('*', $item['kode'], DDKPatternEnum::PRODUKSI_TAHUN_INI_LUAS_PANEN);
                    $array_replace[$kode] =  $ddk_produksi_data != false ? $ddk_produksi_data->luas_panen : '';
                }

                $kode = str_replace('*', $item['kode'], DDKPatternEnum::PRODUKSI_TAHUN_INI_PRODUKSI);
                $array_replace[$kode] =  $ddk_produksi_data !== false ? $ddk_produksi_data->nilai_produksi_per_satuan : '';

                $kode = str_replace('*', $item['kode'], DDKPatternEnum::PRODUKSI_TAHUN_INI_SATUAN);
                $array_replace[$kode] =  $ddk_produksi_data !== false ? $item['satuan'] : '';

                $kode = str_replace('*', $item['kode'], DDKPatternEnum::PRODUKSI_TAHUN_INI_PEMASARAN_HASIL);
                $array_replace[$kode] =  $ddk_produksi_data !== false ? $ddk_produksi_data->pemasaran_hasil : '';
            }
            $this->replaceBuffer($buffer, $array_replace);
        }
    }

    private function replaceDataAnggota(&$buffer, &$check_mark, &$strikethrough_star_pattern, $keluarga,  $no_urut, $anggota)
    {
        $ddk               = $keluarga->prodeskelDDK;

        $baris_hub_kk = baris_pilihan_di_coret(
            $hub_tersedia = [
                'Istri'     => 3, // ISTRI
                'Suami'     => 2, // SUAMI
                'Anak'      => 4, // ANAK
                'Cucu'      => 6, // CUCU
                'Mertua'    => 8, // MERTUA
                'Menantu'   => 5, // MENANTU
                'Keponakan' => 9, // FAMILIAIN
                'Lain-Lain' => 11, // LAINNYA
            ],
            $compare_value = $anggota->kk_level,
            $default_key = false,
            $strikethrough_star_pattern = $strikethrough_star_pattern,
            $space_divider_replacement = ' '
        );
        $baris_stat_kawin = baris_pilihan_di_coret(
            $hub_tersedia = [
                'Kawin' => 1, // KAWIN
                'Belum Kawin' => 2, // BELUM KAWIN
                'Pernah Kawin' => [
                    3, // CERAI HIDUP
                    4, // CERAI MATI
                ]
            ],
            $compare_value = $anggota->status_kawin,
            $default_key = false,
            $strikethrough_star_pattern = $strikethrough_star_pattern,
            $space_divider_replacement = ' '
        );
        $baris_agama = baris_pilihan_di_coret(
            $hub_tersedia = [
                'Islam'         => 1, // ISLAM
                'Protestan'     => 7, // Kepercayaan Terhadap Tuhan YME / Lainnya
                'Katolik'       => 3, // KATHOLIK
                'Hindu'         => 4, // HINDU
                'Budha'         => 5, // BUDHA
                'Kong Hu Chu'   => 6, // KHONGHUCU
            ],
            $compare_value = $anggota->agama_id,
            $default_key = false,
            $strikethrough_star_pattern = $strikethrough_star_pattern,
            $space_divider_replacement = ' '
        );
        $baris_gol_darah = baris_pilihan_di_coret(
            $hub_tersedia = [
                'O'  => [
                    4, //O
                    11, //O+
                    12, //O-
                ],
                'A'  => [
                    1, //A
                    5, //A+
                    6, //A-
                ],
                'B'  => [
                    2, //B
                    7, //B+
                    8, //B-
                ],
                'AB' => [
                    3, //AB
                    9, //AB+
                    10, //AB-
                ],
            ],
            $compare_value = $anggota->golongan_darah_id,
            $default_key = false,
            $strikethrough_star_pattern = $strikethrough_star_pattern,
            $space_divider_replacement = ' '
        );
        $baris_pendidikan_terakhir = baris_pilihan_di_coret(
            $hub_tersedia = [
                'SD'        => 3, // TAMAT SD / SEDERAJAT
                'SMP'       => 4, // SLTP/SEDERAJAT
                'SMA'       => 5, // SLTA / SEDERAJAT
                'Diploma'   => [
                    6, // DIPLOMA I / II
                    7, // AKADEMI/ DIPLOMA III/S. MUDA
                ],
                'S1'        => 8, // DIPLOMA IV/ STRATA I
                'S2'        => 9, // STRATA II
                'S3'        => 10, // STRATA III
            ],
            $compare_value = $anggota->pendidikan_kk_id,
            $default_key = false,
            $strikethrough_star_pattern = $strikethrough_star_pattern,
            $space_divider_replacement = ' '
        );
        $baris_akseptor_kb = baris_pilihan_di_coret(
            $hub_tersedia = [
                'Pil'       => 1,
                'Spiral'    => 2,
                'Suntik'    => 3,
                'Susuk'     => 4,
                'Kondom'    => 5,
                'Vasektomi' => 6,
                'Tubektomi' => 7,
            ],
            $compare_value = $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_AKSEPTOR_KB]->value ?? [],
            $default_key = false,
            $strikethrough_star_pattern = $strikethrough_star_pattern,
            $space_divider_replacement = ' '
        );

        $array_replace = [
            str_replace('*', DDKEnum::KODE_NO_URUT, DDKPatternEnum::KODE_VALUE) => $no_urut,
            str_replace('*', DDKEnum::KODE_NIK, DDKPatternEnum::KODE_VALUE) => $anggota->nik,
            str_replace('*', DDKEnum::KODE_NAMA_LENGKAP, DDKPatternEnum::KODE_VALUE) => $anggota->nama,
            str_replace('*', DDKEnum::KODE_AKTE_KELAHIRAN, DDKPatternEnum::KODE_VALUE) => $anggota->akta_lahir,
            str_replace('*', DDKEnum::KODE_JENIS_KELAMIN, DDKPatternEnum::KODE_VALUE) => JenisKelaminEnum::valueOf($anggota->sex),
            str_replace('*', DDKEnum::KODE_HUB_KK, DDKPatternEnum::KODE_VALUE) => $baris_hub_kk,
            str_replace('*', DDKEnum::KODE_TEMPAT_LAHIR, DDKPatternEnum::KODE_VALUE) => $anggota->tepatlahir,
            str_replace('*', DDKEnum::KODE_TANGGAL_LAHIR, DDKPatternEnum::KODE_VALUE) => tgl_indo($anggota->tanggallahir),
            str_replace('*', DDKEnum::KODE_TANGGAL_PENCATATAN_LAHIR, DDKPatternEnum::KODE_VALUE) => $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_TANGGAL_PENCATATAN_LAHIR]->value ?? '',
            str_replace('*', DDKEnum::KODE_STATUS_PERKAWINAN, DDKPatternEnum::KODE_VALUE) => $baris_stat_kawin,
            str_replace('*', DDKEnum::KODE_AGAMA, DDKPatternEnum::KODE_VALUE) => $baris_agama,
            str_replace('*', DDKEnum::KODE_GOL_DARAH, DDKPatternEnum::KODE_VALUE) => $baris_gol_darah,
            str_replace('*', DDKEnum::KODE_KEWARGANEGARAAN, DDKPatternEnum::KODE_VALUE) => $anggota->suku,
            str_replace('*', DDKEnum::KODE_PENDIDIKAN, DDKPatternEnum::KODE_VALUE) => $baris_pendidikan_terakhir,
            str_replace('*', DDKEnum::KODE_PEKERJAAN, DDKPatternEnum::KODE_VALUE) => $anggota->pekerjaan ? ucwords(strtolower($anggota->pekerjaan->nama)) : '',
            str_replace('*', DDKEnum::KODE_NAMA_BAPAK_IBU_KANDUNG, DDKPatternEnum::KODE_VALUE) => $anggota->nama_ayah . ' / ' . $anggota->nama_ibu,
            str_replace('*', DDKEnum::KODE_AKSEPTOR_KB, DDKPatternEnum::KODE_VALUE) => $baris_akseptor_kb,
        ];
        $semua_kecuali_produksi_bahan_galian = ProdeskelDDKPilihanServices::semuaPilihanAnggotaKecualiBahanGalian($this->semuaCustomValueDDK());
        $semua_kecuali_produksi_bahan_galian = array_filter($semua_kecuali_produksi_bahan_galian, function($item){
            return ! in_array($item, [DDKEnum::KODE_AKSEPTOR_KB]);
        });
        foreach($semua_kecuali_produksi_bahan_galian as $kode_field => $daftar_pilihan){
            $terpilih = $ddk->detailAnggota[$anggota->id][$kode_field]->value ?? null;
            $this->generateReplaceValue($daftar_pilihan, $kode_field, $terpilih, $check_mark, $array_replace, $ddk->detail);
        }
        foreach(ProdeskelDDKPilihanServices::produksiBahanGalianAgt($this->semuaCustomValueDDK(), false) as $index => $text) {
            $key = DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA . '_' . $index;
            // TEXT
            $kode_nama = str_replace('*', $key, DDKPatternEnum::KODE_TEXT);
            if(is_array($text)){
                $text = $text['text'];
            }
            $array_replace[$kode_nama] =  $text;

            // VALUE
            $ddk_produksi_data = $ddk->bahanGalianAnggota->where('penduduk_id', $anggota->id)
                ? $ddk->bahanGalianAnggota->where('penduduk_id', $anggota->id)
                    ->where('kode_komoditas', $index)->first()
                : false;

            $kode = str_replace('*', $key, DDKPatternEnum::PRODUKSI_BAHAN_GALIAN_PRODUKSI);
            $array_replace[$kode] = $ddk_produksi_data != false ? $ddk_produksi_data->nilai_produksi : '';
            $kode = str_replace('*', $key, DDKPatternEnum::PRODUKSI_BAHAN_GALIAN_MILIK_ADAT);
            $array_replace[$kode] = $ddk_produksi_data != false ? $ddk_produksi_data->milik_adat : '';
            $kode = str_replace('*', $key, DDKPatternEnum::PRODUKSI_BAHAN_GALIAN_PERORANGAN);
            $array_replace[$kode] = $ddk_produksi_data != false ? $ddk_produksi_data->milik_perorangan : '';
            $kode = str_replace('*', $key, DDKPatternEnum::PRODUKSI_BAHAN_GALIAN_PEMASARAN_HASIL);
            $array_replace[$kode] = $ddk_produksi_data != false ? $ddk_produksi_data->pemasaran_hasil : '';
        }

        $this->replaceBuffer($buffer, $array_replace);
    }

    private function generateReplaceValue($daftar_pilihan, $kode_field, $terpilih, &$check_mark, &$array_replace, $detail_collection)
    {
        foreach($daftar_pilihan as $no => $pilihan){
            $key = $kode_field . '_' . $no;
            // TEXT
            $kode_nama = str_replace('*', $key, DDKPatternEnum::KODE_TEXT);
            if(is_array($pilihan) && $pilihan['kode'] == $no){
                $array_replace[$kode_nama] = $pilihan['text'];
            }else{
                $array_replace[$kode_nama] = $pilihan;
            }
            // VALUE
            $kode_value = str_replace('*', $key, DDKPatternEnum::KODE_VALUE);
            if(in_array($kode_field, array_keys(DDKEnum::semuaCheckbox()))){
                // NOTE : $i = 1 s.d 4 karena checkbox KODE_SUMBER_AIR_MINUM dan KODE_KEPEMILIKAN_LAHAN memiliki 4 pilihan
                for($i = 1; $i <= 4; $i++){
                    if(!is_array($terpilih)  || ! array_key_exists($no, $terpilih)){
                        $array_replace[$kode_value . $i] = '';
                        continue;
                    }

                    $value_terpilih = $terpilih[$no];
                    if($kode_field == DDKEnum::KODE_SUMBER_AIR_MINUM){
                        $array_replace[$kode_value . $i] = in_array($i, DDKPilihanCheckboxEnum::getKodeSumberAirMinum($value_terpilih))
                            ? $check_mark : '';
                    }elseif($kode_field == DDKEnum::KODE_KEPEMILIKAN_LAHAN){
                        $array_replace[$kode_value . $i] = $i == $value_terpilih
                            ? $check_mark : '';
                    }
                }

                continue;
            }
            if($terpilih != null){
                if(is_array($terpilih) && in_array($kode_field, array_keys(DDKEnum::semuaJumlah()))){
                    $data = $detail_collection->firstWhere('kode_field', $kode_field);
                    $data = ( ! $data) ? collect(['value' => '']) : $data;
                    $array_replace[$kode_value] = array_key_exists($no, $terpilih)
                        ? $data->value[$no] : '';
                }elseif(is_array($terpilih) && in_array($kode_field, array_keys(DDKEnum::semuaMultipleSelect()))){
                    $array_replace[$kode_value] = in_array($no, $terpilih)
                        ? $check_mark : '';
                }else{
                    $array_replace[$kode_value] = ( ! is_array($terpilih) && $no == $terpilih)
                        ? $check_mark : '';
                }
            }else{
                $array_replace[$kode_value] = '';
            }
        }
    }

    private function replaceBuffer(&$buffer, &$array_replace, &$array_key = [])
    {
        // Optimasi pertama
        $keys = array_map(function ($key) {
            return '/' . preg_quote($key, '/') . '/';
        }, array_keys($array_replace));
        $buffer = preg_replace($keys, array_values($array_replace), $buffer);
        // log_message('info', 'Replacing rtf kode : ' . json_encode(array_keys($array_replace)));
        $array_replace = [];
    }

    public function createHashTemplateFile()
    {
        $hash = ProdeskelCustomValue::createMany([
            [
                'kategori' => DDKEnum::KATEGORI,
                'kode_value' => DDKEnum::HASH_TEMPLATE_DDK,
                'value' => hash_file(DDKEnum::HASH_ALGO, DDKEnum::PATH_TEMPLATE . DDKEnum::FILE_TEMPLATE_DDK),
                'value_long' => '',
            ],
            [
                'kategori' => DDKEnum::KATEGORI,
                'kode_value' => DDKEnum::HASH_TEMPLATE_DDK_ANGGOTA,
                'value' => hash_file(DDKEnum::HASH_ALGO, DDKEnum::PATH_TEMPLATE . DDKEnum::FILE_TEMPLATE_DDK_ANGGOTA),
                'value_long' => '',
            ]
        ]);
    }
}

<?php

/*
 *
 * File ini bagian dari:
 *
 * OpenSID
 *
 * Sistem informasi desa sumber terbuka untuk memajukan desa
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * Hak Cipta 2016 - 2024 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 *
 * Dengan ini diberikan izin, secara gratis, kepada siapa pun yang mendapatkan salinan
 * dari perangkat lunak ini dan file dokumentasi terkait ("Aplikasi Ini"), untuk diperlakukan
 * tanpa batasan, termasuk hak untuk menggunakan, menyalin, mengubah dan/atau mendistribusikan,
 * asal tunduk pada syarat berikut:
 *
 * Pemberitahuan hak cipta di atas dan pemberitahuan izin ini harus disertakan dalam
 * setiap salinan atau bagian penting Aplikasi Ini. Barang siapa yang menghapus atau menghilangkan
 * pemberitahuan ini melanggar ketentuan lisensi Aplikasi Ini.
 *
 * PERANGKAT LUNAK INI DISEDIAKAN "SEBAGAIMANA ADANYA", TANPA JAMINAN APA PUN, BAIK TERSURAT MAUPUN
 * TERSIRAT. PENULIS ATAU PEMEGANG HAK CIPTA SAMA SEKALI TIDAK BERTANGGUNG JAWAB ATAS KLAIM, KERUSAKAN ATAU
 * KEWAJIBAN APAPUN ATAS PENGGUNAAN ATAU LAINNYA TERKAIT APLIKASI INI.
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * @copyright Hak Cipta 2016 - 2024 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 * @link      https://github.com/OpenSID/OpenSID
 *
 */

namespace Modules\Prodeskel\Services;

use App\Enums\StatusDasarEnum;
use Modules\Prodeskel\Models\Keluarga;
use Modules\Prodeskel\Models\Penduduk;
use Modules\Prodeskel\Services\ProdeskelDDKPilihanServices;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Prodeskel\Enums\DDKEnum;
use Modules\Prodeskel\Enums\DDKLabelEnum;
use Modules\Prodeskel\Enums\DDKMaxCustomDataPilihanEnum;
use Modules\Prodeskel\Enums\DDKPilihanCheckboxEnum;
use Modules\Prodeskel\Enums\DDKPilihanMultipleJumlahEnum;
use Modules\Prodeskel\Enums\DDKPilihanMultipleSelectEnum;
use Modules\Prodeskel\Enums\DDKPilihanProduksiTahunIniEnum;
use Modules\Prodeskel\Enums\DDKPilihanSelectEnum;
use Modules\Prodeskel\Models\ProdeskelDDK;
use Modules\Prodeskel\Models\ProdeskelDDKBahanGalianAnggota;
use Modules\Prodeskel\Models\ProdeskelDDKDetail;
use Modules\Prodeskel\Models\ProdeskelDDKProduksi;
use OpenSpout\Reader\Common\Creator\ReaderEntityFactory;
use OpenSpout\Writer\Common\Creator\Style\BorderBuilder;
use OpenSpout\Writer\Common\Creator\Style\StyleBuilder;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use OpenSpout\Writer\XLSX\Writer;
use Throwable;

defined('BASEPATH') || exit('No direct script access allowed');

class ProdeskelDDKImporServices extends ProdeskelDDKServices
{
    protected $to_be_update_ddk = [];
    protected $to_be_insert_ddk = [];

    /**
     * ['prodeskel_ddk_id' => int, 'keluarga_id' => int, 'kode_field' => string, 'value' => json_encode,]
     */
    protected $to_be_update_ddk_detail = [];

    /**
     * ['prodeskel_ddk_id' => null, 'keluarga_id' => int, 'kode_field' => string, 'value' => json_encode,]
     */
    protected $to_be_insert_ddk_detail = [];

    protected $to_be_insert_produksi_tahun_ini = [];
    protected $to_be_update_produksi_tahun_ini = [];

    /**
     * $to_be_deleted_group_by_prodeskel_ddk_id_produksi[prodeskel_ddk_id] = array of kode_komoditas
     * */
    protected $to_be_deleted_group_by_prodeskel_ddk_id_produksi = [];

    /**
     * ['prodeskel_ddk_id' => null, 'keluarga_id' => int, 'kode_field' => string, 'penduduk_id' => 'value' => json_encode,]
     */
    protected $to_be_update_bahan_galian = [];

    protected $to_be_insert_bahan_galian = [];

    /**
     * $to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian[prodeskel_ddk_id][penduduk_id] = array of kode_komoditas
     * */
    protected $to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian = [];

    protected $messages = [
        'error'   => [],
        'success' => [],
    ];
    protected Collection $semua_keluarga;
    protected $excel_reader;

    public function downloadDaftarDataImpor($request)
    {
        try {
            $ids = explode(',', $request['ids']);
            if (empty($request) || empty($ids) || ! is_array($ids)) {
                redirect_with('error', 'Not Acceptable', 'prodeskel/ddk/impor');
            }
            $keluarga = Keluarga::status()
                ->when(array_search('semua', $ids) === false, static function ($builder) use ($ids) {
                    $builder->whereIn('id', $ids);
                })
                ->with([
                    'kepalaKeluarga' => static function ($builder) {
                        $builder->withOnly('wilayah');
                        $builder->status(StatusDasarEnum::HIDUP);
                    },
                    'anggota' => static function ($builder) {
                        $builder->withOnly([]);
                        $builder->status(StatusDasarEnum::HIDUP);
                    },
                    'prodeskelDDK',
                    'prodeskelDDK.produksi',
                    'prodeskelDDK.detail',
                    'prodeskelDDK.bahanGalianAnggota',
                ])
                ->get();

            $writer   = WriterEntityFactory::createXLSXWriter();
            $fileName = 'Daftar Keluarga dan Anggota DDK.xlsx';
            $writer->openToBrowser($fileName); // stream data directly to the browser
            $sheets = DDKEnum::sheetsName();

            // set nama sheet kemudian replace $sheets[index] dengan Sheet Object
            foreach ($sheets as $index => $name) {
                if ($index == 0) {
                    $sheet = $writer->getCurrentSheet();
                } else {
                    $sheet = $writer->addNewSheetAndMakeItCurrent();
                }
                $sheet->setName($name);
                $sheets[$index] = $sheet;
            }

            $this->setHeaderRowSheet($writer, $sheets);

            foreach ($keluarga as $index => $keluarganya) {
                if ($index == 0) {
                    $ci = &get_instance();
                    $ci->load->helper('tglindo_helper');
                }
                $this->generateExcelDaftarDataKeluarga($writer, $sheets, $keluarganya);

                foreach ($keluarganya->anggota as $anggota) {
                    $this->generateExcelDaftarDataAnggotaKeluarga($writer, $sheets, $keluarganya, $anggota);
                }
            }
            $this->generateExcelDaftarPilihan($writer, $sheets);

            $writer->close();

        } catch (Throwable $th) {
            log_message('error', $th->getMessage());
            show_error($th->getMessage());
        }
    }

    private function generateExcelDaftarDataKeluarga(Writer &$writer, $sheets, Keluarga $keluarga)
    {
        $ddk = $keluarga->prodeskelDDK;

        // 0 => 'DDK'
        $writer->setCurrentSheet($sheets[0]);
        $column_data = [
            $keluarga->kepalaKeluarga->nama ?? '',
            $keluarga->no_kk ?? '',
            $ddk->nama_pengisi ?? '',
            $ddk->pekerjaan ?? '',
            $ddk->jabatan ?? '',
            $ddk->bulan ?? date('m'),
            $ddk->tahun ?? date('Y'),
            $ddk->sumber_data_1 ?? '',
            $ddk->sumber_data_2 ?? '',
            $ddk->sumber_data_3 ?? '',
            $ddk->sumber_data_4 ?? '',
            '', // empty column
            $ddk->jumlah_penghasilan_perbulan ?? '',
            $ddk->jumlah_pengeluaran_perbulan ?? '',
        ];
        $semua_kode = array_keys(DDKMaxCustomDataPilihanEnum::semua());
        $semua_kode = array_filter($semua_kode, static function ($item) {
            $kecuali = array_merge(
                array_keys(DDKEnum::semuaCheckbox()), // 1.4 dan 1.5
                array_keys(DDKMaxCustomDataPilihanEnum::KATEGORI_PRODUKSI_TAHUN_INI), // 1.6
                [
                    DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI,
                    DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN,
                    DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN,
                ],
                DDKEnum::semuaKodeAnggota()
            );

            return ! in_array($item, $kecuali);
        });

        foreach ($semua_kode as $kode_field) {
            $value = $ddk->detailKeluarga[$kode_field]->value ?? '';
            if (is_array($value)) {
                $value2        = implode(', ', $value);
                $column_data[] = $value2;
            } else {
                $column_data[] = $value;
            }
        }
        $writer->addRow(WriterEntityFactory::createRowFromArray($column_data));

        // 1 => 'DDK 1.4 s.d 1.5',
        $writer->setCurrentSheet($sheets[1]);
        $column_datas = [];

        foreach ([DDKEnum::KODE_SUMBER_AIR_MINUM, DDKEnum::KODE_KEPEMILIKAN_LAHAN] as $kode_field) {
            $row = 1;

            foreach ($ddk->detailKeluarga[$kode_field]->value ?? [] as $value_pilihan => $value_kondisi) {
                $column_datas[$row][$kode_field] = [
                    $keluarga->kepalaKeluarga->nama ?? '',
                    $keluarga->no_kk ?? '',
                ];
                $column_datas[$row][$kode_field][] = $value_pilihan;

                for ($i = 1; $i <= 4; $i++) {
                    $column_datas[$row][$kode_field][] = ($value_kondisi == $i) ? '1' : '';
                }
                $column_datas[$row][$kode_field][] = ''; // empty column
                $row++;
            }
        }
        if (count($column_datas) > 0) {
            $writer->addRows(array_map(static function ($item) {
                $merged = array_merge(...array_values($item));

                return WriterEntityFactory::createRowFromArray($merged);
            }, $column_datas));
        }

        // 2 => 'DDK 1.6',
        $writer->setCurrentSheet($sheets[2]);
        $column_datas = [];

        foreach ($ddk->produksi ?? [] as $produksi) {
            $jenis_komoditas_kode = substr($produksi->kode_komoditas, 0, strrpos($produksi->kode_komoditas, '_'));
            $jenis_komoditas      = DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI[$jenis_komoditas_kode];
            $komoditas            = substr($produksi->kode_komoditas, strrpos($produksi->kode_komoditas, '_') + 1);
            $satuan               = DDKPilihanProduksiTahunIniEnum::DATA[$jenis_komoditas][$komoditas]['satuan'] ?? '';
            $column_datas[]       = [
                $keluarga->kepalaKeluarga->nama ?? '',
                $keluarga->no_kk ?? '',
                $jenis_komoditas,
                $komoditas,
                $produksi->jumlah_pohon,
                $produksi->luas_panen,
                $produksi->nilai_produksi_per_satuan,
                $satuan,
                $produksi->pemasaran_hasil,
            ];
        }
        if (count($column_datas) > 0) {
            $writer->addRows(array_map(static fn ($item) => WriterEntityFactory::createRowFromArray($item), $column_datas));
        }

        // // 3 => 'DDK 1.7 s.d 1.8',
        $writer->setCurrentSheet($sheets[3]);
        $column_datas = [];

        foreach ([DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI, DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN] as $kode_field) {
            $row = 1;

            foreach ($ddk->detailKeluarga[$kode_field]->value ?? [] as $value_pilihan => $value_kondisi) {
                $column_datas[$row][$kode_field] = [
                    $keluarga->kepalaKeluarga->nama ?? '',
                    $keluarga->no_kk ?? '',
                    $value_pilihan ?? '',
                    $value_kondisi ?? '',
                    '', // empty column
                ];
                $row++;
            }
        }
        if (count($column_datas) > 0) {
            $writer->addRows(array_map(static function ($item) {
                $merged = array_merge(...array_values($item));

                return WriterEntityFactory::createRowFromArray($merged);
            }, $column_datas));
        }
        // // 4 => 'DDK 1.18',
        $writer->setCurrentSheet($sheets[4]);
        $column_datas = [];
        $kode_field   = DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN;
        $row          = 1;

        foreach ($ddk->detailKeluarga[$kode_field]->value ?? [] as $value_pilihan => $value_kondisi) {
            $column_datas[$row] = [
                $keluarga->kepalaKeluarga->nama ?? '',
                $keluarga->no_kk ?? '',
                $value_pilihan ?? '',
                $value_kondisi ?? '',
            ];
            $row++;
        }
        if (count($column_datas) > 0) {
            $writer->addRows(array_map(static fn ($item) => WriterEntityFactory::createRowFromArray($item), $column_datas));
        }
    }

    private function generateExcelDaftarDataAnggotaKeluarga(Writer &$writer, $sheets, Keluarga $keluarga, Penduduk $anggota)
    {
        $ddk = $keluarga->prodeskelDDK;

        // 5 => 'DDK Anggota',
        $writer->setCurrentSheet($sheets[5]);
        $column_data = [
            strtoupper($keluarga->kepalaKeluarga->nama) ?? '',
            $keluarga->no_kk ?? '',
            $anggota->nik ?? '',
            $anggota->nama ?? '',
            $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_TANGGAL_PENCATATAN_LAHIR]->value ?? '',
            implode(', ', $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_AKSEPTOR_KB]->value ?? []),
        ];
        $semua_kode = array_keys(DDKEnum::semuaKhususAnggotaTanpaGalian());
        $semua_kode = array_filter($semua_kode, static fn ($item) => ! in_array($item, [DDKEnum::KODE_TANGGAL_PENCATATAN_LAHIR, DDKEnum::KODE_AKSEPTOR_KB]));

        foreach ($semua_kode as $kode_field) {
            $value = $ddk->detailAnggota[$anggota->id][$kode_field]->value ?? '';
            if (is_array($value)) {
                $value2        = implode(', ', $value);
                $column_data[] = $value2;
            } else {
                $column_data[] = $value;
            }
        }
        $writer->addRow(WriterEntityFactory::createRowFromArray($column_data));

        // 6 => 'DDK Anggota 2.7',
        $writer->setCurrentSheet($sheets[6]);
        $column_datas = [];
        if ($ddk->bahanGalianAnggota) {
            $ddk_produksi_datas = $ddk->bahanGalianAnggota->where('penduduk_id', $anggota->id);
            if ($ddk_produksi_datas->count() > 0) {
                foreach ($ddk_produksi_datas as $bahan_galian_anggota) {
                    $column_datas[] = [
                        strtoupper($keluarga->kepalaKeluarga->nama) ?? '',
                        $keluarga->no_kk ?? '',
                        $anggota->nik ?? '',
                        $anggota->nama ?? '',
                        $bahan_galian_anggota->kode_komoditas ?? '',
                        $bahan_galian_anggota->nilai_produksi ?? '',
                        $bahan_galian_anggota->milik_adat ?? '',
                        $bahan_galian_anggota->milik_perorangan ?? '',
                        $bahan_galian_anggota->pemasaran_hasil ?? '',
                    ];
                }
                $writer->addRows(array_map(static fn ($item) => WriterEntityFactory::createRowFromArray($item), $column_datas));
            }
        }
    }

    public function generateExcelDaftarPilihan(Writer &$writer, $sheets_object)
    {
        // 7 => 'Kode Pilihan',
        $writer->setCurrentSheet($sheets_object[7]);
        $column_datas       = [];
        $semua_kode_pilihan = array_merge([DDKEnum::KODE_AKSEPTOR_KB], array_keys(DDKMaxCustomDataPilihanEnum::semua()));
        natsort($semua_kode_pilihan);
        $max_data_to_row = 1;
        $row             = 1;

        while ($row <= $max_data_to_row) {
            foreach ($semua_kode_pilihan as $kode_field) {
                if ($kode_field == DDKEnum::KODE_SUMBER_AIR_MINUM) {
                    $pilihan = ProdeskelDDKPilihanServices::sumberAirMinum($this->semuaCustomValueDDK(), false);
                } elseif ($kode_field == DDKEnum::KODE_KEPEMILIKAN_LAHAN) {
                    $pilihan = ProdeskelDDKPilihanServices::kepemilikanLahan($this->semuaCustomValueDDK(), false);
                } elseif (in_array($kode_field, DDKPilihanSelectEnum::semuaKode())) {
                    $pilihan = ProdeskelDDKPilihanServices::select($this->semuaCustomValueDDK(), $kode_field, false);
                } elseif (in_array($kode_field, DDKPilihanMultipleJumlahEnum::semuaKode())) {
                    $pilihan = ProdeskelDDKPilihanServices::multipleJumlah($this->semuaCustomValueDDK(), $kode_field, false);
                } elseif (in_array($kode_field, DDKPilihanMultipleSelectEnum::semuaKode())) {
                    $pilihan = ProdeskelDDKPilihanServices::multipleSelect($this->semuaCustomValueDDK(), $kode_field, false);
                } elseif (in_array($kode_field, array_keys(DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI))) {
                    $pilihan = ProdeskelDDKPilihanServices::produksiTahunIni($this->semuaCustomValueDDK(), $kode_field, false);
                    $pilihan = $pilihan[DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI[$kode_field]];
                } elseif ($kode_field == DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA) {
                    $pilihan = ProdeskelDDKPilihanServices::produksiBahanGalianAgt($this->semuaCustomValueDDK(), $kode_field, false);
                } else {
                    log_message('error', 'Pilihan Kode field ' . $kode_field . ' tidak ditemukan');
                }
                $pilihan_count = count($pilihan);
                if ($row == 1) {
                    $max_data_to_row = $pilihan_count > $max_data_to_row ? $pilihan_count : $max_data_to_row;
                }
                // ID
                $column_datas[$row][] = $pilihan_count < $row ? '' : $row;
                // TEXT
                // jika produksi tahun ini, tambahkan text komoditas
                if (in_array($kode_field, array_keys(DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI))) {
                    $column_datas[$row][] = ($pilihan_count < $row ? '' : ($pilihan[$row]['komoditas'] == '' ? '?' : $pilihan[$row]['komoditas'])) ?? '';
                    $column_datas[$row][] = ($pilihan_count < $row ? '' : ($pilihan[$row]['satuan'] == '' ? '?' : $pilihan[$row]['satuan'])) ?? '';
                }
                // text custom value
                elseif (is_array($pilihan[$row])) {
                    $column_datas[$row][] = $pilihan_count < $row ? '' : ($pilihan[$row]['text'] == '' ? '?' : $pilihan[$row]['text']);
                } else {
                    $column_datas[$row][] = ($pilihan_count < $row ? '' : $pilihan[$row]) ?? '';
                }
            }
            $row++;
        }
        $writer->addRows(array_map(static fn ($item) => WriterEntityFactory::createRowFromArray($item), $column_datas));
    }

    private function setHeaderRowSheet(Writer &$writer, $sheets_object)
    {
        $style_1 = (new StyleBuilder())->setFontBold()->setFontSize(14)->build();
        $style_2 = (new StyleBuilder())->setFontBold()->setFontSize(14)->setBorder((new BorderBuilder())->setBorderBottom()->build())->build();

        // 0 => 'DDK'
        $writer->setCurrentSheet($sheets_object[0]);
        $data = [
            ['Nama Kepala Keluarga', ''],
            ['No KK', ''],
            ['Pengisi Data', 'Nama'],
            ['', 'Pekerjaan'],
            ['', 'Jabatan'],
            ['Bulan', ''],
            ['Tahun', ''],
            ['Sumber Data untuk Mengisi Data Dasar Keluarga', '1'],
            ['', '2'],
            ['', '3'],
            ['', '4'],
            ['', ''],
            ['Jumlah Penghasilan dan Pengeluaran Perbulan', '1.1 Penghasilan'],
            ['', '1.2 Pengeluaran'],
            ['1.3 Status Kepemilikan Rumah', ''],
            ['1.9 Pemanfaatan Danau/ Sungai/ Waduk/ situ/ Mata Air oleh Keluarga', ''],
            ['1.10 Lembaga Pendidikan Yang Dimiliki Keluarga /Komunitas', ''],
            ['1.11 Penguasaan Aset Tanah oleh Keluarga', ''],
            ['1.12 Aset Sarana Transportasi Umum', ''],
            ['1.13 Aset Sarana Produksi', ''],
            ['1.14 Aset Perumahan', 'Dinding'],
            ['', 'Lantai'],
            ['', 'Atap'],
            ['1.15 Aset Lainnya dalam Keluarga', ''],
            ['1.16 Kualitas Ibu Hamil dalam Keluarga (jika ada/ pernah ada ibu hamil/nifas)', ''],
            ['1.17 Kualitas Bayi dalam Keluarga (jika ada/ pernah ada bayi)', ''],
            ['1.18 Kualitas Tempat Persalinan dalam Keluarga (jika ada/ pernah ada)', ''],
            ['1.19 Cakupan Imunisasi', ''],
            ['1.20 Penderita Sakit dan Kelainan dalam Keluarga (jika ada/ pernah)', ''],
            ['1.21 Perilaku hidup bersih dan sehat dalam Keluarga', ''],
            ['1.22 Pola makan Keluarga', ''],
            ['1.23 Kebiasaan berobat bila sakit dalam keluarga', ''],
            ['1.24 Status Gizi Balita dalam Keluarga', ''],
            ['1.25 Jenis Penyakit yang diderita Anggota Keluarga', ''],
            ['1.26 Kerukunan', ''],
            ['1.27 Perkelahian', ''],
            ['1.28 Pencurian', ''],
            ['1.29 Penjarahan', ''],
            ['1.30 Perjudian', ''],
            ['1.31 Pemakaian Miras dan Narkoba', ''],
            ['1.32 Pembunuhan', ''],
            ['1.33 Penculikan', ''],
            ['1.34 Kejahatan seksual', ''],
            ['1.35 Kekerasan Dalam Keluarga/ Rumah Tangga', ''],
            ['1.36 Masalah Kesejahteraan Keluarga', ''],
        ];
        $writer->addRows([
            WriterEntityFactory::createRowFromArray(array_column($data, 0), $style_1),
            WriterEntityFactory::createRowFromArray(array_column($data, 1), $style_2),
        ]);

        // 1 => 'DDK 1.4 s.d 1.5',
        $writer->setCurrentSheet($sheets_object[1]);
        $data = [
            ['1.4 Sumber Air Minum yang digunakan anggota keluarga', 'Nama Kepala Keluarga', ''],
            ['', 'No KK', ''],
            ['', 'Sumber Air Minum', ''],
            ['', 'Kondisi Air', 'Baik'],
            ['', '', 'Berasa'],
            ['', '', 'Berwarna'],
            ['', '', 'Berbau'],
            ['', '', ''],
            ['1.5 Kepemilikan Lahan', 'Nama Kepala Keluarga', ''],
            ['', 'No KK', ''],
            ['', 'Jenis Lahan', ''],
            ['', 'Memiliki kurang 0,5 ha', ''],
            ['', 'Memiliki 0,5 - 1,0 ha', ''],
            ['', 'Memiliki Lebih dari 1,0 ha', ''],
            ['', 'Tidak memiliki', ''],
        ];
        $writer->addRows([
            WriterEntityFactory::createRowFromArray(array_column($data, 0), $style_1),
            WriterEntityFactory::createRowFromArray(array_column($data, 1), $style_1),
            WriterEntityFactory::createRowFromArray(array_column($data, 2), $style_2),
        ]);

        // 2 => 'DDK 1.6',
        $writer->setCurrentSheet($sheets_object[2]);
        $data = [
            ['1.6 Produksi tahun ini', 'Nama Kepala Keluarga'],
            ['', 'No KK'],
            ['', 'Jenis Komoditas'],
            ['', 'Komoditas'],
            ['', 'Jumlah Pohon'],
            ['', 'Luas Panen (M2)'],
            ['', 'Produksi'],
            ['', 'Satuan'],
            ['', 'Pemasaran Hasil'],
        ];
        $writer->addRows([
            WriterEntityFactory::createRowFromArray(array_column($data, 0), $style_1),
            WriterEntityFactory::createRowFromArray(array_column($data, 1), $style_2),
        ]);

        // 3 => 'DDK 1.7 s.d 1.8',
        $writer->setCurrentSheet($sheets_object[3]);
        $data = [
            ['1.7 Kepemilikan Jenis Ternak Keluarga Tahun Ini', 'Nama Kepala Keluarga'],
            ['', 'No KK'],
            ['', 'Jenis Binatang Ternak'],
            ['', 'Jumlah (Ekor)'],
            ['', ''],
            ['1.8 Alat produksi budidaya ikan', 'Nama Kepala Keluarga'],
            ['', 'No KK'],
            ['', 'Nama Alat'],
            ['', 'Jumlah'],
        ];
        $writer->addRows([
            WriterEntityFactory::createRowFromArray(array_column($data, 0), $style_1),
            WriterEntityFactory::createRowFromArray(array_column($data, 1), $style_2),
        ]);

        // 4 => 'DDK 1.18',
        $writer->setCurrentSheet($sheets_object[4]);
        $data = [
            ['1.18 Kualitas Pertolongan Persalinan dalam Keluarga (jika ada/pernah ada)', 'Nama Kepala Keluarga'],
            ['', 'No KK'],
            ['', 'Pertolongan Persalinan'],
            ['', 'Jumlah Pertolongan'],
        ];
        $writer->addRows([
            WriterEntityFactory::createRowFromArray(array_column($data, 0), $style_1),
            WriterEntityFactory::createRowFromArray(array_column($data, 1), $style_2),
        ]);

        // 5 => 'DDK Anggota',
        $writer->setCurrentSheet($sheets_object[5]);
        $data = [
            ['Nama Kepala Keluarga', ''],
            ['No KK', ''],
            ['2.1.2 Nomor Induk Kependudukan (NIK) Anggota', ''],
            ['2.1.3 Nama Lengkap Anggota', ''],
            ['2.1.9 Tanggal Pencatatan Lahir (DD-MM-YYYY)', ''],
            ['2.1.17 Akseptor KB', ''],
            ['2.2 Cacat Menurut Jenis', 'Cacat Fisik'],
            ['', 'Cacat Mental'],
            ['2.3 Kedudukan Anggota Keluarga sebagai Wajib Pajak dan Retribusi', ''],
            ['2.4 Lembaga Pemerintahan Yang Diikuti Anggota Keluarga', ''],
            ['2.5 Lembaga Kemasyarakatan Yang Diikuti Anggota Keluarga', ''],
            ['2.6 Lembaga Ekonomi Yang Dimiliki Anggota Keluarga', ''],
        ];
        $writer->addRows([
            WriterEntityFactory::createRowFromArray(array_column($data, 0), $style_1),
            WriterEntityFactory::createRowFromArray(array_column($data, 1), $style_2),
        ]);

        // 6 => 'DDK Anggota 2.7',
        $writer->setCurrentSheet($sheets_object[6]);
        $data = [
            ['2.7 Produksi bahan galian yang dimiliki anggota keluarga', 'Nama Kepala Keluarga'],
            ['', 'No KK'],
            ['', 'NIK Anggota'],
            ['', 'Nama Anggota'],
            ['', 'Jenis Bahan Galian'],
            ['', 'Produksi (Ton/Tahun)'],
            ['', 'Milik Adat'],
            ['', 'Milik Perorangan'],
            ['', 'Pemasaran Hasil'],
        ];
        $writer->addRows([
            WriterEntityFactory::createRowFromArray(array_column($data, 0), $style_1),
            WriterEntityFactory::createRowFromArray(array_column($data, 1), $style_2),
        ]);

        // 7 => 'Kode Pilihan',
        $writer->setCurrentSheet($sheets_object[7]);
        $data = [
            ['1.3 Status Kepemilikan Rumah', '', 'ID'],
            ['', '', 'Text'],
            ['1.4 Sumber Air Minum yang digunakan anggota keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['1.5 Kepemilikan Lahan (Pilihan Keterangan di kolom akhir)', '', 'ID'],
            ['', '', 'Text'],
            ['1.6 Produksi tahun ini', 'Tanaman Pangan', 'ID'],
            ['', '', 'Text'],
            ['', '', 'Satuan'],
            ['1.6 Produksi tahun ini', 'Buah-Buahan', 'ID'],
            ['', '', 'Text'],
            ['', '', 'Satuan'],
            ['1.6 Produksi tahun ini', 'Tanaman Obat', 'ID'],
            ['', '', 'Text'],
            ['', '', 'Satuan'],
            ['1.6 Produksi tahun ini', 'Tanaman Perkebunan', 'ID'],
            ['', '', 'Text'],
            ['', '', 'Satuan'],
            ['1.6 Produksi tahun ini', 'Hasil Hutan', 'ID'],
            ['', '', 'Text'],
            ['', '', 'Satuan'],
            ['1.6 Produksi tahun ini', 'Pengolahan Hasil Ternak', 'ID'],
            ['', '', 'Text'],
            ['', '', 'Satuan'],
            ['1.6 Produksi tahun ini', 'Perikanan', 'ID'],
            ['', '', 'Text'],
            ['', '', 'Satuan'],
            ['1.7 Kepemilikan Jenis Ternak Keluarga Tahun Ini', '', 'ID'],
            ['', '', 'Text'],
            ['1.8 Alat produksi budidaya ikan', '', 'ID'],
            ['', '', 'Text'],
            ['1.9 Pemanfaatan Danau/ Sungai/ Waduk/ situ/ Mata Air oleh Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['1.10 Lembaga Pendidikan Yang Dimiliki Keluarga /Komunitas', '', 'ID'],
            ['', '', 'Text'],
            ['1.11 Penguasaan Aset Tanah oleh Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['1.12 Aset Sarana Transportasi Umum', '', 'ID'],
            ['', '', 'Text'],
            ['1.13 Aset Sarana Produksi', '', 'ID'],
            ['', '', 'Text'],
            ['1.14 Aset Perumahan', 'Dinding', 'ID'],
            ['', '', 'Text'],
            ['', 'Lantai', 'ID'],
            ['', '', 'Text'],
            ['', 'Atap', 'ID'],
            ['', '', 'Text'],
            ['1.15 Aset Lainnya dalam Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['1.16 Kualitas Ibu Hamil dalam Keluarga (jika ada/ pernah ada ibu hamil/nifas)', '', 'ID'],
            ['', '', 'Text'],
            ['1.17 Kualitas Bayi dalam Keluarga (jika ada/ pernah ada bayi)', '', 'ID'],
            ['', '', 'Text'],
            ['1.18 Kualitas Tempat Persalinan dalam Keluarga (jika ada/ pernah ada)', '', 'ID'],
            ['', '', 'Text'],
            ['1.18 Kualitas Pertolongan Persalinan dalam Keluarga (jika ada/ pernah ada)', '', 'ID'],
            ['', '', 'Text'],
            ['1.19 Cakupan Imunisasi', '', 'ID'],
            ['', '', 'Text'],
            ['1.20 Penderita Sakit dan Kelainan dalam Keluarga (jika ada/ pernah)', '', 'ID'],
            ['', '', 'Text'],
            ['1.21 Perilaku hidup bersih dan sehat dalam Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['1.22 Pola makan Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['1.23 Kebiasaan berobat bila sakit dalam keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['1.24 Status Gizi Balita dalam Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['1.25 Jenis Penyakit yang diderita Anggota Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['1.26 Kerukunan', '', 'ID'],
            ['', '', 'Text'],
            ['1.27 Perkelahian', '', 'ID'],
            ['', '', 'Text'],
            ['1.28 Pencurian', '', 'ID'],
            ['', '', 'Text'],
            ['1.29 Penjarahan', '', 'ID'],
            ['', '', 'Text'],
            ['1.30 Perjudian', '', 'ID'],
            ['', '', 'Text'],
            ['1.31 Pemakaian Miras dan Narkoba', '', 'ID'],
            ['', '', 'Text'],
            ['1.32 Pembunuhan', '', 'ID'],
            ['', '', 'Text'],
            ['1.33 Penculikan', '', 'ID'],
            ['', '', 'Text'],
            ['1.34 Kejahatan seksual', '', 'ID'],
            ['', '', 'Text'],
            ['1.35 Kekerasan Dalam Keluarga/ Rumah Tangga', '', 'ID'],
            ['', '', 'Text'],
            ['1.36 Masalah Kesejahteraan Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['2.1.17 Akseptor KB', '', 'ID'],
            ['', '', 'Text'],
            ['2.2 Cacat Menurut Jenis', 'Fisik', 'ID'],
            ['', '', 'Text'],
            ['', 'Mental', 'ID'],
            ['', '', 'Text'],
            ['2.3 Kedudukan Anggota Keluarga sebagai Wajib Pajak dan Retribusi', '', 'ID'],
            ['', '', 'Text'],
            ['2.4 Lembaga Pemerintahan Yang Diikuti Anggota Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['2.5 Lembaga Kemasyarakatan Yang Diikuti Anggota Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['2.6 Lembaga Ekonomi Yang Dimiliki Anggota Keluarga', '', 'ID'],
            ['', '', 'Text'],
            ['2.7 Produksi bahan galian yang dimiliki anggota keluarga', '', 'ID'],
            ['', '', 'Text'],
        ];
        $writer->addRows([
            WriterEntityFactory::createRowFromArray(array_column($data, 0), $style_1),
            WriterEntityFactory::createRowFromArray(array_column($data, 1), $style_1),
            WriterEntityFactory::createRowFromArray(array_column($data, 2), $style_2),
        ]);
    }

    /**
     * @param mixed $request
     */
    public function prosesImporExcelUbahan($request)
    {
        $impor_file  = $_FILES['userfile'];
        $redirect_to = 'prodeskel/ddk/impor';
        // Adakah berkas yang disertakan?
        if (empty($impor_file['name'])) {
            redirect_with('error', 'Pilih File terlebih dahulu', $redirect_to);
        }
        // Tes tidak berisi script PHP
        if (isPHP($impor_file['tmp_name'], $impor_file['name'])) {
            redirect_with('error', 'Jenis file ini tidak diperbolehkan', $redirect_to);
        }
        $mime_type_excel = ['application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel.sheet.macroenabled.12'];
        if (! in_array(strtolower($impor_file['type']), $mime_type_excel)) {
            redirect_with('error', 'Jenis file salah: ' . $impor_file['type'], $redirect_to);
        }

        try {
            $this->excel_reader = ReaderEntityFactory::createXLSXReader();
            $this->excel_reader->setShouldFormatDates(true);
            $this->excel_reader->setShouldPreserveEmptyRows(true);
            $this->excel_reader->open($impor_file['tmp_name']);

            // Ambil semua keluarga
            $this->setSemuaKeluarga();

            $this->prosesSheetDDKdanSimpanDDKBaru();
            $this->prosesSheetDDK1415();
            $this->prosesSheetDDK16();
            $this->prosesSheetDDK1718();
            $this->prosesSheetDDK118();

            $this->prosesSheetDDKAnggota();
            $this->prosesSheetDDKAnggota27();
            $this->messages['success'] = array_merge(
                $this->messages['success'],
                [
                    'Jumlah DDK diubah =' . count($this->to_be_update_ddk) ?? 0,
                    'Jumlah DDK Detail diubah = ' . count($this->to_be_update_ddk_detail) ?? 0,
                    'Jumlah DDK Detail ditambah = ' . count($this->to_be_insert_ddk_detail) ?? 0,
                    'Jumlah DDK Produksi Tahun Ini diubah = ' . count($this->to_be_update_produksi_tahun_ini) ?? 0,
                    'Jumlah DDK Produksi Tahun Ini ditambah = ' . count($this->to_be_insert_produksi_tahun_ini) ?? 0,
                    'Jumlah DDK Produksi Tahun Ini dihapus = ' . count($this->to_be_deleted_group_by_prodeskel_ddk_id_produksi) ?? 0,
                    'Jumlah DDK Bahan Galian Anggota diubah = ' . count($this->to_be_update_bahan_galian) ?? 0,
                    'Jumlah DDK Bahan Galian Anggota ditambah = ' . count($this->to_be_insert_bahan_galian) ?? 0,
                    'Jumlah DDK Bahan Galian Anggota dihapus = ' . count($this->to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian) ?? 0,
                ]
            );
            if (count($this->messages['error']) > 0) {
                $error = nl2br(PHP_EOL . implode(',' . PHP_EOL, $this->messages['error']));
                set_session('error', $error);
            }
            // DB::enableQueryLog();
            $this->simpan();
            // log_message('error', str_replace('query', "\nquery", json_encode(DB::getQueryLog())));

            $this->excel_reader->close();
            redirect_with('success', 'Data penduduk berhasil diimpor.' . nl2br(PHP_EOL . implode(',' . PHP_EOL, $this->messages['success'])), $redirect_to);
        } catch (Exception $e) {
            log_message('error', $e->getMessage());

            redirect_with('error', 'Data penduduk gagal diimpor.' . $e->getMessage(), $redirect_to);
        }

        exit;
    }

    private function getSheetByName($sheet_name)
    {
        foreach ($this->excel_reader->getSheetIterator() as $item) {
            if ($item->getName() === $sheet_name) {
                return $item;
            }
        }

        return [];
    }

    private function setSemuaKeluarga()
    {
        $semua_no_kk = [];

        foreach ($this->getSheetByName(DDKEnum::sheetsName()[0])->getRowIterator() as $row_index => $row) {
            if ($row_index < 5) {
                continue;
            }
            $semua_no_kk[] = ($row->getCells()[1] ? $row->getCells()[1]->getValue() : '');
        }

        $this->semua_keluarga = Keluarga::status()->select(['id', 'no_kk', 'nik_kepala'])
            ->withoutDefaultRelations()
            ->whereIn('no_kk', $semua_no_kk)
            ->with([
                'kepalaKeluarga' => static function ($builder) {
                    $builder->select(['id', 'nama', 'nik', 'id_kk', 'kk_level']);
                    $builder->withoutDefaultRelations();
                    $builder->status(StatusDasarEnum::HIDUP);
                },
                'anggota' => static function ($builder) {
                    $builder->select(['id', 'nama', 'nik', 'id_kk', 'kk_level']);
                    $builder->withOnly([]);
                    $builder->status(StatusDasarEnum::HIDUP);
                },
                'prodeskelDDK',
                'prodeskelDDK.produksi' => static function ($builder) {
                    $builder->withTrashed();
                },
                'prodeskelDDK.detail',
                'prodeskelDDK.bahanGalianAnggota' => static function ($builder) {
                    $builder->withTrashed();
                },
            ])
            ->get();
    }

    /** handle DDK Detail Data
     * - ddk_id exists or not, and
     *
     * @param mixed $ddk_id_or_false
     * @param mixed $keluarga_id
     * @param mixed $kode_field
     * @param mixed $kode_index_value
     * @param mixed $item_detail_data
     * - both of keluarga_id and kode_field is exists or not */
    private function handleDdkDetailData($ddk_id_or_false, $keluarga_id, $kode_field, $kode_index_value, $item_detail_data)
    {
        $aktifkan_log = false;
        // jika ddk diisi cek dahulu apakah detail nya sudah ada, jika belum ada tambahkan detail
        if ($ddk_id_or_false !== false) {
            $keluarganya = $this->semua_keluarga->firstWhere('id', $keluarga_id);
            $detailnya   = $keluarganya->prodeskelDDK->detail->firstWhere('kode_field', $kode_field);
            if (! $detailnya) {
                $this->to_be_insert_ddk_detail[] = [
                    'prodeskel_ddk_id' => null,
                    'keluarga_id'      => $keluarga_id,
                    'kode_field'       => $kode_field,
                    'value'            => $kode_index_value
                        ? json_encode([$item_detail_data['index_value'] => $item_detail_data['tmp_value']])
                        : null,
                ];

                return;
            }
        }

        if ($ddk_id_or_false === false) {
            $is_exists = array_filter($this->to_be_insert_ddk_detail, static fn ($item_filter) => $item_filter['keluarga_id'] == $keluarga_id && $item_filter['kode_field'] == $kode_field);
            if (count($is_exists) === 0) {
                $this->to_be_insert_ddk_detail[] = [
                    'prodeskel_ddk_id' => null,
                    'keluarga_id'      => $keluarga_id,
                    'kode_field'       => $kode_field,
                    'value'            => $kode_index_value
                        ? json_encode([$item_detail_data['index_value'] => $item_detail_data['tmp_value']])
                        : null,
                ];
                $aktifkan_log && log_message('error', 'try4| ' . $kode_field . '|' . json_encode(end($this->to_be_update_ddk_detail)));
            } else {
                $index_to_be_insert_ddk_detail = key($is_exists);
                $aktifkan_log && log_message('error', 'try3| ' . $kode_field . '|' . json_encode($kode_index_value), '|' . json_encode($is_exists) . '|' . json_encode($index_to_be_insert_ddk_detail));
                if ($kode_index_value) {
                    $value                                                                  = json_decode($this->to_be_insert_ddk_detail[$index_to_be_insert_ddk_detail]['value']);
                    $value->{$item_detail_data['index_value']}                              = $item_detail_data['tmp_value'];
                    $this->to_be_insert_ddk_detail[$index_to_be_insert_ddk_detail]['value'] = json_encode($value);
                }
            }
        } elseif ($ddk_id_or_false !== false) {
            $is_exists = array_filter($this->to_be_update_ddk_detail, static fn ($item_filter) => $item_filter['keluarga_id'] == $keluarga_id && $item_filter['kode_field'] == $kode_field);
            if (count($is_exists) === 0) {
                $this->to_be_update_ddk_detail[] = [
                    'prodeskel_ddk_id' => $ddk_id_or_false,
                    'keluarga_id'      => $keluarga_id,
                    'kode_field'       => $kode_field,
                    'value'            => $kode_index_value
                        ? json_encode([$item_detail_data['index_value'] => $item_detail_data['tmp_value']])
                        : null,
                ];
                $aktifkan_log && log_message('error', 'try2| ' . $kode_field . '|' . json_encode(end($this->to_be_update_ddk_detail)));
            } else {
                $index_to_be_update_ddk_detail = key($is_exists);
                $aktifkan_log && log_message('error', 'try| ' . $kode_field . '|' . json_encode($kode_index_value), '|' . json_encode($is_exists) . '|' . json_encode($index_to_be_update_ddk_detail));
                if ($kode_index_value) {
                    $value                                                                  = json_decode($this->to_be_update_ddk_detail[$index_to_be_update_ddk_detail]['value']);
                    $value->{$item_detail_data['index_value']}                              = $item_detail_data['tmp_value'];
                    $this->to_be_update_ddk_detail[$index_to_be_update_ddk_detail]['value'] = json_encode($value);
                }
            }
        } else {
            $aktifkan_log && log_message('error', 'try5| ' . $kode_field . '|' . json_encode([
                '$ddk_id_or_false'  => $ddk_id_or_false,
                '$keluarga_id'      => $keluarga_id,
                '$kode_field'       => $kode_field,
                '$kode_index_value' => $kode_index_value,
                '$item_detail_dat'  => $item_detail_data,
            ]));
        }
    }

    /**
     * DDK 1.3, 1.9 s.d 1.36
     */
    private function prosesSheetDDKdanSimpanDDKBaru()
    {
        $first_row_index = 5;
        $sheet_index     = 0;

        foreach ($this->getSheetByName(DDKEnum::sheetsName()[$sheet_index])->getRowIterator() as $row_index => $row) {
            if ($row_index < $first_row_index) {
                continue;
            }

            $cells = $row->getCells();
            if (count($cells) == 0 || $cells[1]->getValue() == '') {
                continue;
            }
            $keluarga = $this->semua_keluarga->firstWhere('no_kk', $cells[1]->getValue());
            if ( ! $keluarga) {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} : Keluarga tidak ditemukan";

                continue;
            }

            $ddk_data = [
                'keluarga_id'                 => $keluarga->id,
                'bulan'                       => empty_as_null_or_value($cells[5]->getValue()),
                'tahun'                       => empty_as_null_or_value($cells[6]->getValue()),
                'nama_pengisi'                => empty_as_null_or_value(nama($cells[2]->getValue())),
                'pekerjaan'                   => empty_as_null_or_value(nama($cells[3]->getValue())),
                'jabatan'                     => empty_as_null_or_value(nama($cells[4]->getValue())),
                'sumber_data_1'               => empty_as_null_or_value(alamat($cells[7]->getValue())),
                'sumber_data_2'               => empty_as_null_or_value(alamat($cells[8]->getValue())),
                'sumber_data_3'               => empty_as_null_or_value(alamat($cells[9]->getValue())),
                'sumber_data_4'               => empty_as_null_or_value(alamat($cells[10]->getValue())),
                'jumlah_penghasilan_perbulan' => empty_as_null_or_value(bilangan_titik($cells[12]->getValue())),
                'jumlah_pengeluaran_perbulan' => empty_as_null_or_value(bilangan_titik($cells[13]->getValue())),
            ];
            $ddk_detail_data = [];

            $semua_kode = array_keys(DDKMaxCustomDataPilihanEnum::semua());
            $semua_kode = array_filter($semua_kode, static function ($item) {
                $kecuali = array_merge(
                    array_keys(DDKEnum::semuaCheckbox()), // 1.4 dan 1.5
                    array_keys(DDKMaxCustomDataPilihanEnum::KATEGORI_PRODUKSI_TAHUN_INI), // 1.6
                    [
                        DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI,
                        DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN,
                        DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN,
                    ],
                    DDKEnum::semuaKodeAnggota()
                );

                return ! in_array($item, $kecuali);
            });
            natsort($semua_kode);

            $cell_index = 14; //O

            foreach ($semua_kode as $kode_field) {
                $value                        = $cells[$cell_index] ? $cells[$cell_index]->getValue() : '';
                $value                        = $this->sesuaikanValueSebelumDisimpan($value, $kode_field, false);
                $ddk_detail_data[$kode_field] = $value;
                $cell_index++;
            }
            if ( ! $keluarga->prodeskelDDK) {
                $this->to_be_insert_ddk[]      = $ddk_data;
                $this->to_be_insert_ddk_detail = array_merge(
                    $this->to_be_insert_ddk_detail,
                    array_map(static function ($key, $item) use ($keluarga) {
                        return [
                            'prodeskel_ddk_id' => null,
                            'keluarga_id'      => $keluarga->id,
                            'kode_field'       => $key,
                            'value'            => $item,
                        ];
                    }, array_keys($ddk_detail_data), array_values($ddk_detail_data))
                );
            } else {
                $this->to_be_update_ddk[]      = array_merge($ddk_data, ['id' => $keluarga->prodeskelDDK->id]);
                $this->to_be_update_ddk_detail = array_merge(
                    $this->to_be_update_ddk_detail,
                    array_map(static function ($key, $item) use ($keluarga) {
                        return [
                            'prodeskel_ddk_id' => $keluarga->prodeskelDDK->id,
                            'keluarga_id'      => $keluarga->id,
                            'kode_field'       => $key,
                            'value'            => $item,
                        ];
                    }, array_keys($ddk_detail_data), array_values($ddk_detail_data))
                );
            }
        }

        // Insert DDK
        try {
            if (count($this->to_be_insert_ddk) > 0) {
                $is_created_ddk = ProdeskelDDK::insert($this->to_be_insert_ddk);
                if ( ! $is_created_ddk) {
                    $this->messages['error'][] = 'Error proses Simpan DDK Baru';
                    log_message('error', 'error insert new ddk');
                }
                $additional_ddk_imported = ProdeskelDDK::select('id', 'keluarga_id')
                    ->whereIn('keluarga_id', collect($this->to_be_insert_ddk)->pluck('keluarga_id'))
                    ->get();

                // Append the results of the second query to the existing collection
                $this->semua_keluarga = $this->semua_keluarga->transform(static function ($item) use ($additional_ddk_imported) {
                    if (! $item->prodeskelDDK) {
                        $ddk_keluarga = $additional_ddk_imported->firstWhere('keluarga_id', $item->id);
                        if ($ddk_keluarga) {
                            $item->prodeskelDDK = $ddk_keluarga;
                        }
                    }

                    return $item;
                });
                $this->messages['success'][] = 'Jumlah DDK ditambah = ' . count($this->to_be_insert_ddk) ?? 0;
            }
            // exit;
        } catch (Throwable $th) {
            log_message('error', 'insert_ddk: ' . json_encode($th->getMessage()));
        }
    }

    /**
     * DDK 1.4 s.d 1.5
     */
    private function prosesSheetDDK1415()
    {
        $first_row_index = 5;
        $sheet_index     = 1;
        $ddk_detail_data = [];

        foreach ($this->getSheetByName(DDKEnum::sheetsName()[$sheet_index])->getRowIterator() as $row_index => $row) {
            if ($row_index < $first_row_index) {
                continue;
            }

            $cells = $row->getCells();
            if (count($cells) == 0 || $cells[1]->getValue() == '') {
                continue;
            }

            $keluarga = $this->semua_keluarga->firstWhere('no_kk', $cells[1]->getValue());
            if ( ! $keluarga && $cells[1]->getValue() != '') {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} KODE_SUMBER_AIR_MINUM : Keluarga tidak ditemukan";

                continue;
            }
            if ($cells[1]->getValue() != '') {
                // DDKPilihanCheckboxEnum::SUMBER_AIR_MINUM_CHECKBOX;
                if ($cells[3]->getValue() == '1') { // kondisi baik
                    $ddk_detail_data[DDKEnum::KODE_SUMBER_AIR_MINUM][] = [
                        'index_value'     => $cells[2]->getValue(),
                        'tmp_value'       => 1,
                        'keluarga_id'     => $keluarga->id,
                        'ddk_id_or_false' => $keluarga->prodeskelDDK ? $keluarga->prodeskelDDK->id : false,
                    ];
                } else {
                    $value = [ // can be multi value
                        $cells[4]->getValue() == '1' ? 2 : 0, // kondisi berasa
                        $cells[5]->getValue() == '1' ? 3 : 0, // kondisi berwarna
                        $cells[6]->getValue() == '1' ? 4 : 0, // kondisi berbau
                    ];
                    $value                                             = array_sum($value);
                    $ddk_detail_data[DDKEnum::KODE_SUMBER_AIR_MINUM][] = [
                        'index_value'     => $cells[2]->getValue(),
                        'tmp_value'       => $value,
                        'keluarga_id'     => $keluarga->id,
                        'ddk_id_or_false' => $keluarga->prodeskelDDK ? $keluarga->prodeskelDDK->id : false,
                    ];
                }
            }

            $keluarga = $this->semua_keluarga->firstWhere('no_kk', $cells[9]->getValue());
            if ( ! $keluarga && $cells[9]->getValue() != '') {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} KODE_KEPEMILIKAN_LAHAN : Keluarga tidak ditemukan";

                continue;
            }
            if ($cells[9]->getValue() != '') {
                // DDKPilihanCheckboxEnum::KEPEMILIKAN_LAHAN_CHECKBOX;
                // single value
                $value                                              = $cells[11]->getValue() == '1' ? 1 : ''; // Memiliki kurang 0,5 ha
                $value                                              = $cells[12]->getValue() == '1' ? 2 : $value; // Memiliki 0,5 - 1,0 ha
                $value                                              = $cells[13]->getValue() == '1' ? 3 : $value; // Memiliki Lebih dari 1,0 ha
                $value                                              = $cells[14]->getValue() == '1' ? 4 : $value; // Tidak memiliki
                $ddk_detail_data[DDKEnum::KODE_KEPEMILIKAN_LAHAN][] = [
                    'index_value'     => $cells[10]->getValue(),
                    'tmp_value'       => $value,
                    'keluarga_id'     => $keluarga->id,
                    'ddk_id_or_false' => $keluarga->prodeskelDDK ? $keluarga->prodeskelDDK->id : false,
                ];
            }
        }

        $row_index = $first_row_index;

        foreach ($ddk_detail_data as $kode_field => $array_detail) {
            foreach ($array_detail as $index => $item_detail_data) {
                $keluarga_id     = $item_detail_data['keluarga_id'];
                $ddk_id_or_false = $item_detail_data['ddk_id_or_false'];

                // tambahkan pesan jika tidak sesuai, kemudian sesuaikan ulang valuenya masing2
                $kode_index_value = $this->sesuaikanValueSebelumDisimpan([
                    $item_detail_data['index_value'] => $item_detail_data['tmp_value'],
                ], $kode_field, true);
                if (! $kode_index_value) {
                    $label                     = DDKLabelEnum::semua()[$kode_field] ?? '';
                    $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index}, {$label}: Pilihan tidak ditemukan";
                }

                $this->handleDdkDetailData($ddk_id_or_false, $keluarga_id, $kode_field, $kode_index_value, $item_detail_data);
            }
            if ($kode_field == DDKEnum::KODE_KEPEMILIKAN_LAHAN) {
                $row_index++;
            }
        }
    }

    /**
     * DDK 1.6
     */
    private function prosesSheetDDK16()
    {
        $first_row_index = 4;
        $sheet_index     = 2;

        $data_gabungan = array_merge(...array_values(ProdeskelDDKPilihanServices::produksiTahunIni($this->semuaCustomValueDDK(), false)));

        foreach ($this->getSheetByName(DDKEnum::sheetsName()[$sheet_index])->getRowIterator() as $row_index => $row) {
            if ($row_index < $first_row_index) {
                continue;
            }

            $cells = $row->getCells();
            if (count($cells) == 0 || $cells[1]->getValue() == '') {
                continue;
            }
            $keluarga = $this->semua_keluarga->firstWhere('no_kk', $cells[1]->getValue());
            if ( ! $keluarga && $cells[1]->getValue() != '') {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} : Keluarga tidak ditemukan";

                continue;
            }
            if ($cells[1]->getValue() != '') {
                $prefix_kode = array_search($cells[2]->getValue(), DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI);
                if ($prefix_kode === false) {
                    $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} : Kode tidak sesuai";

                    continue;
                }
                $kode_komoditas            = $prefix_kode . '_' . $cells[3]->getValue();
                $jumlah_pohon              = $cells[4]->getValue();
                $luas_panen                = $cells[5]->getValue();
                $nilai_produksi_per_satuan = $cells[6]->getValue();
                $pemasaran_hasil           = $cells[8]->getValue();
                // abaikan dan hapus yg tidak ada dalam daftar kode, atau ketiga datanya kosong
                if ( ! in_array($kode_komoditas, array_column($data_gabungan, 'kode'))
                    || (
                        trim($luas_panen) == ''
                        && trim($nilai_produksi_per_satuan) == ''
                        && trim($pemasaran_hasil) == ''
                    )) {
                    $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} : Ada Data yang kosong/Pilihan tidak ditemukan";

                    continue;
                }
                $produksi_id = null;
                if ($keluarga->prodeskelDDK && $keluarga->prodeskelDDK->produksi) {
                    $produksi_id = $keluarga->prodeskelDDK->produksi->firstWhere('kode_komoditas', $kode_komoditas);
                    $produksi_id = $produksi_id ? $produksi_id->id : null;
                }
                if ($keluarga->prodeskelDDK && $produksi_id !== null) {
                    $this->to_be_update_produksi_tahun_ini[] = [
                        'id'                        => $produksi_id,
                        'prodeskel_ddk_id'          => $keluarga->prodeskelDDK->id,
                        'kode_komoditas'            => $kode_komoditas,
                        'deleted_at'                => null,
                        'jumlah_pohon'              => empty_as_null_or_value(bilangan($jumlah_pohon)),
                        'luas_panen'                => empty_as_null_or_value(bilangan_titik($luas_panen)),
                        'nilai_produksi_per_satuan' => empty_as_null_or_value(bilangan_titik($nilai_produksi_per_satuan)),
                        'pemasaran_hasil'           => empty_as_null_or_value(alamat($pemasaran_hasil)),
                    ];
                } else {
                    $this->to_be_insert_produksi_tahun_ini[] = [
                        'prodeskel_ddk_id'          => null,
                        'keluarga_id'  /** Note */  => $keluarga->id, // compare this attr to get prodeskel_ddk_id then remove this attr before inserted to DB
                        'kode_komoditas'            => $kode_komoditas,
                        'jumlah_pohon'              => empty_as_null_or_value(bilangan($jumlah_pohon)),
                        'luas_panen'                => empty_as_null_or_value(bilangan_titik($luas_panen)),
                        'nilai_produksi_per_satuan' => empty_as_null_or_value(bilangan_titik($nilai_produksi_per_satuan)),
                        'pemasaran_hasil'           => empty_as_null_or_value(alamat($pemasaran_hasil)),
                    ];
                }
            }
        }
    }

    /**
     * DDK 1.7 s.d 1.8
     */
    private function prosesSheetDDK1718()
    {
        $first_row_index = 4;
        $sheet_index     = 3;
        $ddk_detail_data = [];

        foreach ($this->getSheetByName(DDKEnum::sheetsName()[$sheet_index])->getRowIterator() as $row_index => $row) {
            if ($row_index < $first_row_index) {
                continue;
            }

            $cells = $row->getCells();
            if (count($cells) == 0 || $cells[1]->getValue() == '') {
                continue;
            }
            // 1.7 Kepemilikan Jenis Ternak Keluarga Tahun Ini
            $keluarga = $this->semua_keluarga->firstWhere('no_kk', $cells[1]->getValue());
            if ( ! $keluarga && $cells[1]->getValue() != '') {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} Kepemilikan Jenis Ternak : Keluarga tidak ditemukan";

                continue;
            }
            if ($cells[1]->getValue() != '') {
                $ddk_detail_data[DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI][] = [
                    'index_value'     => $cells[2]->getValue(),
                    'tmp_value'       => $cells[3]->getValue(),
                    'keluarga_id'     => $keluarga->id,
                    'ddk_id_or_false' => $keluarga->prodeskelDDK ? $keluarga->prodeskelDDK->id : false,
                ];
            }

            // 1.8 Alat produksi budidaya ikan
            $keluarga = $this->semua_keluarga->firstWhere('no_kk', $cells[6]->getValue());
            if ( ! $keluarga && $cells[6]->getValue() != '') {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} Alat produksi budidaya ikan : Keluarga tidak ditemukan";

                continue;
            }
            if ($cells[6]->getValue() != '') {
                $ddk_detail_data[DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN][] = [
                    'index_value'     => $cells[7]->getValue(),
                    'tmp_value'       => $cells[8]->getValue(),
                    'keluarga_id'     => $keluarga->id,
                    'ddk_id_or_false' => $keluarga->prodeskelDDK ? $keluarga->prodeskelDDK->id : false,
                ];
            }
        }

        $row_index = $first_row_index;

        foreach ($ddk_detail_data as $kode_field => $array_detail) {
            foreach ($array_detail as $index => $item_detail_data) {
                $keluarga_id     = $item_detail_data['keluarga_id'];
                $ddk_id_or_false = $item_detail_data['ddk_id_or_false'];

                $kode_index_value = $this->sesuaikanValueSebelumDisimpan([
                    $item_detail_data['index_value'] => $item_detail_data['tmp_value'],
                ], $kode_field, false);
                if (in_array($kode_index_value, [null, 'null'])) {
                    $label                     = DDKLabelEnum::semua()[$kode_field] ?? '';
                    $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index}, {$label}: Pilihan tidak ditemukan";
                }

                $this->handleDdkDetailData($ddk_id_or_false, $keluarga_id, $kode_field, $kode_index_value, $item_detail_data);
            }
            if ($kode_field == DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI) {
                $row_index++;
            }
        }
    }

    /**
     * DDK 1.18
     */
    private function prosesSheetDDK118()
    {
        $first_row_index = 4;
        $sheet_index     = 4;
        $ddk_detail_data = [];

        foreach ($this->getSheetByName(DDKEnum::sheetsName()[$sheet_index])->getRowIterator() as $row_index => $row) {
            if ($row_index < $first_row_index) {
                continue;
            }

            $cells = $row->getCells();
            if (count($cells) == 0 || $cells[1]->getValue() == '') {
                continue;
            }
            $keluarga = $this->semua_keluarga->firstWhere('no_kk', $cells[1]->getValue());
            if ( ! $keluarga && $cells[1]->getValue() != '') {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} Kualitas Pertolongan Persalinan : Keluarga tidak ditemukan";

                continue;
            }
            if ($cells[1]->getValue() != '') {
                $ddk_detail_data[DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN][] = [
                    'index_value'     => $cells[2]->getValue(),
                    'tmp_value'       => $cells[3]->getValue(),
                    'keluarga_id'     => $keluarga->id,
                    'ddk_id_or_false' => $keluarga->prodeskelDDK ? $keluarga->prodeskelDDK->id : false,
                ];
            }
        }

        $row_index = $first_row_index;

        foreach ($ddk_detail_data as $kode_field => $array_detail) {
            foreach ($array_detail as $index => $item_detail_data) {
                $keluarga_id     = $item_detail_data['keluarga_id'];
                $ddk_id_or_false = $item_detail_data['ddk_id_or_false'];

                $kode_index_value = $this->sesuaikanValueSebelumDisimpan([
                    $item_detail_data['index_value'] => $item_detail_data['tmp_value'],
                ], $kode_field, false);
                if (in_array($kode_index_value, [null, 'null'])) {
                    $label                     = DDKLabelEnum::semua()[$kode_field] ?? '';
                    $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index}, {$label} : Pilihan tidak ditemukan";
                }

                $this->handleDdkDetailData($ddk_id_or_false, $keluarga_id, $kode_field, $kode_index_value, $item_detail_data);
            }
            if ($kode_field == DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN) {
                $row_index++;
            }
        }
    }

    private function prosesSheetDDKAnggota()
    {
        $first_row_index = 4;
        $sheet_index     = 5;
        $cells           = [];

        foreach ($this->getSheetByName(DDKEnum::sheetsName()[$sheet_index])->getRowIterator() as $row_index => $row) {
            if ($row_index < $first_row_index) {
                continue;
            }

            $cells = $row->getCells();
            if (count($cells) == 0 || $cells[1]->getValue() == '') {
                continue;
            }
            $keluarga = $this->semua_keluarga->firstWhere('no_kk', $cells[1]->getValue());
            if ( ! $keluarga) {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} : Keluarga tidak ditemukan";

                continue;
            }
            $anggota = $keluarga->anggota->firstWhere('nik', $cells[2]->getValue());
            if ( ! $anggota) {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} : Anggota Keluarga tidak ditemukan";

                continue;
            }

            $semua_kode = array_keys(DDKEnum::semuaKhususAnggotaTanpaGalian());
            $semua_kode = array_filter($semua_kode, static function ($item) {
                $kecuali = array_merge(
                    array_keys(DDKEnum::semuaInputKhususAnggota())
                );

                return ! in_array($item, $kecuali);
            });
            natsort($semua_kode);

            $ddk_detail_data = [
                DDKEnum::KODE_TANGGAL_PENCATATAN_LAHIR => $this->sesuaikanValueSebelumDisimpan(
                    $cells[4]->getValue() !== '' ? date('Y-m-d', strtotime($cells[4]->getValue())) : '',
                    DDKEnum::KODE_TANGGAL_PENCATATAN_LAHIR,
                    true
                ),
            ];

            $cell_index = 5; //F

            foreach ($semua_kode as $kode_field) {
                $value                        = $cells[$cell_index] ? $cells[$cell_index]->getValue() : '';
                $value                        = $this->sesuaikanValueSebelumDisimpan($value, $kode_field, false);
                $ddk_detail_data[$kode_field] = $value;
                $cell_index++;
            }
            if ( ! $keluarga->prodeskelDDK) {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} : Keluarga belum punya Prodeskel DDK";
            } else {
                foreach ($ddk_detail_data as $kode_field => $value) {
                    $detail_ddk = $keluarga->prodeskelDDK->detail->where('penduduk_id', $anggota->id)->where('kode_field', $kode_field)->first();
                    if (! $detail_ddk) {
                        $this->to_be_insert_ddk_detail[] = [
                            'prodeskel_ddk_id' => null,
                            'keluarga_id'      => $keluarga->id,
                            'penduduk_id'      => $anggota->id,
                            'kode_field'       => $kode_field,
                            'value'            => $value,
                        ];
                    } else {
                        $this->to_be_update_ddk_detail[] = [
                            'prodeskel_ddk_id' => $detail_ddk->prodeskel_ddk_id,
                            'keluarga_id'      => $keluarga->id,
                            'penduduk_id'      => $anggota->id,
                            'kode_field'       => $kode_field,
                            'value'            => $value,
                        ];

                    }
                }
            }
        }
    }

    private function prosesSheetDDKAnggota27()
    {
        $first_row_index = 4;
        $sheet_index     = 6;

        foreach ($this->getSheetByName(DDKEnum::sheetsName()[$sheet_index])->getRowIterator() as $row_index => $row) {
            if ($row_index < $first_row_index) {
                continue;
            }

            $cells = $row->getCells();
            if (count($cells) == 0 || $cells[1]->getValue() == '') {
                continue;
            }
            $keluarga = $this->semua_keluarga->firstWhere('no_kk', $cells[1]->getValue());
            if ( ! $keluarga) {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} : Keluarga tidak ditemukan";

                continue;
            }
            $anggota = $keluarga->anggota->firstWhere('nik', $cells[2]->getValue());
            if ( ! $anggota) {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} : Anggota Keluarga tidak ditemukan";

                continue;
            }

            $kode_komoditas  = $cells[4]->getValue();
            $produksi        = $cells[5]->getValue();
            $milik_adat      = $cells[6]->getValue();
            $perorangan      = $cells[7]->getValue();
            $pemasaran_hasil = $cells[8]->getValue();
            // abaikan dan hapus yg tidak ada dalam daftar kode, atau ketiga datanya kosong
            if ( ! in_array($kode_komoditas, array_keys(ProdeskelDDKPilihanServices::produksiBahanGalianAgt($this->semuaCustomValueDDK(), false)))
            || (
                trim($milik_adat) == ''
                && trim($perorangan) == ''
                && trim($produksi) == ''
                && trim($pemasaran_hasil) == ''
            )) {
                $this->messages['error'][] = 'Sheet ' . DDKEnum::sheetsName()[$sheet_index] . " baris {$row_index} : Ada Data yang kosong/Pilihan tidak ditemukan";

                continue;
            }

            $produksi_id = null;
            if ($keluarga->prodeskelDDK && $keluarga->prodeskelDDK->bahanGalianAnggota) {
                $produksi_id = $keluarga->prodeskelDDK->bahanGalianAnggota->where('penduduk_id', $anggota->id)->where('kode_komoditas', $kode_komoditas)->first();
                $produksi_id = $produksi_id ? $produksi_id->id : null;
            }
            if ($keluarga->prodeskelDDK && $produksi_id !== null) {
                $this->to_be_update_bahan_galian[] = [
                    'id'               => (int) $produksi_id,
                    'prodeskel_ddk_id' => $keluarga->prodeskelDDK->id,
                    'penduduk_id'      => $anggota->id,
                    'kode_komoditas'   => $kode_komoditas,
                    'deleted_at'       => null,
                    'nilai_produksi'   => empty_as_null_or_value(bilangan_titik($produksi)),
                    'milik_adat'       => empty_as_null_or_value(alamat($milik_adat)),
                    'milik_perorangan' => empty_as_null_or_value(alamat($perorangan)),
                    'pemasaran_hasil'  => empty_as_null_or_value(alamat($pemasaran_hasil)),
                ];
            } else {
                $this->to_be_insert_bahan_galian[] = [
                    'prodeskel_ddk_id'         => null,
                    'penduduk_id'              => $anggota->id,
                    'keluarga_id'  /** Note */ => $keluarga->id, // compare this attr to get prodeskel_ddk_id then remove this attr before inserted to DB
                    'kode_komoditas'           => $kode_komoditas,
                    'deleted_at'               => null,
                    'nilai_produksi'           => empty_as_null_or_value(bilangan_titik($produksi)),
                    'milik_adat'               => empty_as_null_or_value(alamat($milik_adat)),
                    'milik_perorangan'         => empty_as_null_or_value(alamat($perorangan)),
                    'pemasaran_hasil'          => empty_as_null_or_value(alamat($pemasaran_hasil)),
                ];
            }
        }
    }

    /**
     * Note : menggunakan try-catch pada masing2 langkah untuk memudahkan debug nantinya.
     *        jika salah satu error rollback semua data proses sebelumnya
     */
    private function simpan()
    {
        DB::beginTransaction();
        $all_ddk_imported = $this->semua_keluarga->map(static fn ($item) => $item->prodeskelDDK);

        // Update DDK
        try {
            foreach ($this->to_be_update_ddk as $item) {
                $updated = ProdeskelDDK::where('id', $item['id'])->update($item);
            }
        } catch (Throwable $th) {
            log_message('error', 'update_ddk: ' . json_encode($th->getMessage()));
            DB::rollback();
        }

        // Insert DDK Detail
        try {
            if (count($this->to_be_insert_ddk_detail) > 0) {
                $this->to_be_insert_ddk_detail = array_map(function ($index, $value) use ($all_ddk_imported) {
                    $found = collect($all_ddk_imported)->filter(static fn ($item) => $item->keluarga_id == $value['keluarga_id']);
                    if ($found->count() > 0) {
                        $value['prodeskel_ddk_id'] = $found->first()->id;
                        $value['penduduk_id'] ??= null;

                        return $value;
                    }
                        log_message('error', '1 data gagal diimpor, insert ddk detail, $all_ddk_imported tidak ditemukan, ' . json_encode($value));
                        unset($this->to_be_insert_ddk_detail[$index]);

                }, array_keys($this->to_be_insert_ddk_detail), array_values($this->to_be_insert_ddk_detail));
                $all_ddk_imported_detail = ProdeskelDDKDetail::insert($this->to_be_insert_ddk_detail);
            }
        } catch (Throwable $th) {
            log_message('error', 'insert_ddk_detail: ' . json_encode($th->getMessage()));
            DB::rollback();
        }

        // Update DDK Detail
        try {
            if (count($this->to_be_update_ddk_detail) > 0) {
                foreach ($this->to_be_update_ddk_detail as $item) {
                    if (isset($item['penduduk_id'])) {
                        $updated = ProdeskelDDKDetail::where('keluarga_id', $item['keluarga_id'])
                            ->where('kode_field', $item['kode_field'])
                            ->where('prodeskel_ddk_id', $item['prodeskel_ddk_id'])
                            ->where('penduduk_id', $item['penduduk_id'])
                            ->update($item);

                    } else {
                        $updated = ProdeskelDDKDetail::where('keluarga_id', $item['keluarga_id'])
                            ->where('kode_field', $item['kode_field'])
                            ->where('prodeskel_ddk_id', $item['prodeskel_ddk_id'])
                            ->update($item);
                    }
                }
            }
        } catch (Throwable $th) {
            log_message('error', 'update_ddk_detail: ' . json_encode($th->getMessage()));
            DB::rollback();
        }

        // siapkan daftar data yg akan didelete
        foreach ($all_ddk_imported as $item_ddk) {
            $this->to_be_deleted_group_by_prodeskel_ddk_id_produksi[$item_ddk->id]     = $item_ddk->produksi->pluck('kode_komoditas')->toArray();
            $this->to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian[$item_ddk->id] = $item_ddk->bahanGalianAnggota->groupBy('penduduk_id')->map(static fn ($item_bga) => $item_bga->pluck('kode_komoditas'))->toArray();
        }

        // Update DDK Produksi Tahun Ini, data terupdate dihapus dari daftar persiapan delete
        try {
            if (count($this->to_be_update_produksi_tahun_ini) > 0) {
                foreach ($this->to_be_update_produksi_tahun_ini as $item) {
                    $updated = ProdeskelDDKProduksi::where('kode_komoditas', $item['kode_komoditas'])
                        ->where('prodeskel_ddk_id', $item['prodeskel_ddk_id'])
                        ->update($item);
                    if (array_key_exists($item['prodeskel_ddk_id'], $this->to_be_deleted_group_by_prodeskel_ddk_id_produksi)) {
                        $this->to_be_deleted_group_by_prodeskel_ddk_id_produksi[$item['prodeskel_ddk_id']] = array_filter($this->to_be_deleted_group_by_prodeskel_ddk_id_produksi[$item['prodeskel_ddk_id']], static fn ($item_ddk) => $item['kode_komoditas'] == $item_ddk['kode_komoditas']);
                    }
                }
            }
        } catch (Throwable $th) {
            log_message('error', 'update_produksi_tahun_ini: ' . json_encode($th->getMessage()));
            DB::rollback();
        }

        // Delete unused DDK Produksi Tahun ini
        try {
            if (count($this->to_be_deleted_group_by_prodeskel_ddk_id_produksi) > 0) {
                $deleted = ProdeskelDDKProduksi::query();

                foreach ($this->to_be_deleted_group_by_prodeskel_ddk_id_produksi as $prodeskel_ddk_id => $all_kode_komoditas) {
                    $deleted->orWhere(static function ($query) use ($prodeskel_ddk_id, $all_kode_komoditas) {
                        $query->where('prodeskel_ddk_id', $prodeskel_ddk_id)->whereIn('kode_komoditas', $all_kode_komoditas);
                    });
                }
                $deleted = $deleted->delete();
            }
        } catch (Throwable $th) {
            log_message('error', 'delete_unused_ddk_produksi_tahun_ini: ' . json_encode($th->getMessage()));
            DB::rollback();
        }

        // Insert DDK Produksi Tahun Ini
        try {
            if (count($this->to_be_insert_produksi_tahun_ini) > 0) {
                $this->to_be_insert_produksi_tahun_ini = array_map(function ($index, $value) use ($all_ddk_imported) {
                    $found = collect($all_ddk_imported)->filter(static fn ($item) => $item->keluarga_id == $value['keluarga_id']);
                    if ($found->count()) {
                        unset($value['keluarga_id']);
                        $value['prodeskel_ddk_id'] = $found->first()->id;

                        return $value;
                    }
                        log_message('error', '1 data gagal diimpor, ddk produksi, $all_ddk_imported tidak ditemukan, ' . json_encode($value));
                        unset($this->to_be_insert_produksi_tahun_ini[$index]);

                }, array_keys($this->to_be_insert_produksi_tahun_ini), array_values($this->to_be_insert_produksi_tahun_ini));
                $all_ddk_imported_produksi_tahun_ini = ProdeskelDDKProduksi::insert($this->to_be_insert_produksi_tahun_ini);
            }
        } catch (Throwable $th) {
            log_message('error', 'insert_produksi_tahun_ini: ' . json_encode($th->getMessage()));
            DB::rollback();
        }

        // ########
        // ########
        // ########

        // Update DDK bahan galian anggota, data terupdate dihapus dari daftar persiapan delete
        try {
            if (count($this->to_be_update_bahan_galian) > 0) {
                foreach ($this->to_be_update_bahan_galian as $item) {
                    $updated = ProdeskelDDKBahanGalianAnggota::where('kode_komoditas', $item['kode_komoditas'])
                        ->where('penduduk_id', $item['penduduk_id'])
                        ->where('prodeskel_ddk_id', $item['prodeskel_ddk_id'])
                        ->withTrashed()
                        ->first()
                        ->update($item);
                    if (array_key_exists($item['prodeskel_ddk_id'], $this->to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian)) {
                        if (array_key_exists($item['penduduk_id'], $this->to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian[$item['prodeskel_ddk_id']])) {
                            $this->to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian[$item['prodeskel_ddk_id']][$item['penduduk_id']] = array_diff(
                                $this->to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian[$item['prodeskel_ddk_id']][$item['penduduk_id']],
                                [$item['kode_komoditas']]
                            );
                        }
                    }
                }
            }
        } catch (Throwable $th) {
            log_message('error', 'update_bahan_galian_anggota: ' . json_encode($th->getMessage()));
            DB::rollback();
        }

        // Delete unused DDK bahan galian anggota
        try {
            if (count($this->to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian) > 0) {
                log_message('error', 'deleted' . json_encode($this->to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian));
                $deleted = ProdeskelDDKBahanGalianAnggota::query();

                foreach ($this->to_be_deleted_group_by_prodeskel_ddk_id_bahan_galian as $prodeskel_ddk_id => $all_penduduk) {
                    foreach ($all_penduduk as $penduduk_id => $all_kode_komoditas) {
                        $deleted->orWhere(static function ($query) use ($prodeskel_ddk_id, $penduduk_id, $all_kode_komoditas) {
                            $query->where('prodeskel_ddk_id', $prodeskel_ddk_id)->where('penduduk_id', $penduduk_id)->whereIn('kode_komoditas', $all_kode_komoditas);
                        });
                    }
                }
                $deleted = $deleted->delete();
            }
        } catch (Throwable $th) {
            log_message('error', 'delete_unused_ddk_bahan_galian_anggota: ' . json_encode($th->getMessage()));
            DB::rollback();
        }

        // Insert DDK bahan galian anggota
        try {
            if (count($this->to_be_insert_bahan_galian) > 0) {
                $this->to_be_insert_bahan_galian = array_map(function ($index, $value) use ($all_ddk_imported) {
                    $found = collect($all_ddk_imported)->filter(static fn ($item) => $item->keluarga_id == $value['keluarga_id']);
                    if ($found->count()) {
                        unset($value['keluarga_id']);
                        $value['prodeskel_ddk_id'] = $found->first()->id;

                        return $value;
                    }
                        log_message('error', '1 data gagal diimpor, ddk bahan galian anggota, $all_ddk_imported tidak ditemukan, ' . json_encode($value));
                        unset($this->to_be_insert_bahan_galian[$index]);

                }, array_keys($this->to_be_insert_bahan_galian), array_values($this->to_be_insert_bahan_galian));
                $all_ddk_imported_bahan_galian_anggota = ProdeskelDDKBahanGalianAnggota::insert($this->to_be_insert_bahan_galian);
            }
        } catch (Throwable $th) {
            log_message('error', 'insert_ddk_bahan_galian_anggota: ' . json_encode($th->getMessage()));
            DB::rollback();
        }

        DB::commit();
    }
}

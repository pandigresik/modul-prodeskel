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

use Modules\Prodeskel\Enums\DDKEnum;
use Modules\Prodeskel\Enums\DDKLabelEnum;
use Modules\Prodeskel\Enums\DDKMaxCustomDataPilihanEnum;
use Modules\Prodeskel\Enums\DDKPilihanBahanGalianAnggotaEnum;
use Modules\Prodeskel\Enums\DDKPilihanProduksiTahunIniEnum;
use Modules\Prodeskel\Models\ProdeskelCustomValue;
use Modules\Prodeskel\Services\ProdeskelDDKImporServices;
use Modules\Prodeskel\Services\ProdeskelDDKServices;
use Modules\Prodeskel\Services\ProdeskelDDKTemplateHtmlServices;

defined('BASEPATH') || exit('No direct script access allowed');

class Prodeskel extends Admin_Controller
{    
    public function __construct()
    {
        parent::__construct();
        $this->modul_ini     = 'satu-data';
        $this->sub_modul_ini = 'prodeskel';
    }

    public function pengaturan()
    {
        $navigasi      = 'pengaturan';
        $semua_kode    = array_keys(DDKMaxCustomDataPilihanEnum::semua());
        $custom_values = ProdeskelCustomValue::select('kode_value', 'value_long')
            ->where('kategori', DDKEnum::KATEGORI)
            ->whereIn('kode_value', $semua_kode)
            ->get()
            ->transform(static function ($item) {
                $item->value_long = json_decode($item->value_long, true);

                return $item;
            });
        $label = collect(DDKLabelEnum::semua())
            ->map(static function ($item, $key) use ($custom_values) {
                $custom_values = $custom_values->firstWhere('kode_value', $key);
                if ($custom_values) {
                    $custom_values = $custom_values->toArray();
                }

                $jumlah = DDKMaxCustomDataPilihanEnum::semua()[$key];
                if (array_key_exists($key, DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI)) {
                    $kode_kustom = [];

                    for ($i = 1; $i <= $jumlah; $i++) {
                        $pilihan                           = DDKPilihanProduksiTahunIniEnum::DATA[DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI[$key]];
                        $kode_kustom[count($pilihan) + $i] = ['komoditas' => '', 'satuan' => ''];
                    }
                } elseif ($key == DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA) {
                    $kode_kustom = [];

                    for ($i = 1; $i <= $jumlah; $i++) {
                        $pilihan                           = DDKPilihanBahanGalianAnggotaEnum::DATA;
                        $kode_kustom[count($pilihan) + $i] = '';
                    }
                } else {
                    $kode_kustom = [];

                    for ($i = 1; $i <= $jumlah; $i++) {
                        $pilihan = DDKEnum::valuesOf($key);
                        end($pilihan);
                        $kode_kustom[key($pilihan) + $i] = '';
                    }
                }

                return ['label' => $item, 'custom_values' => $custom_values ?? ['value_long' => $kode_kustom]];
            });
        $config       = App\Models\Config::first();
        $sebutan_desa = setting('sebutan_desa');
        $bulan        = bulan();
        $tahun        = range(2020 - 2, date('Y'));
        $tahun        = array_combine($tahun, $tahun);

        return view('pengaturan', compact('navigasi', 'custom_values', 'label', 'config', 'sebutan_desa', 'bulan', 'tahun'));
    }

    public function ddk()
    {
        $data = ['navigasi' => 'ddk'];

        return view('ddk.index', $data);
    }

    public function datatablesDDK()
    {
        if ($this->input->is_ajax_request()) {
            return (new ProdeskelDDKServices())->datatables();
        }

        show_error('Hanya bisa diakses melalui ajax', 405);
    }

    public function ddkForm($keluarga_id)
    {
        return (new ProdeskelDDKServices())->form($keluarga_id);
    }

    public function ddkSave($tipe, $keluarga_id = null)
    {
        if ( ! $this->input->is_ajax_request()) {
            show_error('Hanya bisa diakses melalui ajax', 405);
        }

        switch($tipe) {
            case 'pengaturan': return (new ProdeskelDDKServices())->savePengaturan($this->request);

            case 'form': return (new ProdeskelDDKServices())->save($keluarga_id, $this->request);

            default: show_404();
        }
    }

    public function ddkCetak($keluarga_id)
    {
        return (new ProdeskelDDKServices())->rtf($keluarga_id);
    }

    public function ddkImpor($tipe = 'impor')
    {
        if ( ! $this->input->is_ajax_request()) {
            switch($tipe) {
                case 'download-template-impor-ddk' :
                    ambilBerkas('TemplateImporDDK.xlsm', null, null, DDKEnum::PATH_TEMPLATE);
                    break;

                case 'download-daftar-data-impor-ddk':
                    (new ProdeskelDDKImporServices())->downloadDaftarDataImpor($this->request);
                    redirect('prodeskel/ddk/impor');
                    break;

                case 'upload-excel-ubahan':
                    (new ProdeskelDDKImporServices())->prosesImporExcelUbahan($this->request);
                    break;

                default: return view('ddk.impor');
            }
        }

        switch($tipe) {
            default: show_404();
        }
    }

    public function templateHtml()
    {
        echo ProdeskelDDKTemplateHtmlServices::templateKeluarga() . ProdeskelDDKTemplateHtmlServices::templateAnggota();
    }
}

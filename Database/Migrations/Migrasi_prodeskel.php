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

use App\Models\GrupAkses;
use App\Models\Modul;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Prodeskel\Enums\DDKEnum;
use Modules\Prodeskel\Enums\DDKMaxCustomDataPilihanEnum;
use Modules\Prodeskel\Enums\DDKPilihanBahanGalianAnggotaEnum;
use Modules\Prodeskel\Enums\DDKPilihanProduksiTahunIniEnum;
use Modules\Prodeskel\Models\ProdeskelCustomValue;

defined('BASEPATH') || exit('No direct script access allowed');

return new class extends MY_model
{
    public function up()
    {
        $hasil = true;

        $hasil = $hasil && $this->tambahSubMenuProdeskelPadaSatuData($hasil);
        $hasil = $hasil && $this->createProdeskelDDKTable($hasil);
        $hasil = $hasil && $this->createProdeskelDDKDetailTable($hasil);
        $hasil = $hasil && $this->createProdeskelDDKProduksiTable($hasil);
        $hasil = $hasil && $this->createProdeskelDDKBahanGalianTable($hasil);
        $hasil = $hasil && $this->createProdeskelCustomValueTable($hasil);        
    }

    protected function tambahSubMenuProdeskelPadaSatuData($hasil)
    {        
        // cek modul satu data dan prodeskel
        $satu_data = Modul::where(['modul' => 'Satu Data'])->count();
        $prodeskel = Modul::where(['modul' => 'Prodeskel', 'slug' => 'prodeskel'])->first();

        if ( ! $satu_data) {
            log_message('error', 'Menu Satu Data tidak ditemukan');

            throw new Exception("Menu 'Satu Data' tidak ditemukan");
        }

        if ($prodeskel) {
            log_message('error', "Migrasi Prodeskel Gagal, Prodeskel sudah ada");

            throw new Exception("Migrasi Prodeskel Gagal, Prodeskel sudah ada");
        }        

        return $hasil && $this->tambah_modul([
                'config_id'  => identitas('id'),                
                'modul'      => 'Prodeskel',
                'url'        => 'prodeskel',
                'slug'       => 'prodeskel',
                'aktif'      => '1',
                'ikon'       => 'fa-exchange',
                'urut'       => '2',
                'level'      => '2',
                'parent'     => $satu_data->id,
                'hidden'     => '0',
                'ikon_kecil' => 'fa-exchange',
            ]);

    }

    protected function createProdeskelDDKTable($hasil)
    {
        if (! Schema::hasTable('prodeskel_ddk')) {
            Schema::create('prodeskel_ddk', static function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->integer('keluarga_id');
                $table->unsignedTinyInteger('bulan');
                $table->year('tahun');
                $table->string('nama_pengisi', 100)->nullable();
                $table->string('pekerjaan', 100)->nullable();
                $table->string('jabatan', 100)->nullable();
                $table->string('sumber_data_1', 100)->nullable();
                $table->string('sumber_data_2', 100)->nullable();
                $table->string('sumber_data_3', 100)->nullable();
                $table->string('sumber_data_4', 100)->nullable();
                $table->unsignedDecimal('jumlah_penghasilan_perbulan', 65)->nullable()->default(0);
                $table->unsignedDecimal('jumlah_pengeluaran_perbulan', 65)->nullable()->default(0);
                $table->timestamps();                                
                $table->foreign('config_id')->references('id')->on('config')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('keluarga_id')->references('id')->on('tweb_keluarga')->onUpdate('cascade')->onDelete('cascade');
            });
        }
        
        return $hasil;
    }

    public function createProdeskelDDKDetailTable($hasil)
    {
        if (! Schema::hasTable('prodeskel_ddk_detail')) {
            Schema::create('prodeskel_ddk_detail', static function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->integer('prodeskel_ddk_id');
                $table->integer('keluarga_id');
                $table->integer('penduduk_id');
                $table->string('kode_field', 50);
                $table->longText('value')->nullable();
                $table->timestamps();
                $table->foreign('prodeskel_ddk_id')->references('id')->on('prodeskel_ddk')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('keluarga_id')->references('id')->on('tweb_keluarga')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('penduduk_id')->references('id')->on('tweb_penduduk')->onUpdate('cascade')->onDelete('cascade');
            });
        }
        return $hasil;                            
    }

    protected function createProdeskelDDKProduksiTable($hasil)
    {
        if (! Schema::hasTable('prodeskel_ddk_produksi')) {
            Schema::create('prodeskel_ddk_produksi', static function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->integer('prodeskel_ddk_id');
                $table->string('kode_komoditas', 50);
                $table->integer('jumlah_pohon')->nullable();
                $table->unsignedDecimal('luas_panen', 65)->nullable();
                $table->unsignedDecimal('nilai_produksi_per_satuan', 65)->nullable();
                $table->string('pemasaran_hasil', 150);
                $table->softDeletes();
                $table->timestamps();
                $table->foreign('config_id')->references('id')->on('config')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('prodeskel_ddk_id')->references('id')->on('prodeskel_ddk')->onUpdate('cascade')->onDelete('cascade');
                $table->unique(['config_id', 'prodeskel_ddk_id', 'kode_komoditas'], 'prodeskel_ddk_produksi_unique_1');
            });
        }
        return $hasil;
    }

    protected function createProdeskelDDKBahanGalianTable($hasil)
    {
        if (! Schema::hasTable('prodeskel_ddk_bahan_galian')) {
            Schema::create('prodeskel_ddk_bahan_galian', static function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->integer('prodeskel_ddk_id');
                $table->integer('penduduk_id');
                $table->string('kode_komoditas', 50);
                $table->unsignedDecimal('nilai_produksi', 65)->nullable();
                $table->string('milik_adat', 100)->nullable();
                $table->string('milik_perorangan', 100)->nullable();
                $table->string('pemasaran_hasil', 150)->nullable();
                $table->softDeletes();
                $table->timestamps();
                $table->foreign('config_id')->references('id')->on('config')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('prodeskel_ddk_id')->references('id')->on('prodeskel_ddk')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('penduduk_id')->references('id')->on('tweb_penduduk')->onUpdate('cascade')->onDelete('cascade');
                $table->unique(['config_id', 'prodeskel_ddk_id', 'kode_komoditas'], 'prodeskel_ddk_bahan_galian_unique_1');
            });
        }
       return $hasil;
    }

    protected function createProdeskelCustomValueTable($hasil)
    {
        if (! Schema::hasTable('prodeskel_custom_value')) {
            Schema::create('prodeskel_custom_value', static function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('config_id');
                $table->string('kategori', 100);
                $table->string('kode_value', 50);
                $table->string('value', 100);
                $table->longText('value_long');
                $table->integer('created_by')->nullable();
                $table->integer('updated_by')->nullable();
                $table->timestamps();
                $table->unique(['config_id','kategori', 'kode_value']);
                $table->foreign('config_id')->references('id')->on('config')->onUpdate('cascade')->onDelete('cascade');
            });

            $this->insertDefaultCustomDataValue($hasil);
            $this->insertHashFileTemplate($hasil);
        }
        return $hasil;
    }

    public function insertDefaultCustomDataValue($hasil)
    {
        // insert atau abaikan jika duplikat
        $insert_sql = 'INSERT INTO ' . (new ProdeskelCustomValue())->getTable()
            . ' (config_id, kategori, kode_value, value, value_long) VALUES (?, ?, ?, ?, ?) '
            . ' ON DUPLICATE KEY UPDATE kategori = kategori, kode_value = kode_value, value = value, value_long = value_long ';

        foreach (DDKMaxCustomDataPilihanEnum::semua() as $kode_pilihan => $jumlah) {
            if ($jumlah == 0) {
                $kode_kustom = '';
            } elseif (array_key_exists($kode_pilihan, DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI)) {
                $kode_kustom = [];

                for ($i = 1; $i <= $jumlah; $i++) {
                    $pilihan                           = DDKPilihanProduksiTahunIniEnum::DATA[DDKEnum::KODE_KATEGORI_PRODUKSI_TAHUN_INI[$kode_pilihan]];
                    $kode_kustom[count($pilihan) + $i] = ['komoditas' => '', 'satuan' => ''];
                }
            } elseif ($kode_pilihan == DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA) {
                $kode_kustom = [];

                for ($i = 1; $i <= $jumlah; $i++) {
                    $pilihan                           = DDKPilihanBahanGalianAnggotaEnum::DATA;
                    $kode_kustom[count($pilihan) + $i] = '';
                }
            } else {
                $kode_kustom = [];

                for ($i = 1; $i <= $jumlah; $i++) {
                    $pilihan = DDKEnum::valuesOf($kode_pilihan);
                    end($pilihan);
                    $kode_kustom[key($pilihan) + $i] = '';
                }
            }

            $hasil = $hasil && DB::insert($insert_sql, [
                identitas('id'),
                DDKEnum::KATEGORI,
                $kode_pilihan,
                '',
                json_encode($kode_kustom),
            ]);
        }

        return $hasil;
    }

    protected function insertHashFileTemplate($hasil)
    {
        $insert_sql = 'INSERT INTO ' . (new ProdeskelCustomValue())->getTable()
            . ' (config_id, kategori, kode_value, value, value_long) VALUES (?, ?, ?, ?, ?) '
            . ' ON DUPLICATE KEY UPDATE kategori = kategori, kode_value = kode_value, value = value, value_long = value_long ';

        $hasil = $hasil && DB::insert($insert_sql, [
            identitas('id'),
            DDKEnum::KATEGORI,
            DDKEnum::HASH_TEMPLATE_DDK,
            hash_file(DDKEnum::HASH_ALGO, DDKEnum::PATH_TEMPLATE . DDKEnum::FILE_TEMPLATE_DDK),
            '',
        ]);

        return $hasil && DB::insert($insert_sql, [
            identitas('id'),
            DDKEnum::KATEGORI,
            DDKEnum::HASH_TEMPLATE_DDK_ANGGOTA,
            hash_file(DDKEnum::HASH_ALGO, DDKEnum::PATH_TEMPLATE . DDKEnum::FILE_TEMPLATE_DDK_ANGGOTA),
            '',
        ]);
    }    

    public function down(){
        $prodeskel = Modul::whereSlug('prodeskel')->get();
        if ($prodeskel){
            GrupAkses::whereIn('id_modul', $prodeskel->pluck('id'))->delete();
            Modul::whereSlug('prodeskel')->delete();
        }
        
        Schema::dropIfExists('prodeskel_custom_value');
        Schema::dropIfExists('prodeskel_ddk_bahan_galian');
        Schema::dropIfExists('prodeskel_ddk_produksi');
        Schema::dropIfExists('prodeskel_ddk_detail');
        Schema::dropIfExists('prodeskel_ddk');
    }
};
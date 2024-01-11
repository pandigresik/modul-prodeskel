<?php

namespace Modules\Prodeskel\Models;

use App\Models\Keluarga;
use App\Traits\ConfigId;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Prodeskel\Models\ProdeskelDDKDetail;
use Modules\Prodeskel\Models\ProdeskelDDKProduksi;
use Modules\Prodeskel\Models\ProdeskelDDKBahanGalianAnggota;

defined('BASEPATH') || exit('No direct script access allowed');

class ProdeskelDDK extends BaseModel
{
    use ConfigId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prodeskel_ddk';

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'created_at'          => 'date:Y-m-d H:i:s',
        'updated_at'          => 'date:Y-m-d H:i:s',
    ];

    /**
     * Define a one-to-one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class, 'keluarga_id');
    }

    public function produksi()
    {
        return $this->hasMany(ProdeskelDDKProduksi::class, 'prodeskel_ddk_id', 'id');
    }

    public function detail()
    {
        return $this->hasMany(ProdeskelDDKDetail::class, 'prodeskel_ddk_id', 'id');
    }

    public function bahanGalianAnggota()
    {
        return $this->hasMany(ProdeskelDDKBahanGalianAnggota::class, 'prodeskel_ddk_id', 'id');
    }

    public function getKepalaKeluargaAttribute()
    {
        $this->loadMissing([
            'keluarga.kepalaKeluarga' => static function ($builder) {
                $builder->withoutDefaultRelations();
            },
        ]);

        return $this->keluarga->kepalaKeluarga;
    }

    /**
     * @return detailKeluarga[kode_field] => item
     */
    public function getDetailKeluargaAttribute()
    {
        $this->loadMissing(['detail']);
        return $this->detail->whereNull('penduduk_id')->keyBy('kode_field');
    }

    /**
     * @return detailAnggota[penduduk_id][kode_field] => item
     * */
    public function getDetailAnggotaAttribute()
    {
        $this->loadMissing(['detail']);
        return $this->detail->whereNotNull('penduduk_id')
            ->groupBy('penduduk_id')
            ->transform(function ($item, $key) { return $item->keyBy('kode_field'); }) ;
    }

    public function getJenisKelaminKepalaKeluargaAttribute()
    {
        $this->loadMissing([
            'keluarga.kepalaKeluarga' => static function ($builder) {
                $builder->withoutDefaultRelations();
            },
        ]);

        return $this->keluarga->kepalaKeluarga->sex;
    }

    public function getNikKKAttribute()
    {
        $this->loadMissing([
            'keluarga.kepalaKeluarga' => static function ($builder) {
                $builder->withoutDefaultRelations();
            },
        ]);

        return $this->keluarga->kepalaKeluarga->nik;
    }

    public function getNoKKAttribute()
    {
        return $this->getKepalaKeluargaAttribute()->keluarga->no_kk;
    }
}

<?php

namespace Modules\Prodeskel\Models;

use App\Models\Penduduk;
use App\Traits\ConfigId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

defined('BASEPATH') || exit('No direct script access allowed');

class ProdeskelDDKBahanGalianAnggota extends BaseModel
{
    use ConfigId, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prodeskel_ddk_bahan_galian';

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    protected $touches = ['ddk'];

    protected $casts = [
        'created_at'          => 'date:Y-m-d H:i:s',
        'updated_at'          => 'date:Y-m-d H:i:s',
    ];

    /**
     * Define a one-to-one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ddk(): BelongsTo
    {
        return $this->belongsTo(ProdeskelDDK::class, 'prodeskel_ddk_id');
    }

    /**
     * Define a one-to-one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function penduduk(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'penduduk_id');
    }

}

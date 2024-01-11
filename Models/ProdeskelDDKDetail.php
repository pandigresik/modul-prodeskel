<?php

namespace Modules\Prodeskel\Models;

use App\Models\Keluarga;
use App\Traits\ConfigId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

defined('BASEPATH') || exit('No direct script access allowed');

class ProdeskelDDKDetail extends BaseModel
{
    use ConfigId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prodeskel_ddk_detail';

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
    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class, 'keluarga_id');
    }

    public function getValueAttribute()
    {
        return json_decode($this->attributes['value'], true);
    }
}

<?php
namespace Modules\Prodeskel\Models;

use App\Traits\Author;
use App\Traits\ConfigId;

defined('BASEPATH') || exit('No direct script access allowed');

class ProdeskelCustomValue extends BaseModel
{
    use ConfigId, Author;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prodeskel_custom_value';

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

}

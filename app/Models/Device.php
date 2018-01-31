<?php

namespace App\Models;

use App\Models\Reading;
use App\Models\Error;
use App\Models\Area;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'latitude', 'longitude', 'area_id'
    ];

    public $incrementing = false;

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function readings($period = null, $filters = [])
    {
        return Reading::retrieve($period, $filters, $this->id);
    }

    public function errors($period = null, $filters = [])
    {
        return Error::retrieve($period, $filters, $this->id);
    }
}

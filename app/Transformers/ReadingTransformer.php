<?php 

namespace App\Transformers;

use League\Fractal;
use App\Models\Device;

class ReadingTransformer extends Fractal\TransformerAbstract
{	

	/**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
		'device'
	];
	
	/**
     * Transform object into an json api array to display
     *
     * @return array
     */
	public function transform($data)
	{	
	    $id = array_get($data, 'time') .'-'. array_get($data, 'device');

	    return [
	    	'id'                  => $id,
	        'device_id'           => array_get($data, 'device'),
	        'type'                => (int) array_get($data, 'type'),
	        'display_type'        => array_get($data, 'display_type'),
			'prev_difference_val' => array_get($data, 'prev_difference_val'),
			'prev_difference_pct' => array_get($data, 'prev_difference_pct'),
	        'reading'             => array_get($data, 'reading'),
	        'power'               => array_get($data, 'power'),
	        'time'                => array_get($data, 'time'),
	    ];
	}

	/**
     * Include Device
     *
     * @return League\Fractal\ItemResource
     */
    public function includeDevice($data)
    {
        $device = Device::find($data['device']);
		if($device) {
			return $this->item($device, new DeviceTransformer, 'device');
		}
		return $this->null();
	}
}
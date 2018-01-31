<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InfluxDb;
use League\Fractal;
use DateTime;

class Reading extends Model
{

    public $incrementing = false;

    public static function retrieve($period = null, $filters = [], $deviceId = null)
    {
        switch ($period) {
            case 'm':
                $period = 'w';
                $number = 4;
                break;
            case 'y':
                $period = 'd';
                $number = 365;
                break;
            default:
                $number = 1;
                break;
        }

        $results = InfluxDb::getQueryBuilder()
            ->select('*')
            ->from('readings');
        
        if (!empty($period)) {
            $results = $results->select('mean(reading) as reading, first(device) as device, mean(power) as power, time')  
                ->groupBy("time({$number}{$period}), device");
        } 

        if (!empty($filters)) {
            $results = $results->where($filters);
        }

        if (!empty($deviceId)) {
            if(is_array($deviceId)) {
                $query = "(";

                for ($i = 0; $i < count($deviceId); $i++) {
                    $query .= "device = '{$deviceId}'";
                    if($i > 0 && $i < (count($deviceId)-1)) {
                        $query .= " OR ";
                    }
                }
                $query .= ")";

                $results = $results->where([$query]);
            } else {
                $results = $results->where(["device = '{$deviceId}'"]);
            }
        }

        $results = $results->orderBy('time', 'DESC')
            ->getResultSet()
            ->getPoints();

    	return $results;
    }
}

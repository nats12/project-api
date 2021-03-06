<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InfluxDb;
use League\Fractal;
use DateTime;
use App\Traits\HydrateInfluxTrait;

class Reading extends Model
{
    use HydrateInfluxTrait;

    public $incrementing = false;

    public static function retrieve($period = null, $page = 0, $filters = [], $deviceId = null)
    {
        switch ($period) {
            case 'm':
                $period = 'd';
                $number = 30;
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
            $results = $results->select('
                    mean(reading) as reading, 
                    mean(prev_difference_val) as prev_difference_val, 
                    mean(prev_difference_pct) as prev_difference_pct, 
                    mean(power) as power
                ')  
                ->groupBy("time({$number}{$period}), device, type, display_type");
        } 

        if (!empty($filters)) {
            $results = $results->where($filters);
        }

        if (!empty($deviceId)) {
            if (is_array($deviceId)) {
                $query = "";

                for ($i = 0; $i < count($deviceId); $i++) {
                    $query .= "device = '{$deviceId[$i]}'";
                    if ($i < (count($deviceId)-1)) {
                        $query .= " OR ";
                    }
                }

                $results = $results->where([$query]);
            } else {
                $results = $results->where(["device = '{$deviceId}'"]);
            }
        }

        $results = $results->orderBy('time', 'DESC');

        if ($page >= 0) {
            $results = $results->limit(500);
        }
        
        $results = $results->getQuery();

        if ($page > 0) {
            $results .= sprintf(' OFFSET %s', $page * 500);
        }
        

        $results = InfluxDB::query($results);
        
        if (!empty($period)) {
            $results = self::hydrateResultSet($results);
        } else {
            $results = $results->getPoints();
        }

    	return $results;
    }
}

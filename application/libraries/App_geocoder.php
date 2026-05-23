<?php

defined('BASEPATH') or exit('No direct script access allowed');

require "geocoder/autoload.php";

class App_geocoder
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function get_coordinate($search)
    {
        $lat = "";
        $long = "";
        $geocoder = new \OpenCage\Geocoder\Geocoder('a125cb0d07b44c2384baef2435e3abff');
        $result = $geocoder->geocode($search);
        if ($result && $result['total_results'] > 0) {
            $first = $result['results'][0];
            $lat = $first['geometry']['lat'];
            $long = $first['geometry']['lng'];
        }

        if(!empty($lat) && !empty($long)){
            return ['latitude' => $lat, 'longitude' => $long];
        }else{
            return false;
        }
    }
}

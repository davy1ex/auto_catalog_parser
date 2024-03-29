<?php

class Car {
    public $condition;
    public $googleProductCategory;
    public $storeCode;
    public $vehicleFulfillment;
    public $brand;
    public $model;
    public $year;
    public $color;
    public $mileage;
    public $price;
    public $vin;
    public $imageLink;
    public $linkTemplate;

    public function __construct($data) {
        $this->condition = $data['Condition'];
        $this->googleProductCategory = $data['google_product_category'];
        $this->storeCode = $data['store_code'];
        $this->vehicleFulfillment = $data['vehicle_fulfillment(option:store_code)'];
        $this->brand = $data['Brand'];
        $this->model = $data['Model'];
        $this->year = $data['Year'];
        $this->color = $data['Color'];
        $this->mileage = $data['Mileage'];
        $this->price = $data['Price'];
        $this->vin = $data['VIN'];
        $this->imageLink = $data['image_link'];
        $this->linkTemplate = $data['link_template'];
    }
}

class HttpFetcher {
    public static function fetch($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }
}


class CarParser {    
    public $reg_pattern_pages = '/<a class=\"listing-image\" href=\"(.*?)\">/is'; 
    public $reg_pattern2 = '/<a class="listing-tax" href=".*?">(.*?)<\/a>/';
    public $text_pattern = '/<div class="text">(Year|Mileage|VIN):<\/div>\s*<div class="value">([a-zA-Z0-9]+)<\/div>/';
    public $reg_pattern_price = '/<span class="price-text">([^<]+)<\/span>/';
    public $reg_pattern_href = '/<a href="([^"]+)" data-elementor-lightbox-slideshow="voiture-gallery"/';

    public $data_pattern = [
        'Condition' => "Used",                                              // 0
        'google_product_category' => "123",                                 // 1
        'store_code' => "xpremium",                                         // 2
        'vehicle_fulfillment(option:store_code)' => "in_store:premium",     // 3
        'Brand' => "",                                                      // 4
        'Model' => "", //5 
        'Year' => "", // 6 
        'Color' => "", // 7
        'Mileage' => "", // 8
        'Price' => "", // 9
        'VIN' => "", // 10
        'image_link' => "", // 11
        'link_template' => "", // 12
    ];

    public $consts;


    public function __construct($consts) {
        $this->consts = $consts;
    }

    public function fetchCarListingPages($html) {
        preg_match_all($this->reg_pattern_pages, $html, $matches);

        return $matches;
    }

    public function parseCar($page) {
        $html_page = file_get_contents($page);

        preg_match_all($this->reg_pattern2, $html_page, $matches_car_param);
        preg_match_all($this->text_pattern, $html_page, $matches_car_text_param);
        preg_match_all($this->reg_pattern_price, $html_page, $matches_price);
        preg_match($this->reg_pattern_href, $html_page, $matches_href);

        $this->data_dump["Condition"] = $this->consts["Condition"];
        $this->data_dump["google_product_category"] = $this->consts["google_product_category"];
        $this->data_dump["store_code"] = $this->consts["store_code"];
        $this->data_dump["vehicle_fulfillment(option:store_code)"] = $this->consts["vehicle_fulfillment(option:store_code)"];

        $this->data_dump["Brand"] = $matches_car_param[1][1]; 
        $this->data_dump["Model"] = $matches_car_param[1][2]; 
        $this->data_dump["Year"] = $matches_car_text_param[2][0]; 
        $this->data_dump["Color"] = $matches_car_param[1][3]; 
        $this->data_dump["Mileage"] = $matches_car_text_param[2][1] . " miles"; // Mileage + "miles"
        
        
        $this->data_dump["Price"] = $matches_price[1][0]; 
        $this->data_dump["VIN"] = $matches_car_text_param[2][2]; 
        $this->data_dump["image_link"] = $matches_href[1]; 
        
        $this->data_dump["link_template"] = substr($page, 0, -1) . "&store=" . $this->consts['store_code']; // URL страницы машины + GET-параметр store={store_code}

        return $this->data_dump ;
    }
}

function generateNewFilename($baseFilename = 'data_cars', $extension = '.csv') {
    $i = 0;
    do {
        $i++;
        $newFilename = $baseFilename . $i . $extension;
    } while (file_exists($newFilename));
    return $newFilename;
}



// START MAIN

$url = "https://premiumcarsfl.com/listing-list-full/";

$html = HttpFetcher::fetch($url);

$consts = [
    'Condition' => "Used",                                              
    'google_product_category' => "123",                                 
    'store_code' => "xpremium",                                         
    'vehicle_fulfillment(option:store_code)' => "in_store:premium",     
];
$parser = new CarParser($consts);

$matches = $parser->fetchCarListingPages($html);

$filename = generateNewFilename();
$file = fopen($filename, 'a');

$header = ['Condition', 'google_product_category', 'store_code', 'vehicle_fulfillment(option:store_code)', 'Brand', 'Model', 'Year', 'Color', 'Mileage', 'Price', 'VIN', 'image_link', 'link_template'];



fputcsv($file, $header); // put header to csv

foreach ($matches[1] as $page) {
    $data_dump = $parser->parseCar($page);

    echo "<pre>";
    print_r($data_dump);

    fputcsv($file, $data_dump);
}


fclose($file);
?>
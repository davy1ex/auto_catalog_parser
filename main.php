<?php 

// GET DATA OF CATALOG
$ch = curl_init("https://premiumcarsfl.com/listing-list-full/");

$consts = [
    'Condition' => "Used",                                              
    'google_product_category' => "123",                                 
    'store_code' => "xpremium",                                         
    'vehicle_fulfillment(option:store_code)' => "in_store:premium",     
];

$data_pattern = [
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

$first_row  = [
    'Condition',  // 0
    'google_product_category',  // 1
    'store_code',  // 2
    'vehicle_fulfillment(option:store_code)', // 3
    'Brand',  // 4
    'Model',  //5 
    'Year', // 6 
    'Color',  // 7
    'Mileage',  // 8
    'Price',  // 9
    'VIN', // 10
    'image_link', // 11
    'link_template', // 12
];


curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$html = curl_exec($ch);
file_put_contents("list.html", $html);

// END GET DATA OF CATALOG





// GET DATA OF PAGES
$html = file_get_contents("list.html");
$reg_pattern = '/<a class=\"listing-image\" href=\"(.*?)\">/is';
preg_match_all($reg_pattern, $html, $matches);



// FOR EACH PAGE in PAGES
$file = fopen('data_cars.csv', 'a');
fputcsv($file, $first_row);

foreach ($matches[1] as $page)
{

    $html_page = file_get_contents($page);

    $reg_pattern2 = '/<a class="listing-tax" href=".*?">(.*?)<\/a>/';
    $text_pattern = '/<div class="text">(Year|Mileage|VIN):<\/div>\s*<div class="value">([a-zA-Z0-9]+)<\/div>/';
    $reg_pattern_price = '/<span class="price-text">([^<]+)<\/span>/';
    $reg_pattern_href = '/<a href="([^"]+)" data-elementor-lightbox-slideshow="voiture-gallery"/';



    preg_match_all($reg_pattern2, $html_page, $matches_car_param);
    preg_match_all($text_pattern, $html_page, $matches_car_text_param);
    preg_match_all($reg_pattern_price, $html_page, $matches_price);
    preg_match($reg_pattern_href, $html_page, $matches_href);

    // echo "МАССИВ ДАННЫХ ТАБЛИЦЫ МАШИНЫ <pre>" . var_export($matches_car_param, 1) . "</pre";
    // echo "ОСТАЛЬНОЙ МАССИВ ДАННЫХ ТАБЛИЦЫ МАШИНЫ <pre>" . var_export($matches_car_text_param, 1) . "</pre";
    // var_dump($matches_href) ;
    
    
    $data_dump = $data_pattern;
    
  

    $data_dump["Condition"] = $consts['Condition']; 
    $data_dump["google_product_category"] = $consts['google_product_category']; 
    $data_dump["store_code"] = $consts['store_code']; 
    $data_dump["vehicle_fulfillment(option:store_code)"] = $consts['vehicle_fulfillment(option:store_code)']; 
    

    $data_dump["Brand"] = $matches_car_param[1][1]; 
    $data_dump["Model"] = $matches_car_param[1][2]; 
    $data_dump["Year"] = $matches_car_text_param[2][0]; 
    $data_dump["Color"] = $matches_car_param[1][3]; 
    $data_dump["Mileage"] = $matches_car_text_param[2][1] . " miles"; // Mileage + "miles"
    
    
    $data_dump["Price"] = $matches_price[1][0]; 
    $data_dump["VIN"] = $matches_car_text_param[2][2]; 
    $data_dump["image_link"] = $matches_href[1]; 
    
    $data_dump["link_template"] = substr($page, 0, -1) . "&store=" . $consts['store_code']; // URL страницы машины + GET-параметр store={store_code}



    echo "<hr><pre>" . var_export($data_dump, 1) . "</pre";
    
    fputcsv($file, $data_dump);
    // break;
}

fclose($file);
?>
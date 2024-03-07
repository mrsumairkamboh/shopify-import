<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'azcontacts';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$results = mysqli_query($conn, "SELECT id,product_name,manufacturer_id,has_valid_main_image_url,short_desc,product_weight_lbs,cached_current_sell_price_currency_id_1,created,modified,approved,tt_avg_rating,tt_rating_count FROM `products` WHERE 1 ORDER BY id ASC");
while ($proRow = mysqli_fetch_assoc($results)) {
    $itemsArray[] = $proRow;
}
// Open a file in write mode ('w')
$f = fopen('php://memory', 'w');
$headers = [
    "ID", "Title", "Variants"
];
fputcsv($f, $headers);


foreach ($itemsArray as $key => $row) {

    $current_product_id = $row['id'];
    $next_id = $key + 1;
    if(empty($itemsArray[$next_id])){
        $next_product_id=9999999;
    }else{
        $next_product_id = $itemsArray[$next_id]['id'];
    }



    $vQ = "SELECT V.value_varchar FROM product_shippable_to_attribute_type_values AS V WHERE V.product_id>$current_product_id AND V.product_id<$next_product_id AND product_shippable_setup_attribute_type_id=1 GROUP BY V.value_varchar";
    $vqResults = mysqli_query($conn, $vQ);

    $columns = [
        "ID" => $row['id'],
        "Title" => $row['product_name'],
        'Variants' => mysqli_num_rows($vqResults)
    ];

    $bc_list = $dia_list = "";
    fputcsv($f, $columns);

}


// Reset the file pointer
fseek($f, 0);

// Set headers to download file rather than displayed
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="shopify_import_count.csv";');

// Output all remaining data on a file pointer
fpassthru($f);
?>

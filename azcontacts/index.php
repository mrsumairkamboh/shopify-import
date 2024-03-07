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
$results = mysqli_query($conn, "SELECT P.id,P.product_name,P.manufacturer_id,P.has_valid_main_image_url,P.short_desc,P.product_weight_lbs,P.cached_current_sell_price_currency_id_1,P.created,P.modified,P.approved,P.tt_avg_rating,P.tt_rating_count, M.name as manufacturer_name , L.units_per_eye_per_year,P.new_image, P.new_price
FROM `products`  AS P
LEFT JOIN manufacturers AS M ON M.id=P.manufacturer_id
LEFT JOIN product_data_contact_lenses AS L ON L.id=P.id
WHERE 1 AND new_image IS NOT NULL ORDER BY P.id ASC");
while ($proRow = mysqli_fetch_assoc($results)) {
    $itemsArray[] = $proRow;
}
// Open a file in write mode ('w')
$f = fopen('php://memory', 'w');
$headers = [
    "ID", "Handle", "Title", "Body HTML", "Vendor", "Type", "Tags",
    "Status", "Published", "Template Suffix", "URL", "Total Inventory Qty",
    "Image Type", "Image Src", "Image Position",
    "Image Alt Text", "Variant Inventory Item ID",
    "Variant ID", "Option1 Name", "Option1 Value", "Variant Position", "Variant SKU",
    "Variant Barcode", "Variant Image", "Variant Weight", "Variant Weight Unit",
    "Variant Price", "Variant Compare At Price", "Variant Taxable", "Variant Tax Code",
    "Variant Inventory Tracker", "Variant Inventory Policy", "Variant Fulfillment Service",
    "Variant Requires Shipping", "Variant Inventory Qty", "Variant Inventory Adjust",
    "Metafield: title_tag [string]", "Metafield: description_tag [string]",
    "Metafield: custom.test1 [list.number_decimal]", "Metafield: custom.test [list.number_integer]",
    "Metafield: custom.bca [list.single_line_text_field]",
    "Metafield: custom.dia [list.single_line_text_field]",
    "Metafield: custom.lenses_product_reference [product_reference]",
    "Metafield: yotpo.reviews_average [single_line_text_field]",
    "Metafield: yotpo.reviews_count [single_line_text_field]",
    "Metafield: custom.lenses_minimum_quantity [number_integer]"
];
fputcsv($f, $headers);

//1 Year Subscription_4_default
foreach ($itemsArray as $key => $row) {

    $current_product_id = $row['id'];
    $next_id = $key + 1;
    if (empty($itemsArray[$next_id])) {
        $next_product_id = 9999999;
    } else {
        $next_product_id = $itemsArray[$next_id]['id'];
    }


    $bcQ = "SELECT CONCAT('[\"', GROUP_CONCAT(DISTINCT V.value_varchar SEPARATOR '\",\"'), '\"]') AS bc_list 
        FROM product_shippable_to_attribute_type_values AS V 
        WHERE V.product_id > $current_product_id 
        AND V.product_id < $next_product_id 
        AND product_shippable_setup_attribute_type_id = 2;";
    $bc_list = mysqli_fetch_assoc(mysqli_query($conn, $bcQ))['bc_list'];

    $diaQ = "SELECT CONCAT('[\"', GROUP_CONCAT(DISTINCT V.value_varchar SEPARATOR '\",\"'), '\"]') AS dia_list 
                FROM product_shippable_to_attribute_type_values AS V 
                WHERE V.product_id > $current_product_id 
                AND V.product_id < $next_product_id 
                AND product_shippable_setup_attribute_type_id = 3;";

    $dia_list = mysqli_fetch_assoc(mysqli_query($conn, $diaQ))['dia_list'];


    $vQ = "SELECT V.value_varchar FROM product_shippable_to_attribute_type_values AS V WHERE V.product_id>$current_product_id AND V.product_id<$next_product_id AND product_shippable_setup_attribute_type_id=1 GROUP BY V.value_varchar";
    $vqResults = mysqli_query($conn, $vQ);
    if (mysqli_num_rows($vqResults) > 0 && mysqli_num_rows($vqResults) < 101) {
        while ($powerRow = mysqli_fetch_assoc($vqResults)) {

            $units_per_eye_per_year = $row['units_per_eye_per_year'] ?? "0";
            $columns = [
                "ID" => $row['id'],
                "Handle" => formatUriString($row['product_name']),
                "Title" => $row['product_name'],
                "Body_HTML" => $row['short_desc'],
                "Vendor" => $row['manufacturer_name'],
                "Type" => "Lenses",
                "Tags" => "1 Year Subscription_" . $units_per_eye_per_year . "_default,lens",
                "Status" => "Active",
                "Published" => "TRUE",
                "Template_Suffix" => "lends",
                "URL" => "",
                "Total_Inventory_Qty" => "",
                "Image_Type" => "IMAGE",
                "Image_Src" => $row['new_image'], // "https://do6sydhp1s299.cloudfront.net/web/" . $row['has_valid_main_image_url'],
                "Image_Position" => "1",
                "Image_Alt_Text" => "",
                "Variant_Inventory_Item_ID" => "",
                "Variant_ID" => "",
                "Option1_Name" => "Power",
                "Option1_Value" => $powerRow['value_varchar'],
                "Variant_Position" => "",
                "Variant_SKU" => "",
                "Variant_Barcode" => "",
                "Variant_Image" => "",
                "Variant_Weight" => $row['product_weight_lbs'],
                "Variant_Weight_Unit" => "lb",
                "Variant_Price" => $row['new_price'], //$row['cached_current_sell_price_currency_id_1'],
                "Variant_Compare_At_Price" => "",
                "Variant_Taxable" => "TRUE",
                "Variant_Tax_Code" => "",
                "Variant_Inventory_Tracker" => "",
                "Variant_Inventory_Policy" => "deny",
                "Variant_Fulfillment_Service" => "manual",
                "Variant_Requires_Shipping" => "TRUE",
                "Variant_Inventory_Qty" => "",
                "Variant_Inventory_Adjust" => "",
                "Metafield_title_tag" => "",
                "Metafield_description_tag" => "",
                "Metafield_custom_test1" => "",
                "Metafield_custom_test" => "",
                "Metafield_custom_bca" => $bc_list,
                "Metafield_custom_dia" => $dia_list,
                "Metafield_custom_lenses_product_reference" => "",
                "Metafield_yotpo_reviews_average" => $row['tt_avg_rating'],
                "Metafield_yotpo_reviews_count" => $row['tt_rating_count'],
                "Metafield_custom_lenses_minimum_quantity" => "",
            ];

            $bc_list = $dia_list = "";
            fputcsv($f, $columns);
        }
    }
}

function formatUriString($string)
{
    // Remove whitespace from the beginning and end of the string
    $trimmed = trim($string);

    // Convert the string to lowercase
    $lowercase = strtolower($trimmed);

    // Replace any non-alphanumeric characters with hyphens
    $formatted = preg_replace('/[^a-z0-9]+/', '-', $lowercase);

    // Remove any hyphens from the beginning and end of the string
    $formatted = trim($formatted, '-');

    return $formatted;
}


// Reset the file pointer
fseek($f, 0);

// Set headers to download file rather than displayed
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="lenses_shopify_import.csv";');

// Output all remaining data on a file pointer
fpassthru($f);
?>

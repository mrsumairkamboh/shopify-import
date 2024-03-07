<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'az2';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$results = mysqli_query($conn, "SELECT DISTINCT(name) as item_name FROM items ORDER BY NAME ASC limit 5,10");
$itemsNameArray = [];
while ($row = mysqli_fetch_assoc($results)) {
    $itemsNameArray[] = $conn->real_escape_string($row['item_name']);
}
$items = "('" . implode("','", $itemsNameArray) . "')";

//print_r($itemsNameArray);die();

// Query to fetch items
$sql = "SELECT 
                I.id, 
                I.name as Title, 
                I.description as Body_HTML, 
                I.brand_name as Vendor, 
                I.mpn_sku as Variant_SKU,
                I.upc as Variant_Barcode,
                I.source_id as source_id,
                'RxAble' as Product_Category,
                COALESCE(IA_color.value_text, 'Default Title') as Color,
                COALESCE(IA_type.value_text, '') as Type,
                COALESCE(IA_Image.value_text, '') as Image_Src,
                I.msrp as Variant_Price,
                COALESCE(IA_templeSize.value_text, '') as Temple_Size,
                COALESCE(IA_noseSize.value_text, '') as Nose_Size,
                COALESCE(IA_eyeSize.value_text, '') as Eye_Size
            FROM 
                items AS I
            LEFT JOIN 
                item_attributes AS IA_color ON I.id = IA_color.item_id AND IA_color.name = 'frameColor'
            LEFT JOIN 
                item_attributes AS IA_type ON I.id = IA_type.item_id AND IA_type.name = 'frameType'
            LEFT JOIN 
                item_attributes AS IA_Image ON I.id = IA_Image.item_id AND IA_Image.name = 'mainImage'
            LEFT JOIN 
                item_attributes AS IA_templeSize ON I.id = IA_templeSize.item_id AND IA_templeSize.name = 'templeSize'
            LEFT JOIN 
                item_attributes AS IA_noseSize ON I.id = IA_noseSize.item_id AND IA_noseSize.name = 'noseSize'
            LEFT JOIN 
                item_attributes AS IA_eyeSize ON I.id = IA_eyeSize.item_id AND IA_eyeSize.name = 'eyeSize'
            WHERE 
                I.name IN $items  AND IA_type.value_text IN ('Optical', 'Eyeglasses', 'Sun', 'Sunglasses', 'Sunwear')
            ORDER BY 
                I.name;

        ";
$result = $conn->query($sql);

//while ($row = mysqli_fetch_assoc($result)) {
//    echo "<pre>";
//    print_r($row);
//}
//
//
//die();
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

function getSourceNickById($sourceId)
{
    $sources = array(
        1 => 'mysafilo',
        2 => 'marcolin',
        6 => 'luxottica',
        9 => 'marchon',
        11 => 'kenmark',
        12 => 'remeyewear',
        14 => 'bushnell',
        15 => 'kering',
        17 => 'wileyx',
        19 => 'tura',
        20 => 'mauijim',
        21 => 'vonzipper',
        23 => 'OPG',
        24 => 'costadelmar',
        25 => 'ogi',
        26 => 'eseyewear',
        27 => 'capri',
        28 => 'spy',
        29 => 'modern',
        30 => 'thelios',
        31 => 'libertysport',
        32 => 'edeldita',
        33 => 'silhouette',
        34 => 'electric',
        35 => 'sliik',
        36 => 'modo',
        37 => 'altair',
        38 => 'julbo',
        39 => 'legre'
    );

    if (isset($sources[$sourceId])) {
        return $sources[$sourceId];
    } else {
        return "source-not-found";
    }
}

function mapFrameType($input)
{
    switch (strtolower($input)) {
        case 'optical':
        case 'eyeglasses':
            return 'Eyeglass';
        case 'sun':
        case 'sunglasses':
        case 'sunwear':
            return 'Sunglass';
        default:
            return $input; // Return input value if not matched
    }
}

function mapTemplateSuffix($input)
{
    switch ($input) {
        case 'Eyeglass':
            return 'eyeglasses';
        case 'Sunglass':
            return 'sunglasses';
        default:
            return $input; // Return input value if not matched
    }
}

function formatSizes($Eye_Size, $Nose_Size, $Temple_Size)
{
    // Replace empty values with 0
    $Eye_Size = empty($Eye_Size) ? 0 : $Eye_Size;
    $Nose_Size = empty($Nose_Size) ? 0 : $Nose_Size;
    $Temple_Size = empty($Temple_Size) ? 0 : $Temple_Size;

    // Get the integer part of decimal values
    $Eye_Size = intval($Eye_Size);
    $Nose_Size = intval($Nose_Size);
    $Temple_Size = intval($Temple_Size);

    // Format the string as Eye_Size-Nose_Size-Temple_Size
    $formattedString = $Eye_Size . '-' . $Nose_Size . '-' . $Temple_Size;

    return $formattedString;
}

function getTagsOld($conn, $item_id)
{
    $itemQ = "SELECT GROUP_CONCAT(value_text) as atts FROM item_attributes WHERE item_id=$item_id AND name IN ('bridgeShape','colorCode','eyeShape','frameColor','frameMaterial','frameType','genderGroup','lensColor','templeMaterial','rxable','frontColorFamily','rimType','frameShape','gender','material','wholesale','minimumSuggestedRetail','vendorCategory','simpleColor','parentImage','styleNote','frontMaterial','eyeWire','thickness','hinge','lensColorCode','frameColorCode','ageGroup','colorDescription','marketingGroupCode','marketingGroupDescription','lenseType','familyColor','size','baseCurve','styleName','lensMaterial','lensColour','lensEffect','frontColour','templeColour','ean','collection','fitting','materials','templeMaterials','hinges','mounts','lenses','nosePads','genders','bridges','clip-on','caseName','nrfColorCode','chassisInd','backOrderDate','lensType','frameDescription','productCode','limitedEdition','filterProtection','counterCard','flexTemple','caseType','geofit','base','lensProperties','templeLength');";
    $result = $conn->query($itemQ);
    $row = mysqli_fetch_assoc($result);
    if (!empty($row)) {
        return $row['atts'];
    }
    return "";
}

function getTags($conn,$item_name){
    $item_name= $conn->real_escape_string($item_name);
    $items = mysqli_fetch_assoc(mysqli_query($conn,"SELECT GROUP_CONCAT(id) as items FROM items WHERE name='$item_name'"))['items'];
    $attributesQ="SELECT 
                GROUP_CONCAT(DISTINCT CONCAT(
                    CASE 
                        WHEN name = 'ageGroup' THEN 'ageGroup:' 
                        ELSE name 
                    END,
                    ':',
                    CASE 
                        WHEN name = 'ageGroup' THEN 
                            CASE 
                                WHEN value_text IN ('CHILD', 'Children', 'Youth/Teen') THEN 'Child' 
                                ELSE value_text 
                            END 
                        ELSE value_text 
                    END
                ) SEPARATOR ', ') AS tags
            FROM (
                SELECT DISTINCT item_id, name, value_text
                FROM item_attributes
                WHERE item_id IN ($items)
                AND name NOT IN (
                    'collectionName', 'collection', 'chassisInd', 'childProductName', 'colorCode', 'colorDescription',
                    'colorFamily', 'colorNumber', 'counterCard', 'ean', 'eyeWire', 'fashionCollection', 'filterProtection',
                    'frameColorCode', 'frameDescription', 'frameName', 'lensColorCode', 'lenses', 'mainImage', 'mainImage1',
                    'mainImage2', 'mainImage3', 'mainImage4', 'mainImage5', 'mainImage6', 'mAXPOWER', 'minimumSuggestedRetail',
                    'modelName', 'modelNumber', 'nrfColorCode', 'origImage', 'originCountry', 'parentImage', 'prefix360',
                    'productCode', 'productGroupLogo', 'sideImage', 'sideImage1', 'sideImage2', 'sku', 'styleName', 'styleNote',
                    'vendorCategory', 'wholesale'
                )
            ) AS filtered_attributes;";
    $tags = mysqli_fetch_assoc(mysqli_query($conn,$attributesQ))['tags'];
    if(substr($tags, -1) === ',') {
        $tags = substr($tags, 0, -1);
    }
    return $tags;
}

// Open a file in write mode ('w')
$f = fopen('php://memory', 'w');

// Write column headers
$headers = array('Handle', 'Title', 'Body (HTML)', 'Vendor', 'Product Category', 'Type', 'Tags', 'Published', 'Template Suffix', 'Option1 Name', 'Option1 Value', 'Option2 Name', 'Option2 Value', 'Option3 Name', 'Option3 Value', 'Variant SKU', 'Variant Grams', 'Variant Inventory Tracker', 'Variant Inventory Qty', 'Variant Inventory Policy', 'Variant Fulfillment Service', 'Variant Price', 'Variant Compare At Price', 'Variant Requires Shipping', 'Variant Taxable', 'Variant Barcode', 'Image Src', 'Image Position', 'Image Alt Text', 'Gift Card', 'SEO Title', 'SEO Description', 'Google Shopping / Google Product Category', 'Google Shopping / Gender', 'Google Shopping / Age Group', 'Google Shopping / MPN', 'Google Shopping / AdWords Grouping', 'Google Shopping / AdWords Labels', 'Google Shopping / Condition', 'Google Shopping / Custom Product', 'Google Shopping / Custom Label 0', 'Google Shopping / Custom Label 1', 'Google Shopping / Custom Label 2', 'Google Shopping / Custom Label 3', 'Google Shopping / Custom Label 4', 'Variant Image', 'Variant Weight Unit', 'Variant Tax Code', 'Cost per item', 'Price / International', 'Compare At Price / International', 'Status');
fputcsv($f, $headers);

// Loop over the query results, format each row and write it to the CSV
if ($result->num_rows > 0) {
    $tags = '';
    $option3Name = '';
    $option3Value = '';
    $variantGrams = '';
    $variantInventoryTracker = '';
    $variantInventoryQty = '';
    $variantCompareAtPrice = '';
    $variantRequiresShipping = '';
    $variantTaxable = '';
    $imageAltText = '';
    $giftCard = '';
    $seoTitle = '';
    $seoDescription = '';
    $googleShoppingGoogleProductCategory = '';
    $googleShoppingGender = '';
    $googleShoppingAgeGroup = '';
    $googleShoppingMpn = '';
    $googleShoppingAdwordsGrouping = '';
    $googleShoppingAdwordsLabels = '';
    $googleShoppingCondition = '';
    $googleShoppingCustomProduct = '';
    $googleShoppingCustomLabel0 = '';
    $googleShoppingCustomLabel1 = '';
    $googleShoppingCustomLabel2 = '';
    $googleShoppingCustomLabel3 = '';
    $googleShoppingCustomLabel4 = '';
    $variantImage = '';
    $variantWeightUnit = '';
    $variantTaxCode = $costPerItem = $priceInternational = $compareAtPriceInternational = '';

    $previousTitle = null;
    $colorsArray = [];
    while ($row = mysqli_fetch_assoc($result)) {

        $handle = formatUriString($row['Title']);

        $Body_HTML = $row['Body_HTML'];
        $Vendor = $row['Vendor'];
        $Product_Category = $row['Product_Category'];
        $Type = mapFrameType($row['Type']);
        $tags = getTags($conn, $row['Title']);
        $sourceNick = getSourceNickById($row['source_id']);
        $Image_Src = "https://useyewear.info/zzz/images/" . $sourceNick . "/" . $row['Image_Src'];
        $variantImage = $Image_Src;
        $Variant_Price = $row['Variant_Price'];
        $Variant_SKU = $row['Variant_SKU'];
        $Status = 'active';
        $Variant_Inventory_Policy = 'deny';
        $Variant_Fulfillment_Service = 'manual';
        $Published = 'TRUE';
        $TemplateSuffix = mapTemplateSuffix($Type);
        $Option1Name = 'Color';
        $Option1Value = $row['Color'];
        $Variant_Barcode = $row['Variant_Barcode'];
        $Image_Position = 1;
        $option2Name = 'Size';
        $option2Value = formatSizes($row['Eye_Size'], $row['Nose_Size'], $row['Temple_Size']);
//echo "<pre>";
//echo $Option1Value;
//print_r($colorsArray);
        if (in_array($Option1Value, $colorsArray)) {
            continue;
        }

        $colorsArray[] = $Option1Value;
        if ($previousTitle === $row['Title']) {
            $Title = $Body_HTML = $Vendor = $Product_Category = $Type = $Option1Name = $Image_Position = $tags = "";
        } else {
            //Reset for new product
            $Title = $row['Title'];
            $colorsArray = [];
        }
        $previousTitle = $row['Title'];
        $colorsArray[] = $Option1Value;

        $line = array($handle, $Title, $Body_HTML, $Vendor, '', $Type, $tags, $Published, $TemplateSuffix, $Option1Name, $Option1Value, $option2Name,
            $option2Value, $option3Name, $option3Value, $Variant_SKU, $variantGrams,
            $variantInventoryTracker, $variantInventoryQty, $Variant_Inventory_Policy, $Variant_Fulfillment_Service, $Variant_Price,
            $variantCompareAtPrice, $variantRequiresShipping, $variantTaxable, $Variant_Barcode, $Image_Src,
            $Image_Position, $imageAltText, $giftCard, $seoTitle, $seoDescription,
            $googleShoppingGoogleProductCategory, $googleShoppingGender, $googleShoppingAgeGroup, $googleShoppingMpn, $googleShoppingAdwordsGrouping,
            $googleShoppingAdwordsLabels, $googleShoppingCondition, $googleShoppingCustomProduct, $googleShoppingCustomLabel0, $googleShoppingCustomLabel1,
            $googleShoppingCustomLabel2, $googleShoppingCustomLabel3, $googleShoppingCustomLabel4, $variantImage, $variantWeightUnit,
            $variantTaxCode, $costPerItem, $priceInternational, $compareAtPriceInternational, $Status);
        fputcsv($f, $line);
    }
} else {
    echo "0 results";
}

// Reset the file pointer
fseek($f, 0);

// Set headers to download file rather than displayed
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="shopify_import.csv";');

// Output all remaining data on a file pointer
fpassthru($f);
?>

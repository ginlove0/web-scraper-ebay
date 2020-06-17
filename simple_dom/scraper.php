<?php
require 'simple_html_dom.php';

$id = 1;

$url = 'https://www.ebay.com/sch/i.html?_fsrp=1&_nkw=cisco&_sacat=0&_from=R40&Brand=Cisco&LH_ItemCondition=1000%7C1500%7C3000%7C2000%7C2500&_pgn='.$id.'&rt=nc';

$html = file_get_html($url);

$data = [];
$sellerData= [];
$itemData = [];



if(!empty($html))
{
    $divClass = $title = ''; $i = 0;

    foreach ($html->find('#mainContent') as $divClass)
    {
        if($id > 2)
        {
            break;
        }

        foreach ($divClass -> find('#srp-river-results') as $items)
        {
            foreach ($items -> find('.s-item') as $item){


                $itemImg = $item -> find('img.s-item__image-img', 0);
                $imgSrc = $itemImg -> src;
                $itemTitle = $itemImg -> alt;

                $itemLink = $item -> find('a.s-item__link', 0);

                $itemUrl = $itemLink -> href;
//                $itemPrice = $item -> find('span.s-item__price', 0)->plaintext;

                $html2 = file_get_html($itemUrl);

                foreach ($html2->find('#RightSummaryPanel') as $sellerDetail)
                {
                    $sellerName = $sellerDetail -> find('.mbg-nw',0)->plaintext;
                    $score = $sellerDetail -> find('.vi-mbgds3-bkImg', 0);
                    $sellerScore = $score -> title;
                    $positiveRate = $sellerDetail -> find('#si-fb',0)->plaintext;

                    $sellerData[$i] = [
                        'name' => $sellerName,
                        'score' => $sellerScore,
                        'rate' => $positiveRate
                    ];
                }

                foreach ($html2 -> find('#LeftSummaryPanel') as $itemInfo)
                {
                    $itemCondition = $itemInfo -> find('#vi-itm-cond', 0)-> plaintext;
                    $itemPrice = $itemInfo -> find('.notranslate', 0)-> plaintext;

                    $itemShipping = $itemInfo -> find('#fshippingCost', 0) -> plaintext;
                    if(!$itemShipping){
                        $itemShipping = 'FREE';
                    }
                    $itemData[$i] = [
                        'condition' => $itemCondition,
                        'price' => $itemPrice,
                        'shippingFee' => trim($itemShipping)
                    ];
                }


                $data[$i] = [
                    'title' => $itemTitle,
                    'url' => $itemUrl,
                    'img' => $imgSrc,
                    'seller' => $sellerData[$i],
                    'itemData' => $itemData[$i]
                ];

                $i++;
            }
        }
        $id++;
    }
}
else{
    echo 'huhu';
}


//function definition to convert array to xml
function array_to_xml($array, &$xml_user_info) {
    foreach($array as $key => $value) {
        if(is_array($value)) {
            $subnode = $xml_user_info->addChild("Review$key");
foreach ($value as $k=>$v) {
    $xml_user_info->addChild("$k", $v);
}
}else {
            $xml_user_info->addChild("$key",htmlspecialchars("$value"));
}
    }
    return $xml_user_info->asXML();
}
//creating object of SimpleXMLElement
$xml_user_info = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>', null, false);
//function call to convert array to xml and return whole xml content with tag
$xmlContent = array_to_xml($data,$xml_user_info);

// Create a xml file
$my_file = "Ebay.xml";
$handle = fopen($my_file, 'w') or die('Cannot open file: '.$my_file);
//success and error message based on xml creation
if(fwrite($handle, $xmlContent)) {
    echo 'XML file have been generated successfully.';
}
else{
    echo 'XML file generation error.';
}
?>




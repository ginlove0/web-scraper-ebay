<?php
require 'simple_html_dom.php';


$url = 'https://www.it-market.com/en/cisco-systems';

$html = file_get_html($url);

$data = [];
$sellerData= [];
$itemData = [];


if(!empty($html))
{
    $i = 0;

    foreach ($html->find('#site-wrap') as $divClass)
    {

        foreach ($divClass -> find('#content') as $content)
        {

            foreach ($content -> find('#more-categories') as $categoryLinkList){

                foreach($categoryLinkList -> find('a') as $categoryLink) {
                    $link = $categoryLink -> href;

                    $html1 = file_get_html($link);

                    foreach($html1 -> find('#more-categories') as $layer2Category) {

                        foreach ($layer2Category -> find('a') as $layer2CategoryLink) {

//                            $layer2Link =  $layer2CategoryLink -> href;
                            $layer2Link = 'https://www.it-market.com/en/cisco-systems/cisco-router-series/cisco-series-3800';
                            $page = 2;
                            $html2 = file_get_html($layer2Link . '?cat=200&next_page=' . $page);


                            foreach ($html2->find('#site-wrap') as $itemPage) {
                                foreach ($itemPage->find('#content') as $contentItemPage) {
                                    foreach ($contentItemPage->find('.panel-default') as $panelItemPage) {
                                        foreach ($panelItemPage->find('.vertical-helper') as $itemDetailUrl) {
                                            $itemLink = $itemDetailUrl->href;
                                            $html3 = file_get_html($itemLink);

                                            if (!empty($html3)) {
                                                foreach ($html3->find('#container') as $itemDetailPage) {
                                                    foreach ($itemDetailPage->find('#breadcrumb-main') as $breadCrumb) {
                                                        $detailBreadCrumb = $breadCrumb->find('span[itemprop=title]');
                                                        $text1 = html_entity_decode($detailBreadCrumb[0]->plaintext);
                                                        $text2 = html_entity_decode($detailBreadCrumb[1]->plaintext);
                                                        $text3 = html_entity_decode($detailBreadCrumb[2]->plaintext);


                                                        $category = $text1 . '>' . $text2 . '>' . $text3;
                                                    }
//

                                                    foreach ($itemDetailPage->find('#content') as $itemContent) {
                                                        //title
                                                        $itemTitle = $itemContent->find('h1[class=h3 top-heading]');
                                                        $textItemTitle = html_entity_decode($itemTitle[0]->plaintext);

                                                            //SKU
                                                        $sku = str_replace("Cisco Systems ","",$textItemTitle);


                                                        //short description
                                                        $itemShortDescription = $itemContent->find('h2[class=short-description textstyles text-word-wrap]');
                                                        $textShortDescription = html_entity_decode($itemShortDescription[0]->plaintext);

                                                        //img


                                                        foreach ($itemContent -> find('#product-images') as $productImage) {

                                                            foreach ($productImage -> find('div[class="image product-image img-thumbnail center"]') as $imgDiv) {

                                                                foreach ($imgDiv -> find('img') as $imgLink) {
                                                                    $itemImg = 'https://www.it-market.com' . $imgLink->src;
                                                                }

                                                            }
                                                        }


                                                        //description
                                                        $itemDes = $itemContent->find('#description');
                                                        $description = preg_replace( '<br />', '', rtrim(html_entity_decode(strip_tags($itemDes[0]))) );

                                                        $data = '"'.trim($sku).'"' .','. '"'.trim($category).'"'.','.'"'.trim($sku).'"'.','.'"'.trim($textShortDescription) .'"'.','.'"'.trim($itemImg).'"'.','.'"'.trim($description).'"'."\n";
                                                        file_put_contents("test.csv",$data,FILE_APPEND);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $page++;
                        }
                    }



                }

                $i++;
            }
        }
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
$my_file = "it-market.xml";
$handle = fopen($my_file, 'w') or die('Cannot open file: '.$my_file);
//success and error message based on xml creation
if(fwrite($handle, $xmlContent)) {
    echo 'XML file have been generated successfully.';
}
else{
    echo 'XML file generation error.';
}
?>




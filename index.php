<?php
require('CurlRequest.php');
$curl = new CurlRequest();
$start = microtime(true);
$url = 'https://www.otaus.com.au/find-an-ot';



$curl->httpGet($url);


//get the first option of area of pratice
$id = pq('select#memberSearch_AreaOfPracticeId option:nth-child(2)')->val();

$post = [
    'AreaOfPracticeId' => $id,
    'ServiceType' => 2,
];
$response = $curl->httpPost('https://www.otaus.com.au/search/membersearchdistance', $post);
$ids = json_decode($response)->mainlist;

$chuncked = array_chunk($ids, 40);
$idsQueryArr = [];
/**
 * Append ids for query
 */
foreach ($chuncked as $ids) {
    $idsQuery = "";
    foreach ($ids as $key => $id) {
        $idsQuery .= !$key ? "ids=" . $id : "&ids=" . $id;
    }
    $idsQueryArr[] = $idsQuery;

}



$datas = [];
$getContactsUrl = "https://www.otaus.com.au/search/getcontacts?";


/**
 * Open file & write into it
 */
$fp = fopen('otaus'.time().'.csv', 'w');
fputcsv($fp, [
    'Practice Name',
    'Contact Name',
    'Phone',
    'Address Street',
    'Address City',
    'Address State',
    'Address PostCode',
    'Address Country',
    'Funding Scheme',
    'Area(s) of Practice',
]);

foreach ($idsQueryArr as $k => $value) {
    $d = $curl->httpGet($getContactsUrl . $value);

    $i = 2;
    foreach (pq('.results__item') as $key => $d) {
//        $datas[]=pq(".title__tag:nth-child($key)")->text();
//        $child=$key+1;
        $address = pq(".results__item:nth-child($i) .content .content__row .main-contact-content p:nth-child(3)")->text();
        $addressArrByNewLine = preg_split('/\r\n|\r|\n/', trim($address));
        $addNewLineCount =count($addressArrByNewLine);
        $countryKey=$addNewLineCount===3?2:1;
        $addrKey=$addNewLineCount===3?1:0;
        $street = isset($addressArrByNewLine[0])&&$addNewLineCount===3 ? trimWhiteSpace($addressArrByNewLine[0]) : '-';
        $addressArr = isset($addressArrByNewLine[$addrKey]) ? explode(',', $addressArrByNewLine[$addrKey]) : [];



        $info = pq(".results__item:nth-child($i) .content__col:nth-child(2) p")->html();
        $infoArr = explode('<br>', $info);
        fputcsv($fp,[
            'p_name' => trimWhiteSpace(pq(".results__item:nth-child($i) .title__tag")->text()),
            'c_name' => pq(".results__item:nth-child($i) .main-contact-content p strong.name")->text(),
            'phone' => pq(".results__item:nth-child($i) .content .content__row .main-contact-content p:nth-child(6) a")->text(),
            'street' => $street,
            'city' => isset($addressArr[0]) ? trimWhiteSpace($addressArr[0]) : '-',
            'state' => isset($addressArr[1]) ? trimWhiteSpace($addressArr[1]) : '-',
            'postal_code' => isset($addressArr[2]) ? trimWhiteSpace($addressArr[2]) : '-',
            'country' => isset($addressArrByNewLine[$addNewLineCount]) && $addressArrByNewLine[$addNewLineCount]=='Australia'? trimWhiteSpace($addressArrByNewLine[$addNewLineCount]) : 'Australia',
            'funding' => getDescription($infoArr, 'Funding Scheme(s):'),
            'pratice' => getDescription($infoArr, 'Area(s) of Practice:'),
        ]);
        $i++;


    }

}

fclose($fp);

$time_elapsed_secs = microtime(true) - $start;
print  $time_elapsed_secs;
die();


/**
 * @param $text
 * @return string
 */
function trimWhiteSpace($text)
{
    return trim(preg_replace('/\s+/', ' ', $text));
}

/**
 * @param $infos array
 * @param $toFind string
 * @return string
 * @description get the text of area of interest & funding
 */
function getDescription($infos, $toFind)
{
    foreach ($infos as $info) {
        if (strpos($info, $toFind)) {
            return trimWhiteSpace(str_replace($toFind, '', strip_tags($info)));
        }
    }
}


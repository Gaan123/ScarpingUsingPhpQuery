<?php
require('phpQuery/phpQuery.php');

class CurlRequest
{
    /**
     * @param $url
     * @return phpQueryObject|QueryTemplatesParse|QueryTemplatesSource|QueryTemplatesSourceQuery
     */
    public function httpGet($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        $document = curl_exec($ch);
        return phpQuery::newDocumentHTML($document, $charset = 'utf-8');
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    function httpPost($url, $data)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}

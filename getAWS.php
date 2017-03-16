<?php
/**
 * Created by PhpStorm.
 * User: gremlin
 * Date: 2017/03/16
 * Time: 5:41
 */
// http://mayer.jp.net/?p=4586
$aws_access_key_id = 'AKIAIMPUAN24IAU4NMAQ';                    //'自分のAccess Key ID';
$aws_secret_key = '9GbMJB4o62YrdQaRas1A9dlyOMIWCUApi+Mn2sms';   //'自分のSecret Key ID';
$AssociateTag='balance009-22';                                  //'自分のトラッキングID';

//URL生成
$endpoint = 'webservices.amazon.co.jp';
$uri = '/onca/xml';

for($i=1; $i<=2; $i++){//2ページ取得、ItemSearchの最大値は10まで
    //パラメータ群
    $params = array(
        'Service' => 'AWSECommerceService',
        'Operation' => 'ItemSearch',
        'AWSAccessKeyId' => $aws_access_key_id,
        'AssociateTag' => $AssociateTag,
        'SearchIndex' => 'Books',
        'ResponseGroup' => 'Medium',
        'Keywords' => '進撃の巨人',
        'ItemPage' => $i
    );

    //timestamp
    if (!isset($params['Timestamp'])) {
        $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
    }

    //パラメータをソート
    ksort($params);

    $pairs = array();
    foreach ($params as $key => $value) {
        array_push($pairs, rawurlencode($key).'='.rawurlencode($value));
    }

    //リクエストURLを生成
    $canonical_query_string = join('&', $pairs);
    $string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;
    $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $aws_secret_key, true));
    $request_url = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);

    $amazon_xml=simplexml_load_string(@file_get_contents($request_url));//@はエラー回避

    foreach($amazon_xml->Items->Item as $item_a=>$item){
        $detailURL=$item->DetailPageURL;//商品のURL
        $image=$item->MediumImage->URL;//画像のURL
        $title=$item->ItemAttributes->Title;//商品名
        $author=$item->ItemAttributes->Author;//著者名
        $price=$item->ItemAttributes->ListPrice->Amount;//価格

        print '<div style="clear:both; margin-bottom:20px;"><a href="'.$detailURL.'" target="_blank"><img src="'.$image.'" align="left"></a><br>
タイトル：<a href="'.$detailURL.'" target="_blank">'.$title.'</a><br>
著者：'.$author.'<br>
価格：'.$price.'<br>
URL：'.$detailURL.'</div>';
        print PHP_EOL;
    }

    //1秒おく
    sleep(1);
}

<?php
/**
 * Created by PhpStorm.
 * User: JV
 * Date: 2017/5/29
 * Time: 10:35
 */

namespace invoice\src;


class PackageInfo
{
    private static $_instance = null;

    public static function getInstance()
    {
        if(self::$_instance == null){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /***
     * @param $interface
     * @return string
     */
    public function getXml($interface)
    {
        $config = include "config.php";
        $content = $this->getContent($config);
        $rand = rand(1000000000,9999999999);
        $pwd = $rand.base64_encode(md5($rand.$config['REGISTERCODE']));
        $terminalcode = $config['TERMINALCODE'];
        $appid = $config['APPID'];
        $dsptbm = $config['DSPTBM'];
        $password = $pwd;
        $date = date('Y-m-d');
        $taxpayerid = $config['TAXPAYWERID'];
        $authorizationcode = $config['AUTHORIZATIONCODE'];
        $response = $config['RESPONSECODE'];
        $dataexchangeid = $config['REQUESTCODE'].date('Ymd').substr($rand,0,9);
        $str = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<interface xmlns="" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.chinatax.gov.cn/tirip/dataspec/interfaces.xsd"
	version="DZFP1.0">
<globalInfo>
		 <terminalCode>{$terminalcode}</terminalCode>
		 <appId>{$appid}</appId>
		 <version>2.0</version>
		 <interfaceCode>{$interface}</interfaceCode>
		 <requestCode>{$dsptbm}</requestCode>
		 <requestTime>{$date}</requestTime>
		 <responseCode>{$response}</responseCode>
		 <dataExchangeId>{$dataexchangeid}</dataExchangeId>
		 <userName>{$dsptbm}</userName>
		 <passWord>{$password}</passWord>
		 <taxpayerId>{$taxpayerid}</taxpayerId>
		 <authorizationCode>{$authorizationcode}</authorizationCode>
</globalInfo>
<returnStateInfo>
<returnCode/>
<returnMessage/>
</returnStateInfo>
<Data>
	<dataDescription>
		  <zipCode>0</zipCode>
		  <encryptCode>0</encryptCode>
		  <codeType>0</codeType>
	</dataDescription>
	<content>
		{$content}	
	</content>
</Data>
</interface>
XML;


        return $str;

    }

    /***
     * @param array $config
     * @param array $arr
     * @return string
     */
    private function getContent(array $config,array $arr)
    {
        $fpkj = '';
        foreach ($this->content_0($config) as $key => $item){
            if($item['text']!==''){
                $fpkj .= '<'.strtoupper($item['key']).'>'.$item['text'].'</'.$item['key'].'>';
            }else{
                $fpkj .= '<'.strtoupper($item['key']).'>'.$arr[$item['key']].'</'.$item['key'].'>';
            }
        }
        $xm_size = count($arr['items']);
        $fpkj_xm = '';
        foreach ($arr['items'] as $num => $value){
            $fpkj_xm .= '<FPKJXX_XMXX>';
            foreach ($this->content_1($config) as $key=>$item){
                if($item['text']!==''){
                    $fpkj_xm .= '<'.strtoupper($item['key']).'>'.$item['text'].'</'.$item['key'].'>';
                }else{
                    $fpkj_xm .= '<'.strtoupper($item['key']).'>'.$value[$item['key']].'</'.$item['key'].'>';
                }
            }
            $fpkj_xm .= '</FPKJXX_XMXX>';
            //津贴被折扣行
            if(isset($value['discount'])){
                //size对应
                $xm_size++;
                $fpkj_xm .= '<FPKJXX_XMXX>';
                foreach ($this->content_1($config) as $key=>$item){
                    if($item['text']!==''){
                        $fpkj_xm .= '<'.strtoupper($item['key']).'>'.$item['text'].'</'.$item['key'].'>';
                    }else{
                        $fpkj_xm .= '<'.strtoupper($item['key']).'>'.$value['discount'][$item['key']].'</'.$item['key'].'>';
                    }
                }
                $fpkj_xm .= '</FPKJXX_XMXX>';
            }
        }
        $fpkj_dd = '';
        foreach ($this->content_2() as $key=>$item){
            if($item['text']!==''){
                $fpkj_dd .= '<'.strtoupper($item['key']).'>'.$item['text'].'</'.$item['key'].'>';
            }else{
                if($item['text']===null){
                    $fpkj_dd .= '<'.strtoupper($item['key']).'/>';
                    continue;
                }
                $fpkj_dd .= '<'.strtoupper($item['key']).'>'.$arr[$item['key']].'</'.$item['key'].'>';
            }
        }
        $root = <<<ROOT
<REQUEST_FPKJXX class="REQUEST_FPKJXX">
    <FPKJXX_FPTXX class="FPKJXX_FPTXX">
       {$fpkj}
    </FPKJXX_FPTXX>
    <FPKJXX_XMXXS class="FPKJXX_XMXX;" size="{$xm_size}">
    {$fpkj_xm}
    </FPKJXX_XMXXS>
    <FPKJXX_DDXX class="FPKJXX_DDXX">
    {$fpkj_dd}
    </FPKJXX_DDXX>
</REQUEST_FPKJXX>
ROOT;

        return base64_encode($root);
    }

    /***
     * @param string $xml
     * @return mixed|\SimpleXMLElement
     */
    public function XML2array(string $xml)
    {
        $arr = simplexml_load_string($xml);
        $arr = json_decode(json_encode($arr),TRUE);
        return $arr;
    }

    /***
     * @param $config
     * @return array
     */
    private function content_0($config)
    {
        return  [
            'FPQQLSH'=>[
                'key'=>'FPQQLSH',
                'text'=>'',
                'comment'=>'请求流水号'
            ],
            'DSPTBM'=>[
                'key'=>'DSPTBM',
                'text'=>$config['DSPTBM'],
                'comment'=>'平台编码'
            ],
            'NSRSBH'=>[
                'key'=>'NSRSBH',
                'text'=>$config['NSRSBH'],
                'comment'=>'开票方识别号'
            ],
            'NSRMC'=>[
                'key'=>'NSRMC',
                'text'=>$config['NSRMC'],
                'comment'=>'开票方名称'
            ],
            'DKBZ'=>[
                'key'=>'DKBZ',
                'text'=>'0'
            ],
            'KPXM'=>[
                'key'=>'KPXM',
                'text'=>'',
                'comment'=>'商品信息中第一条'
            ],
            'BMB_BBH'=>[
                'key'=>'BMB_BBH',
                'text'=>'1.0'
            ],
            'XHF_NSRSBH'=>[
                'key'=>'XHF_NSRSBH',
                'text'=>$config['NSRSBH'],
                'comment'=>'销方识别码'
            ],
            'XHFMC'=>[
                'key'=>'XHFMC',
                'text'=>$config['NSRMC'],
                'comment'=>'销方名称'
            ],
            'XHF_DZ'=>[
                'key'=>'XHF_DZ',
                'text'=>$config['XHF_DZ'],
                'comment'=>'销方地址'
            ],
            'XHF_DH'=>[
                'key'=>'XHF_DH',
                'text'=>$config['XHF_DH'],
                'comment'=>'销方电话'
            ],
            'XHF_YHZH'=>[
                'key'=>'XHF_YHZH',
                'text'=>$config['XHF_YHZH'],
                'comment'=>'销方银行账号'
            ],
            'GHFMC'=>[
                'key'=>'GHFMC',
                'text'=>'',
                'comment'=>'购货方名称'
            ],
            'GHF_SJ'=>[
                'key'=>'GHF_SJ',
                'text'=>'',
                'comment'=>'购货方手机'
            ],
            //01-企业 02-机关事业单位 03-个人  04-其他
            'GHFQYLX'=>[
                'key'=>'GHFQYLX',
                'text'=>'',
                'comment'=>'购货方名称'
            ],
            'SKY'=>[
                'key'=>'SKY',
                'text'=>$config['SKY'],
            ],
            'KPY'=>[
                'key'=>'KPY',
                'text'=>$config['KPY'],
            ],
            //1 正票  2 红票
            'KPLX'=>[
                'key'=>'KPLX',
                'text'=>'',
                'comment'=>'开票类型'
            ],
            //10 正票正常开具 11 正票错票重开 20 退货折让红票 21 错票重开红票 22 换票冲红（全冲红电子发票,开具纸质发票）
            'CZDM'=>[
                'key'=>'CZDM',
                'text'=>'',
                'comment'=>'操作代码'
            ],
            'QD_BZ'=>[
                'key'=>'QD_BZ',
                'text'=>'0'
            ],
            //小数点后2位 以元为单位精确到分  double
            'KPHJJE'=>[
                'key'=>'KPHJJE',
                'text'=>'',
                'comment'=>'价税合计金额'
            ],
            //double
            'HJBHSJE'=>[
                'key'=>'HJBHSJE',
                'text'=>'',
                'comment'=>'合计不含税金额'
            ],
            'HJSE'=>[
                'key'=>'HJSE',
                'text'=>'',
                'comment'=>'合计税额'
            ]
        ];
    }


    /***
     * @param array $config
     * @return array
     */
    private function content_1(array $config)
    {
        return [
            'XMMC'=>[
                'key'=>'XMMC',
                'text'=>'',
                'comment'=>'项目名称'
            ],
            'XMSL'=>[
                'key'=>'XMSL',
                'text'=>'',
                'comment'=>'项目数量'
            ],
            'HSBZ'=>[
                'key'=>'HSBZ',
                'text'=>$config['HSBZ']
            ],
            'FPHXZ'=>[
                'key'=>'FPHXZ',
                'text'=>'',
            ],
            //小数点后8位小数
            'XMDJ'=>[
                'key'=>'XMDJ',
                'text'=>''
            ],
            'SPBM'=>[
                'key'=>'SPBM',
                'text'=>''
            ],
            'ZXBM'=>[
                'key'=>'ZXBM',
                'text'=>''
            ],
            'YHZCBS'=>[
                'key'=>'YHZCBS',
                'text'=>'0',
                'comment'=>'优惠政策标识'
            ],
            //小数点后2位
            'XMJE'=>[
                'key'=>'XMJE',
                'text'=>'',
                'comment'=>'项目金额'
            ],
            //税率
            'SL'=>[
                'key'=>'SL',
                'text'=>'0.03'
            ],
        ];
    }

    /***
     * @return array
     */
    private function content_2()
    {
        return [
            'DDH'=>[
                'key'=>'DDH',
                'text'=>''
            ],
            'DDDATE'=>[
                'key'=>'DDDATE',
                'text'=>null,
            ]
        ];
    }

    /***
     * @param array $config
     * @return array
     */
    private function download(array $config)
    {
        return [
            'DDH'=>[
                'key'=>'DDH',
                'text'=>'',
            ],
            'FPQQLSH'=>[
                'key'=>'FPQQLSH',
                'text'=>''
            ],
            'DSPTBM'=>[
                'key'=>'DSPTBM',
                'text'=>$config['DSPTBM'],
            ],
            'NSRSBH'=>[
                'key'=>'NSRSBH',
                'text'=>$config['NSRSBH'],
            ],
            'PDF_XZFS'=>[
                'key'=>'PDF_XZFS',
                'text'=>''  //0-发票状态查询 1-pdf文件
            ]
        ];
    }

    /***
     * @param array $config
     * @return array
     */
    public function email(array $config)
    {
        return [
            'TSFS'=>'',
            'EMAIL'=>'',
            'FPQQLSH'=>'',
            'NSRSBH'=>$config['NSRSBH'],
            'FP_DM'=>'',
            'FP_HM'=>''
        ];
    }
}
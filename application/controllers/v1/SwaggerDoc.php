<?php
defined ( "BASEPATH" ) or exit ( "No direct script access allowed" );
class SwaggerDoc extends CI_Controller {
	private $data_view;
	function __construct() {
		parent::__construct ();
	}
	public function index() {
		if (isset ( $_GET [''] )) {
		}
		// $api_host = (ENVIRONMENT == 'production') ? "plugin-billing.vidol.tv" : "cplugin-billing.vidol.tv";
		$api_host = $_SERVER ['HTTP_HOST'];
		$doc_array = array (
				"swagger" => "2.0",
				"info" => array (
						"title" => "RESTful API Documentation",
						"description" => "RESTful api control panel of technical documents.",
						"termsOfService" => "#",
						"contact" => array (
								"email" => "zeren828@gmail.com" 
						),
						"license" => array (
								"name" => "Apache 2.0",
								"url" => "#" 
						),
						"version" => "V 1.0" 
				),
				"host" => $api_host,
				"basePath" => "/v1",
				"tags" => array (
						array (
								"name" => "1.users",
								"description" => "使用者" 
						),
						array (
								"name" => "2.billings",
								"description" => "金流" 
						),
						array (
								"name" => "3.invoice",
								"description" => "電子發票" 
						),
						array (
								"name" => "4.video",
								"description" => "多媒體" 
						),
						array (
								"name" => "99.system",
								"description" => "系統用" 
						) 
				),
				"schemes" => array (
						"http" 
				),
				"paths" => array (
						"/user/levels/level" => array (
								"get" => array (
										"tags" => array (
												"1.users" 
										),
										"summary" => "取得會員等級",
										"description" => "取得會員等級",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "user_no",
														"description" => "購買會員(user_pk等會員整合後使用)",
														"in" => "query",
														"type" => "integer" 
												),
												array (
														"name" => "mongo_id",
														"description" => "會員mongo_id",
														"in" => "query",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "member_id",
														"description" => "會員ID",
														"in" => "query",
														"type" => "string" 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "query",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功",
														"schema" => array (
																"title" => "result",
																"type" => "object",
																"description" => "api result data",
																"properties" => array (
																		"result" => $this->__get_responses_data ( "user level info" ),
																		"code" => array (
																				"type" => "string",
																				"description" => "狀態碼" 
																		),
																		"message" => array (
																				"type" => "string",
																				"description" => "訊息" 
																		),
																		"time" => array (
																				"type" => "string",
																				"description" => "耗費時間" 
																		) 
																) 
														) 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/user/orders/all" => array (
								"get" => array (
										"tags" => array (
												"1.users" 
										),
										"summary" => "取得會員全部訂單",
										"description" => "取得會員全部訂單",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "user_no",
														"description" => "購買會員(user_pk等會員整合後使用)",
														"in" => "query",
														"type" => "integer" 
												),
												array (
														"name" => "mongo_id",
														"description" => "會員mongo_id",
														"in" => "query",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "member_id",
														"description" => "會員ID",
														"in" => "query",
														"type" => "string" 
												),
												array (
														"name" => "status",
														"description" => "狀態(-1:fail,0:pending,1:success,2:cancel)",
														"in" => "query",
														"type" => "integer",
														"enum" => array (
																- 1,
																0,
																1,
																2 
														) 
												),
												array (
														"name" => "sort",
														"description" => "排序(1:舊到新)",
														"in" => "query",
														"type" => "integer",
														"enum" => array (
																1 
														) 
												),
												array (
														"name" => "page",
														"description" => "頁數",
														"in" => "query",
														"type" => "integer" 
												),
												array (
														"name" => "page_size",
														"description" => "每頁筆數",
														"in" => "query",
														"type" => "integer" 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "query",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功",
														"schema" => array (
																"title" => "result",
																"type" => "object",
																"description" => "api result data",
																"properties" => array (
																		"result" => $this->__get_responses_data ( "order info" ),
																		"pagination" => $this->__get_responses_data ( "pagination info" ) 
																) 
														) 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/user/promos/dealer" => array (
								"post" => array (
										"tags" => array (
												"1.users" 
										),
										"summary" => "新註冊會員優惠",
										"description" => "新註冊會員優惠",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "user_no",
														"description" => "購買會員(user_pk等會員整合後使用)",
														"in" => "formData",
														"type" => "integer" 
												),
												array (
														"name" => "mongo_id",
														"description" => "會員mongo_id",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "member_id",
														"description" => "會員ID",
														"in" => "formData",
														"type" => "string" 
												),
												array (
														"name" => "dealer",
														"description" => "經銷商",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "ip",
														"description" => "IP",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												),
												"200" => array (
														"description" => "成功",
														"schema" => array (
																"title" => "result",
																"type" => "object",
																"description" => "api result data",
																"properties" => array (
																		"result" => array (
																				"title" => "order info",
																				"type" => "object",
																				"description" => "訂單",
																				"properties" => array (
																						"coupon" => array (
																								"type" => "string",
																								"description" => "經銷商票卷" 
																						) 
																				) 
																		),
																		"code" => array (
																				"type" => "string",
																				"description" => "狀態碼" 
																		),
																		"message" => array (
																				"type" => "string",
																				"description" => "訊息" 
																		),
																		"time" => array (
																				"type" => "string",
																				"description" => "耗費時間" 
																		) 
																) 
														) 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/billing/packages/all" => array (
								"get" => array (
										"tags" => array (
												"2.billings" 
										),
										"summary" => "取得產品包清單資訊",
										"description" => "取得產品包清單資訊",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "type",
														"description" => "類型(1:所有節目,2:單片,3...)",
														"in" => "query",
														"type" => "integer" 
												),
												array (
														"name" => "show",
														"description" => "顯示(1:在前端顯示)",
														"in" => "query",
														"type" => "integer" 
												),
												array (
														"name" => "page",
														"description" => "頁數",
														"in" => "query",
														"type" => "integer" 
												),
												array (
														"name" => "page_size",
														"description" => "每頁筆數",
														"in" => "query",
														"type" => "integer" 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "query",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功",
														"schema" => array (
																"title" => "result",
																"type" => "object",
																"description" => "api result data",
																"properties" => array (
																		"result" => $this->__get_responses_data ( "package info" ),
																		"pagination" => $this->__get_responses_data ( "pagination info" ) 
																) 
														) 
												),
												"403" => array (
														"description" => "token未授權" 
												) 
										) 
								) 
						),
						"/billing/packages/package/{package_no}" => array (
								"get" => array (
										"tags" => array (
												"2.billings" 
										),
										"summary" => "取得產品包資訊",
										"description" => "取得產品包資訊",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "package_no",
														"description" => "產品包號碼",
														"in" => "path",
														"type" => "integer",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "query",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功",
														"schema" => array (
																"title" => "result",
																"type" => "object",
																"description" => "api result data",
																"properties" => array (
																		"result" => $this->__get_responses_data ( "package info" ),
																		"payment" => $this->__get_responses_data ( "payment info" ) 
																) 
														) 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/billing/payments/payment" => array (
								"post" => array (
										"tags" => array (
												"2.billings" 
										),
										"summary" => "建立訂單",
										"description" => "建立訂單",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "user_creat",
														"description" => "訂單建立者(0:system)對應後台帳號pk",
														"in" => "formData",
														"type" => "integer" 
												),
												array (
														"name" => "user_no",
														"description" => "購買會員(user_pk等會員整合後使用)",
														"in" => "formData",
														"type" => "integer" 
												),
												array (
														"name" => "mongo_id",
														"description" => "會員mongo_id",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "member_id",
														"description" => "會員ID",
														"in" => "formData",
														"type" => "string" 
												),
												array (
														"name" => "package_no",
														"description" => "產品包編號",
														"in" => "formData",
														"type" => "integer",
														"required" => TRUE 
												),
												array (
														"name" => "package_title",
														"description" => "商品名稱",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "payment_proxy",
														"description" => "代理商代號<br/>spgateway:智付通<br/>pay2go:智付寶",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE,
														"enum" => array (
																"spgateway",
																"pay2go" 
														) 
												),
												array (
														"name" => "payment_type",
														"description" => "付款類型<br/>CREDIT:信用卡<br/>WEBATM:WEBATM<br/>VACC:ATM轉帳<br/>CVS:超商代碼<br/>BARCODE:條碼",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE,
														"enum" => array (
																"CREDIT",
																"WEBATM",
																"VACC",
																"CVS",
																"BARCODE" 
														) 
												),
												array (
														"name" => "coupon_sn",
														"description" => "優惠卷",
														"in" => "formData",
														"type" => "string" 
												),
												array (
														"name" => "rs",
														"description" => "續扣<br/>0:一般刷卡(單次)<br/>1:約定信用卡(自動扣)",
														"in" => "formData",
														"type" => "integer",
														"enum" => array (
																0,
																1 
														) 
												),
												array (
														"name" => "invoice_type",
														"description" => "發票類型<br/>1:電子發票用vidol載具<br/>2:捐贈發票<br/>3:索取三連發票",
														"in" => "formData",
														"type" => "integer",
														"required" => TRUE,
														"enum" => array (
																1,
																2,
																3 
														) 
												),
												array (
														"name" => "BuyerName",
														"description" => "買受人名稱",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "BuyerUBN",
														"description" => "買受人統一編號(8碼)",
														"in" => "formData",
														"type" => "string" 
												),
												array (
														"name" => "BuyerPhone",
														"description" => "買受人電話",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "BuyerAddress",
														"description" => "買受人地址",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "BuyerEmail",
														"description" => "買受人電子信箱",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "LoveCode",
														"description" => "愛心碼",
														"in" => "formData",
														"type" => "string" 
												),
												array (
														"name" => "Comment",
														"description" => "備註",
														"in" => "formData",
														"type" => "string" 
												),
												array (
														"name" => "return_url",
														"description" => "交易成功/失敗返回網址",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "ip",
														"description" => "購買會員IP",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功",
														"schema" => array (
																"title" => "result",
																"type" => "object",
																"description" => "api result data",
																"properties" => array (
																		"order" => array (
																				"title" => "order info",
																				"type" => "object",
																				"description" => "訂單",
																				"properties" => array (
																						"status_code" => array (
																								"type" => "integer",
																								"description" => "狀態碼" 
																						),
																						"order_sn" => array (
																								"type" => "string",
																								"description" => "訂單序號" 
																						),
																						"price" => array (
																								"type" => "integer",
																								"description" => "訂單金額" 
																						) 
																				) 
																		),
																		"invoice" => array (
																				"title" => "invoice info",
																				"type" => "object",
																				"description" => "發票",
																				"properties" => array (
																						"status_code" => array (
																								"type" => "integer",
																								"description" => "狀態碼" 
																						) 
																				) 
																		),
																		"cash" => array (
																				"title" => "invoice info",
																				"type" => "object",
																				"description" => "成功狀態",
																				"properties" => array (
																						"status_code" => array (
																								"type" => "integer",
																								"description" => "狀態碼" 
																						) 
																				) 
																		),
																		"html" => array (
																				"type" => "string",
																				"description" => "HTML程式碼" 
																		) 
																) 
														) 
												),
												"401" => array (
														"description" => "優惠卷無法使用" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"404" => array (
														"description" => "超出用戶兌換次數上限" 
												),
												"405" => array (
														"description" => "無此商品" 
												),
												"406" => array (
														"description" => "重複購買"
												),
												"408" => array (
														"description" => "金流通路錯誤" 
												),
												"409" => array (
														"description" => "商品與序號衝突(產包不同)" 
												),
												// "411" => array (
												// "description" => "統一編號錯誤"
												// ),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/billing/coupons/exchange" => array (
								"post" => array (
										"tags" => array (
												"2.billings" 
										),
										"summary" => "序號兌換",
										"description" => "序號兌換",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "user_creat",
														"description" => "訂單建立者(0:system)對應後台帳號pk",
														"in" => "formData",
														"type" => "integer" 
												),
												array (
														"name" => "user_no",
														"description" => "購買會員(user_pk等會員整合後使用)",
														"in" => "formData",
														"type" => "integer" 
												),
												array (
														"name" => "mongo_id",
														"description" => "會員mongo_id",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "member_id",
														"description" => "會員ID",
														"in" => "formData",
														"type" => "string" 
												),
												array (
														"name" => "coupon_sn",
														"description" => "優惠卷",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "ip",
														"description" => "購買會員IP",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功",
														"schema" => array (
																"title" => "result",
																"type" => "object",
																"description" => "api result data",
																"properties" => array (
																		"coupon" => array (
																				"title" => "order info",
																				"type" => "object",
																				"description" => "優惠卷",
																				"properties" => array (
																						"status_code" => array (
																								"type" => "integer",
																								"description" => "狀態碼" 
																						),
																						"order_sn" => array (
																								"type" => "string",
																								"description" => "訂單序號" 
																						),
																						"price" => array (
																								"type" => "integer",
																								"description" => "訂單金額" 
																						) 
																				) 
																		),
																		"cash" => array (
																				"title" => "invoice info",
																				"type" => "object",
																				"description" => "成功狀態",
																				"properties" => array (
																						"status_code" => array (
																								"type" => "integer",
																								"description" => "狀態碼" 
																						) 
																				) 
																		) 
																) 
														) 
												),
												"401" => array (
														"description" => "優惠卷無法使用" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"404" => array (
														"description" => "超出用戶兌換次數上限" 
												),
												"405" => array (
														"description" => "無此商品" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/billing/receptions/spgateway" => array (
								"post" => array (
										"tags" => array (
												"2.billings" 
										),
										"summary" => "接收智付通回傳",
										"description" => "智付通交易成功回傳資料處理用",
										"parameters" => array (
												array (
														"name" => "JSONData",
														"description" => "智付通回傳資料",
														"in" => "formData",
														"type" => "string" 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功",
														"schema" => array (
																"title" => "result",
																"type" => "object",
																"description" => "api result data",
																"properties" => array (
																		"cash" => array (
																				"title" => "invoice info",
																				"type" => "object",
																				"description" => "成功狀態",
																				"properties" => array (
																						"status_code" => array (
																								"type" => "integer",
																								"description" => "狀態碼" 
																						) 
																				) 
																		) 
																) 
														) 
												),
												"401" => array (
														"description" => "資料來源不正確" 
												),
												"405" => array (
														"description" => "訂單金流錯誤" 
												),
												"409" => array (
														"description" => "接收資料不足" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/billing/automatics/package" => array (
								"get" => array (
										"tags" => array (
												"2.billings" 
										),
										"summary" => "自動續扣產包",
										"description" => "自動續扣產包",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "query",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												),
												"401" => array (
														"description" => "資料來源不正確" 
												),
												"405" => array (
														"description" => "訂單金流錯誤" 
												),
												"409" => array (
														"description" => "接收資料不足" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/invoice/pay2go/send" => array (
								"get" => array (
										"tags" => array (
												"3.invoice" 
										),
										"summary" => "開立發票",
										"description" => "自動篩選達到須開立發票的日期開立發票,資料庫建立時開立時間已是7天後日期",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string" 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "query",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												) 
										) 
								) 
						),
						"/video/checks/rights" => array (
								"get" => array (
										"tags" => array (
												"4.video" 
										),
										"summary" => "檢查多媒體觀看權限",
										"description" => "檢查多媒體觀看權限",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "user_no",
														"description" => "購買會員(user_pk等會員整合後使用)",
														"in" => "query",
														"type" => "integer" 
												),
												array (
														"name" => "mongo_id",
														"description" => "會員mongo_id",
														"in" => "query",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "member_id",
														"description" => "會員ID",
														"in" => "query",
														"type" => "string" 
												),
												array (
														"name" => "video_type",
														"description" => "多媒體類型",
														"in" => "query",
														"type" => "string",
														"required" => TRUE,
														"enum" => array (
																"episode",
																"channel",
																"live",
																"event",
																"all" 
														) 
												),
												array (
														"name" => "video_no",
														"description" => "多媒體號碼",
														"in" => "query",
														"type" => "integer",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "query",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功",
														"schema" => array (
																"title" => "result",
																"type" => "object",
																"description" => "api result data",
																"properties" => array (
																		"check_rights" => array (
																				"title" => "invoice info",
																				"type" => "object",
																				"description" => "成功狀態",
																				"properties" => array (
																						"status_code" => array (
																								"type" => "integer",
																								"description" => "狀態碼" 
																						) 
																				) 
																		),
																		"ad" => array (
																				"title" => "invoice info",
																				"type" => "boolean",
																				"description" => "顯示廣告" 
																		) 
																) 
														) 
												),
												"401" => array (
														"description" => "未授權" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/video/videos/package/{video_type}/{video_no}" => array (
								"get" => array (
										"tags" => array (
												"4.video" 
										),
										"summary" => "取得多媒體產品包資訊",
										"description" => "取得多媒體產品包資訊",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "video_type",
														"description" => "多媒體類型",
														"in" => "path",
														"type" => "string",
														"required" => TRUE,
														"enum" => array (
																"episode",
																"channel",
																"live",
																"event",
																"all" 
														) 
												),
												array (
														"name" => "video_no",
														"description" => "多媒體號碼",
														"in" => "path",
														"type" => "integer",
														"required" => TRUE 
												),
												array (
														"name" => "show",
														"description" => "顯示(1:在前端顯示)",
														"in" => "query",
														"type" => "integer" 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "query",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功",
														"schema" => array (
																"title" => "result",
																"type" => "object",
																"description" => "api result data",
																"properties" => array (
																		"result" => array (
																				"title" => "video package info",
																				"type" => "object",
																				"description" => "產品包資訊",
																				"properties" => array (
																						"no" => array (
																								"type" => "integer",
																								"description" => "產品編號" 
																						),
																						"title" => array (
																								"type" => "string",
																								"description" => "標題" 
																						),
																						"description" => array (
																								"type" => "string",
																								"description" => "描述" 
																						) 
																				) 
																		) 
																) 
														) 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								),
								"post" => array (
										"tags" => array (
												"4.video" 
										),
										"summary" => "更新多媒體產品包資訊",
										"description" => "更新多媒體產品包資訊(只建立/更新不取消)",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "programme_no",
														"description" => "節目號碼",
														"in" => "formData",
														"type" => "integer" 
												),
												array (
														"name" => "video_type",
														"description" => "多媒體類型",
														"in" => "path",
														"type" => "string",
														"required" => TRUE,
														"enum" => array (
																"episode",
																"channel",
																"live",
																"event",
																"all" 
														) 
												),
												array (
														"name" => "video_no",
														"description" => "多媒體號碼",
														"in" => "path",
														"type" => "integer",
														"required" => TRUE 
												),
												array (
														"name" => "package",
														"description" => "產品包(ex:1,2,3)(null全清)",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								),
								"put" => array (
										"tags" => array (
												"4.video" 
										),
										"summary" => "更新多媒體產品包資訊",
										"description" => "更新多媒體產品包資訊(先全部取消在建立/更新)",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "programme_no",
														"description" => "節目號碼",
														"in" => "formData",
														"type" => "integer" 
												),
												array (
														"name" => "video_type",
														"description" => "多媒體類型",
														"in" => "path",
														"type" => "string",
														"required" => TRUE,
														"enum" => array (
																"episode",
																"channel",
																"live",
																"event",
																"all" 
														) 
												),
												array (
														"name" => "video_no",
														"description" => "多媒體號碼",
														"in" => "path",
														"type" => "integer",
														"required" => TRUE 
												),
												array (
														"name" => "package",
														"description" => "產品包(ex:1,2,3)(null全清)",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								),
								"delete" => array (
										"tags" => array (
												"4.video" 
										),
										"summary" => "更新多媒體產品包資訊",
										"description" => "更新多媒體產品包資訊(只取消)",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "programme_no",
														"description" => "節目號碼",
														"in" => "formData",
														"type" => "integer" 
												),
												array (
														"name" => "video_type",
														"description" => "多媒體類型",
														"in" => "path",
														"type" => "string",
														"required" => TRUE,
														"enum" => array (
																"episode",
																"channel",
																"live",
																"event",
																"all" 
														) 
												),
												array (
														"name" => "video_no",
														"description" => "多媒體號碼",
														"in" => "path",
														"type" => "integer",
														"required" => TRUE 
												),
												array (
														"name" => "package",
														"description" => "產品包(ex:1,2,3)(null全清)",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/system/caches/memcached" => array (
								"get" => array (
										"tags" => array (
												"99.system" 
										),
										"summary" => "取得暫存",
										"description" => "取得暫存",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "key",
														"description" => "暫存key",
														"in" => "query",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "query",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								),
								"post" => array (
										"tags" => array (
												"99.system" 
										),
										"summary" => "建立暫存",
										"description" => "建立暫存",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "key",
														"description" => "暫存key",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "value",
														"description" => "暫存資料",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								),
								"delete" => array (
										"tags" => array (
												"99.system" 
										),
										"summary" => "清除暫存",
										"description" => "清除暫存",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string" 
												),
												array (
														"name" => "key",
														"description" => "暫存key",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/system/strings/aes_encode" => array (
								"post" => array (
										"tags" => array (
												"99.system" 
										),
										"summary" => "AES加密",
										"description" => "AES加密",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "key",
														"description" => "加密key",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "iv",
														"description" => "加密iv",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "string",
														"description" => "字串",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						),
						"/system/strings/aes_decode" => array (
								"post" => array (
										"tags" => array (
												"99.system" 
										),
										"summary" => "AES解密",
										"description" => "AES解密",
										"parameters" => array (
												array (
														"name" => "Authorization",
														"description" => "token",
														"in" => "header",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "key",
														"description" => "加密key",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "iv",
														"description" => "加密iv",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "string",
														"description" => "字串",
														"in" => "formData",
														"type" => "string",
														"required" => TRUE 
												),
												array (
														"name" => "debug",
														"description" => "除錯用多列印出取得資料變數",
														"in" => "formData",
														"type" => "string",
														"enum" => array (
																'debug' 
														) 
												) 
										),
										"responses" => array (
												"200" => array (
														"description" => "成功" 
												),
												"403" => array (
														"description" => "token未授權" 
												),
												"416" => array (
														"description" => "傳遞資料錯誤" 
												) 
										) 
								) 
						) 
				) 
		);
		$this->output->set_content_type ( "application/json" );
		$this->output->set_output ( json_encode ( $doc_array ) );
	}
	
	/**
	 * 回傳的資料整理
	 *
	 * @param unknown $type        	
	 * @return string[]
	 */
	function __get_responses_data($type) {
		$responses = array ();
		switch ($type) {
			case "user level info" :
				$responses = array (
						"title" => "user level info",
						"type" => "object",
						"description" => "會員等級資訊",
						"properties" => array (
								"no" => array (
										"type" => "integer",
										"description" => "序號" 
								),
								"title" => array (
										"type" => "integer",
										"description" => "會員等級" 
								),
								"tag" => array (
										"type" => "string",
										"description" => "會員等級代號" 
								) 
						) 
				);
				break;
			case "order info" :
				$responses = array (
						"title" => "order info",
						"type" => "object",
						"description" => "訂單資訊",
						"properties" => array (
								"order_sn" => array (
										"type" => "integer",
										"description" => "訂單序號" 
								),
								"package_no" => array (
										"type" => "integer",
										"description" => "產品包編號" 
								),
								"package_title" => array (
										"type" => "string",
										"description" => "產品包標題" 
								),
								"coupon_sn" => array (
										"type" => "string",
										"description" => "序號" 
								),
								"coupon_title" => array (
										"type" => "string",
										"description" => "序號設定標題" 
								),
								"expenses" => array (
										"type" => "integer",
										"description" => "折扣" 
								),
								"subtotal" => array (
										"type" => "integer",
										"description" => "實際付金額" 
								),
								"status" => array (
										"type" => "integer",
										"description" => "狀態(-1:fail,0:pending,1:success,2:cancel)" 
								),
								"createdAt" => array (
										"type" => "string",
										"description" => "建立時間" 
								),
								"activeAt" => array (
										"type" => "string",
										"description" => "啟用時間" 
								),
								"deadlineAt" => array (
										"type" => "string",
										"description" => "到期時間" 
								),
								"note" => array (
										"type" => "string",
										"description" => "備註" 
								) 
						) 
				);
				break;
			case "package info" :
				$responses = array (
						"title" => "package info",
						"type" => "object",
						"description" => "產品包資訊",
						"properties" => array (
								"no" => array (
										"type" => "integer",
										"description" => "產品編號" 
								),
								"title" => array (
										"type" => "string",
										"description" => "標題" 
								),
								"description" => array (
										"type" => "string",
										"description" => "描述" 
								),
								"cost" => array (
										"type" => "integer",
										"description" => "成本" 
								),
								"price" => array (
										"type" => "integer",
										"description" => "銷售價錢" 
								),
								"createdAt" => array (
										"type" => "string",
										"description" => "建立時間" 
								),
								"updatedAt" => array (
										"type" => "string",
										"description" => "更新時間" 
								) 
						) 
				);
				break;
			case "payment info" :
				$responses = array (
						"title" => "payment info",
						"type" => "object",
						"description" => "金流通路",
						"properties" => array (
								"no" => array (
										"type" => "integer",
										"description" => "通路號碼" 
								),
								"title" => array (
										"type" => "string",
										"description" => "通路" 
								),
								"description" => array (
										"type" => "string",
										"description" => "描述" 
								),
								"proxy" => array (
										"type" => "string",
										"description" => "代理商代號(spgateway:智付通,pay2go:智付寶)" 
								),
								"type" => array (
										"type" => "string",
										"description" => "付款類型(CREDIT:信用卡,WEBATM:WEBATM,VACC:ATM轉帳,CVS:超商代碼,BARCODE:條碼)" 
								) 
						) 
				);
				break;
			case "pagination info" :
				$responses = array (
						"title" => "pagination info",
						"type" => "object",
						"description" => "分頁資訊",
						"properties" => array (
								"page_previous" => array (
										"type" => "integer",
										"description" => "上一頁數" 
								),
								"page" => array (
										"type" => "integer",
										"description" => "當前頁數" 
								),
								"page_next" => array (
										"type" => "integer",
										"description" => "下一頁數" 
								),
								"page_size" => array (
										"type" => "integer",
										"description" => "每頁資料筆數" 
								),
								"page_total" => array (
										"type" => "integer",
										"description" => "總頁數" 
								),
								"count_total" => array (
										"type" => "integer",
										"description" => "總資料筆數" 
								) 
						) 
				);
				break;
			default :
				$responses = array (
						"description" => "OK" 
				);
				break;
		}
		return $responses;
	}
}

/* End of file swaggerDoc.php */
/* Location: ./application/controllers/v1/swaggerDoc.php */

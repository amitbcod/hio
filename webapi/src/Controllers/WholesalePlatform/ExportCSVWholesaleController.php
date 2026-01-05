<?php

namespace App\Controllers\WholesalePlatform;

use DbWholeSale;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class ExportCSVWholesaleController
{
	private $wholesale_obj;

	public function __construct(){
		$this->wholesale_obj = new DbWholeSale();
	}

	public function __invoke(Request $request, Response $response, $args){

		$shopcode = $args['shopcode'];

		$sis_export_header = ["Style","Full Style Name","Name","Price","Location Available","Size","Size2","Color","Season","UPC","Internal ID","Weight","Release Date","Manufacturer Country","Manufacturer Tariff"];

		$product_list = $this->wholesale_obj->getInStockProducts($shopcode, 0);
		$ExportValuesArr = [];

		if($product_list != false){

			foreach($product_list as $item){
				if($item['parent_id'] > 0 ){
					$product_code = $item['parent_product_code'];
					$launch_date = date('d/m/Y',$item['parent_launch_date']);
				} else {
					$product_code = $item['product_code'];
					$launch_date = date('d/m/Y',$item['launch_date']);
				}

				$SingleRow = [
					$product_code,
					$item['sku'],
					$item['name'],
					round(($item['price'] / 0.85),0),
					$item['available_qty'],
					$item['size'] ?? '',
					$item['shoe_size'] ?? '',
					$item['color'] ?? '',
					$item['season'] ?? '',
					$item['barcode'],
					$item['id'],
					$item['weight'],
					$launch_date,
					$item['country_of_origin'] ?? '',
					$item['manufacturer_tariff'] ?? '',
				];

				$ExportValuesArr[]=$SingleRow;
			}
		}

		$filename = 'Catalog-' . $shopcode .'-' . time() . '.csv';


		$stream = fopen('php://memory', 'wb+');
		fputcsv($stream, $sis_export_header);

		if(isset($ExportValuesArr) && count($ExportValuesArr)>0){
			foreach ($ExportValuesArr as $readData) {
				fputcsv($stream, $readData);
			}
		}

		rewind($stream);

		$response = $response->withHeader('Content-Type', 'text/csv');
		$response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

		return $response->withBody(new Stream($stream));
	}

}

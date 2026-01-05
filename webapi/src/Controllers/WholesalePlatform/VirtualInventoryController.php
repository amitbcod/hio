<?php

namespace App\Controllers\WholesalePlatform;

use DbWholeSale;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class VirtualInventoryController
{
    private $wholesale_obj;

    public function __construct()
    {
        $this->wholesale_obj = new DbWholeSale();
    }

    public function __invoke(Request $request, Response $response, $args): Response
	{
        $shopcode = 'shop1';

        $uses_limit_15 = ($_GET['show_lower_than_15'] ?? '0') !== '1';
        $product_list = $this->wholesale_obj->getInStockProducts($shopcode, $uses_limit_15 ? 15 : 0);

        $ExportValuesArr = array();
        if($product_list){
            foreach($product_list as $item){
                if($item['parent_id'] > 0 ){
                    $product_code = $item['parent_product_code'] ?? '';
                    $launch_date = date('Y-m-d',$item['parent_launch_date']);
                }else{
                    $product_code = $item['product_code'];
                    $launch_date = date('Y-m-d',$item['launch_date']);
                }

                $ExportValuesArr[] = [
                    $item['sku'],
                    $item['name'],
                    $item['barcode'],
                    round(($item['price'] / 0.85),0),
                    $product_code,
                    min($item['available_qty'], 50),
                    $launch_date,
                ];
            }
        }

        $filename = 'VirtualInventory-' . date('Ymdhis') . '.csv';

		$stream = fopen('php://memory', 'wb+');
		fputcsv($stream, ["Name","Display Name","UPC Code","Base Price", "Style", "Quantity Available","Release Date"]);

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

<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Orders extends ResourceController
{
    protected $modelName = 'App\Models\OrderModel';
    protected $format = 'json';

    public function create()
    {
        $data = $this->request->getJSON();
        $orderData = [
            'table_number' => $data->table_number,
            'total_price' => 0
        ];
        $orderId = $this->model->insert($orderData);

        $totalPrice = 0;
        foreach ($data->items as $item) {
            $product = $this->model->getProductById($item->product_id);
            $totalPrice += $product->price * $item->quantity;
            $this->model->addOrderDetail($orderId, $item->product_id, $item->quantity);
        }

        $this->model->update($orderId, ['total_price' => $totalPrice]);

        $printers = $this->determinePrinters($data->items);

        return $this->respond(['order_id' => $orderId, 'printers' => $printers]);
    }

    private function determinePrinters($items)
    {
        $printers = ['A'];
        $hasFood = false;
        $hasDrink = false;
    
        foreach ($items as $item) {
            
            $product = $this->model->getProductById($item->product_id);
    
            if ($product) {
                if ($product->category === 'Makanan' || $product->name === 'Promo Nasi Goreng + Jeruk Dingin') {
                    $hasFood = true;
                } elseif ($product->category === 'Minuman') {
                    $hasDrink = true;
                }
            }
        }
    
        if ($hasFood) {
            $printers[] = 'B';
        }
        if ($hasDrink) {
            $printers[] = 'C';
        }
    
        return array_unique($printers);
    }
    

    public function show($id = null)
    {
        $order = $this->model->find($id);
        if (!$order) {
            return $this->failNotFound('Order not found');
        }

        $details = $this->model->getOrderDetails($id);
        $order['details'] = $details;

        return $this->respond($order);
    }
}

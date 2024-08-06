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

        $printerStatus = $this->determinePrinterStatus($data->items);
        $this->model->setPrinterStatus($orderId, $printerStatus);

        $printers = $this->getPrinterNames($printerStatus);

        return $this->respond(['order_id' => $orderId, 'printers' => $printers]);
    }

    private function determinePrinterStatus($items)
    {
        $status = 0;

        foreach ($items as $item) {
            $product = $this->model->getProductById($item->product_id);

            if ($product) {
                if ($product->category === 'Makanan' || $product->name === 'Promo Nasi Goreng + Jeruk Dingin') {
                    $status |= (1 << 1); // Set bit 1 for Printer Dapur
                }
                if ($product->category === 'Minuman') {
                    $status |= (1 << 2); // Set bit 2 for Printer Bar
                }
                if ($product->name === 'Nasi Goreng + Jeruk Dingin') {
                    $status |= (1 << 0); // Set bit 0 for Printer Kasir
                }
            }
        }

        return $status;
    }

    private function getPrinterNames($printerStatus)
    {
        $printers = [
            0 => 'Printer Kasir',
            1 => 'Printer Dapur (Makanan)',
            2 => 'Printer Bar (Minuman)'
        ];

        $printerNames = [];
        foreach ($printers as $bit => $printer) {
            if ($printerStatus & (1 << $bit)) {
                $printerNames[] = $printer;
            }
        }

        return $printerNames;
    }

    public function show($id = null)
    {
        $order = $this->model->find($id);
        if (!$order) {
            return $this->failNotFound('Order not found');
        }

        $details = $this->model->getOrderDetails($id);
        $order['details'] = $details;

        
        $printerStatus = $this->model->getPrinterStatus($id);
        $order['printers'] = $this->getPrinterNames($printerStatus);

        return $this->respond($order);
    }
}

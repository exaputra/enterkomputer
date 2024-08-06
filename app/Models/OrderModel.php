<?php namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $allowedFields = ['table_number', 'total_price'];

    public function getProductById($id)
    {
        return $this->db->table('products')->where('id', $id)->get()->getRow();
    }

    public function addOrderDetail($orderId, $productId, $quantity)
    {
        $this->db->table('order_details')->insert([
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
    }

    public function getOrderDetails($orderId)
    {
        return $this->db->table('order_details')
                        ->join('products', 'order_details.product_id = products.id')
                        ->where('order_details.order_id', $orderId)
                        ->select('products.name, order_details.quantity, products.price')
                        ->get()
                        ->getResultArray();
    }
}

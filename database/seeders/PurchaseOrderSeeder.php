<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $purchase_orders = [
        //     [
        //         "apc_purchase_order_id" => "2021-0001",
        //         //YYYY-MM-DD
        //         "date_filed" => "2021-01-01",
        //         "purchase_date" => "2021-02-01",
        //         "total_cost" => 1000,

        //         "requested_by" => "",
        //         "verified_by" => "",
        //         "funding_assured_by" => "",
        //         "approved_by" => "",
        //     ],
        //     [
        //         "apc_purchase_order_id" => "2022-0089",
        //         "date_filed" => "2022-01-01",
        //         "purchase_date" => "2022-02-01",
        //         "total_cost" => 2000,

        //         "requested_by" => "",
        //         "verified_by" => "",
        //         "funding_assured_by" => "",
        //         "approved_by" => "",
        //     ],
        // ];
        // foreach ($purchase_orders as $purchase_order) {
        //     PurchaseOrder::create($purchase_order);
        // }
    }
}

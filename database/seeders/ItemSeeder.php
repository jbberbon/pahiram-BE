<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStatus;
use App\Utils\Constants\GROUP_CATEGORY_SAMPLE;
use App\Utils\Constants\SampleData\ITEM_GROUP_SAMPLE;
use App\Utils\Constants\Statuses\ITEM_STATUS;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    private function getItemGroupPK(string $modelName)
    {
        $itemGroup = ItemGroup::where("model_name", $modelName)->firstOrFail();
        return $itemGroup['id'];
    }

    private function getActiveStatus()
    {
        $active = ItemStatus::where('item_status', ITEM_STATUS::ACTIVE)->firstOrFail();
        return $active['id'];
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itemGroup = new ITEM_GROUP_SAMPLE();
        $items = [
            // ITRO
            // Canon
            [
                'apc_item_id' => '000001',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Canon 200d")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000011',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Canon 200d")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000002',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Canon 200d")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000022',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Canon 200d")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            // Macbook
            [
                'apc_item_id' => '000003',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("MacBook Air M1")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000033',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("MacBook Air M1")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000004',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("MacBook Air M1")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000044',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("MacBook Air M1")),
                'item_status_id' => $this->getActiveStatus(),
            ],

            // Arduino
            [
                'apc_item_id' => '000005',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Arduino Uno R4 WiFi")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000055',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Arduino Uno R4 WiFi")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000006',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Arduino Uno R4 WiFi")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000066',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Arduino Uno R4 WiFi")),
                'item_status_id' => $this->getActiveStatus(),
            ],

            // BMO
            // Spalding
            [
                'apc_item_id' => '000007',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Spalding FIBA 2007")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000077',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Spalding FIBA 2007")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000008',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Spalding FIBA 2007")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000088',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Spalding FIBA 2007")),
                'item_status_id' => $this->getActiveStatus(),
            ],

            // Lifetime
            [
                'apc_item_id' => '000009',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Lifetime 6ft Folding Table")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '000099',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Lifetime 6ft Folding Table")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '100000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Lifetime 6ft Folding Table")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '110000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Lifetime 6ft Folding Table")),
                'item_status_id' => $this->getActiveStatus(),
            ],

            // JBL
            [
                'apc_item_id' => '200000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("JBL Quantum Duo Speaker")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '220000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("JBL Quantum Duo Speaker")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '300000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("JBL Quantum Duo Speaker")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '330000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("JBL Quantum Duo Speaker")),
                'item_status_id' => $this->getActiveStatus(),
            ],

            // Eslo
            // Microscope
            [
                'apc_item_id' => '400000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Micron Cresta ZS Microscope")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '440000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Micron Cresta ZS Microscope")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '500000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Micron Cresta ZS Microscope")),
                'item_status_id' => $this->getActiveStatus(),
            ],

            // 3d Printer
            [
                'apc_item_id' => '550000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Replicator+ 3D Printer")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '600000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Replicator+ 3D Printer")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '660000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Replicator+ 3D Printer")),
                'item_status_id' => $this->getActiveStatus(),
            ],

            // Voltage Tester
            [
                'apc_item_id' => '700000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("FLUKE T150 Voltage Tester")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '770000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("FLUKE T150 Voltage Tester")),
                'item_status_id' => $this->getActiveStatus(),
            ],
            [
                'apc_item_id' => '800000',
                'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("FLUKE T150 Voltage Tester")),
                'item_status_id' => $this->getActiveStatus(),
            ],

        ];
        foreach ($items as $item) {
            Item::create($item);
        }
    }
}

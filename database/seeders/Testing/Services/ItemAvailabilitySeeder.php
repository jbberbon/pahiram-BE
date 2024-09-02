<?php

namespace Database\Seeders\Testing\Services;
use App\Models\AccountStatus;
use App\Models\BorrowedItem;
use App\Models\BorrowedItemStatus;
use App\Models\BorrowPurpose;
use App\Models\BorrowTransaction;
use App\Models\BorrowTransactionStatus;
use App\Models\Department;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemStatus;
use App\Models\Role;
use App\Models\User;
use App\Utils\Constants\SampleData\ITEM_GROUP_SAMPLE;
use App\Utils\Constants\Statuses\ITEM_STATUS;
use Database\Seeders\AccountStatusSeeder;
use Database\Seeders\BorrowedItemStatusSeeder;
use Database\Seeders\BorrowPurposeSeeder;
use Database\Seeders\BorrowTransactionStatusSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ItemGroupCategorySeeder;
use Database\Seeders\ItemGroupSeeder;
use Database\Seeders\ItemStatusSeeder;
use Database\Seeders\PenalizedTransactionStatusesSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Seeder;

class ItemAvailabilitySeeder extends Seeder
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
    public function run($argument): void
    {
        $this->call([
                // Status
            AccountStatusSeeder::class,
            BorrowedItemStatusSeeder::class,
            PenalizedTransactionStatusesSeeder::class,
            BorrowTransactionStatusSeeder::class,
            ItemStatusSeeder::class,

                // Non Statuses
            BorrowPurposeSeeder::class,
            DepartmentSeeder::class,
            RoleSeeder::class,

                // NEED FK
            ItemGroupCategorySeeder::class,
            ItemGroupSeeder::class,
        ]);

        User::create([
            'apc_id' => '1',
            'first_name' => 'John Christian',
            'last_name' => 'Berbon',
            'email' => 'test@student.apc.edu.ph',

            'user_role_id' => Role::getIdByRole('BORROWER'),
            'acc_status_id' => AccountStatus::getIdByStatus('ACTIVE')
        ]);

        $itemGroup = new ITEM_GROUP_SAMPLE();
        Item::create([
            'apc_item_id' => '000001',
            'item_group_id' => $this->getItemGroupPK($itemGroup->getItemGroupSample("Canon 200d")),
            'item_status_id' => $this->getActiveStatus(),
        ]);

        BorrowTransaction::create([
            'department_id' => Department::getIdBasedOnAcronym('ITRO'),
            'transac_status_id' => BorrowTransactionStatus::getIdByStatus('ON_GOING'),
            'purpose_id' => BorrowPurpose::getIdByPurpose('OTHERS'),
            'borrower_id' => User::first()->id
        ]);

        BorrowedItem::create([
            'borrowing_transac_id' => BorrowTransaction::first()->id,
            'item_id' => Item::first()->id,

            'borrowed_item_status_id' => BorrowedItemStatus::getIdByStatus($argument['borrowed_item_status']),
            'start_date' => $argument['start_date'],
            'due_date' => $argument['due_date']
        ]);
    }
}
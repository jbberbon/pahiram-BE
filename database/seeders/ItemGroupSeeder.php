<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\ItemGroup;
use App\Models\ItemGroupCategory;
use App\Utils\Constants\GROUP_CATEGORY_SAMPLE;
use App\Utils\Constants\ITEM_GROUP_SAMPLE;
use App\Utils\Constants\OFFICE_LIST;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemGroupSeeder extends Seeder
{
    protected $GROUP_CATEGORY_NAMES = [
        "CAMERA",
        "LAPTOP",
        "BALL",
        "TABLE",
        "MICROSCOPE",
        "3D_PRINTER",
        "MICROCONTROLLER",
        "SPEAKER",
        "VOLTAGE_TESTER"
    ];

    private function getItemGroupCategoryPK(string $categoryName)
    {
        $itemGroupCategory = ItemGroupCategory::where("category_name", $categoryName)->firstOrFail();
        return $itemGroupCategory['id'];
    }

    private function getDepartmentPK(string $departmentAcronym)
    {
        $department = Department::where("department_acronym", $departmentAcronym)->firstOrFail();
        return $department['id'];
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupCategorySample = new GROUP_CATEGORY_SAMPLE();
        $itemGroup = new ITEM_GROUP_SAMPLE();
        $groups = [
            // ITRO
            [
                "model_name" => $itemGroup->getItemGroupSample("Canon 200d"),
                "is_required_supervisor_approval" => true,

                "group_category_id" => $this->getItemGroupCategoryPK($groupCategorySample->getGroupCategorySampleData("CAMERA")),
                "department_id" => $this->getDepartmentPK(OFFICE_LIST::getOfficeAcronymFromOfficeConstant("ITRO")),
            ],
            [
                "model_name" => $itemGroup->getItemGroupSample("MacBook Air M1"),
                "is_required_supervisor_approval" => true,

                "group_category_id" => $this->getItemGroupCategoryPK($groupCategorySample->getGroupCategorySampleData("LAPTOP")),
                "department_id" => $this->getDepartmentPK(OFFICE_LIST::getOfficeAcronymFromOfficeConstant("ITRO")),
            ],
            [
                "model_name" => $itemGroup->getItemGroupSample("Arduino Uno R4 WiFi"),
                "is_required_supervisor_approval" => true,

                "group_category_id" => $this->getItemGroupCategoryPK($groupCategorySample->getGroupCategorySampleData("MICROCONTROLLER")),
                "department_id" => $this->getDepartmentPK(OFFICE_LIST::getOfficeAcronymFromOfficeConstant("ITRO")),
            ],
            // BMO
            [
                "model_name" => $itemGroup->getItemGroupSample("Spalding FIBA 2007"),
                "is_required_supervisor_approval" => false,

                "group_category_id" => $this->getItemGroupCategoryPK($groupCategorySample->getGroupCategorySampleData("BALL")),
                "department_id" => $this->getDepartmentPK(OFFICE_LIST::getOfficeAcronymFromOfficeConstant("BMO")),

            ],
            [
                "model_name" => $itemGroup->getItemGroupSample("Lifetime 6ft Folding Table"),
                "is_required_supervisor_approval" => false,

                "group_category_id" => $this->getItemGroupCategoryPK($groupCategorySample->getGroupCategorySampleData("TABLE")),
                "department_id" => $this->getDepartmentPK(OFFICE_LIST::getOfficeAcronymFromOfficeConstant("BMO")),
            ],
            [
                "model_name" => $itemGroup->getItemGroupSample("JBL Quantum Duo Speaker"),
                "is_required_supervisor_approval" => true,

                "group_category_id" => $this->getItemGroupCategoryPK($groupCategorySample->getGroupCategorySampleData("SPEAKER")),
                "department_id" => $this->getDepartmentPK(OFFICE_LIST::getOfficeAcronymFromOfficeConstant("BMO")),
            ],
            //ESLO
            [
                "model_name" => $itemGroup->getItemGroupSample("Micron Cresta ZS Microscope"),
                "is_required_supervisor_approval" => true,

                "group_category_id" => $this->getItemGroupCategoryPK($groupCategorySample->getGroupCategorySampleData("3D_PRINTER")),
                "department_id" => $this->getDepartmentPK(OFFICE_LIST::getOfficeAcronymFromOfficeConstant("ESLO")),
            ],
            [
                "model_name" => $itemGroup->getItemGroupSample("FLUKE T150 Voltage Tester"),
                "is_required_supervisor_approval" => false,

                "group_category_id" => $this->getItemGroupCategoryPK($groupCategorySample->getGroupCategorySampleData("VOLTAGE_TESTER")),
                "department_id" => $this->getDepartmentPK(OFFICE_LIST::getOfficeAcronymFromOfficeConstant("ESLO")),
            ],
            [
                "model_name" => $itemGroup->getItemGroupSample("Replicator+ 3D Printer"),
                "is_required_supervisor_approval" => true,

                "group_category_id" => $this->getItemGroupCategoryPK($groupCategorySample->getGroupCategorySampleData("MICROSCOPE")),
                "department_id" => $this->getDepartmentPK(OFFICE_LIST::getOfficeAcronymFromOfficeConstant("ESLO")),
            ],
        ];
        foreach ($groups as $group) {
            ItemGroup::create($group);
        }
    }
}

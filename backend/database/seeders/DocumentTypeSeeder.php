<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'repair_request', 'label_en' => 'Repair Request', 'label_th' => 'แจ้งซ่อม', 'icon' => 'wrench', 'sort_order' => 1, 'routing_mode' => 'hybrid'],
            ['code' => 'pm_am_plan', 'label_en' => 'PM/AM Plan', 'label_th' => 'แผน PM/AM', 'icon' => 'clipboard-document-check', 'sort_order' => 2, 'routing_mode' => 'organization_wide'],
            ['code' => 'spare_parts_requisition', 'label_en' => 'Spare Parts Requisition', 'label_th' => 'เบิกอะไหล่', 'icon' => 'cube', 'sort_order' => 3, 'routing_mode' => 'hybrid'],
        ];

        foreach ($types as $type) {
            DocumentType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'label_en' => $type['label_en'],
                    'label_th' => $type['label_th'],
                    'icon' => $type['icon'],
                    'sort_order' => $type['sort_order'],
                    'routing_mode' => $type['routing_mode'],
                    'is_active' => true,
                ]
            );
        }
    }
}

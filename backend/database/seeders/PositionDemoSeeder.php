<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionDemoSeeder extends Seeder
{
    /** @var list<string> */
    private const FACTORY_CODES = ['MAINT_SUP', 'DEPT_MGR', 'PLANT_MGR', 'WH_KEEPER', 'TECH'];

    public function run(): void
    {
        $positions = [
            ['code' => 'SCH_TEACHER', 'name' => 'ครู / บุคลากรวิชาการ', 'description' => 'Teacher or academic staff — typical form submitter'],
            ['code' => 'SCH_ACAD_HEAD', 'name' => 'หัวหน้าฝ่ายวิชาการ', 'description' => 'Head of academic affairs — first-line academic approval'],
            ['code' => 'SCH_VICE_PRINCIPAL', 'name' => 'รองผู้อำนวยการ', 'description' => 'Vice principal — school-wide approval level'],
            ['code' => 'SCH_ADMIN_OFFICER', 'name' => 'นักวิชาการธุรการ', 'description' => 'Administrative officer — procurement / general affairs'],
            ['code' => 'SCH_FIN_OFFICER', 'name' => 'นักการเงินและบัญชี', 'description' => 'Finance officer — budget and payment-related steps'],
        ];

        foreach ($positions as $pos) {
            Position::updateOrCreate(
                ['code' => $pos['code']],
                ['name' => $pos['name'], 'description' => $pos['description'], 'is_active' => true]
            );
        }

        // Drop legacy CMMS rows when no user and no workflow stage references the position id.
        $positionIdsUsedInStages = DB::table('approval_workflow_stages')
            ->where('approver_type', 'position')
            ->whereNotNull('approver_ref')
            ->where('approver_ref', '!=', '')
            ->pluck('approver_ref')
            ->map(fn (mixed $ref) => (int) $ref)
            ->unique()
            ->values()
            ->all();

        $q = Position::query()
            ->whereIn('code', self::FACTORY_CODES)
            ->whereDoesntHave('users');

        if ($positionIdsUsedInStages !== []) {
            $q->whereNotIn('id', $positionIdsUsedInStages);
        }

        $q->delete();

        $this->command?->info('PositionDemoSeeder: '.count($positions).' school positions.');
    }
}

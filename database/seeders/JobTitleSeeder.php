<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobTitleSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            'Site Engineer',
            'Project Manager',
            'Safety Officer',
            'Quality Inspector',
            'Civil Engineer',
            'Mechanical Engineer',
            'Electrical Engineer',
            'Architect',
            'Site Supervisor',
            'Document Controller',
            'Procurement Officer',
            'HR Specialist',
            'Finance Officer',
            'IT Support',
            'Operations Manager',
        ];

        $now = now();

        $rows = array_map(static fn (string $title) => [
            'name'       => $title,
            'created_at' => $now,
            'updated_at' => $now,
        ], $titles);

        DB::table('job_titles')->upsert($rows, ['name'], ['updated_at']);
    }
}

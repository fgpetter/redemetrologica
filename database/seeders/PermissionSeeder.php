<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['permission' => 'admin'],
            ['permission' => 'funcionario'],
            ['permission' => 'avaliacoes'],
            ['permission' => 'cursos'],
            ['permission' => 'interlabs'],
            ['permission' => 'financeiro'],
            ['permission' => 'cliente'],
        ];
        DB::table('permissions')->insert($permissions);
    }
}

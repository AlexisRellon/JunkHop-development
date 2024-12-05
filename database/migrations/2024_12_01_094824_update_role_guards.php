<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateRoleGuards extends Migration
{
    public function up()
    {
        DB::table('roles')->update(['guard_name' => 'web']);
    }

    public function down()
    {
        DB::table('roles')->update(['guard_name' => 'api']);
    }
}

<?php

use Illuminate\Database\Seeder;
use App\Models\Department\Department;

class ChangeStatusDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = Department::all();

        foreach ($departments as $department) {
           $department->update([
               'status' => false
           ]);
        }
    }
}

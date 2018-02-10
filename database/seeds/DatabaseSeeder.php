<?php

use Illuminate\Database\Seeder;
use App\Models\EmployeesTreeGenerator;
use App\Models\Employees;


class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(EmployeesSeeder::class);
    }
}


class EmployeesSeeder extends Seeder {
	public function run(){
		DB::table('employees')->delete();
		$data = EmployeesTreeGenerator::generate();

		foreach($data as $employee) {
			Employees::create([
				'deep_level' => $employee->getDepth(),
				'employee_id' => $employee->getId(),
				'boss_id' => $employee->getBossId(),
				'f' => $employee->getF(),
				'i' => $employee->getI(),
				'o' => $employee->getO(),
				'position' => $employee->getPosition(),
				'salary' => $employee->getSalary(),
				'hiring_date' => $employee->getHiringDate(),
				'subordinates' => json_encode($employee->getSubordinates()),
			]);
		}
		
	}
}

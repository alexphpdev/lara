<?

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class EmployeesTreeGenerator{

	/**
	 * Возвращает массив работников класса Employee
	 *
	 *
	 * @return array
	 */
	static function generate() {
		// сколько всего сотрудников в фирме
		$amount_employees = rand(50000, 60000);

		// сколько человек = 1 проценту от общего числа
		$one_persent_employees = $amount_employees / 100;

		// общее число сотрудников на каждом уровне иерархии
		$counter_employees = [];
		//$counter_employees[0] = 1;
		$counter_employees[1] = round($one_persent_employees * 3);
		$counter_employees[2] = round($one_persent_employees * 7);
		$counter_employees[3] = round($one_persent_employees * 30);
		$counter_employees[4] = round($one_persent_employees * 60) - 1;

		// на нулевом уровнее иерархии 1 человек - самый-самый главный начальник.
		$main = [];
		$main[0] = [new Employee()];
		

		foreach ($counter_employees as $lvl => $count) {
			// создаёт для каждого не нулевого уровня массив
			// в котором храняться работники соответствующего уровня
			$main[$lvl] = [];

			for ($i=0; $i < $count; $i++) {
				// создаём работника 
				$employee = new Employee($lvl);

				// случайным образом определяем его начальника из предыдущего уровня
				$boss_key = rand(0, count($main[$lvl-1]) - 1);

				// получает идентификатор начальника
				$boss_id = $main[$lvl-1][$boss_key]->getId();

				// задаём идентификатор начальника
				$employee->setBossId($boss_id);

				// добавляем начальнику в массив идентификатор подчинённого
				$main[$lvl-1][$boss_key]->setSubId($employee->getId());

				// помещяем работника в массив на его уровень
				$main[$lvl][] = $employee;
			}
		}

		print_r($counter_employees);
		print_r($amount_employees);
		

		$result = [];
		foreach($main as $lvl) {
			foreach($lvl as $employee) {
				$result[] = $employee;
			}
		}

		shuffle($result); // чтоб не всё было так красиво в БД)

		return $result;
	}

}
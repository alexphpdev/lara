<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Exceptions\FormValidationException;
use App\Models\Exceptions\AvatarLoadException;

class Index extends Model
{

	static $sortedEmployees = [];
	static $employees = [];
	static $find_indexes = []; // индексы , которые нужно пропустить

	static function getAllEmployeesForIndexPage() {
		self::$employees = DB::table('employees')
            ->select('id', 'deep_level', 'employee_id', 'f', 'i', 'o', 'position', 'subordinates', 'img')
            ->orderBy('deep_level')
        	->orderBy('employee_id')
            ->get()
        ;
        
        self::sortEmployees();

        return self::$sortedEmployees;
	}

    static function getAllEmployees() {

        self::$employees = DB::table('employees')
            ->where('deep_level', '<', 2)
        	->orderBy('deep_level')
        	->orderBy('employee_id')
            ->get()
        ;

        self::$sortedEmployees = [];
        self::$find_indexes = [];
        
        self::sortEmployees();

        return self::$sortedEmployees;
    }

    static function sortEmployees() {
    	for($i=0, $size = count(self::$employees); $i<$size; $i++) {

    		if(array_search($i, self::$find_indexes) !== false) continue;

    		$employee = self::$employees[$i];
    		if(isset($employee->hiring_date)) $employee->hiring_date = date('d-m-Y', $employee->hiring_date);

    		// проверка существования файла, если его нет, то очистить поле в БД и заменить строку на noAvatar
            if(empty($employee->img)) $employee->img = '/img/avatars/thumbnails/noAvatar.png';
            else {
            	if(file_exists('img/avatars/thumbnails/' . $employee->img) === false 
            		|| file_exists('img/avatars/source/' . $employee->img) === false){

            		$employee->img = '/img/avatars/thumbnails/noAvatar.png';

            		DB::table('employees')
            			->where('id', $employee->id)
            			->update(['img' => null]);

            	} else {
            		$employee->img = '/img/avatars/thumbnails/' . $employee->img;
            	}
            }
            
            

    		self::$sortedEmployees[] = $employee;

    		$finded_subs = self::getSubs($employee, $i+1);
    		if(!empty($finded_subs)) {
    			self::$find_indexes = array_merge(self::$find_indexes, array_keys($finded_subs));
    			self::$sortedEmployees += $finded_subs;
    		}
    	}
    }

    // возвращает подчинённых
    static function getSubs($employee, $start_index=1){
    	$res = [];
    	$subs_id = json_decode($employee->subordinates);
    	$count_subs = count($subs_id);
    	$find_subs = 0;

    	if($count_subs) {
    		for($i = $start_index, $size = count(self::$employees); $i<$size; $i++){
    			$sub = self::$employees[$i];
    			if(array_search($sub->employee_id, $subs_id) !== false) {

    				if(isset($sub->hiring_date)) $sub->hiring_date = date('d-m-Y', $sub->hiring_date);

            		// проверка существования файла, если его нет, то очистить поле в БД и заменить строку на noAvatar
                    if(empty($sub->img)) $sub->img = '/img/avatars/thumbnails/noAvatar.png';
                    else {
                    	if(file_exists('img/avatars/thumbnails/' . $sub->img) === false 
                    		|| file_exists('img/avatars/source/' . $sub->img) === false){

                    		$sub->img = '/img/avatars/thumbnails/noAvatar.png';

                    		DB::table('employees')
                    			->where('id', $sub->id)
                    			->update(['img' => null]);

                    	} else {
                    		$sub->img = '/img/avatars/thumbnails/' . $sub->img;
                    	}
    				}

    				$res[$i] = $sub;

    				$subs = self::getSubs($sub, $i+1);
    				if(!empty($subs)) $res += $subs;
    				if(++$find_subs == $count_subs) break;
    			}
    		}
    	}

    	return $res;
    }

    static function sortEmployeesByField($column, $type){
    	self::$employees = DB::table('employees')
            ->where('deep_level', '<', 2)
    		->orderBy('deep_level')
    		->orderBy($column, $type)
    	    ->get()
    	;
    	
    	self::sortEmployees();

        $res = [];
        foreach (self::$sortedEmployees as $v) {
            $res[] = $v;
        }
    	return $res;
    }

    static function searchEmployees($searchString) {

        $q = DB::table('employees')
            ->orderBy('deep_level')
            ->orderBy('employee_id')
            ->where('employee_id', 'like', '%'.$searchString.'%')
            ->orWhere('f', 'like', '%'.$searchString.'%')
            ->orWhere('i', 'like', '%'.$searchString.'%')
            ->orWhere('o', 'like', '%'.$searchString.'%')
            ->orWhere('position', 'like', '%'.$searchString.'%')
            ->orWhere('salary', 'like', '%'.$searchString.'%')
        ;
        

        $formatedDate = strtotime($searchString);
        if($formatedDate !== false){
            $q->orWhere('hiring_date', 'like', $formatedDate);
        }

        self::$employees = $q->get();
        
        self::sortEmployees();

        $res = [];
        foreach (self::$sortedEmployees as $v) {
            $res[] = $v;
        }
        return $res;
    }

    static function searchBosses($searchString) {

        self::$employees = DB::table('employees')
            ->orderBy('deep_level')
            ->orderBy('f')
            ->where('deep_level', '<', 4)
            ->where(function ($query) use ($searchString) {
                $query->orWhere('f', 'like', '%'.$searchString.'%')
                        ->orWhere('i', 'like', '%'.$searchString.'%')
                        ->orWhere('o', 'like', '%'.$searchString.'%');
            })
            ->get(['id', 'employee_id', 'f', 'i', 'o', 'position', 'deep_level'])
        ;
        
        return self::$employees;
    }

    static function addEmployeeValidateForm() {
        $data = [];
        $error = 0;

        $employee_f = $_POST['employee_f'];
        if(strlen($employee_f) > 255) {
            $data['employee_f'] = [
                'value'     => $employee_f,
                'error_msg' => 'Длина фамилии не должна превышать 255 символов',
            ];
            $error = 1;
        } elseif(strlen($employee_f) <= 0) {
            $data['employee_f'] = [
                'value'     => '',
                'error_msg' => 'Вы забыли указать фамилию',
            ];
            $error = 1;
        } else { $data['employee_f']['value'] = $employee_f; }

        

        // ------



        $employee_i = $_POST['employee_i'];
        if(strlen($employee_i) > 255) {
            $data['employee_i'] = [
                'value'     => $employee_i,
                'error_msg' => 'Длина имени не должна превышать 255 символов',
            ];
            $error = 1;
        } elseif(strlen($employee_i) <= 0) {
            $data['employee_i'] = [
                'value'     => '',
                'error_msg' => 'Вы забыли указать имя',
            ];
            $error = 1;
        } else { $data['employee_i']['value'] = $employee_i; }


        // ------


        $employee_o = $_POST['employee_o'];
        if(strlen($employee_o) > 255) {
            $data['employee_o'] = [
                'value'     => $employee_o,
                'error_msg' => 'Длина отчества не должна превышать 255 символов',
            ];
            $error = 1;
        } elseif(strlen($employee_o) <= 0) {
            $data['employee_o'] = [
                'value'     => '',
                'error_msg' => 'Вы забыли указать отчество',
            ];
            $error = 1;
        } else { $data['employee_o']['value'] = $employee_o; }


        // ------


        $position = $_POST['position'];
        if(strlen($position) > 255) {
            $data['position'] = [
                'value'     => $position,
                'error_msg' => 'Длина названия должности не должна превышать 255 символов',
            ];
            $error = 1;
        } elseif(strlen($position) <= 0) {
            $data['position'] = [
                'value'     => '',
                'error_msg' => 'Вы забыли указать должность',
            ];
            $error = 1;
        } else { $data['position']['value'] = $position; }


        // ------


        $salary = intval($_POST['salary']); 
        $salary_str = strval($salary);

        if(strlen($_POST['salary']) == 0){
            $data['salary'] = [
                'value'     => '',
                'error_msg' => 'Вы забыли указать заработную плату',
            ];
            $error = 1;
        } elseif($salary <= 0) {
            $data['salary'] = [
                'value'     => '0',
                'error_msg' => 'Мы не приветствуем рабский труд!',
            ];
            $error = 1;
        } elseif($salary > 65535) {
            $data['salary'] = [
                'value'     => $salary,
                'error_msg' => 'Ой, не, к сожалению, наша фирма не может выплачивать столь высоку заработную плату (максимум 65535)',
            ];
            $error = 1;
        } elseif(strlen($_POST['salary']) != strlen($salary_str)) {
            // intval('3 мешка зерна') = 3
            // strlen('3 мешка зерна') != strlen(strval(3))

            $data['salary'] = [
                'value'     => $salary_str,
                'error_msg' => 'Зарплата должна выплачиваться деньгами, а не продукцией',
            ];
            $error = 1;
        } else { $data['salary']['value'] = $salary; }


        // ------


        $hiring_date = $_POST['hiring_date'];
        $formatedDate = strtotime($hiring_date);
        if(strlen($hiring_date) <= 0) {
            $data['hiring_date'] = [
                'value'     => '',
                'error_msg' => 'Забыли указать дату приёма на работу',
            ];
            $error = 1;
        } elseif($formatedDate === false){
            $data['hiring_date'] = [
                'value'     => $hiring_date,
                'error_msg' => 'Неверный формат даты',
            ];
            $error = 1;
        } else { $data['hiring_date']['value'] = $hiring_date; }


        // ------


        $boss_string = $_POST['boss_string'];
        $boss_id     = $_POST['boss_id'];
        $boss_employee_id = $_POST['boss_employee_id'];
        $boss_deep_level = $_POST['boss_deep_level'];

        if(!$boss_string){
            $data['boss'] = [
                'value_boss_string'     => '',
                'error_msg' => 'Забыли указать начальника',
            ];
            $error = 1;
        } elseif(empty($boss_string) || empty($boss_id)) {
            $data['boss'] = [
                'value_boss_string'     => '',
                'error_msg' => 'Ошибка заполнения поля начальника',
            ];
            $error = 1;
        }  else { 
            $data['boss']['value_boss_string'] = $boss_string;
            $data['boss']['value_boss_id'] = $boss_id;
            $data['boss']['value_boss_employee_id'] = $boss_employee_id;
            $data['boss']['value_boss_deep_level'] = $boss_deep_level;
        }

        if($error) throw new FormValidationException($data);
        
        return $data;
        
    }


    static function editEmployeeValidateForm() {
        $data = [];
        $error = 0;

        $employee_f = $_POST['edit_employee_f'];
        if(strlen($employee_f) > 255) {
            $data['edit_employee_f'] = [
                'value'     => $employee_f,
                'error_msg' => 'Длина фамилии не должна превышать 255 символов',
            ];
            $error = 1;
        } elseif(strlen($employee_f) <= 0) {
            $data['edit_employee_f'] = [
                'value'     => '',
                'error_msg' => 'Вы забыли указать фамилию',
            ];
            $error = 1;
        } else { $data['edit_employee_f']['value'] = $employee_f; }

        

        // ------



        $employee_i = $_POST['edit_employee_i'];
        if(strlen($employee_i) > 255) {
            $data['edit_employee_i'] = [
                'value'     => $employee_i,
                'error_msg' => 'Длина имени не должна превышать 255 символов',
            ];
            $error = 1;
        } elseif(strlen($employee_i) <= 0) {
            $data['edit_employee_i'] = [
                'value'     => '',
                'error_msg' => 'Вы забыли указать имя',
            ];
            $error = 1;
        } else { $data['edit_employee_i']['value'] = $employee_i; }


        // ------


        $employee_o = $_POST['edit_employee_o'];
        if(strlen($employee_o) > 255) {
            $data['edit_employee_o'] = [
                'value'     => $employee_o,
                'error_msg' => 'Длина отчества не должна превышать 255 символов',
            ];
            $error = 1;
        } elseif(strlen($employee_o) <= 0) {
            $data['edit_employee_o'] = [
                'value'     => '',
                'error_msg' => 'Вы забыли указать отчество',
            ];
            $error = 1;
        } else { $data['edit_employee_o']['value'] = $employee_o; }


        // ------


        $position = $_POST['edit_position'];
        if(strlen($position) > 255) {
            $data['edit_position'] = [
                'value'     => $position,
                'error_msg' => 'Длина названия должности не должна превышать 255 символов',
            ];
            $error = 1;
        } elseif(strlen($position) <= 0) {
            $data['edit_position'] = [
                'value'     => '',
                'error_msg' => 'Вы забыли указать должность',
            ];
            $error = 1;
        } else { $data['edit_position']['value'] = $position; }


        // ------


        $salary = intval($_POST['edit_salary']); 
        $salary_str = strval($salary);

        if(strlen($_POST['edit_salary']) == 0){
            $data['edit_salary'] = [
                'value'     => '',
                'error_msg' => 'Вы забыли указать заработную плату',
            ];
            $error = 1;
        } elseif($salary <= 0) {
            $data['edit_salary'] = [
                'value'     => '0',
                'error_msg' => 'Мы не приветствуем рабский труд!',
            ];
            $error = 1;
        } elseif($salary > 65535) {
            $data['edit_salary'] = [
                'value'     => $salary,
                'error_msg' => 'Ой, не, к сожалению, наша фирма не может выплачивать столь высоку заработную плату (максимум 65535)',
            ];
            $error = 1;
        } elseif(strlen($_POST['edit_salary']) != strlen($salary_str)) {
            // intval('3 мешка зерна') = 3
            // strlen('3 мешка зерна') != strlen(strval(3))

            $data['edit_salary'] = [
                'value'     => $salary_str,
                'error_msg' => 'Зарплата должна выплачиваться деньгами, а не продукцией',
            ];
            $error = 1;
        } else { $data['edit_salary']['value'] = $salary; }


        // ------


        $hiring_date = $_POST['edit_hiring_date'];
        $formatedDate = strtotime($hiring_date);
        if(strlen($hiring_date) <= 0) {
            $data['edit_hiring_date'] = [
                'value'     => '',
                'error_msg' => 'Забыли указать дату приёма на работу',
            ];
            $error = 1;
        } elseif($formatedDate === false){
            $data['edit_hiring_date'] = [
                'value'     => $hiring_date,
                'error_msg' => 'Неверный формат даты',
            ];
            $error = 1;
        } else { $data['edit_hiring_date']['value'] = $hiring_date; }


        // ------

        // если не назначать начальника ( оставить поле пустым ), то работник перемещается на место суперБосса
        $boss_string = $_POST['edit_boss_string'];
        $boss_id     = $_POST['edit_boss_id'];
        $boss_employee_id = $_POST['edit_boss_employee_id'];
        $boss_deep_level = $_POST['edit_boss_deep_level'];

        $data['edit_boss']['value_boss_string'] = $boss_string;
        $data['edit_boss']['value_boss_id'] = $boss_id;
        $data['edit_boss']['value_boss_employee_id'] = $boss_employee_id;
        $data['edit_boss']['value_boss_deep_level'] = $boss_deep_level;

        if($error) throw new FormValidationException($data);
        
        return $data;
        
    }


    static function addEmployeeValidateAvatar(){

        // если пользователь не захотел добавлять аватарку к новому работнику, то оставляем изображение по умолчанию 
        if($_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) return null;


        $error = [];
        if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) { 
            
            $info = getimagesize($_FILES['avatar']['tmp_name']);
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $types = array('jpg', 'jpeg');

            $filename = pathinfo($_FILES['avatar']['tmp_name'], PATHINFO_FILENAME) . time() . '.' . $ext;

            if ($info === FALSE) {

               $error['image'] = [
                   'error_msg' => 'Невозможно определить тип загружаемого файла изображения'
               ];


            } elseif ($info[2] !== IMAGETYPE_JPEG || !in_array($ext, $types) ) {

                $error['image'] = [
                    'error_msg' => 'Недопустимое расширение изображения. Пожалуйста, загрузите JPG(JPEG) изображение.'
                ];


            }

            self::reduceImage($_FILES['avatar']['tmp_name'], 'img/avatars/source/'. $filename, 300);
            self::reduceImage($_FILES['avatar']['tmp_name'], 'img/avatars/thumbnails/' . $filename, 50, 50);

        } else {

            $error['image'] = [
                'error_msg' => self::codeToMessage($_FILES['avatar']['error'])
            ];
        
        }

        if(!empty($error)) throw new AvatarLoadException($error);

        return $filename;

    }

    static function editEmployeeValidateAvatar(){

        // если пользователь не захотел добавлять аватарку, то оставляем изображение по умолчанию 
        if($_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) return null;

        $error = [];
        if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) { 
            
            $info = getimagesize($_FILES['avatar']['tmp_name']);
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $types = array('jpg', 'jpeg');

            $filename = pathinfo($_FILES['avatar']['tmp_name'], PATHINFO_FILENAME) . time() . '.' . $ext;

            if ($info === FALSE) {

               $error['edit_image'] = [
                   'error_msg' => 'Невозможно определить тип загружаемого файла изображения'
               ];


            } elseif ($info[2] !== IMAGETYPE_JPEG || !in_array($ext, $types) ) {

                $error['edit_image'] = [
                    'error_msg' => 'Недопустимое расширение изображения. Пожалуйста, загрузите JPG(JPEG) изображение.'
                ];


            }

            self::reduceImage($_FILES['avatar']['tmp_name'], 'img/avatars/source/'. $filename, 300);
            self::reduceImage($_FILES['avatar']['tmp_name'], 'img/avatars/thumbnails/' . $filename, 50, 50);

        } else {

            $error['edit_image'] = [
                'error_msg' => self::codeToMessage($_FILES['avatar']['error'])
            ];
        
        }

        if(!empty($error)) throw new AvatarLoadException($error);


        return $filename;

    }

    static function codeToMessage($code) 
    { 
        switch ($code) { 
            case UPLOAD_ERR_INI_SIZE: 
                // $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini"; 
                $message = "Слишком большой размер файла. Уложитесь пожалуйста в 1 Мб."; 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                // $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                $message = "Слишком большой размер файла. Уложитесь пожалуйста в 1 Мб.";
                break; 
            case UPLOAD_ERR_PARTIAL: 
                // $message = "The uploaded file was only partially uploaded"; 
                $message = "Картинка не загрузилась полностью"; 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                // $message = "No file was uploaded"; 
                $message = "Забыли добавить изображение"; 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                // $message = "Missing a temporary folder"; 
                $message = "Ошибка сервера"; 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                // $message = "Failed to write file to disk";
                $message = "Ошибка сервера";  
                break; 
            case UPLOAD_ERR_EXTENSION: 
                // $message = "File upload stopped by extension"; 
                $message = "Ошибка сервера"; 
                break; 

            default: 
                // $message = "Unknown upload error";
                $message = "Неизвестная ошибка. Интересно...";  
                break; 
        } 
        return $message; 
    }

    static function reduceImage($from, $to, $px, $quality = 100){
        $image = imagecreatefromjpeg($from);

        list($width_orig, $height_orig) = getimagesize($from);

        $ratio_orig = $width_orig/$height_orig;

        // задание максимальной ширины и высоты
        $width  = $px;
        $height = $px;

        if ($width/$height > $ratio_orig) {
           $width = $height*$ratio_orig;
        } else {
           $height = $width/$ratio_orig;
        }

        $width = round($width);
        $height = round($height);

        // ресэмплирование
        $image_p = imagecreatetruecolor($width, $height);

        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

        // вывод
        imagejpeg($image_p, $to, $quality);

        imagedestroy($image); 
        imagedestroy($image_p);
    }


    static function addEmployeeDb($avatarName){

        $employee_id = DB::table('employees')->max('employee_id') + 1;        
        $insertData = [
            'deep_level'    => $_POST['boss_deep_level'] + 1,
            'employee_id'   => $employee_id,
            'boss_id'       => $_POST['boss_employee_id'],
            'f'             => trim($_POST['employee_f']),
            'i'             => trim($_POST['employee_i']),
            'o'             => trim($_POST['employee_o']),
            'position'      => trim($_POST['position']),
            'salary'        => $_POST['salary'],
            'hiring_date'   => strtotime($_POST['hiring_date']),
            'subordinates'  => json_encode([]),
            // 'created_at'    => date('Y-m-d H:i:s'),
            // 'updated_at'    => date('Y-m-d H:i:s'),
        ];


        if($avatarName) $insertData['img'] = $avatarName;


        DB::table('employees')->insert($insertData);

        $subs = DB::table('employees')
            ->where('id', $_POST['boss_id'])
            ->first(['subordinates']);

        $subs = json_decode($subs->subordinates);
        $subs[] = $employee_id;

        DB::table('employees')
            ->where('id', $_POST['boss_id'])
            ->update([
                'subordinates' => json_encode($subs)
            ]);

    }

    static function shift_employees($current_employee, $new_boss){
        
        // если босса перемещаем на уровень ниже. Т.е. ставив самого себе в подчинение
        if($current_employee->boss_id == $current_employee->employee_id && empty($_POST['edit_boss_string'])) {
            // определяем нового босса всех боссов: --
            $candidates = DB::table('employees')
                ->where('deep_level', 1)
                ->get();

            
            $candidates = $candidates->toArray();
            shuffle($candidates);
            $new_super_boss = reset($candidates);
            // перемещаем его на вершину иерархии
            DB::table('employees')
                ->where('employee_id', $new_super_boss->employee_id)
                ->update([
                    'deep_level' => 0,
                    'boss_id' => $new_super_boss->employee_id
                ]);
            // --

            // текущего босса перемещаем на 1ый уровень
            DB::table('employees')
                ->where('employee_id', $current_employee->employee_id)
                ->update([
                    'deep_level' => 1,
                    'subordinates' => json_encode([])
                ]);
            // --

            // находим соседа
            $candidates = DB::table('employees')
                ->where('deep_level', 1)
                ->get();

            
            $candidates = $candidates->toArray();
            shuffle($candidates);
            $neighbor = reset($candidates);

            // соседу добавляем подчинённых нового суперБосса
            $subs = array_merge(json_decode($neighbor->subordinates), json_decode($new_super_boss->subordinates));
            DB::table('employees')
                ->where('employee_id', $neighbor->employee_id)
                ->update([
                    'subordinates' => json_encode($subs)
                ]);
            // --

            // получаем всех 1го уровня
            $share_employees = DB::table('employees')
                ->where('deep_level', 1)
                ->get(['employee_id']);

            $first_level_employees = [];
            foreach ($share_employees as $item) {
                $first_level_employees[] = $item->employee_id;
            }
            // --

            // устанавливаем им в качестве босса - нового суперБосса
            DB::table('employees')
                ->where('deep_level', 1)
                ->update([
                    'boss_id' => $new_super_boss->employee_id
                ]);
            // --

            // устанавливаем новому боссу в качестве подчинённых всех работников 1го уровня

            DB::table('employees')
                ->where('employee_id', $new_super_boss->employee_id)
                ->update([
                    'subordinates' => json_encode($first_level_employees)
                ]);
            // --
        }
        // перемещаем босса всех боссов: <----------
        // если boss_id == employee_id - т.е. работник сам себе босс - значит, это самый главный начальник.
        // случайным образом, выбираем подчинённого из следующего уровня иерархии.
        // делаем его новым главным боссом.
        // подчинённых нового босса(всю ветку) передаём случайному работнику того же уровня, в котором находился новый босс.
        elseif($current_employee->boss_id == $current_employee->employee_id) {

            // перемещаем старого босса --
            DB::table('employees')
                ->where('employee_id', $current_employee->employee_id)
                ->update([
                    'boss_id' => $new_boss->employee_id,
                    'deep_level' => $new_boss->deep_level + 1,
                    'subordinates' => json_encode([])
                ]);

            // задаём его в качестве подчинённого
            $subs = DB::table('employees')
                ->where('employee_id', $new_boss->employee_id)
                ->first(['subordinates']);
            $subs = json_decode($subs->subordinates);
            $subs[] = $current_employee->employee_id;

            DB::table('employees')
                ->where('employee_id', $new_boss->employee_id)
                ->update([
                    'subordinates' => json_encode($subs)
                ]);
            // --

            // определяем нового босса всех боссов: --
            $candidates = DB::table('employees')
                ->where('deep_level', 1)
                ->get();

            
            $candidates = $candidates->toArray();
            shuffle($candidates);
            $new_super_boss = reset($candidates);

            // перемещаем его на вершину иерархии
            DB::table('employees')
                ->where('employee_id', $new_super_boss->employee_id)
                ->update([
                    'deep_level' => 0,
                    'boss_id' => $new_super_boss->employee_id
                ]);
            // --

            // находим соседа
            $neighbor = next($candidates);

            // соседу добавляем подчинённых нового суперБосса
            $subs = array_merge(json_decode($neighbor->subordinates), json_decode($new_super_boss->subordinates));
            DB::table('employees')
                ->where('employee_id', $neighbor->employee_id)
                ->update([
                    'subordinates' => json_encode($subs)
                ]);
            // --

            // получаем всех 1го уровня
            $share_employees = DB::table('employees')
                ->where('deep_level', 1)
                ->get(['employee_id']);

            $first_level_employees = [];
            foreach ($share_employees as $item) {
                $first_level_employees[] = $item->employee_id;
            }
            // --

            // устанавливаем им в качестве босса - нового суперБосса
            DB::table('employees')
                ->where('deep_level', 1)
                ->update([
                    'boss_id' => $new_super_boss->employee_id
                ]);
            // --

            // устанавливаем новому боссу в качестве подчинённых всех работников 1го уровня

            DB::table('employees')
                ->where('employee_id', $new_super_boss->employee_id)
                ->update([
                    'subordinates' => json_encode($first_level_employees)
                ]);
            // --

              


        // перемещаем работника на место супербосса
        } elseif(empty($_POST['edit_boss_string'])) {

            // получаем текущего суперБосса
            $superBoss = DB::table('employees')->where('deep_level', 0)->first();
            // --


            // получаем подчинённых суперБосса
            $superBoss_subs = json_decode($superBoss->subordinates);
            // --

            // очищаем список подчинённых и переставляем на следующий уровень и устанавливаем в качестве его босса - нового босса
            DB::table('employees')
                ->where('employee_id', $superBoss->employee_id)
                ->update([
                    'subordinates' => json_encode([]),
                    'deep_level' => 1,
                    'boss_id' => $current_employee->employee_id
                ]);

            // --



            // получаем подчинённых текущего работника
            $current_employee_subs = json_decode($current_employee->subordinates);

            // если они есть, то записываем их случайному соседу того же уровня 
            if($current_employee_subs){

                $candidates = DB::table('employees')
                    ->where('deep_level', $current_employee->deep_level)
                    ->where('id', '<>', $current_employee->id)
                    ->get();

                // определяем случайного соседа:
                $candidates = $candidates->toArray();

                shuffle($candidates);
                $delegate_boss = reset($candidates);
                // --

                // записываем подчинённых текущего работника соседу
                $delegate_subs = json_decode($delegate_boss->subordinates);
                // $delegate_subs += $current_employee_subs;
                $delegate_subs = array_merge($delegate_subs, $current_employee_subs);

                DB::table('employees')
                    ->where('employee_id', $delegate_boss->employee_id)
                    ->update([
                        'subordinates' => json_encode($delegate_subs)
                    ]);
                // --

                // устанавливаем соседа, в качестве босса для своих , уже бывших подчинённых
                DB::table('employees')
                    ->where('boss_id', $current_employee->employee_id)
                    ->update([
                        'boss_id' => $delegate_boss->employee_id,
                    ]);
                    
                // --

                // у текущего работника очищаем подчинённых
                DB::table('employees')
                    ->where('employee_id', $current_employee->employee_id)
                    ->update([
                        'subordinates' => json_encode([])
                    ]);
                // --

            }
            

            // назначаем подчинённых старого босса Новому + в массив добавить идентификатор Старого босса
            $superBoss_subs[] = $superBoss->employee_id;

            DB::table('employees')
                ->where('employee_id', $current_employee->employee_id)
                ->update([
                    'subordinates' => json_encode($superBoss_subs),
                    'deep_level' => 0,
                    'boss_id' => $current_employee->employee_id
                ]);

            // --

            // привязываем подчинённых старого босса новому
            DB::table('employees')
                ->where('boss_id', $superBoss->employee_id)
                ->update([
                    'boss_id' => $current_employee->employee_id,
                ]);
                
            // --


        // перемещение работника не затрагивая суперБосса
        } else {

            // получаем подчинённых текущего работника
            $current_employee_subs = json_decode($current_employee->subordinates);

            // если они есть, то записываем их случайному соседу того же уровня
            if($current_employee_subs){

                $candidates = DB::table('employees')
                    ->where('deep_level', $current_employee->deep_level)
                    ->where('id', '<>', $current_employee->id)
                    ->get();

                // определяем случайного соседа:
                $candidates = $candidates->toArray();
                shuffle($candidates);
                $delegate_boss = reset($candidates);
                // --

                // записываем подчинённых текущего работника соседу
                $delegate_subs = json_decode($delegate_boss->subordinates);
                // $delegate_subs += $current_employee_subs;
                $delegate_subs = array_merge($delegate_subs, $current_employee_subs);

                DB::table('employees')
                    ->where('employee_id', $delegate_boss->employee_id)
                    ->update([
                        'subordinates' => json_encode($delegate_subs)
                    ]);
                // --

                // устанавливаем соседа, в качестве босса для своих , уже бывших подчинённых
                DB::table('employees')
                    ->where('boss_id', $current_employee->employee_id)
                    ->update([
                        'boss_id' => $delegate_boss->employee_id,
                    ]);
                    
                // --

                // у текущего работника очищаем подчинённых
                DB::table('employees')
                    ->where('employee_id', $current_employee->employee_id)
                    ->update([
                        'subordinates' => json_encode([])
                    ]);
                // --
            }

            // из подчинённых, босса текущего работника, удаляем id текущего работника
            $old_boss_subs = DB::table('employees')
                ->where('employee_id', $current_employee->boss_id)
                ->first(['subordinates']);                

            $old_boss_subs = json_decode($old_boss_subs->subordinates);
            $key = array_search($current_employee->employee_id, $old_boss_subs);
            unset($old_boss_subs[$key]);
            $old_boss_subs = array_values($old_boss_subs);

            DB::table('employees')
                ->where('employee_id', $current_employee->boss_id)
                ->update([
                    'subordinates' => json_encode($old_boss_subs)
                ]);
            // --


            // задаём текущему работнику идентификатор нового Босса
            DB::table('employees')
                ->where('employee_id', $current_employee->employee_id)
                ->update([
                    'boss_id' => $new_boss->employee_id,
                    'deep_level' => $new_boss->deep_level + 1
                ]);
            // --

            // новому боссу в подчинённые добавляем текущего работника
            $new_boss_subs = DB::table('employees')
                ->where('employee_id', $new_boss->employee_id)
                ->first(['subordinates']);

            $new_boss_subs = json_decode($new_boss_subs->subordinates);
            $new_boss_subs[] = $current_employee->employee_id;

            DB::table('employees')
                ->where('employee_id', $new_boss->employee_id)
                ->update([
                    'subordinates' => json_encode($new_boss_subs)
                ]);
            // --

        }
        
    }

    static function editEmployeeDb($avatarName){

        DB::transaction(function () use ($avatarName) {

            $data = [
                'f' => trim($_POST['edit_employee_f']),
                'i' => trim($_POST['edit_employee_i']),
                'o' => trim($_POST['edit_employee_o']),
                'position' => trim($_POST['edit_position']),
                'salary' => trim($_POST['edit_salary']),
                'hiring_date' => strtotime($_POST['edit_hiring_date']),
            ];

            if ($avatarName) $data['img'] = trim($avatarName);

            DB::table('employees')
                ->where('employee_id', $_POST['employee_id'])
                ->limit(1)
                ->update($data);

            

            // Отдельными запросами будем менять позицию работника в иерархии, если это требуется
            $current_employee = DB::table('employees')->where('employee_id', $_POST['employee_id'])->first();

            if(strval($current_employee->boss_id) === $_POST['edit_boss_employee_id']) return null; // начальник остался прежним

            // получаем данные нового босса
            $new_boss = null;
            if(!empty($_POST['edit_boss_employee_id'])) {
                $new_boss = DB::table('employees')->where('employee_id', $_POST['edit_boss_employee_id'])->first();
            }
            // --

            self::shift_employees($current_employee, $new_boss);

        });

    }

    static function getDataForEditForm($item_id){
        $employee = DB::table('employees as em1')
            ->join('employees as em2', 'em1.boss_id', '=', 'em2.employee_id')
            ->select(
                'em1.employee_id',
                'em1.f',
                'em1.i',
                'em1.o',
                'em1.position',
                'em1.salary',
                'em1.hiring_date',
                'em1.img',
                
                'em2.id as boss_id',
                'em2.f as boss_f',
                'em2.i as boss_i',
                'em2.o as boss_o',
                'em2.position as boss_position',
                'em2.employee_id as boss_employee_id',
                'em2.deep_level as boss_deep_level'
            )
            ->where('em1.id', '=', $item_id)
            ->first()
        ;

        if(!empty($employee->img)) $employee->img = '/img/avatars/source/' . $employee->img;
        else $employee->img = '/img/avatars/source/noAvatar.png';
        if(!empty($employee->hiring_date)) $employee->hiring_date = date('d-m-Y', $employee->hiring_date);

        return $employee;
    }

    static function remove_avatar($employee_id) {
        DB::table('employees')
            ->where('employee_id', $employee_id)
            ->limit(1)
            ->update(['img' => '']);

        return DB::table('employees')
            ->where('employee_id', $employee_id)
            ->first(['id'])->id;
    }

    static function fired($employee_id){
        $employee = DB::table('employees')
            ->where('employee_id', $employee_id)
            ->first();

        // удаляем работника
        DB::table('employees')->where('employee_id', $employee_id)->delete();
        // --

        // если удаляем суперБосса
        if($employee->deep_level == 0) {

            // определяем нового босса всех боссов: --
            $candidates = DB::table('employees')
                ->where('deep_level', 1)
                ->get();

            
            $candidates = $candidates->toArray();
            shuffle($candidates);
            $new_super_boss = reset($candidates);
            // перемещаем его на вершину иерархии
            DB::table('employees')
                ->where('employee_id', $new_super_boss->employee_id)
                ->update([
                    'deep_level' => 0,
                    'boss_id' => $new_super_boss->employee_id
                ]);
            // --

            
            // находим соседа
            $candidates = DB::table('employees')
                ->where('deep_level', 1)
                ->get();

            
            $candidates = $candidates->toArray();
            shuffle($candidates);
            $neighbor = reset($candidates);

            // соседу добавляем подчинённых нового суперБосса
            $subs = array_merge(json_decode($neighbor->subordinates), json_decode($new_super_boss->subordinates));
            DB::table('employees')
                ->where('employee_id', $neighbor->employee_id)
                ->update([
                    'subordinates' => json_encode($subs)
                ]);
            // --

            // получаем всех 1го уровня
            $share_employees = DB::table('employees')
                ->where('deep_level', 1)
                ->get(['employee_id']);

            $first_level_employees = [];
            foreach ($share_employees as $item) {
                $first_level_employees[] = $item->employee_id;
            }
            // --

            // устанавливаем им в качестве босса - нового суперБосса
            DB::table('employees')
                ->where('deep_level', 1)
                ->update([
                    'boss_id' => $new_super_boss->employee_id
                ]);
            // --

            // устанавливаем новому боссу в качестве подчинённых всех работников 1го уровня

            DB::table('employees')
                ->where('employee_id', $new_super_boss->employee_id)
                ->update([
                    'subordinates' => json_encode($first_level_employees)
                ]);
            // --
        } else {

            // находим соседа
            $candidates = DB::table('employees')
                ->where('deep_level', $employee->deep_level)
                ->get();

            
            $candidates = $candidates->toArray();
            shuffle($candidates);
            $neighbor = reset($candidates);

            if(json_decode($employee->subordinates)) {
                // соседу добавляем подчинённых нового суперБосса
                $subs = array_merge(json_decode($neighbor->subordinates), json_decode($employee->subordinates));
                DB::table('employees')
                    ->where('employee_id', $neighbor->employee_id)
                    ->update([
                        'subordinates' => json_encode($subs)
                    ]);
                // --

                // зададим подчинённым нового локального босса
                DB::table('employees')
                    ->where('boss_id', $employee->employee_id)
                    ->update([
                        'boss_id' => $neighbor->employee_id
                    ]);
                // --
            }

            // изменяем список подчинённых у начальника удалённого работника
            $boss_subs = DB::table('employees')
                ->where('employee_id', $employee->boss_id)
                ->first(['subordinates']);                

            $boss_subs = json_decode($boss_subs->subordinates);
            $key = array_search($employee->employee_id, $boss_subs);
            unset($boss_subs[$key]);
            $boss_subs = array_values($boss_subs);

            DB::table('employees')
                ->where('employee_id', $employee->boss_id)
                ->update([
                    'subordinates' => json_encode($boss_subs)
                ]);
            // --

        }
    }

    static function getChildId($child_employee_id) {
        $child_info = DB::table('employees')->where('employee_id', $child_employee_id)->first();
        $childs = json_decode($child_info->subordinates);
        $data = [];

        if($childs) {
            // $data[$child_info->id] = [];
            foreach ($childs as $child_employee_id) {
                $data[$child_info->id][] = self::getChildId($child_employee_id);
            }

            return $data;

        } else {

            return $child_info->id;

        }
    }

    static function changeBoss($id, $boss_id){

        DB::beginTransaction();

        $current_employee = DB::table('employees')->where('id', $id)->first();
        $new_boss = DB::table('employees')->where('id', $boss_id)->first();

        if($current_employee->boss_id == $new_boss->employee_id) return;

        $response = [
            'new_deep_level' => $new_boss->deep_level + 1,
            'neighbor_id' => null, // ID соседа, которому передаются все подчинённые
            'subs_list' => [],  // список ID которые нужно переставить
            'new_boss_id' => $new_boss->id, // ID босса к которому добавляется новый сотрудник,
            'new_super_boss_id' => null, // ID нового суперБосса, если такой появляется
        ];

        // перемещаем босса всех боссов
        if($current_employee->boss_id == $current_employee->employee_id) {

            // перемещаем старого босса --
            DB::table('employees')
                ->where('employee_id', $current_employee->employee_id)
                ->update([
                    'boss_id' => $new_boss->employee_id,
                    'deep_level' => $new_boss->deep_level + 1,
                    'subordinates' => json_encode([])
                ]);

            // задаём его в качестве подчинённого
            $subs = DB::table('employees')
                ->where('employee_id', $new_boss->employee_id)
                ->first(['subordinates']);
            $subs = json_decode($subs->subordinates);
            $subs[] = $current_employee->employee_id;

            DB::table('employees')
                ->where('employee_id', $new_boss->employee_id)
                ->update([
                    'subordinates' => json_encode($subs)
                ]);
            // --

            // определяем нового босса всех боссов: --
            $candidates = DB::table('employees')
                ->where('deep_level', 1)
                ->get();

            
            $candidates = $candidates->toArray();
            shuffle($candidates);
            $new_super_boss = reset($candidates);


            $response['new_super_boss_id'] = $new_super_boss->id;


            // перемещаем его на вершину иерархии
            DB::table('employees')
                ->where('employee_id', $new_super_boss->employee_id)
                ->update([
                    'deep_level' => 0,
                    'boss_id' => $new_super_boss->employee_id
                ]);
            // --

            // находим соседа
            $neighbor = next($candidates);
            if($neighbor === false) {
                DB::rollBack();
                return; // отменяет перемещение, т.к. некем заменить суперБосса
            } 



            $subs_id = [];
            $subs_info = DB::table('employees')
                ->whereIn('employee_id', json_decode($new_super_boss->subordinates))
                ->get(['id', 'subordinates']);
            if($subs_info) {

                foreach ($subs_info as $sub) {
                    
                    $childs = json_decode($sub->subordinates);
                    if($childs) {
                        $subs_id[$sub->id] = [];
                        foreach ($childs as $child_employee_id) {
                            $subs_id[$sub->id][] = self::getChildId($child_employee_id);
                        }
                        
                    } else {
                        $subs_id[$sub->id] = $sub->id;
                    }
                }

                $response['subs_list'] = $subs_id;
            }

            $response['neighbor_id'] = $neighbor->id;

            



            // соседу добавляем подчинённых нового суперБосса
            $subs = array_merge(json_decode($neighbor->subordinates), json_decode($new_super_boss->subordinates));
            DB::table('employees')
                ->where('employee_id', $neighbor->employee_id)
                ->update([
                    'subordinates' => json_encode($subs)
                ]);
            // --

            // получаем всех 1го уровня
            $share_employees = DB::table('employees')
                ->where('deep_level', 1)
                ->get(['employee_id']);

            $first_level_employees = [];
            foreach ($share_employees as $item) {
                $first_level_employees[] = $item->employee_id;
            }
            // --

            // устанавливаем им в качестве босса - нового суперБосса
            DB::table('employees')
                ->where('deep_level', 1)
                ->update([
                    'boss_id' => $new_super_boss->employee_id
                ]);
            // --

            // устанавливаем новому боссу в качестве подчинённых всех работников 1го уровня

            DB::table('employees')
                ->where('employee_id', $new_super_boss->employee_id)
                ->update([
                    'subordinates' => json_encode($first_level_employees)
                ]);
            // --


        // перемещение работника не затрагивая суперБосса
        } else {

            // получаем подчинённых текущего работника
            $current_employee_subs = json_decode($current_employee->subordinates);

            // если они есть, то записываем их случайному соседу того же уровня
            if($current_employee_subs){

                $candidates = DB::table('employees')
                    ->where('deep_level', $current_employee->deep_level)
                    ->where('id', '<>', $current_employee->id)
                    ->get();

                // определяем случайного соседа:
                $candidates = $candidates->toArray();
                shuffle($candidates);
                $delegate_boss = reset($candidates);
                // --

                // записываем подчинённых текущего работника соседу
                $delegate_subs = json_decode($delegate_boss->subordinates);

                $subs_id = [];
                $subs_info = DB::table('employees')
                    ->whereIn('employee_id', json_decode($current_employee->subordinates))
                    ->get(['id', 'subordinates']);
                if($subs_info) {

                    foreach ($subs_info as $sub) {
                        
                        $childs = json_decode($sub->subordinates);
                        if($childs) {
                            $subs_id[$sub->id] = [];
                            foreach ($childs as $child_employee_id) {
                                $subs_id[$sub->id][] = self::getChildId($child_employee_id);
                            }
                            
                        } else {
                            $subs_id[$sub->id] = $sub->id;
                        }
                    }

                    $response['subs_list'] = $subs_id;
                }

                $response['neighbor_id'] = $delegate_boss->id;


                // $delegate_subs += $current_employee_subs;
                $delegate_subs = array_merge($delegate_subs, $current_employee_subs);

                DB::table('employees')
                    ->where('employee_id', $delegate_boss->employee_id)
                    ->update([
                        'subordinates' => json_encode($delegate_subs)
                    ]);
                // --

                // устанавливаем соседа, в качестве босса для своих , уже бывших подчинённых
                DB::table('employees')
                    ->where('boss_id', $current_employee->employee_id)
                    ->update([
                        'boss_id' => $delegate_boss->employee_id,
                    ]);
                    
                // --

                // у текущего работника очищаем подчинённых
                DB::table('employees')
                    ->where('employee_id', $current_employee->employee_id)
                    ->update([
                        'subordinates' => json_encode([])
                    ]);
                // --
            }

            // из подчинённых, босса текущего работника, удаляем id текущего работника
            $old_boss_subs = DB::table('employees')
                ->where('employee_id', $current_employee->boss_id)
                ->first(['subordinates']);                

            $old_boss_subs = json_decode($old_boss_subs->subordinates);
            $key = array_search($current_employee->employee_id, $old_boss_subs);
            unset($old_boss_subs[$key]);
            $old_boss_subs = array_values($old_boss_subs);

            DB::table('employees')
                ->where('employee_id', $current_employee->boss_id)
                ->update([
                    'subordinates' => json_encode($old_boss_subs)
                ]);
            // --


            // задаём текущему работнику идентификатор нового Босса
            DB::table('employees')
                ->where('employee_id', $current_employee->employee_id)
                ->update([
                    'boss_id' => $new_boss->employee_id,
                    'deep_level' => $new_boss->deep_level + 1
                ]);
            // --

            // новому боссу в подчинённые добавляем текущего работника
            $new_boss_subs = DB::table('employees')
                ->where('employee_id', $new_boss->employee_id)
                ->first(['subordinates']);

            $new_boss_subs = json_decode($new_boss_subs->subordinates);
            $new_boss_subs[] = $current_employee->employee_id;

            DB::table('employees')
                ->where('employee_id', $new_boss->employee_id)
                ->update([
                    'subordinates' => json_encode($new_boss_subs)
                ]);
            // --
            
        }

        DB::commit();
        
        return $response;
    }



    static function getTree($parent_id, $sortField, $sortType) {

        $parent = DB::table('employees')
            ->where('id', $parent_id)
            ->first();

        $subs = json_decode($parent->subordinates);
        if(!$subs) return null;




        self::$employees = self::getChilds($subs, $sortField, $sortType);

        self::sortEmployees();

        return self::$sortedEmployees;

    }

    static function getChilds($subs, $sortField, $sortType) {

        $items = DB::table('employees')
            ->whereIn('employee_id', $subs)
            ->orderBy($sortField, $sortType)
            ->get();

        $data = [];
        foreach($items as $item) {
            $data[] = $item;
            $subs = json_decode($item->subordinates);
            if($subs) {
                $data = array_merge($data, self::getChilds($subs, $sortField, $sortType));
            }
        }

        return $data;

    }

    
}

<?
namespace App\Http\Controllers;

use App\Models\Index;
use App\Models\Exceptions\FormValidationException;
use App\Models\Exceptions\AvatarLoadException;

class IndexController extends Controller
{
    /**
     * Отображает всех работников(ФИО, Должность).
     *
     * @return Response
     */
    public function index()
    {
        return view('index', ['employees' => Index::getAllEmployeesForIndexPage()]);
    }

    /**
     * Отображает всех работников(employee_id, ФИО, Должность, Зарплату, Дата приёма на работу).
     *
     * @return Response
     */
    public function indexAdmin()
    {
        return view('indexAdmin', [
        	'employees' => Index::getAllEmployees(),
        	'menu' => $this->getMenu(),
        ]);
    }

    public function sort($column, $type) {
    	return view('indexAdmin', [
        	'employees' => Index::sortEmployeesByField($column, $type),
        	'menu' => $this->getMenu(),
        ]);
    }

    public function sortAJAX($column, $type) {
        $data = [
            'employees' => Index::sortEmployeesByField($column, $type),
            'setType' => $type == 'asc' ? 'desc' : 'asc',
        ];

        echo json_encode($data, JSON_FORCE_OBJECT);
        exit;
    }

    public function resetFiltersAJAX() {
        $data = [
            'employees' => Index::sortEmployeesByField('employee_id', 'asc'),
        ];

        echo json_encode($data, JSON_FORCE_OBJECT);
        exit;
    }

    public function search($searchString) {
        return view('indexAdmin', [
            'employees' => Index::searchEmployees($searchString),
            'menu' => $this->getMenu(),
        ]);
    }

    public function searchAJAX($searchString) {
        $data = [
            'employees' => Index::searchEmployees($searchString, $_POST['field'], $_POST['type']),
        ];

        echo json_encode($data, JSON_FORCE_OBJECT);
        exit;
    }

    public function searchBossesAJAX() {
        $searchString = $_POST['s'];
        $data = [
            'bosses' => Index::searchBosses($searchString),
        ];

        echo json_encode($data, JSON_FORCE_OBJECT);
        exit;
    }

    public function addEmployee() {
        $error = [];

        try {

            // Валидация формы
            $formData = Index::addEmployeeValidateForm();

            // Загрузка аватарки
            $avatarName = Index::addEmployeeValidateAvatar();

            // Добавление нового работника
            Index::addEmployeeDb($avatarName);

        } catch (FormValidationException $e) {
            $error = $e->getError();
        } catch (AvatarLoadException $e) {
            $error = $e->getError() + $formData;
        } catch (\Exception $e) {
            $error['db'] = [
                'error_msg' => 'Ошибка записи в базу данных. Попробуйте еще раз.'
            ];
            $error += $formData;

            @unlink('img/avatars/source/' . $avatarName);
            @unlink('img/avatars/thumbnails/' . $avatarName);
        } 


        
        // если ошибок нет, редиректит на главную
        if(empty($error)) {
            header("Location: /secret/adminPage");
            exit;
        }

        return view('indexAdmin', [
            'employees' => Index::getAllEmployees(),
            'menu' => $this->getMenu(),
            'error' => $error
        ]);
    }

    public function updateEmployee(){
        $error = [];


        $oldAvatar = $_POST['old_avatar'];

        try {

            // Валидация формы
            $formData = Index::editEmployeeValidateForm();

            // Загрузка аватарки
            $avatarName = Index::editEmployeeValidateAvatar();

            // Изменение данных работника
            Index::editEmployeeDb($avatarName);

        } catch (FormValidationException $e) {
            $error = $e->getError();
            $error['old_avatar'] = [
                'src' => $oldAvatar
            ];
            $error['edit_employee'] = ['id' => $_POST['employee_id']];

        } catch (AvatarLoadException $e) {
            $error = $e->getError();
            $error['old_avatar'] = [
                'src' => $oldAvatar
            ];
            $error['edit_employee'] = ['id' => $_POST['employee_id']];
            $error += $formData;

        } catch (\Exception $e) {
            $error['db'] = [
                'error_msg' => 'Ошибка изменения записи в базе данных. Попробуйте еще раз.'
            ];
            $error['old_avatar'] = [
                'src' => $oldAvatar
            ];
            $error['edit_employee'] = ['id' => $_POST['employee_id']];
            $error += $formData;

            @unlink('img/avatars/source/' . $avatarName);
            @unlink('img/avatars/thumbnails/' . $avatarName);
        } 

        
        // если ошибок нет, редиректит на главную
        if(empty($error)) {

            // удаляет старый аватар из ФС
            @unlink('img/avatars/source/'     . pathinfo($_POST['old_avatar'], PATHINFO_FILENAME));
            @unlink('img/avatars/thumbnails/' . pathinfo($_POST['old_avatar'], PATHINFO_FILENAME));


            header("Location: /secret/adminPage");
            exit;
        }

        return view('indexAdmin', [
            'employees' => Index::getAllEmployees(),
            'menu' => $this->getMenu(),
            'error' => $error
        ]);
    }

    public function changeBossAJAX(){

        $id = intval($_POST['id']);
        $boss_id = intval($_POST['boss_id']);
        $data = Index::changeBoss($id, $boss_id);

        echo json_encode($data);
        exit;
    }

    public function remove_avatar() {
        $employee_id = intval($_POST['employee_id']);
        $id = Index::remove_avatar($employee_id);
        echo $id;
        exit;
    }

    public function fired($employee_id){
        Index::fired($employee_id);
        header("Location: /secret/adminPage");
        exit;
    }

    public function getTreeAJAX(){

        $id = intval($_POST['id']);
        $sortField = $_POST['field'];
        $sortType = $_POST['type'];

        $data = [
            'employees' => Index::getTree($id, $sortField, $sortType),
        ];

        echo json_encode($data);
        exit;
    }

    

    public function getMenu() {
    	$menu = [
    		['url' => 'sort/employee_id/', 			'text' => '№, '],
    		['url' => 'sort/f/', 			'text' => 'Фамилия'],
    		['url' => 'sort/i/', 			'text' => 'Имя'],
    		['url' => 'sort/o/', 			'text' => 'Отчество, '],
    		['url' => 'sort/position/', 	'text' => 'Должность, '],
    		['url' => 'sort/salary/', 		'text' => 'Зарплата (грн.), '],
    		['url' => 'sort/hiring_date/', 	'text' => 'Дата приёма на работу'],
    	];

    	$menuString = '';
    	foreach($menu as $info) {
    		$href = "/secret/adminPage/".$info['url'];
    		$find = strpos($_SERVER["REQUEST_URI"], $info['url']);
    		if($find !== false) {
    			$sortType = explode('/', $_SERVER["REQUEST_URI"])[5];
    			if($sortType == 'asc'){
    				$href .= 'desc';
    				$caretClass = 'caret caret_up';
    			} else {
    				$href .= 'asc';
    				$caretClass = 'caret';
    			}

    			$menuString .= "<a href='".$href."' class='headerLink'>";
    			$menuString .= "<span class='".$caretClass."'></span> ";
    		} else {
    			$href .= 'asc';
    			$menuString .= "<a href='".$href."' class='headerLink'>";
    		}
    		$menuString .= $info['text'] .'</a>';
    	}

    	return $menuString;
    }

    public function getDataForEditForm(){
        $item_id = $_POST['item_id'];

        $data = [
            'item_info' => Index::getDataForEditForm($item_id),
        ];

        echo json_encode($data, JSON_FORCE_OBJECT);
        exit;
    }
}
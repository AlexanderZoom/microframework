<?php
class Ctrl_Main extends Lib_Ctrl_Web {
            
    public function init(){}
    
    protected function _checkParam($params){
        
        
            
        $action = empty($params[0]) ? 'index' : $params[0];
        $value = empty($params[1]) ? null : $params[1];
        $pCount = 0;
        
        switch ($action){
            case 'add':
                $pCount =1;
                $action = 'form';
                break;
                
            case 'edit':
                if (!preg_match('/^\d+$/', $value)) return false;
                $action = 'form';
                
            case 'delete':
                if (!preg_match('/^\d+$/', $value)) return false;
                $pCount =2;
                break;
                
            case 'save':
                $pCount =1;
                break;
                
            case 'index':
                $pCount =0;
                break;
                
            default:
                if (count($params) == 1 && preg_match('/^index(\d+)$/', $action, $match)){
                    $this->params = $params = array('index', $match[1]);
                    $action = $params[0];
                    $pCount =2;
                }
                elseif (count($params) == 1 && preg_match('/^item(\d+)$/', $action, $match)){
                    $this->params = $params = array('item', $match[1]);;
                    $action = $params[0];
                    $pCount =2;
                }
                else  return false;
        }
        
        if ($pCount != count($params)) return false;

        $this->setAction($action);
        
        return true;
    }
    
    /**
     * list news
     */
    public function index(){
        $params = $this->getParams();
        $dm = Lib_DataMapper::getInstance();
        
        $this->news_on_page = 2;
        $this->news_count = $dm->getNewsCount();
        $this->news_page = empty($params[1]) ? 1 : $params[1];
        
        $chkPages = ceil($this->news_count / $this->news_on_page);
        if (!$chkPages) $chkPages =1;
         
        if ($this->news_page > $chkPages){
            $this->getDispatcher()->forward(Lib_Config::getVar('app_ctrl_error'), null, 404);
        }
        
        $this->news = $dm->getNews(0, $this->news_on_page, ($this->news_page - 1) * $this->news_on_page);
        
    }
    
    /**
     * show news item
     */
    public function item(){
        $params = $this->getParams();
        $dm = Lib_DataMapper::getInstance();
        $model = $dm->getNews($params[1]);
        if (!$model->loaded()){
            $this->getDispatcher()->forward(Lib_Config::getVar('app_ctrl_error'), null, 404);
        }
        
        $this->news = $model;
    }
    
    
    /**
     * method for print html for add and edit form
     */
    public function form(){
        $params = $this->getParams();
        $data = array(
            'id' => !empty($params[1]) ? $params[1] : 0,
            'subject' => '',
            'text' => '',
            'datetime' => date('Y-m-d H:i:s')
        );
        
        if (!$this->error) $this->error = '';
        if (!$this->warning) $this->warning = '';
        
        
        
        if (!empty($params[1])){
            $dm = Lib_DataMapper::getInstance();
            $model = $dm->getNews($params[1]);
            
            if (!$model->loaded()) $this->error = 'Запись не найдена';
            else $data = $model->getData();
        }
        
        if ($this->getGlobalVar()->post('subject') && !$this->error){
            $lstFields = array('datetime', 'subject', 'text');
            foreach ($lstFields as $fieldName){
                $data[$fieldName] = $this->getGlobalVar()->post($fieldName);
            }
        }
        
        $this->data =  $data;
    }
    
    /**
     * add and edit news
     */
    public function save(){
        $data = array();
        $lstFields = array(
            'id' => array('positive_int', 'Идентификатор новости неправильный'),
            'datetime' => array('not_empty', 'Поле Дата-время должно быть заполнено'),
            'subject' => array(
                            array('not_empty', 'Поле Заголовок должно быть заполнено'),
                            array('length_interval', array(3, 200), 'Поле Заголовок должно быть больше :param1 и меньше :param2 символов')
                         ),
            'text' => array(
                                array('not_empty', 'Поле Текст должно быть заполнено'),
                                array('length_min', array(20), 'Поле Текст должно быть больше :param1 символов')
                         ),
        );
        
        foreach ($lstFields as $field => $validInfo){
            $value = $this->getGlobalVar()->post($field);
            if (in_array($field, array('id'))){
                if ($value){
                    $result = Lib_Helper_Valid::validation($value, $validInfo);
                    if ($result !== true){
                        $this->error = $result;
                        break;
                    }
                    else $data[$field] = $value;
                }
                else ;
            }
            else {
                $result = Lib_Helper_Valid::validation($value, $validInfo);
                if ($result !== true){
                    $this->warning = $result;
                    break;
                }
                else $data[$field] = $value;
            }
        }
        
        $dm = Lib_DataMapper::getInstance();
        
        if (!empty($data['id'])){
            $model = $dm->getNews($data['id']);
            if (!$model->loaded()) $this->error = 'Запись не найдена';
        }
        
        if ($this->warning || $this->error) $this->gotoAction('form');
        else {
            try{
                $model = new Model_News($data);
                $dm->save($model);
                $this->success = empty($data['id']) ? 'add' : 'edit';
            }
            catch (Exception $e){
                if ($dm->getLastErrno() == 1062) $this->warning = 'Запись с таким же заголовком уже есть в бд';
                else $this->warning = 'Произошла ошибка при сохранении данных';
                
                $this->gotoAction('form');
            }
        }
    }
    
    
    /**
     * delete news
     */
    public function delete(){
        $params = $this->getParams();
        $model = new Model_News(array('id' => !empty($params[1]) ? $params[1] : 0));
        $dm = Lib_DataMapper::getInstance();
        $dm->delete($model);
        
        $this->getDispatcher()->redirect(Lib_Helper_Url::main(). '/');
    }
    
    
}
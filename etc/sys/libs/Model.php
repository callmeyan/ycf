<?php
/**
 * File: Model.php.
 * User: Yan<me@xiaoyan.me>
 * DateTime: 2016/4/10 21:47
 */
class ModelInfo extends YC{
    static $PRIMARY_KEY = 1;
}
abstract class Model extends YC
{
    private $primaryKey = null;
    private $tableName = null;
    private $_arrayData = null;

    public function __construct(){
        $this->getTableName();
    }

    private function getTableName(){
        if(null == $this->tableName){
            $this->tableName = get_class($this);
            if(strpos($this->tableName,'\\')){
                $classInfo = explode('\\',$this->tableName);
                $this->tableName = $classInfo[count($classInfo)-1];
            }
        }
        return $this->tableName;
    }

    protected function setPrimaryKey($key){
        $this->primaryKey = $key;
    }
    protected function setTableName($name){
        $this->tableName = $name;
    }

    /**
     * 新增数据
     */
    public function insert()
    {
        $saveData =$this->getNotNullArray();
        if($this->primaryKey){
            unset($saveData[$this->primaryKey]);
        }
        $data = DB()->insert($this->getTableName(),$saveData);
        return $this;
    }

    /**
     * 更新数据
     */
    public function update()
    {
        $saveData =$this->getNotNullArray();
        if(!$this->primaryKey){
            return false;
        }
        $updateWhere = array();
        $updateWhere[$this->primaryKey] = $saveData[$this->primaryKey];
        unset($saveData[$this->primaryKey]);
        DB()->update($this->tableName,$saveData,$updateWhere);
        return $this;
    }

    /**
     * @return DBCore
     */
    public function createQuery(){
        return DB()->table($this->getTableName());
    }

    /**
     * 删除数据
     */
    public function delete()
    {
    }

    /**
     * 查询所有数据
     */
    public function findAll()
    {
        $dataArray = DB()->select($this->getTableName());
        return $dataArray;
    }

    /**
     * 查询数据条数
     * @param array $condition
     * @return int
     */
    public function count(array $condition){
        return DB()->count($this->getTableName(),$condition);
    }

    /**
     * 根据条件查询数据
     * @param array $condition
     * @param null $limit
     * @return array|bool
     */
    public function findByCondition(array $condition,$limit = null){
        $query = DB()->where($condition);
        if($limit) $query->limit($limit);
        return $query->select($this->getTableName());
    }

    /**
     * 查询单个对象
     * @return $this|null
     */
    public function find()
    {
        $data = DB()->where($this->getNotNullArray())
            ->get($this->getTableName());
        if(!$data) return null;
        if($data && is_array($data)) $this->setProperty($data);
        return $this;
    }

    private function getPropValue($propertyName){
        $varValue = get_object_vars($this);
        if(isset($varValue[$propertyName])){
            return $varValue[$propertyName];
        }
        return null;
    }

    public function findByPrimary($objectId = null){
        if(!$this->primaryKey) return null;
        $where = array();
        if($objectId) {
            $where[$this->primaryKey] = $objectId;
        }
        else {
            $where[$this->primaryKey] = $this->getPropValue($this->primaryKey);
        }
        $data = DB()->where($where)->get($this->getTableName());
        if(!$data) return null;
        if($data && is_array($data)) $this->setProperty($data);
        return $this;
    }

    /**
     * 根据SQL语句查询对象
     * @param $querySQL
     * @return $this|null
     */
    public function findByQuery($querySQL){
        $data = DB()->fetch($querySQL);
        if(!$data) return null;
        if($data && is_array($data)) $this->setProperty($data);
        return $this;
    }

    public function setPropertyValue($k,$v){
        $obj = new \ReflectionObject($this);
        $p = $obj->getProperty($k);
        if($p)$p->setValue($this,$v);
        return $this;
    }
    public function setProperty(array $dataArray){
        $obj = new \ReflectionObject($this);

        foreach($dataArray as $k=>$v){
            if(!$k || !is_string($k)) continue;
            if($obj->hasProperty($k)){
                $obj->getProperty($k)->setValue($this,$v);
            }
        }
    }

    private function getNotNullArray(){
        $data = array();
        foreach($this->toArray() as $k=>$v){
            if($v) $data[$k] = $v;
        }
        return $data;
    }

    public function toArray(){
//        if(null != $this->_arrayData) return $this->_arrayData;
        $obj = new \ReflectionObject($this);

        $varValue = get_object_vars($this);
        $vars = $obj->getProperties();
        foreach($vars as $p){
            $this->_arrayData[$p->getName()] = $varValue[$p->getName()];
        }
        return $this->_arrayData;
    }
}
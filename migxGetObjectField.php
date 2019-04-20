<?php
/*
Возвращает поле MIGX DB объекта (json, если результатом является массив)
если нужен доступ к переменной массива поля - &field=`fieldname.key_of_array`
*/
if(!$packageName || !$classname || !$id) return false;
$modx->addPackage($packageName, MODX_BASE_PATH . 'core/components/'.$packageName.'/model/');
$object = $modx->getObject($classname, $id);
if(!$object) return false;
$output = $object->toArray();
if($field) {
    foreach(explode('.', $field) as $field_key) {
        $output = is_array($output) && isset($output[$field_key]) ? $output[$field_key] : false;
    }
}
return is_array($output) ? json_encode($output) : $output;

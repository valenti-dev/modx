<?php
if(!$packageName || !$classname || !$id) return false;
$update_fields = isset($update_fields) ? $update_fields : [];
if(is_string($update_fields)) $update_fields = json_decode($update_fields, true);
if(json_last_error() !== JSON_ERROR_NONE) {
    $update_fields = [];
}

$modx->addPackage($packageName, MODX_BASE_PATH . 'core/components/'.$packageName.'/model/');
$object = $modx->getObject($classname, $id);
if(!$object) return false;
$object_fields = $modx->getFields($classname);
$old_values = [];//сюда пишем старые значения
$new_values = [];//сюда пишем новые значения
foreach($update_fields as $field_key => $field_value) {
    if(!isset($object_fields[$field_key]) && is_array($field_value)) {
        if(isset($field_value['name']) && isset($field_value['value']) && array_search($field_value['name'], $object_fields) !== false) {
            $old_values[$field_value['name']] = $object->get($field_value['name']);//пишем старое значение
            
            if(!$field_value['action']) $field_value['action'] = '';
            switch($field_value['action']) {
                case '+=': {
                    $old_value = $object->get($field_value['name']);
                    $new_value = is_numeric($field_value['value']) && is_numeric($old_value) ? $old_value += $field_value['value'] : $old_value .= $field_value['value'];
                    $object->set($field_value['name'], $new_value);
                    $update_fields[$field_value['name']] = $new_value;
                } break;
                default: {
                    $object->set($field_value['name'], $field_value['value']);
                    $update_fields[$field_value['name']] = $field_value['value'];
                } break;
            }
            
            $new_values[$field_value['name']] = $object->get($field_value['name']);//пишем новое значение
            continue;
        }
    } else if(array_search($field_key, $object_fields) !== false) {
        $old_values[$field_key] = $object->get($field_key);//пишем старое значение
        
        if(is_array($field_value)) $field_value = json_encode($field_value);
        $object->set($field_key, $field_value);
        
        $new_values[$field_key] = $field_value;//пишем новое значение
    }
}
if($object->save() === true) {
    $modx->invokeEvent('OnMigxUpdateObject', [
        'packageName' => $packageName,
        'classname' => $classname,
        'update_fields' => json_encode($update_fields),
        'object' => $object,
        'old_values' => json_encode($old_values),
        'new_values' => json_encode($new_values),
    ]);
    return $object->get('id');
}
return false;

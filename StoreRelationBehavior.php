<?php

namespace RangelReale\storerelation;

use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\db\BaseActiveRecord;

class StoreRelationBehavior extends Behavior
{
    /**
     * @var string
     */    
    public $namingTemplate = '{relation}_store';

    /**
     * @var array List of the model relations in one of the following formats:
     * ```php
     *  [
     *      'first', // This will use default configuration and virtual relation template
     *      'second' => 'target_second', // This will use default configuration with custom relation template
     *      'third' => [
     *          'relation' => 'thrid_rel', // Optional
     *          'targetAttribute' => 'target_third', // Optional
     *          // Rest of configuration
     *      ]
     *  ]
     * ```
     */
    public $storeRelations = [];
    
    /**
     * @var array
     */
    public $storeRelationConfig = ['class' => '\RangelReale\storerelation\StoreRelationAttribute'];
    
    /**
     * @var StoreRelationAttribute[]
     */
    public $storeRelationValues = [];
    
    public function init()
    {
        $this->prepareRelations();
    }
    
    protected function prepareRelations()
    {
        foreach ($this->storeRelations as $key => $value) 
        {
            $config = $this->storeRelationConfig;
            if (is_integer($key)) {
                $originalRelation = $value;
                $targetRelation = $this->processTemplate($originalRelation);
            } else {
                $originalRelation = $key;
                if (is_string($value)) {
                    $targetRelation = $value;
                } else {
                    $targetRelation = ArrayHelper::remove($value, 'targetRelation', $this->processTemplate($originalRelation));
                    $originalRelation = ArrayHelper::remove($value, 'relation', $originalRelation);
                    $config = array_merge($config, $value);
                }
            }
            $config['behavior'] = $this;
            $config['originalRelation'] = $originalRelation;
            $config['targetRelation'] = $targetRelation;
            $this->storeRelationValues[$targetRelation] = $config;
        }
    }    
    
    protected function processTemplate($originalRelation)
    {
        return strtr($this->namingTemplate, [
            '{relation}' => $originalRelation,
        ]);
    }    
    
    public function events()
    {
        $events = [];
        $events[BaseActiveRecord::EVENT_BEFORE_VALIDATE] = 'onBeforeValidate';
        $events[BaseActiveRecord::EVENT_AFTER_FIND] = 'onAfterFind';
        $events[BaseActiveRecord::EVENT_AFTER_INSERT] = 'onAfterSave';
        $events[BaseActiveRecord::EVENT_AFTER_UPDATE] = 'onAfterSave';
        return $events;
    }
    
    /**
     * Performs validation for all the relations
     * @param Event $event
     */
    public function onBeforeValidate($event)
    {
        foreach ($this->storeRelationValues as $targetRelation => $value) {
            if ($value instanceof StoreRelationAttribute && $this->owner->isAttributeSafe($targetRelation)) {
                if (!$value->validate())
                    $event->isValid = false;
            }
        }
    }    

    /**
     * Reset when record changes
     * @param Event $event
     */
    public function onAfterFind($event)
    {
        foreach ($this->storeRelationValues as $targetRelation => $value) {
            if ($value instanceof StoreRelationAttribute) {
                $value->reset();
            }
        }
    }    

    /**
     * Save relation if safe
     * @param Event $event
     */
    public function onAfterSave($event)
    {
        foreach ($this->storeRelationValues as $targetRelation => $value) {
            if ($value instanceof StoreRelationAttribute && $this->owner->isAttributeSafe($targetRelation)) {
                $value->save();
            }
        }
    }    
    
    public function canGetProperty($name, $checkVars = true)
    {
        if ($this->hasStoreRelation($name)) {
            return true;
        }
        return parent::canGetProperty($name, $checkVars);
    }
    
    public function hasStoreRelation($name)
    {
        return isset($this->storeRelationValues[$name]);
    }
    
    public function canSetProperty($name, $checkVars = true)
    {
        if ($this->hasStoreRelation($name)) {
            return true;
        }
        return parent::canSetProperty($name, $checkVars);
    }
    
    public function __get($name)
    {
        if ($this->hasStoreRelation($name)) {
            return $this->getStoreRelation($name)->getValue();
        }
        return parent::__get($name);
    }
    
    public function __set($name, $value)
    {
        if ($this->hasStoreRelation($name)) {
            $this->getStoreRelation($name)->setValue($value);
            return;
        }
        parent::__set($name, $value);
    }
    
    public function getStoreRelation($name)
    {
        if (is_array($this->storeRelationValues[$name])) {
            $this->storeRelationValues[$name] = \Yii::createObject($this->storeRelationValues[$name]);
        }
        return $this->storeRelationValues[$name];
    }    
    
}
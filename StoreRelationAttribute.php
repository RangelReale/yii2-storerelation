<?php

namespace RangelReale\storerelation;

use yii\base\Object;

class StoreRelationAttribute extends Object
{
    /**
     * @var StoreRelationBehavior
     */    
    public $behavior;

    /**
     * @var string
     */
    public $originalRelation; 

    /**
     * @var string
     */
    public $targetRelation; 
    
    public function validate()
    {
        return true;
    }
    
    public function save()
    {
        return true;
    }
    
    public function getValue()
    {
        return $this->behavior->owner->{$this->originalRelation};
    }    
    
    public function setValue($value)
    {
    }
    
    public function reset()
    {
    }
}
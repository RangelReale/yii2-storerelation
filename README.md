Store Relation for Yii2 ActiveRecord
====================================

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist RangelReale/yii2-storerelation "*"
```

or add

```json
"RangelReale/yii2-storerelation": "*"
```

to the `require` section of your composer.json.


The idea
--------

This behavior adds an attribute that can save a relation when set.

The aliased field by default is named <attribute>_store.


How to use
----------

In your model:

```php
class Post extends ActiveRecord
{
    // ... Some code here

    public function behaviors()
    {
        return [
            'storeRelation' => [
                'class' => StoreRelationBehavior::className(), // Our behavior
                'storeRelations' => [
                    'tags' => [
                        'class' => TagStoreRelation::className(),
                    ],
                ],
            ]
        ];
    }
}
```


How is works
------------

Behavior creates "virtual" attribute named attribute_name_store for each relation you define in the 'storeRelations' section.
When you read `$yourModel->attribute_name_store` behavior will return object with the type StoreRelationAttribute. If
this object will be used in the string context, it will be converted to string with the magical __toString method.
And during this original value of `attribute_name` will be converted into the local representation.

When you assign value to the `$yourModel->attribute_name_store` internally it will be assigned to `value` property
of the StoreRelationAttribute class.

Credits
-------

Based on [omnilight/yii2-datetime](https://github.com/omnilight/yii2-datetime).
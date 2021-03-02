<?php

namespace mdm\admin\models;

use Yii;
use mdm\admin\components\Configs;
use yii\db\Query;
use mdm\admin\models\MenuRecordOp;

/**
 * This is the model class for table "menu".
 *
 * @property integer $id Menu id(autoincrement)
 * @property string $name Menu name
 * @property integer $parent Menu parent
 * @property string $route Route for this menu
 * @property integer $order Menu order
 * @property string $data Extra information for this menu
 *
 * @property Menu $menuParent Menu parent
 * @property Menu[] $menus Menu children
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Menu extends \yii\db\ActiveRecord
{
    public $parent_name;

    /**
     * @var MenuRecordOp
     */
    public $record;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Configs::instance()->menuTable;
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        if (Configs::instance()->db !== null) {
            return Configs::instance()->db;
        } else {
            return parent::getDb();
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_name'], 'in',
                'range' => static::find()->select(['name'])->column(),
                'message' => 'Menu "{value}" not found.'],
            [['parent', 'route', 'data', 'order'], 'default'],
            [['parent'], 'filterParent', 'when' => function() {
                return !$this->isNewRecord;
            }],
            [['order'], 'integer'],
            [['route'], 'in',
                'range' => static::getSavedRoutes(),
                'message' => 'Route "{value}" not found.']
        ];
    }

    /**
     * Use to loop detected.
     */
    public function filterParent()
    {
        $parent = $this->parent;
        $db = static::getDb();
        $query = (new Query)->select(['parent'])
            ->from(static::tableName())
            ->where('[[id]]=:id');
        while ($parent) {
            if ($this->id == $parent) {
                $this->addError('parent_name', 'Loop detected.');
                return;
            }
            $parent = $query->params([':id' => $parent])->scalar($db);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rbac-admin', 'ID'),
            'name' => Yii::t('rbac-admin', 'Name'),
            'parent' => Yii::t('rbac-admin', 'Parent'),
            'parent_name' => Yii::t('rbac-admin', 'Parent Name'),
            'route' => Yii::t('rbac-admin', 'Route'),
            'order' => Yii::t('rbac-admin', 'Order'),
            'data' => Yii::t('rbac-admin', 'Data'),
        ];
    }

    /**
     * Get menu parent
     * @return \yii\db\ActiveQuery
     */
    public function getMenuParent()
    {
        return $this->hasOne(Menu::className(), ['id' => 'parent']);
    }

    /**
     * Get menu children
     * @return \yii\db\ActiveQuery
     */
    public function getMenus()
    {
        return $this->hasMany(Menu::className(), ['parent' => 'id']);
    }
    private static $_routes;

    /**
     * Get saved routes.
     * @return array
     */
    public static function getSavedRoutes()
    {
        if (self::$_routes === null) {
            self::$_routes = [];
            foreach (Configs::authManager()->getPermissions() as $name => $value) {
                if ($name[0] === '/' && substr($name, -1) != '*') {
                    self::$_routes[] = $name;
                }
            }
        }
        return self::$_routes;
    }

    public static function getMenuSource()
    {
        $tableName = static::tableName();
        return (new \yii\db\Query())
                ->select(['m.id', 'm.name', 'm.route', 'parent_name' => 'p.name'])
                ->from(['m' => $tableName])
                ->leftJoin(['p' => $tableName], '[[m.parent]]=[[p.id]]')
                ->all(static::getDb());
    }

    public function getParentRecursion($parent, &$list)
    {
        if ($parent == null) {
            return;
        }

        $parentinfo = [];
        $model = Menu::findOne($parent);
        if ($model == null) {
            return;
        } else {
            $parentinfo['name'] = $model->name;
            $parentinfo['route'] = $model->route;
            $parentinfo['parent'] = $model->parent;
            $list[] = $parentinfo;
            $this->getParentRecursion($model->parent, $list);
            return;
        }
    }

    public function beforeDelete()
    {
        $record = new MenuRecordOp();
        $record->menu_op_type = 'delete';
        $record->menu_op_name = $this->name;
        $record->menu_op_route = $this->route;
        $record->menu_op_parent = $this->parent;
        $parentinfo = [];
        $this->getParentRecursion($this->parent, $parentinfo);
        $record->menu_op_parent_info_recursion = json_encode($parentinfo, JSON_UNESCAPED_UNICODE);
        $this->record = $record;

        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        if ($this->record) {
            $this->record->save(false);
        }
        $this->record = null;

        parent::afterDelete();
    }

    public function beforeSave($insert)
    {
        $record = new MenuRecordOp();
        $attrs = $this->getDirtyAttributes();
        foreach ($attrs as $attr => $value) {
            switch ($attr) {
                case 'name';
                    $record->menu_op_name = $value;
                    break;
                case 'route';
                    $record->menu_op_route = $value;
                    break;
                case 'parent';
                    if (!$this->isNewRecord) {
                        // 框架判断是否是脏属性，使用的是全等比较(===)
                        // 由于http form-data的数据没有类型区分，所有字段load进来都是string
                        // 旧值是从数据库里获取的,区分类型
                        // 脏属性方法(getDirtyAttributes)比较int时，会用string和int比较，始终是不相等的
                        $oldParent = $this->getOldAttribute('parent');
                        if ((int)$value === $oldParent) {
                            break;
                        }
                    }
                    // parent指向的是表的主键
                    // 由于不同环境的主键自增序列没有一致性关联，所以要根据(名称name+属性route+位置parent)来定位父级菜单
                    $record->menu_op_parent = $value;
                    $parentinfo = [];
                    $this->getParentRecursion($value, $parentinfo);
                    $record->menu_op_parent_info_recursion = json_encode($parentinfo, JSON_UNESCAPED_UNICODE);
                    break;
                case 'order':
                    $record->menu_op_order = $value;
                    break;
            }
        }
        if ($this->isNewRecord) {
            $record->menu_op_type = 'insert';
        } else {
            $record->menu_op_type = 'update';
            $oldAttrs = $this->getOldAttributes();
            foreach ($oldAttrs as $attr => $value) {
                switch ($attr) {
                    case 'name';
                        $record->menu_op_old_name = $value;
                        break;
                    case 'route';
                        $record->menu_op_old_route = $value;
                        break;
                    case 'parent';
                        $record->menu_op_old_parent = $value;
                        $parentinfo = [];
                        $this->getParentRecursion($value, $parentinfo);
                        $record->menu_op_old_parent_info_recursion = json_encode($parentinfo, JSON_UNESCAPED_UNICODE);
                        break;
                    case 'order':
                        $record->menu_op_old_order = $value;
                }
            }
        }
        $this->record = $record;

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($this->record) {
            $this->record->save(false);
        }
        $this->record = null;
        parent::afterSave($insert, $changedAttributes);
    }
}

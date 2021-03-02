<?php

namespace mdm\admin\models;

use Yii;
use mdm\admin\components\Configs;
use yii\db\Expression;
use yii\db\Query;

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
 *
 * @property int $menu_op_id 主键
 * @property string $menu_op_type 操作类型,insert或update
 * @property string $menu_op_name 菜单名称
 * @property string $menu_op_route 路由
 * @property int $menu_op_parent 父级菜单id
 * @property int $menu_op_order 排序
 * @property string $menu_op_parent_info_recursion 父级菜单信息,递归记录每级parentinfo
 * @property string $menu_op_old_name 原名字
 * @property string $menu_op_old_route 原路由
 * @property int $menu_op_old_parent 原父级菜单
 * @property int $menu_op_old_order 原排序
 * @property string $menu_op_old_parent_info_recursion 原父级菜单信息,递归记录每级parentinfo
 * @property string $create_datetime 执行时间
 * @property string $export_datetime 导出时间
 */
class MenuRecordOp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Configs::instance()->menuRecordTable;
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

    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'updatedAtAttribute' => false,
                'createdAtAttribute' => 'create_datetime',
                'value' => new Expression('NOW()'),
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'menu_op_type',
                    'menu_op_name',
                    'menu_op_route',
                    'menu_op_parent',
                    'menu_op_order',
                    'menu_op_parent_info_recursion',
                    'menu_op_old_name',
                    'menu_op_old_route',
                    'menu_op_old_parent',
                    'menu_op_old_order',
                    'menu_op_old_parent_info_recursion',
                    'create_datetime',
                    'export_datetime',
                ],
                'safe',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'menu_op_type' => Yii::t('rbac-admin', '类型'),
            'menu_op_name' => Yii::t('rbac-admin', '菜单名'),
            'menu_op_route' => Yii::t('rbac-admin', '路由'),
            'menu_op_parent' => Yii::t('rbac-admin', '父级菜单'),
            'menu_op_order' => Yii::t('rbac-admin', '排序'),
            'menu_op_parent_info_recursion' => Yii::t('rbac-admin', '全部上级菜单节点'),
            'menu_op_old_name' => Yii::t('rbac-admin', '原菜单名'),
            'menu_op_old_route' => Yii::t('rbac-admin', '原路由'),
            'menu_op_old_parent' => Yii::t('rbac-admin', '原父级菜单'),
            'menu_op_old_order' => Yii::t('rbac-admin', '原排序'),
            'menu_op_old_parent_info_recursion' => Yii::t('rbac-admin', '原上级菜单全部节点'),
            'create_datetime' => Yii::t('rbac-admin', '执行时间'),
            'export_datetime' => Yii::t('rbac-admin', '导出时间'),
        ];
    }
}

<?php

use yii\db\Migration;
use mdm\admin\components\Configs;

class m210302_050000_create_menu_op extends Migration
{


    /**
     * @inheritdoc
     */
    public function up()
    {
        $menuTable = Configs::instance()->menuRecordTable;
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($menuTable, [
            'menu_op_id' => $this->primaryKey(),
            'menu_op_type' => $this->string(16)->notNull()->comment('操作类型,insert,update,delete'),
            'menu_op_name' => $this->string(128)->comment('菜单名称'),
            'menu_op_route' => $this->string(256)->comment('路由'),
            'menu_op_parent' => $this->integer()->comment('父级菜单id'),
            'menu_op_order' => $this->integer()->comment('排序'),
            'menu_op_parent_info_recursion' => $this->text()->comment('父级菜单信息,递归记录每级parentinfo'),
            'menu_op_old_name' => $this->string(128)->comment('原名字'),
            'menu_op_old_route' => $this->string(256)->comment('原路由'),
            'menu_op_old_parent' => $this->integer()->comment('原父级菜单'),
            'menu_op_old_order' => $this->integer()->comment('原排序'),
            'menu_op_old_parent_info_recursion' => $this->text()->comment('原上级菜单'),
            'create_datetime' => $this->datetime()->comment('执行时间'),
            'export_datetime' => $this->datetime()->comment('导出时间'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable(Configs::instance()->menuTable);
    }
}

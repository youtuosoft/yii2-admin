/**
 * Database schema required by yii2-admin.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.5
 */

drop table if exists "menu";
drop table if exists "user";
drop table if exists "epmms_menu_record_op";

create table "menu"
(
    "id" serial NOT NULL PRIMARY KEY,
    "name" varchar(128),
    "parent" integer,
    "route" varchar(256),
    "order" integer,
    "data"   bytea,
    foreign key ("parent") references "menu"("id")  ON DELETE SET NULL ON UPDATE CASCADE
);

create table "user"
(
    "id" serial NOT NULL PRIMARY KEY,
    "username" varchar(32) NOT NULL,
    "auth_key" varchar(32) NOT NULL,
    "password_hash" varchar(256) NOT NULL,
    "password_reset_token" varchar(256),
    "email" varchar(256) NOT NULL,
    "status" integer not null default 10,
    "created_at" integer not null,
    "updated_at" integer not null
);

CREATE TABLE "epmms_menu_record_op" (
     "menu_op_id" serial NOT NULL PRIMARY KEY,
     "menu_op_type" VARCHAR(16) NOT NULL,
     "menu_op_name" VARCHAR(128),
     "menu_op_route" VARCHAR(256),
     "menu_op_parent" INTEGER,
     "menu_op_order" INTEGER,
     "menu_op_parent_info_recursion" TEXT,
     "menu_op_old_name" VARCHAR(128),
     "menu_op_old_route" VARCHAR(256),
     "menu_op_old_parent" INTEGER,
     "menu_op_old_order" INTEGER,
     "menu_op_old_parent_info_recursion" TEXT,
     "create_datetime" TIMESTAMP WITHOUT TIME ZONE,
     "export_datetime" TIMESTAMP WITHOUT TIME ZONE
);

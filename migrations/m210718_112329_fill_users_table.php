<?php

use yii\db\Migration;

/**
 * Class m210718_112329_fill_users_table
 */
class m210718_112329_fill_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        for ($i = 1; $i <= 25; $i++) {
            $number = sprintf('%02d', $i);

            $this->insert("{{%users}}", [
                'username' => "test_user{$number}",
                'auth_key' => Yii::$app->security->generateRandomString(),
                'password_hash' => Yii::$app->getSecurity()->generatePasswordHash("TestPass{$number}"),
                'email' => "test_user{$number}@mail.ru",
                'status' => 10,
                'created_at' => time(),
                'updated_at' => 0,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->truncateTable("{{%users}}");
    }

    /*
    public function safeUp()
    {

    }

    public function safeDown()
    {
        echo "m210718_112329_fill_users_table cannot be reverted.\n";

        return false;
    }
    */
}

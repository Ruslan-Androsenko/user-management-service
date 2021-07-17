<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use app\modules\v1\models\User;


class UserController extends ActiveController
{
    public $modelClass = 'app\modules\v1\models\User';

    public function actionView($id)
    {
        $user = User::findOne(['id' => $id, 'status' => 10]);

        if (!$user) {
            Yii::$app->response->setStatusCode(404);

            return [
                'message' => 'Ошибка! Отсутствует активная запись с id '. $id,
            ];
        }

        return $user;
    }


    public function actionCreate()
    {
        $errors = [];
        $user = new User();
        $user->load(['User' => Yii::$app->request->post()]);
        $password = Yii::$app->request->post('password') ?? '';

        if (strlen($password) >= User::MIN_PASSWORD_LENGTH) {
            Yii::$app->response->setStatusCode(201);

            $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
            $user->auth_key = Yii::$app->security->generateRandomString();
        } else {
            $errors[] = [
                'field' => 'password',
                'message' => 'Пароль не должен быть короче ' . User::MIN_PASSWORD_LENGTH . ' символов.',
            ];
        }

        if (!$user->save()) {
            Yii::$app->response->setStatusCode(422);

            return array_merge($errors, $user->getFormatErrorMessage());
        }

        return $user;
    }

    public function actionDelete($id)
    {
        $user = User::findOne(['id' => $id, 'status' => 10]);

        if (!$user) {
            Yii::$app->response->setStatusCode(404);

            return [
                'message' => 'Ошибка! Отсутствует запись с id '. $id,
            ];
        } else {
            Yii::$app->response->setStatusCode(204);

            $user->status = 0;
            $user->save();
        }

        return $user;
    }

    public function actionUpdate($id)
    {
        $errors = [];
        $user = User::findOne(['id' => $id, 'status' => 10]);

        if (!$user) {
            Yii::$app->response->setStatusCode(404);

            return [
                'message' => 'Ошибка! Отсутствует запись с id '. $id,
            ];
        } else {
            $username = Yii::$app->request->post('username') ?? '';
            $email = Yii::$app->request->post('email') ?? '';
            $password = Yii::$app->request->post('password') ?? '';

            if (!empty($username)) {
                $user->username = $username;
            }

            if (!empty($email)) {
                $user->email = $email;
            }

            if (!empty($password)) {
                if (strlen($password) >= User::MIN_PASSWORD_LENGTH) {
                    $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
                } else {
                    $user->password_hash = '';
                    $errors[] = [
                        'field' => 'password',
                        'message' => 'Пароль не должен быть короче ' . User::MIN_PASSWORD_LENGTH . ' символов.',
                    ];
                }
            }

            if (!$user->save()) {
                Yii::$app->response->setStatusCode(422);

                return array_merge($errors, $user->getFormatErrorMessage());
            }
        }

        return $user;
    }
}

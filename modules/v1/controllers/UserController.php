<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use app\modules\v1\models\User;


class UserController extends ActiveController
{
    public $modelClass = 'app\modules\v1\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['create', 'login'],
        ];

        return $behaviors;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        $token = Yii::$app->request->headers->get('Authorization');
        $token = preg_replace('/Bearer\s(.*)/', '$1', $token);

        if ((in_array($action, ['view', 'delete', 'update'])) && !$model->validateAuthKey($token)) {
            throw new \yii\web\ForbiddenHttpException(sprintf('You cannot execute %s because you are not logged in.', $action));
        }
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['delete']);
        unset($actions['update']);

        return $actions;
    }

    public function actionLogin()
    {
        $username = Yii::$app->request->get('username') ?? '';
        $password = Yii::$app->request->get('password') ?? '';

        if (!empty($username) && !empty($password)) {
            $user = User::findOne(['username' => $username, 'status' => 10]);

            if (!$user) {
                Yii::$app->response->setStatusCode(404);

                return [
                    'message' => 'Ошибка! Пользователя с такими данными авторизации не существует.',
                ];
            } elseif ($user->isAutenticate($password)) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'token' => $user->getAuthKey(),
                ];
            } else {
                Yii::$app->response->setStatusCode(401);

                return [
                    'message' => 'Ошибка! Неверный пароль для данного пользователя.',
                ];
            }
        } else {
            Yii::$app->response->setStatusCode(404);

            return [
                'message' => 'Ошибка! Пустые данные для авторизации.',
            ];
        }
    }

    public function actionView($id)
    {
        $user = User::findOne(['id' => $id, 'status' => 10]);

        if (!$user) {
            Yii::$app->response->setStatusCode(404);

            return [
                'message' => 'Ошибка! Отсутствует активная запись с id '. $id,
            ];
        } else {
            $this->checkAccess($this->action->id, $user);
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
            $this->checkAccess($this->action->id, $user);
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
            $this->checkAccess($this->action->id, $user);
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

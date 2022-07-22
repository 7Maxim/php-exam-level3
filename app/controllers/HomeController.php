<?php

namespace App\controllers;

use App\Redirect;
use League\Plates\Engine;

use App\QueryBuilder;
use Delight\Auth\Auth;

use Azurre\Component\Http\Uploader;

class HomeController
{

    private $templates;
    private $queryBuilder;
    private $auth;


    public function __construct(Engine $engine, QueryBuilder $queryBuilder, Auth $auth)
    {
        $this->templates = $engine;
        $this->auth = $auth;
        $this->queryBuilder = $queryBuilder;

    }


    public function users()
    {

        $this->checkLogin();


        $users = $this->queryBuilder->getAll(USERS_TABLE_NAME);

        $status_classes = array(
            'success' => '1',
            'warning' => '0',
            'danger' => '2',
        );

        $user_auth_info = [
            'id' => $this->auth->getUserId(),
            'role_arr' => $this->auth->getRoles()
        ];

        echo $this->templates->render('users',
            ['users' => $users, 'status_classes' => $status_classes, 'user_auth_info' => $user_auth_info]);

    }


    public function register()
    {

        if ($this->auth->isLoggedIn()) {
            flash()->info('Вы уже зарегистрированы');
            Redirect::to('/users');
        }


        echo $this->templates->render('page_register');
    }


    public function user_register()
    {


        try {
            $this->auth->register($_POST['email'], $_POST['password'], null);

            flash()->success('Registration successful');
            Redirect::to('/login');

        } catch (\Delight\Auth\InvalidEmailException $e) {

            flash()->error('Invalid email address');
            Redirect::to('/register');

        } catch (\Delight\Auth\InvalidPasswordException $e) {

            flash()->error('Invalid password');
            Redirect::to('/register');

        } catch (\Delight\Auth\UserAlreadyExistsException $e) {

            flash()->error('User already exists');
            Redirect::to('/register');
        } catch (\Delight\Auth\TooManyRequestsException $e) {

            flash()->error('Too many requests');
            Redirect::to('/register');
        }


    }


    public function login()
    {

        if ($this->auth->isLoggedIn()) {
            flash()->info('Вы уже авторизованы. Чтобы авторизоваться снова вам необходимо выйти из аккаунта');
            Redirect::to('/');
        }


        echo $this->templates->render('page_login');
    }

    public function user_login()
    {


        if ($_POST['remember'] == 'on') {
            // keep logged in for one year
            $rememberDuration = (int)(60 * 60 * 24 * 365.25);
        } else {
            // do not keep logged in after session ends
            $rememberDuration = null;
        }

        try {
            $this->auth->login($_POST['email'], $_POST['password'], $rememberDuration);
            flash()->success('Successfully logged in');
            Redirect::to('/users');
        } catch (\Delight\Auth\InvalidEmailException $e) {

            flash()->error('Wrong email address');
            Redirect::to('/login');
        } catch (\Delight\Auth\InvalidPasswordException $e) {

            flash()->error('Wrong password');
            Redirect::to('/login');
        } catch (\Delight\Auth\EmailNotVerifiedException $e) {

            flash()->error('Email not verified');
            Redirect::to('/login');
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->error('Too many requests');
            Redirect::to('/login');
        }
    }


    public function logOut()
    {
        try {
            $this->auth->logOutEverywhere();

            flash()->info('Вы вышли из аккаунта');
            Redirect::to('/login');
        } catch (\Delight\Auth\NotLoggedInException $e) {
            flash()->error('Not logged in');
            Redirect::to('/login');
        }
    }

    public function create_user()
    {

        $this->checkLogin();


        if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN)) {
            flash()->error('Для добавления пользователя нужны права админа');
            Redirect::to('/');
        }

        echo $this->templates->render('create_user');
    }


    public function create_user_handler()
    {

        $this->checkLogin();


        if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN)) {
            flash()->error('Для добавления пользователя нужны права админа');
            Redirect::to('/');
        }


        if (empty($_POST['email']) || empty($_POST['password'])) {

            flash()->error("Незаполнены обязательные поля email или password");
            Redirect::to('/');

        }


        if ($this->queryBuilder->findOneByEmail(USERS_TABLE_NAME, $_POST['email'])) {

            flash()->error("Email: " . $_POST['email'] . " занят");
            Redirect::to('/');

        }

        $insertData = [
            'username' => htmlspecialchars($_POST['username']),
            'email' => htmlspecialchars($_POST['email']),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'company' => htmlspecialchars($_POST['company']),
            'phone' => htmlspecialchars($_POST['phone']),
            'address' => htmlspecialchars($_POST['address']),
            'status' => htmlspecialchars($_POST['status']),
            'user_social_vk' => htmlspecialchars($_POST['user_social_vk']),
            'user_social_telegram' => htmlspecialchars($_POST['user_social_telegram']),
            'user_social_instagram' => htmlspecialchars($_POST['user_social_instagram'])

        ];

        $id = $this->queryBuilder->insert($insertData, USERS_TABLE_NAME);

        if (!empty($_FILES['file']['name'])) {
            try {
                $uploader = Uploader::create()
                    //->setDestination('./img/demo/authors/')
                    ->setDestination(USER_IMAGE_DIR)
                    ->setOverwrite(false)// Overwrite existing files?
                    ->setNameFormat(Uploader::NAME_FORMAT_ORIGINAL)
                    ->setReplaceCyrillic(false)// Transliterate cyrillic names
                    ->addValidator(Uploader::VALIDATOR_MIME, ['image/png', 'image/jpeg'])
                    ->addValidator(Uploader::VALIDATOR_EXTENSION, ['png', 'jpg'])
                    ->addValidator(Uploader::VALIDATOR_SIZE, '1M');


                $customData = 'author';
                // Custom name formatter. If you will use custom formatter setNameFormat() setReplaceCyrillic() will be ignored.
                $uploader->setNameFormatter(function ($file, $upl) use ($customData) {
                    /** @var Uploader\File $file */
                    /** @var Uploader $upl */
                    $newName = str_replace(' ', '-', $file->getName());
                    $newName = Uploader::transliterate($newName);
                    $newName .= uniqid("_{$customData}_", true) . ".{$file->getExtension()}";
                    return $newName;
                });

                $uploader->upload('file');


                $this->queryBuilder->update(['image' => $uploader->getFirstFile()->getNewName()], $id, USERS_TABLE_NAME);


            } catch (\Exception $e) {
                flash()->error('Error:' . $e->getMessage());
                Redirect::to('/create-user');

            }
        }


        $newUser = $this->queryBuilder->findOne(USERS_TABLE_NAME, $id);

        flash()->success("Пользователь {$newUser['username']} с почтой {$newUser['email']} успешно добавлен");
        Redirect::to('/');

    }


    public function profile($id)
    {
        $this->checkLogin();


        $user = $this->checkUserExist(USERS_TABLE_NAME, $id);

        echo $this->templates->render('page_profile', ['user' => $user]);
    }

    public function delete_user($id)
    {

        $this->checkLogin();
        $this->checkRights($id);

        $user = $this->checkUserExist(USERS_TABLE_NAME, $id);
        $this->queryBuilder->delete(USERS_TABLE_NAME, $id);

        if ( file_exists(USER_IMAGE_DIR . $user['image']) && ($user['image'] !== USER_IMAGE_DEFAULT) ) {
            unlink(USER_IMAGE_DIR . $user['image']);
        }

        if ( $this->auth->getUserId() == $id ) {
            session_destroy();
            Redirect::to('/register');
        }

        flash()->info("Пользователь успешно удален");
        Redirect::to('/');
    }


    public function edit($id)
    {

        $this->checkLogin();

        $this->checkRights($id);


        $user = $this->checkUserExist(USERS_TABLE_NAME, $id);
        echo $this->templates->render('edit', ['user' => $user]);

    }

    public function edit_user($id)
    {

        $this->checkLogin();

        $this->checkRights($id);

        $this->checkUserExist(USERS_TABLE_NAME, $id);

        $this->queryBuilder->update($_POST, $id, USERS_TABLE_NAME);
        flash()->success("Профиль успешно обновлен");
        Redirect::to('/');

    }


    public function security($id)
    {

        $this->checkLogin();

        $this->checkRights($id);
        $user = $this->checkUserExist(USERS_TABLE_NAME, $id);

        echo $this->templates->render('security', ['user' => $user]);
    }


    public function security_handler($id)
    {

        $this->checkLogin();

        $this->checkRights($id);

        if ($_POST['password'] !== $_POST['confirm_password']) {
            flash()->error("Пароли не совпадают");
            Redirect::to('/security/' . $id);
        }


        $changeable_user = $this->queryBuilder->findOne(USERS_TABLE_NAME, $id);



        $new_email = $_POST['email'];

        $users = $this->queryBuilder->getAll(USERS_TABLE_NAME);

        foreach ($users as $user) {

            if (($user['email'] === $new_email) && ($changeable_user['email'] !== $user['email'])) {

                flash()->error("Пользователь с почтой $new_email уже сущестует");
                Redirect::to('/security/' . $id);

            }
        }

        $this->queryBuilder->update(['email' => $new_email], $id, USERS_TABLE_NAME);

        if ($changeable_user['email'] === $this->auth->getEmail()) {
            $_SESSION['auth_email'] = $new_email;
        }


        $new_hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        if (!empty($_POST['password'])) {
            $this->queryBuilder->update(['password' => $new_hashed_password], $id, USERS_TABLE_NAME);
        }

        flash()->success("Профиль с почтой $new_email успешно обновлен");
        Redirect::to('/');

    }


    public function status($id)
    {

        $this->checkLogin();

        $this->checkRights($id);

        $user = $this->checkUserExist(USERS_TABLE_NAME, $id);

        $statuses_arr = ['Отошел', 'Онлайн', 'Не беспокоить'];



        echo $this->templates->render('status', ['user' => $user, 'statuses_arr' => $statuses_arr]);
    }


    public function status_handler($id)
    {

        $this->checkLogin();
        $this->checkRights($id);

        $this->queryBuilder->update(['status' => $_POST['status']], $id, USERS_TABLE_NAME);
        flash()->success("Профиль успешно обновлен");
        Redirect::to('/');
    }


    public function media($id)
    {


        $this->checkLogin();

        $this->checkRights($id);

        $user = $this->checkUserExist(USERS_TABLE_NAME, $id);

        echo $this->templates->render('media', ['user' => $user]);
    }


    public function media_handler($id)
    {

        $this->checkLogin();

        $this->checkRights($id);

        if (isset($_FILES['file'])) {
            try {
                $uploader = Uploader::create()
                    //->setDestination('./img/demo/authors/')
                    ->setDestination(USER_IMAGE_DIR)
                    ->setOverwrite(false)// Overwrite existing files?
                    ->setNameFormat(Uploader::NAME_FORMAT_ORIGINAL)
                    ->setReplaceCyrillic(false)// Transliterate cyrillic names
                    ->addValidator(Uploader::VALIDATOR_MIME, ['image/png', 'image/jpeg'])
                    ->addValidator(Uploader::VALIDATOR_EXTENSION, ['png', 'jpg'])
                    ->addValidator(Uploader::VALIDATOR_SIZE, '1M');


                $customData = 'author';
                // Custom name formatter. If you will use custom formatter setNameFormat() setReplaceCyrillic() will be ignored.
                $uploader->setNameFormatter(function ($file, $upl) use ($customData) {
                    /** @var Uploader\File $file */
                    /** @var Uploader $upl */
                    $newName = str_replace(' ', '-', $file->getName());
                    $newName = Uploader::transliterate($newName);
                    $newName .= uniqid("_{$customData}_", true) . ".{$file->getExtension()}";
                    return $newName;
                });

                $uploader->upload('file');


                $this->queryBuilder->update(['image' => $uploader->getFirstFile()->getNewName()], $id, USERS_TABLE_NAME);

                flash()->success("Изображение пользователя обновлено");
                Redirect::to('/media/' . $id);

            } catch (\Exception $e) {
                flash()->error('Error:' . $e->getMessage());
                Redirect::to('/media/' . $id);

            }
        }
    }


    private function checkLogin($message = 'Вы не авторизованы. Войдите в аккаунт')
    {

        if (!$this->auth->isLoggedIn()) {
            flash()->error($message);
            Redirect::to('/login');
        }
    }


    private function checkRights($id, $redirectUri = '/', $message = 'Недостаточно прав')
    {

        if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && ($this->auth->getUserId() != $id)) {
            flash()->error($message);
            Redirect::to($redirectUri);
        }
    }


    private function checkUserExist($table, $id) {

        $user = $this->queryBuilder->findOne($table, $id);

        if ( !$user ) {
            flash()->error('Такого пользователя не существует');
            Redirect::to('/');
        }

        return $user;

    }

}
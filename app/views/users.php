
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <meta name="description" content="Chartist.html">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
    <link id="vendorsbundle" rel="stylesheet" media="screen, print" href="/css/vendors.bundle.css">
    <link id="appbundle" rel="stylesheet" media="screen, print" href="/css/app.bundle.css">
    <link id="myskin" rel="stylesheet" media="screen, print" href="css/skins/skin-master.css">
    <link rel="stylesheet" media="screen, print" href="/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/css/fa-brands.css">
    <link rel="stylesheet" media="screen, print" href="/css/fa-regular.css">
</head>
    <body class="mod-bg-1 mod-nav-link">

        <?php include '../app/views/navbar.php' ;?>


        <main id="js-page-content" role="main" class="page-content mt-3">

            <?= flash()->display(); ?>
            <div class="subheader">
                <h1 class="subheader-title">
                    <i class='subheader-icon fal fa-users'></i> Список пользователей
                </h1>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <a class="btn btn-success" href="/create-user">Добавить</a>

                    <div class="border-faded bg-faded p-3 mb-g d-flex mt-3">
                        <input type="text" id="js-filter-contacts" name="filter-contacts" class="form-control shadow-inset-2 form-control-lg" placeholder="Найти пользователя">
                        <div class="btn-group btn-group-lg btn-group-toggle hidden-lg-down ml-3" data-toggle="buttons">
                            <label class="btn btn-default active">
                                <input type="radio" name="contactview" id="grid" checked="" value="grid"><i class="fas fa-table"></i>
                            </label>
                            <label class="btn btn-default">
                                <input type="radio" name="contactview" id="table" value="table"><i class="fas fa-th-list"></i>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="js-contacts">


                <?php foreach ($users as $user) : ?>


                    <div class="col-xl-4">
                        <div id="c_2" class="card border shadow-0 mb-g shadow-sm-hover" data-filter-tags="<?= $user['username']; ?>">
                            <div class="card-body border-faded border-top-0 border-left-0 border-right-0 rounded-top">
                                <div class="d-flex flex-row align-items-center">

                                    <?php foreach ($status_classes as $key => $value) : ?>

                                        <?php if ($value == $user['status']) : ?>
                                            <span class="status status-<?= $key;?> mr-3">
                                            <span class="rounded-circle profile-image d-block " style="background-image:url('/img/demo/authors/<?php
                                            if (file_exists(USER_IMAGE_DIR . $user['image'])) {
                                               echo $user['image'];
                                            } else {
                                                echo USER_IMAGE_DEFAULT;
                                            }


                                            ?>'); background-size: cover;"></span>
                                        </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>


                                    <div class="info-card-text flex-1">
                                        <?php if (in_array('ADMIN', $user_auth_info['role_arr']) || $user['id'] == $user_auth_info['id']) : ?>
                                        <a href="javascript:void(0);" class="fs-xl text-truncate text-truncate-lg text-info" data-toggle="dropdown" aria-expanded="false">
                                            <?= $user['username']; ?>
                                            <i class="fal fas fa-cog fa-fw d-inline-block ml-1 fs-md"></i>
                                            <i class="fal fa-angle-down d-inline-block ml-1 fs-md"></i>

                                        </a>

                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="/edit/<?=$user['id'];?>">
                                                <i class="fa fa-edit"></i>
                                                Редактировать</a>
                                            <a class="dropdown-item" href="/profile/<?= $user['id']; ?>">
                                                <i class="fa fa-camera"></i>
                                                Профиль </a>
                                            <a class="dropdown-item" href="/security/<?=$user['id'];?>">
                                                <i class="fa fa-lock"></i>
                                                Безопасность</a>
                                            <a class="dropdown-item" href="/status/<?=$user['id'];?>">
                                                <i class="fa fa-sun"></i>
                                                Установить статус</a>
                                            <a class="dropdown-item" href="/media/<?=$user['id'];?>">
                                                <i class="fa fa-camera"></i>
                                                Загрузить аватар
                                            </a>
                                            <a href="/delete-user/<?= $user['id']; ?>" class="dropdown-item" onclick="return confirm('are you sure?');">
                                                <i class="fa fa-window-close"></i>
                                                Удалить
                                            </a>
                                        </div>

                                        <?php else : ?>
                                            <a href="profile/<?= $user['id']; ?>" class="fs-xl text-truncate text-truncate-lg text-info">
                                                <?= $user['username']; ?>
                                            </a>

                                        <?php endif; ?>


                                        <span class="text-truncate text-truncate-xl"> <?= $user['position'] ? $user['position'] . ',': ''; ?> <?= $user['company']; ?></span>
                                    </div>

                                    <button class="js-expand-btn btn btn-sm btn-default d-none" data-toggle="collapse" data-target="#c_2 > .card-body + .card-body" aria-expanded="false">
                                        <span class="collapsed-hidden">+</span>
                                        <span class="collapsed-reveal">-</span>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0 collapse show">
                                <div class="p-3">
                                    <a href="tel:<?= $user['phone']; ?>" class="mt-1 d-block fs-sm fw-400 text-dark">
                                        <i class="fas fa-mobile-alt text-muted mr-2"></i> <?= $user['phone']; ?></a>
                                    <a href="mailto:<?= $user['email']; ?>" class="mt-1 d-block fs-sm fw-400 text-dark">
                                        <i class="fas fa-mouse-pointer text-muted mr-2"></i> <?= $user['email']; ?></a>
                                    <a class="fs-sm fw-400 mt-4 text-muted">
                                        <i class="fas fa-map-pin mr-2"></i> <?= $user['address']; ?>
                                    </a>
                                    <div class="d-flex flex-row">
                                        <a href="https://vk.com/<?= $user['user_social_vk']; ?>" class="mr-2 fs-xxl" style="color:#4680C2">
                                            <i class="fab fa-vk"></i>
                                        </a>
                                        <a href="https://t.me/<?= $user['user_social_telegram']; ?>" class="mr-2 fs-xxl" style="color:#38A1F3">
                                            <i class="fab fa-telegram"></i>
                                        </a>
                                        <a href="https://instagram.com/<?= $user['user_social_instagram']; ?>" class="mr-2 fs-xxl" style="color:#E1306C">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <? endforeach; ?>



            </div>
        </main>
     
        <!-- BEGIN Page Footer -->
        <footer class="page-footer" role="contentinfo">
            <div class="d-flex align-items-center flex-1 text-muted">
                <span class="hidden-md-down fw-700">2022 © Учебный проект</span>
            </div>
            <div>
                <ul class="list-table m-0">
                    <li><a href="/" class="text-secondary fw-700">Home</a></li>
                    <li class="pl-3"><a href="/" class="text-secondary fw-700">About</a></li>
                </ul>
            </div>
        </footer>
        
    </body>

    <script src="js/vendors.bundle.js"></script>
    <script src="js/app.bundle.js"></script>
    <script>

        $(document).ready(function()
        {

            $('input[type=radio][name=contactview]').change(function()
                {
                    if (this.value == 'grid')
                    {
                        $('#js-contacts .card').removeClassPrefix('mb-').addClass('mb-g');
                        $('#js-contacts .col-xl-12').removeClassPrefix('col-xl-').addClass('col-xl-4');
                        $('#js-contacts .js-expand-btn').addClass('d-none');
                        $('#js-contacts .card-body + .card-body').addClass('show');

                    }
                    else if (this.value == 'table')
                    {
                        $('#js-contacts .card').removeClassPrefix('mb-').addClass('mb-1');
                        $('#js-contacts .col-xl-4').removeClassPrefix('col-xl-').addClass('col-xl-12');
                        $('#js-contacts .js-expand-btn').removeClass('d-none');
                        $('#js-contacts .card-body + .card-body').removeClass('show');
                    }

                });

                //initialize filter
                initApp.listFilter($('#js-contacts'), $('#js-filter-contacts'));
        });

    </script>
</html>
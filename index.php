<?php 

    /* ===
    > DNT SOFTWARE 2023
    === */

    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED);

    $page = $_GET['route'];

    $pages = [
        'login',
        'reg',
        'home',
        'remove-token'
    ];

    $pageOnlyAuth = [
        'home'
    ];

    $pagesNonAuth = [
        'login',
        'reg',
    ];

    include_once('core.php');

    if (!in_array($page, $pages)){
        //showToUser(renderPage('error_no_page'));
        //die;
        header('Location: home');
        die;
    }

    if (!isset($page)){
        header('Location: login');
        die;
    }

    if ($page == 'remove-token') {
        setcookie('_sess', '', -123, '/', $_SERVER['SERVER_NAME']);

        header('Location: login');
    }

    if (!isset($_COOKIE['_sess'])){
        if (in_array($page, $pageOnlyAuth)){
            header('Location: login');
        } else {
            echo renderPage($page);
        }
    } else {
        if (in_array($page, $pagesNonAuth)){
            header('Location: home');
        } else {
            echo renderPage($page);
        }
    }

    # == SERVICE ==

    
    

?>
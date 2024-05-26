<?php 

    /*
    > Functions stack
    */

    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED);

    # Define block

    define('PASS_SALT', '123#01-');
    define('DBNAME', '1c');
    define('DBUSER', 'root');
    define('DBPASS', '');

    function openDB(){
        try {
            $dbh = new PDO("mysql:dbname=1c;host=localhost", DBUSER, DBPASS);

            return $dbh;
        } catch (PDOException $e) {
            file_put_contents('db.txt', $e->getMessage());
            die($e->getMessage());
        }
    }
    
    function handlePass($pass){
        return strrev(md5($pass . PASS_SALT));
    }

    function renderPage($pageName, $isPredefined = true){
        $page = '';
        
        if ($isPredefined) {
            switch ($pageName) {
                case 'login':
                    $page = rewrite([], file_get_contents('patterns/html/login.html'));
                break;

                case 'reg':
                    $page = rewrite([], file_get_contents('patterns/html/reg.html'));
                break;

                case 'home':
                    $page = rewrite([], file_get_contents('patterns/html/main.html'));
                break;

                case 'error_no_page':
                    $page = _renderCustom(['head' => 'Страница не найдена', 'data' => 'Не удалось найти шаблон']);
                break;
            }
        }

        return $page;
    }

    function _renderCustom($data){
        return rewrite([
            'PASTE_OBJ' => json_encode($data)
        ], file_get_contents('patterns/html/error_custom.html'));
    }

    function rewrite($data, $src){
        if (count($data) == 0){
            return $src;
        }
        
        $data = $src;
        
        foreach ($data as $key => $val){
            $data = str_replace("|#{$key}|", $val, $data);
        }

        return $data;
    }

    function showToUser($content){
        echo $content;
    }


?>
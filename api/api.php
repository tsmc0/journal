<?php 

    /* ====================================
    # PRODUCT: ElectronJournal
    # DATE: MAY 2024
    ==================================== */

    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED);

    $action = $_POST['act'];

    # status list

    define('OK', 200);
    define('NOT_FOUND', 404);
    define('FORBIDDEN', 403);
    define('ERROR_IN_DATA', 105);
    define('DB_ERROR', 107);

    $methods = [
        'auth',
        'reg',
        'get-journals',
        'add-journal',
        'collect-journal-data',
        'set-mark',
        'edit-mark',
        'get-all-groups',
        'get-journals-root',
        'get-students-root',
        'add-students-root',
        'get-teachers-root',
        'add-teachers-root',
        'get-groups-root',
        'add-group-root',
        'get-journals-user',
    ];

    if (!in_array($action, $methods)){
        sendRespond(FORBIDDEN, 'Неизвестный метод');
    }

    switch ($action) {
        case 'add-journal':
            $title = $_POST['title'];
            $comm = $_POST['desc'];
            $group = $_POST['grID'];
            $user = $_POST['user'];

            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->prepare('INSERT INTO `journals` (`id`, `name`, `comment`, `date_create`, `groupID`)
                                  VALUES (:id, :sid, :tid, :jid, :gid)');
            $res = $sth->execute(['id' => null, 'sid' => $title, 'tid' => $comm, 'gid' => $group, 'jid' => time()]);

            if ($res){
                $dc = $dbh->prepare('INSERT INTO `journalsUsers` (`id`, `userID`, `journalID`) VALUES (:id, :uid, :jid)');
                $r = $dc->execute(['uid' => $user, 'id' => null, 'jid' => $dbh->lastInsertId()]);

                if ($r) sendRespond(OK, 'Значение сохранено');
            } else {
                sendRespond(DB_ERROR, 'Сбой при вводе данных');
            }
        break;

        case 'get-all-groups':
            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->query('SELECT * FROM `groups`');
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($res) != 0){
                sendRespond(OK, $res);
            } else {
                sendRespond(DB_ERROR, 'NO DATA');
            }

        break;

        case 'set-mark':
            $userID = $_POST['userID'];
            $journal = $_POST['jouID'];
            $group = $_POST['grID'];
            $student = $_POST['stID'];
            $date = $_POST['date'];
            $mark = $_POST['markValue'];

            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->prepare('INSERT INTO `journalsRelations` (`id`, `studentID`, `teacherID`, `journalID`, `groupID`, `date_create`, `mark`)
                                  VALUES (:id, :sid, :tid, :jid, :gid, :dc, :mark)');
            $res = $sth->execute(['id' => null, 'sid' => $student, 'tid' => $userID, 'jid' => $journal, 'gid' => $group, 'dc' => $date, 'mark' => $mark]);
        
            if ($res){
                $dc = $dbh->prepare('UPDATE `journals` SET `date_last_edit` = :ed WHERE `id` = :id');
                $dc->execute(['ed' => time(), 'id' => $journal]);

                sendRespond(OK, 'Значение сохранено');
            } else {
                sendRespond(DB_ERROR, 'Сбой при вводе данных');
            }
        break;

        # ====================================
        
        case 'collect-journal-data':
            $userID = $_POST['userID'];
            $journal = $_POST['jouID'];
            $group = $_POST['grID'];

            if (!checkValues($journal)){
                sendRespond(ERROR_IN_DATA, 'Проверьте данные формы!');
            }

            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->prepare('SELECT * FROM `journals` 
                                         
                                  WHERE `journals`.`id` = :id');
            $sth->execute(['id' => $journal]);
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($res) != 0){
                $endRes = $res;
                
                $sth = $dbh->prepare('SELECT * FROM `journalsStudents` WHERE `group` = :gr');
                $sth->execute(['gr' => $group]);
                $res_st = $sth->fetchAll(PDO::FETCH_ASSOC);

                $endRes += ['students' => $res_st];

                $sth = $dbh->prepare('SELECT * FROM `journalsRelations` WHERE `journalID` = :gr');
                $sth->execute(['gr' => $journal]);
                $res_stx = $sth->fetchAll(PDO::FETCH_ASSOC);

                $endRes += ['marks' => $res_stx];
                
                sendRespond(OK, $endRes);
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================

        case 'get-journals-user':
            $userID = $_POST['userID'];

            if (!checkValues($userID)){
                sendRespond(ERROR_IN_DATA, 'Проверьте данные формы!');
            }

            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->prepare("SELECT * FROM `journalsUsers` 
                                           INNER JOIN `journals` ON `journals`.`id` = `journalsUsers`.`journalID` 
                                           INNER JOIN `groups` ON `journals`.`groupID` = `groups`.`id` ORDER BY `journals`.`date_last_edit` DESC");
            $sth->execute([]);
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($res) != 0){
                sendRespond(OK, $res);
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================

        case 'get-journals':
            $userID = $_POST['userID'];

            if (!checkValues($userID)){
                sendRespond(ERROR_IN_DATA, 'Проверьте данные формы!');
            }

            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->prepare("SELECT * FROM `journalsUsers` 
                                           INNER JOIN `journals` ON `journals`.`id` = `journalsUsers`.`journalID` 
                                           INNER JOIN `groups` ON `journals`.`groupID` = `groups`.`id` 
                                           WHERE `journalsUsers`.`userID` = :uid ORDER BY `journals`.`date_last_edit` DESC");
            $sth->execute(['uid' => $userID]);
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($res) != 0){
                sendRespond(OK, $res);
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================
        
        case 'get-journals':
            $userID = $_POST['userID'];

            if (!checkValues($userID)){
                sendRespond(ERROR_IN_DATA, 'Проверьте данные формы!');
            }

            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->prepare("SELECT * FROM `journalsUsers` 
                                           INNER JOIN `journals` ON `journals`.`id` = `journalsUsers`.`journalID` 
                                           INNER JOIN `groups` ON `journals`.`groupID` = `groups`.`id` 
                                           WHERE `journalsUsers`.`userID` = :uid ORDER BY `journals`.`date_last_edit` DESC");
            $sth->execute(['uid' => $userID]);
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($res) != 0){
                sendRespond(OK, $res);
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================

        case 'get-journals-root':
            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->query("SELECT * FROM `journals` 
                                           INNER JOIN `groups` ON `journals`.`groupID` = `groups`.`id` 
                                           ORDER BY `journals`.`date_last_edit` DESC");
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($res) != 0){
                sendRespond(OK, $res);
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================

        case 'get-groups-root':
            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->query("SELECT * FROM `groups`");
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($res) != 0){
                sendRespond(OK, $res);
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================

        case 'get-students-root':
            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->query("SELECT `journalsStudents`.`id`, `journalsStudents`.`firstName`, `journalsStudents`.`lastName`, `journalsStudents`.`group`, `journalsStudents`.`fatherName`, `groups`.`groupName`, `groups`.`groupNum` FROM `journalsStudents` INNER JOIN `groups` ON `journalsStudents`.`group` = `groups`.`id`");
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($res) != 0){
                sendRespond(OK, $res);
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================

        case 'get-teachers-root':
            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->query("SELECT * FROM `users` WHERE `access_level` = 2");
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($res) != 0){
                sendRespond(OK, $res);
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================

        case 'add-group-root':
            $name = $_POST['name'];
            $number = $_POST['number'];

            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->prepare('INSERT INTO `groups` (`id`, `groupName`, `groupNum`)
                                  VALUES (:id, :fn, :ln)');
            $res = $sth->execute(['id' => null, 'fn' => $name, 'ln' => $number]);

            if ($res){
                sendRespond(OK, 'OK');
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================

        case 'add-students-root':
            $firstName = $_POST['firstname'];
            $lastName = $_POST['lastname'];
            $fatherName = $_POST['fathername'];
            $groupID = $_POST['grid'];

            include_once('../core.php');

            $dbh = openDB();
            $sth = $dbh->prepare('INSERT INTO `journalsStudents` (`id`, `firstName`, `lastName`, `fatherName`, `group`)
                                  VALUES (:id, :fn, :ln, :fnn, :gid)');
            $res = $sth->execute(['id' => null, 'fn' => $firstName, 'ln' => $lastName, 'fnn' => $fatherName, 'gid' => $groupID]);

            if ($res){
                sendRespond(OK, 'OK');
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================

        case 'add-teachers-root':
            $firstName = $_POST['firstname'];
            $lastName = $_POST['lastname'];
            $fatherName = $_POST['fathername'];

            include_once('../core.php');

            $pass = generateRandomString();
            $login = translit($lastName . '_' . $firstName . $fatherName);

            $dbh = openDB();
            $sth = $dbh->prepare('INSERT INTO `users` (`id`, `name`, `lastName`, `fatherName`, `access_level`, `username`, `pass`, `date_create`, `pass_no_code`)
                                  VALUES (:id, :fn, :ln, :fnn, :gid, :uname, :pass, :dc, :pas)');
            $res = $sth->execute(['id' => null, 'fn' => $firstName, 'ln' => $lastName, 'fnn' => $fatherName, 'gid' => 2, 'pass' => handlePass($pass), 'uname' => $login, 'dc' => time(), 'pas' => $pass]);

            if ($res){
                sendRespond(OK, ['login' => $login, 'password' => $pass]);
            } else {
                sendRespond(NOT_FOUND, 'Ничего нет');
            }
        break;

        # ====================================
        
        case 'auth':
            $username = $_POST['username'];
            $userpass = $_POST['userpass'];

            if (!checkValues($username) || !checkValues($userpass)){
                sendRespond(ERROR_IN_DATA, 'Проверьте данные формы!');
            }

            include_once('../core.php');

            $pass_hash = handlePass($userpass);

            $dbh = openDB();
            $sth = $dbh->prepare("SELECT 
                                            `users`.`id`, 
                                            `users`.`name`, 
                                            `users`.`fatherName`, 
                                            `users`.`lastName`, 
                                            `users`.`username`, 
                                            `users`.`access_level`, 
                                            `access_level`.`title`,
                                            `access_level`.`type`
                                    FROM `users` 
                                    INNER JOIN `access_level` ON `access_level`.`id` = `users`.`access_level` WHERE `users`.`username` = :uname AND `users`.`pass` = :pass LIMIT 1");
            $sth->execute(['uname' => $username, 'pass' => $pass_hash]);
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($res) != 0){
                $sessionID = generateRandomString();
                
                $sth = $dbh->prepare("INSERT INTO `sessions` (`id`, `userID`, `session`, `date_create`, `date_exp`, `ip`) VALUES (:id, :uid, :sess, :dc, :de, :ip)");
                $re = $sth->execute(
                    [
                        'id' => null, 
                        'dc' => time(),
                        'de' => 86400 * 7,
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'uid' => $res[0]['id'],
                        'sess' => $sessionID
                    ]
                );
                
                setcookie('_sess', $sessionID, time() * 2, '/', $_SERVER['SERVER_NAME']);
                
                sendRespond(OK, ['sess' => $sessionID, 'userData' => $res[0]]);
            } else {
                $sth = $dbh->prepare("SELECT 
                                            `journalsStudents`.`id`, 
                                            `journalsStudents`.`firstName`, 
                                            `journalsStudents`.`fatherName`, 
                                            `journalsStudents`.`lastName`, 
                                            `journalsStudents`.`login`, 
                                            `journalsStudents`.`access_level`, 
                                            `access_level`.`title`,
                                            `access_level`.`type`
                                    FROM `journalsStudents` 
                                    INNER JOIN `access_level` ON `access_level`.`id` = `journalsStudents`.`access_level` WHERE `journalsStudents`.`login` = :uname AND `journalsStudents`.`password` = :pass LIMIT 1");
                $sth->execute(['uname' => $username, 'pass' => $pass_hash]);
                $res = $sth->fetchAll(PDO::FETCH_ASSOC);

                if (count($res) != 0) {
                    $sessionID = generateRandomString();

                    $sth = $dbh->prepare("INSERT INTO `sessions` (`id`, `userID`, `session`, `date_create`, `date_exp`, `ip`) VALUES (:id, :uid, :sess, :dc, :de, :ip)");
                    $re = $sth->execute(
                        [
                            'id' => null,
                            'dc' => time(),
                            'de' => 86400 * 7,
                            'ip' => $_SERVER['REMOTE_ADDR'],
                            'uid' => $res[0]['id'],
                            'sess' => $sessionID
                        ]
                    );

                    setcookie('_sess', $sessionID, time() * 2, '/', $_SERVER['SERVER_NAME']);

                    sendRespond(OK, ['sess' => $sessionID, 'userData' => $res[0]]);
                } else {
                    sendRespond(NOT_FOUND, 'Пользователь не найден');
                }
            }
        break;

        # ====================================

        case 'reg':
            $username = $_POST['username'];
            $firstName = $_POST['name'];
            $lastName = $_POST['lastName'];
            $fatherName = $_POST['fatherName'];
            $userpass = $_POST['userpass'];

            if (!checkValues($username) || !checkValues($userpass) || !checkValues($firstName) || !checkValues($lastName)){
                sendRespond(ERROR_IN_DATA, 'Проверьте данные формы!');
            }

            include_once('../core.php');

            $pass_hash = handlePass($userpass);

            $dbh = openDB();
            $sth = $dbh->prepare("SELECT * FROM `users` WHERE `username` = :uname");
            $sth->execute(['uname' => $username]);
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            $uid = 0;

            if (count($res) == 0){
                $sth = $dbh->prepare("INSERT INTO `users` 
                                                (`id`, `username`, `pass`, `name`, `lastName`, `fatherName`, `group_id`, `date_create`)
                                      VALUES
                                                (:id, :username, :pass, :name, :ln, :fn, :gid, :dc)
                                    ");
                $r = $sth->execute(
                    [
                        'id' => null, 
                        'username' => $username, 
                        'pass' => $pass_hash, 
                        'name' => $firstName, 
                        'ln' => $lastName, 
                        'fn' => $fatherName, 
                        'gid' => 0,
                        'dc' => time()
                    ]
                );

                if ($r){
                    $sessionID = generateRandomString();
                    $uid = $dbh->lastInsertId();
                    
                    setcookie('_sess', $sessionID, time() * 2, '/', $_SERVER['SERVER_NAME']);

                    $sth = $dbh->prepare("INSERT INTO `sessions` (`id`, `userID`, `session`, `date_create`, `date_exp`, `ip`) VALUES (:id, :uid, :sess, :dc, :de, :ip)");
                    $re = $sth->execute(
                        [
                            'id' => null, 
                            'dc' => time(),
                            'de' => 86400 * 7,
                            'ip' => $_SERVER['REMOTE_ADDR'],
                            'uid' => $uid,
                            'sess' => $sessionID
                        ]
                    );

                    if ($re){
                        $sth = $dbh->prepare("SELECT * FROM `users` WHERE `id` = :id");
                        $sth->execute(['id' => $uid]);
                        $res = $sth->fetchAll(PDO::FETCH_ASSOC);
                        
                        sendRespond(OK, ['sess' => $sessionID, 'userData' => $res[0]]);
                    } else {
                        sendRespond(DB_ERROR, 'Сбой при попытке сохранить данные');
                    }
                } else {
                    sendRespond(DB_ERROR, 'Сбой при попытке сохранить данные');
                }
            } else {
                sendRespond(ERROR_IN_DATA, 'Имя пользователя уже занято');
            }
        break;

        # ====================================
        
        default:
            sendRespond(FORBIDDEN, 'Неизвестный метод');
        break;
    }

    # == service ==

    function sendRespond($code, $data){
        echo json_encode(['status' => $code, 'content' => $data]);
        die;
    }

    function checkValues($val){
        return (empty(ltrim($val))) ? false : true;
    }

    function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrs092u3tuvwxyzaskdhfhf9882323ABCDEFGHIJKLMNksadf9044OPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

function translit($value)
{
    $converter = array(
        'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
        'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
        'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
        'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
        'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
        'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
        'э' => 'e',    'ю' => 'yu',   'я' => 'ya',

        'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
        'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
        'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
        'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
        'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
        'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
        'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
    );

    $value = strtr($value, $converter);
    return $value;
}

?>
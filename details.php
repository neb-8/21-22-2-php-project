<?php
session_start();
require_once('storage.php');
$storage_series = new Storage(new JsonIO('series.json'));
$series = $storage_series -> findAll();
$storage_users = new UserStorage(new JsonIO('users.json'));
$users = $storage_users -> findAll();
$id = null;
if(isset($_GET['id'])){
    $id = $_GET['id'];
}

$sori = $storage_series->findById($id);

function logout(){
    $_SESSION = [];
    session_destroy();
}
$watched_eps = episode_num($users);


function watched(){
    global $users, $sori, $id, $storage_users;
    /*
    foreach($users as $user){
        if($_SESSION['user']['username']==$user['username']){
            foreach($user['watched'] as $this_series => $ep_num){
                if($id==$this_series){
                    $num = (int)$ep_num;
                    $count = count($sori['episodes']);
                    if($num<$count){
                        
                        $updated = $user;
                        $updated['watched'][$id] += 1;
                        $storage_users->update($user['id'],$updated);
                        header("Refresh:0");
                        
                    }
                }
            }
        }
    }
    */
    foreach($users as $user){
        if($_SESSION['user']['username']==$user['username']){
            foreach($user['watched'] as $this_series => $ep_num){
                if($id==$this_series){
                    $num = (int)$ep_num;
                    $count = count($sori['episodes']);
                    if($num<$count){
                        $updated = $user;
                        $updated['watched'][$id]++;
                        $storage_users->update($user['id'],$updated);

                        /*
                        $storage_users->delete($updated['id']);
                        $storage_users->add($updated);
                        */
                        header("Refresh:0");
                    }
                    return;
                }
            }
        }
    }
}

if(isset($_POST)){
    if(array_key_exists('logout', $_POST)) {
        logout();
    }
    if(array_key_exists('watched', $_POST)) {
        watched();
    }
}

$admin = false;
$logged_in = false;
if(isset($_SESSION['user'])){
    if(isset($_SESSION['user']['roles'])){
        $admin = true;
    }
    else{
        $logged_in = true;
    }
}

function episode_num(){
    if(!isset($_SESSION['user'])||$_SESSION['user']['username']=='admin'){
        return "";
    }
    global $users, $sori, $storage_users;
    foreach($users as $user){
        if($_SESSION['user']['username']==$user['username']){
            if(!in_array($sori['id'],$user['watched'])){
                //hozzáadás
                $updated = $user;
                $updated['watched'] += [$sori['id'] => 0];
                $storage_users->update($user['id'],$updated);
            }
            foreach($user['watched'] as $this_series => $ep_num){
                if($sori['id']==$this_series){
                    return $ep_num;
                }
            }
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en" class="details">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title> <?= $sori['title'] ?> </title>
</head>
<body>
    <?php if($admin) echo "<h1 class='user'>admin</h1>"; ?>
    <?php if($logged_in) echo "<h1 class='user'>" . $_SESSION['user']['username'] . "</h1>" ?>
    <div class="header">
        <h1><?= $sori['title'] ?></h1>
        <h2>(<?= count($sori['episodes']) ?> ep)</h2>
        <?php if($admin) echo "<h2>admin</h2>"; ?>
        <?php if($admin) : ?>
            <td>
                <a href="addep.php?id=<?= $sori['id'] ?>">új rész</a>
            </td>
        <?php endif ?>
        <?php if($logged_in) : ?>
            <h2>
                <?= $_SESSION['user']['username'] ?>
                // megtekintett részek száma: <?= $watched_eps ?>
            </h2>

            <form method="post" novalidate>
                <button type="submit" name="watched">láttam a következőt</button>
            </form>
        <?php endif ?>
    </div>
    <table id="nav">
        <tr>
            <td><a href="index.php">főoldal</a></td>
            <td><a href="login.php"><?= ($logged_in||$admin) ? "átjelentkezés" : "bejelentkezés" ?></a></td>
            <td><a href="register.php">regisztráció</a></td>
            <?php if($logged_in||$admin) : ?>
                <td>
                    <form method="post" novalidate>
                        <button type="submit" name="logout">kilépek</button>
                    </form>
                </td>
            <?php endif ?>
        </tr>
    </table>
    <table class="content">
        <tr style="background-color:yellow" height="100px">
            <td colspan="3" style="width:40%">
                kiadás/sugárzás éve: <?= $sori['year'] ?>
            </td>
            <td colspan="2" rowspan="2">
                <img src="<?= $sori['cover'] ?>" alt="<?= $sori['title'] ?> képe" class="cover">
            </td>
        </tr>
        <tr style="background-color:yellow" height="100px">
            <td colspan="3" style="width:40%">
            a történek röviden: <?= $sori['plot'] ?>
            </td>
        </tr>
        <?php foreach($sori['episodes'] as $episodes) : ?>
            <tr>
                <td style="background-color:lime">
                    <?= $episodes['title'] ?>
                </td>
                <td style="background-color:#BEBEBE">
                    <?= $episodes['date'] ?>
                </td>
                <td style="background-color:#BEBEBE">
                    <?= $episodes['rating'] ?>
                </td>
                <td style="background-color:#BEBEBE">
                    <?= $episodes['plot'] ?>
                </td>
                <?php if($logged_in) : ?>
                    <td style="background-color:grey;text-align:center;"> <?= ($episodes['id']<=$watched_eps) ? "✔" : "❌" ?> </td>
                <?php endif ?>
            </tr>
        <?php endforeach ?>
    </table>
</body>
</html>
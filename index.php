<?php
session_start();
require_once('storage.php');
$storage = new Storage(new JsonIO('series.json'));
$series = $storage -> findAll();

function logout(){
    $_SESSION = [];
    session_destroy();
}

if(isset($_POST)){
    if(array_key_exists('logout', $_POST)) {
        logout();
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


?>

<!DOCTYPE html>
<html lang="en" class="index">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Főoldal</title>
</head>
<body>
    <?php if($admin) echo "<h1 class='user'>admin</h1>"; ?>
    <?php if($logged_in) echo "<h1 class='user'>" . $_SESSION['user']['username'] . "</h1>" ?>
    <div class="header">
        <nobr>
            <h1>Hal mondja hali az ájemdibin</h1>
            <?php if($admin) echo "<h2>admin</h2>"; ?>
            <?php if($logged_in) echo "<h2>" . $_SESSION['user']['username'] . "</h2>" ?>
            <?php if($admin) : ?>
                <form action="addseries.php" novalidate>
                    <button type="submit">új sorozat hozzáadása</button>
                </form>
            <?php endif ?>
        </nobr>
    </div>
    <table id="nav">
        <tr>
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
    <?php if($logged_in) : ?>
        <table class="content">
            <tr>
                <th style="text-align:left">megnézett/megkezdett sorozatok:</th>
            </tr>
            <?php foreach($_SESSION['user']['watched'] as $id => $eps) : ?>
                <tr>
                    <td><?= $storage->findById($id)['title'] ?></td>
                </tr>
            <?php endforeach ?>
        </table>
    <?php endif ?>
    <table class="content">
        <?php foreach($series as $sori) : ?>
            <tr>
                <td>
                    <a href="details.php?id=<?= $sori['id'] ?>"><?= $sori['title'] ?></a>
                </td>
                <td>
                    (<?= count($sori['episodes']) ?> ep)
                </td>
                <td>
                    utolsó sugárzás dátuma: <?= (count($sori['episodes'])!=0) ? end($sori['episodes'])['date'] : "" ?>
                </td>
                <?php if($admin) : ?>
                    <td>
                        <a href="addep.php?id=<?= $sori['id'] ?>">új rész</a>
                    </td>
                <?php endif ?>
                <td>
                    <img src="<?= $sori['cover'] ?>" style="height:200px;"/>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</body>
</html>
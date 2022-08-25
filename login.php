<?php
include('storage.php');

// functions
function redirect($page) {
  header("Location: ${page}");
  exit();
}
function validate($post, &$data, &$errors) {
  
  if(!isset($post['username'])){
    $errors['username'] = 'Hiányzik a felhasználónév';
  }else if(strlen(trim($post['username']))==0){
    $errors['username'] = 'Hiányzik a felhasználónév';
  }

  if(!isset($post['password'])){
    $errors['password'] = 'Hiányzik a jelszó';
  }else if(strlen(trim($post['password']))==0){
    $errors['password'] = 'Hiányzik a jelszó';
  }

  $data = $post;

  return count($errors) === 0;
}
function check_user($user_storage, $username, $password) {
  $users = $user_storage->findMany(function ($user) use ($username, $password) {
    return $user["username"] === $username && 
           password_verify($password, $user["password"]);
  });
  return count($users) === 1 ? array_shift($users) : NULL;
}
function login($user) {
  $_SESSION["user"] = $user;
}

// main
session_start();
$user_storage = new UserStorage();
$data = [];
$errors = [];
if ($_POST) {
  if (validate($_POST, $data, $errors)) {
    $logged_in_user = check_user($user_storage, $data['username'], $data['password']);
    if (!$logged_in_user) {
      $errors['global'] = "Hibás adatok";
    } else {
      login($logged_in_user);
      redirect('index.php');
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en" class="log">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Bejelentkezés</title>
</head>
<body>
<?php if (isset($errors['global'])) : ?>
  <p><span class="error"><?= $errors['global'] ?></span></p>
<?php endif; ?>
<div id="nav">
  <a href="register.php">regisztráció</a>
  <a href="index.php">főoldal</a>
</div>
<form action="" method="post" novalidate>
  <div>
    <label for="username">Felhasználónév: </label><br>
    <input type="text" name="username" id="username" value="<?= $_POST['username'] ?? "" ?>">
    <?php if (isset($errors['username'])) : ?>
      <span class="error"><?= $errors['username'] ?></span>
    <?php endif; ?>
  </div>
  <div>
    <label for="password">Jelszó: </label><br>
    <input type="password" name="password" id="password">
    <?php if (isset($errors['password'])) : ?>
      <span class="error"><?= $errors['password'] ?></span>
    <?php endif; ?>
  </div>
  <div>
    <button type="submit">Bejelentkezés</button>
  </div>
</form>
</body>
</html>
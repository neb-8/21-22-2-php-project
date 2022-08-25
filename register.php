<?php
function redirect($page){
    header("Location: ${page}");
    exit();
}

include('storage.php');

// functions
function validate($post, &$data, &$errors) {
  
  if(!isset($post['username'])){
    $errors['username'] = 'Hiányzik a felhasználónév';
  }else if(strlen(trim($post['username']))<3){
    $errors['username'] = 'A felhasználónév túl rövid';
  }

  if(!isset($post['email'])){
    $errors['email'] = 'Hiányzik az email cím';
  }else if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)){
    $errors['email'] = 'Nem valid email cím';
  }

  if(!isset($post['password'])){
    $errors['password'] = 'Hiányzik a jelszó';
  }else if(strlen(trim($post['password']))<3){
    $errors['password'] = 'A jelszó minimum három karakter kell legyen';
  }

  if(!isset($post['password2'])){
    $errors['password2'] = 'Ismételje meg a jelszót';
  }else if($post['password2']!=$post['password']){
    $errors['password2'] = 'Hibás a megadott jelszó';
  }

  $data = $post;
  return count($errors) === 0;
}


function user_exists($user_storage, $username) {
  $users = $user_storage->findOne(['username' => $username]);
  return !is_null($users);
}
function add_user($user_storage, $data) {
  $user = [
    'username'  => $data['username'],
    'password'  => password_hash($data['password'], PASSWORD_DEFAULT),
    'email'  => $data['email'],
    'watched' => []
  ];
  return $user_storage->add($user);
}

// main
$user_storage = new UserStorage();
$errors = [];
$data = [];
if (count($_POST) > 0) {
  if (validate($_POST, $data, $errors)) {
    if (user_exists($user_storage, $data['username'])) {
      $errors['global'] = "Már létező felhasználónév";
    } else {
      add_user($user_storage, $data);
      redirect('login.php');
    } 
  }
}
?>

<!DOCTYPE html>
<html lang="en" class="reg">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Regisztráció</title>
</head>
<body>
<?php if (isset($errors['global'])) : ?>
  <p><span class="error"><?= $errors['global'] ?></span></p>
<?php endif; ?>
<div id="nav">
  <a href="login.php">bejelentkezés</a>
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
    <label for="email">Email cím: </label><br>
    <input type="email" name="email" id="email">
    <?php if (isset($errors['email'])) : ?>
      <span class="error"><?= $errors['email'] ?></span>
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
    <label for="password2">Jelszó újra: </label><br>
    <input type="password" name="password2" id="password2">
    <?php if (isset($errors['password2'])) : ?>
      <span class="error"><?= $errors['password2'] ?></span>
    <?php endif; ?>
  </div>
  <div>
    <button type="submit">Regisztráció</button>
  </div>
</form>
</body>
</html>
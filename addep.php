<?php
function redirect($page){
    header("Location: ${page}");
    exit();
}

include('storage.php');

// functions
function validate($post, &$data, &$errors) {
  
  if(!isset($post['title'])){
    $errors['title'] = 'Hiányzik a rész címe';
  }else if(strlen(trim($post['title']))==0){
    $errors['title'] = 'A cím túl rövid';
  }

  if(!isset($post['date'])){
    $errors['date'] = 'Hiányzik a sugárzási év';
  }else if($post['date']<1900||$post['date']>2022){
    $errors['date'] = 'Hibás év';
  }

  if(!isset($post['rating'])){
    $errors['rating'] = 'Hiányzik az értékelés';
  }else if(!is_numeric($post['rating'])){
    $errors['rating'] = 'Nem szám a megadott értékelés';
  }else if($post['rating']<0||$post['rating']>10){
    $errors['rating'] = 'Hibás értékelés';
  }

  if(!isset($post['plot'])){
    $errors['plot'] = 'Hiányzik a rövid leírás';
  }else if(strlen(trim($post['plot']))<10){
    $errors['plot'] = 'Túl rövid a leírás';
  }

  $data = $post;
  return count($errors) === 0;
}


function ep_exists($storage, $title) {
  $series = $storage->findAll();
  foreach($series as $sori){
    if($sori['id']==$_GET['id']){
        foreach($sori['episodes'] as $ep){
            if($ep['title']==$title){
                return true;
            }
        }
    }
  }
  return false;
}
function add_ep($storage, $data) {
  if(!isset($_GET['id'])){
    return;
  }
  $seriesid = $_GET['id'];

  $updated = $storage->findById($seriesid);
  $new_ep = [
    "id" => (count($updated['episodes'])+1),
    "date" => $data['date'],
    "title" => $data['title'],
    "plot" => $data['plot'],
    "rating" => $data['rating']
  ];
  $updated['episodes'] += [ (count($updated['episodes'])+1) => $new_ep];

  return $storage->update($seriesid,$updated);
}

session_start();
$storage = new Storage(new JsonIO('series.json'));
$errors = [];
$data = [];
if (count($_POST) > 0) {
  if (validate($_POST, $data, $errors)) {
    if (ep_exists($storage, $data['title'])) {
      $errors['global'] = "Már létező rész";
    } else {
      add_ep($storage, $data);
      redirect('index.php');
    } 
  }
}
?>
<!DOCTYPE html>
<html lang="en" class="newep">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Új rész</title>
</head>
<body>
<?php if (isset($errors['global'])) : ?>
  <p><span class="error"><?= $errors['global'] ?></span></p>
<?php endif; ?>
<a href="index.php">főoldal</a>
<form action="" method="post" novalidate>
  <div>
    <label for="title">Rész címe: </label><br>
    <input type="text" name="title" id="title" value="<?= $_POST['title'] ?? "" ?>">
    <?php if (isset($errors['title'])) : ?>
      <span class="error"><?= $errors['title'] ?></span>
    <?php endif; ?>
  </div>

  <div>
    <label for="date">Sugárzás éve: </label><br>
    <input type="date" name="date" id="date" value="<?= $_POST['date'] ?? "" ?>">
    <?php if (isset($errors['date'])) : ?>
      <span class="error"><?= $errors['date'] ?></span>
    <?php endif; ?>
  </div>

  <div>
    <label for="rating">Értékelése: </label><br>
    <input type="number" step="0.1" name="rating" id="rating" value="<?= $_POST['rating'] ?? "" ?>">
    <?php if (isset($errors['rating'])) : ?>
      <span class="error"><?= $errors['rating'] ?></span>
    <?php endif; ?>
  </div>

  <div>
    <label for="plot">A története röviden: </label><br>
    <textarea name="plot" id="plot" rows="10" cols="30"><?= $_POST['plot'] ?? "" ?></textarea>
    <?php if (isset($errors['plot'])) : ?>
      <span class="error"><?= $errors['plot'] ?></span>
    <?php endif; ?>
  </div>
  
  <div>
    <button type="submit">Felvisz</button>
  </div>
</form>
</body>
</html>
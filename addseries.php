<?php
function redirect($page){
    header("Location: ${page}");
    exit();
}

include('storage.php');

// functions
function validate($post, &$data, &$errors) {
  
  if(!isset($post['title'])){
    $errors['title'] = 'Hiányzik a sorozat neve';
  }else if(strlen(trim($post['title']))==0){
    $errors['title'] = 'A név túl rövid';
  }

  if(!isset($post['year'])){
    $errors['year'] = 'Hiányzik a megjelenési év';
  }else if(!is_numeric($post['year'])){
    $errors['year'] = 'Nem szám a megadott év';
  }else if($post['year']<1900||$post['year']>2022){
    $errors['year'] = 'Hibás év';
  }

  if(!isset($post['cover'])){
    $errors['cover'] = 'Hiányzik a borítókép';
  }else if(!filter_var($post['cover'], FILTER_VALIDATE_URL)){
    $errors['cover'] = 'Nem valid link';
  }

  if(!isset($post['plot'])){
    $errors['plot'] = 'Hiányzik a rövid leírás';
  }else if(strlen(trim($post['plot']))<10){
    $errors['plot'] = 'túl rövid a leírás';
  }

  $data = $post;
  return count($errors) === 0;
}


function series_exists($storage, $title) {
  $series = $storage->findOne(['title' => $title]);
  return !is_null($series);
}
function add_series($storage, $data) {
  $new_series = [
    'id' => (count($storage->findAll())+1),
    'title'  => $data['title'],
    'year'  => $data['year'],
    'plot' => $data['plot'],
    'cover' => $data['cover'],
    'episodes' => []
  ];
  return $storage->add($new_series);
}

session_start();
$storage = new Storage(new JsonIO('series.json'));
$errors = [];
$data = [];
if (count($_POST) > 0) {
  if (validate($_POST, $data, $errors)) {
    if (series_exists($storage, $data['title'])) {
      $errors['global'] = "Már létező sorozat";
    } else {
      add_series($storage, $data);
      redirect('index.php');
    } 
  }
}
?>
<!DOCTYPE html>
<html lang="en" class="newser">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Új sorozat</title>
</head>
<body>
<?php if (isset($errors['global'])) : ?>
  <p><span class="error"><?= $errors['global'] ?></span></p>
<?php endif; ?>
<a href="index.php">főoldal</a>
<form action="" method="post" novalidate>
  <div>
    <label for="title">Sorozat neve: </label><br>
    <input type="text" name="title" id="title" value="<?= $_POST['title'] ?? "" ?>">
    <?php if (isset($errors['title'])) : ?>
      <span class="error"><?= $errors['title'] ?></span>
    <?php endif; ?>
  </div>

  <div>
    <label for="year">Megjelenésének éve: </label><br>
    <input type="number" name="year" id="year" value="<?= $_POST['year'] ?? "" ?>">
    <?php if (isset($errors['year'])) : ?>
      <span class="error"><?= $errors['year'] ?></span>
    <?php endif; ?>
  </div>

  <div>
    <label for="cover">Sorozat borítóképe: </label><br>
    <input type="text" name="cover" id="cover" value="<?= $_POST['cover'] ?? "" ?>">
    <?php if (isset($errors['cover'])) : ?>
      <span class="error"><?= $errors['cover'] ?></span>
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
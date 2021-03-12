<?php 
    session_start();
    // logout logic
    if(isset($_GET['action']) and $_GET['action'] == 'logout'){
        session_start();
        unset($_SESSION['username']);
        unset($_SESSION['password']);
        unset($_SESSION['logged_in']);
    }
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css">

    <title>PHP FileSystem Browser</title>
  </head>
  <body>

  <?php
    $msg = '';
    if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) { 
        if ($_POST['username'] == 'Vardas' && 
          $_POST['password'] == '12345'
        ) {
          $_SESSION['logged_in'] = true;
          $_SESSION['timeout'] = time();
          $_SESSION['username'] = 'Vardas';
        } else {
          $msg = '<div class="alert alert-danger" role="alert">Wrong username or password</div>';
        }
    }
  ?>     

  <?php if($_SESSION['logged_in'] != true): ?>   
  <div class="container">
    <div class="row align-items-center">
      <div class="col">
        <h2>Login</h2>
        <?php echo $msg; ?>
        <form action="./index.php" method="post">
              <div class="col-4 mb-3 ">
            <input type="text" name="username" class="form-control" placeholder="Your name? = Vardas" required autofocus>
              </div>
              <div class="col-4 mb-3 ">
            <input type="password" name="password" class="form-control" placeholder="12345" required>
              </div>
              <button type="submit" name="login" class="btn btn-primary">Login</button>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
  

<?php if($_SESSION['logged_in'] == true): ?>

<div class="container-fluid">
  <div class="row">
      <div class="col-12">
      <h1>Directory contents: <?php echo $_SERVER['REQUEST_URI']; ?></h1>

      <?php if(isset($_POST['submit']) && $_POST['name'] != ''): ?>     
        <?php $name = $_POST['name']; ?>
        <?php if($name == true): mkdir($name); endif;?>
      <?php endif; ?>

      <?php if(isset($_POST['submit']) && $_POST['delefile'] != ''): ?>
          <?php $file = $_POST['delefile']; ?>
          <?php unlink($file); ?>
      <?php endif; ?>

      <?php
        if(isset($_GET['download'])) {
          $file = './' . $_GET['download'];
          $fileToDownloadEscaped = str_replace("&nbsp;", " ", htmlentities($file, null, 'uft-8'));
          // ob_clean();
          // ob_start();
          header('Content-Description: File Tra');
          header('Content-Type:' . mime_content_type($fileToDownloadEscaped));
          header('Content-Disposition: attachment; filename=' . basename($fileToDownloadEscaped));
          header('Content-Transfer-Encoding: binary');
          header('Expires: 0');
          header('Cache-Control: must-revalidate, post-check=0, prie-check=0');
          header('Pragma: public');
          header('Content-Length: ' . filesize($fileToDownloadEscaped));
          // ob_end_flush();
          readfile($fileToDownloadEscaped);
          exit;
        }
      ?>

      <?php
        if(isset($_FILES['image'])) {
          $errors= array();
          $file_name = $_FILES['image']['name'];
          $file_size = $_FILES['image']['size'];
          $file_tmp = $_FILES['image']['tmp_name']; 
          $file_type = $_FILES['image']['type'];

          $file_ext = strtolower(end(explode('.', $_FILES['image']['name'])));
          $extensions = array("jpeg","jpg","png");
          if(in_array($file_ext, $extensions) === false) {
            $errors[] = 'Extension not allowed, please choose a JPEG or PNG file';
          }
          if($file_size > 2097152) {
            $errors[] = 'File size must be smaller than 2 MB';
          }
          if(empty($errors) == true) {
            move_uploaded_file($file_tmp, "./" . $path .  $file_name);
          } else {
            echo '<div class="alert alert-danger" role="alert">' . $errors[0] . '</div>';
          }
        }
      ?>

      <?php
      global $dir_path;
      $projectCatalog = str_replace('/index.php', '', $_SERVER["REQUEST_URI"]);
      if (isset($_GET["directory"])) {
          $dir_path = $_GET["directory"];
      } else {
          $dir_path = $_SERVER["DOCUMENT_ROOT"]. "/" . $projectCatalog . "/"; 
      }

      $directories = array_diff(scandir($dir_path), array('..', '.')); 
      ?>
      <table class="table table-striped table-hover">
        <thead style="background-color: red; color:white;">
          <tr>
            <th scope="col">Type</th>
            <th scope="col">Name</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($directories as $entry): ?>
            <?php if (is_dir($dir_path . "/" . $entry)) : ?>   
              <tr>
                <td><i class='bi bi-folder'></i> Directory</td>
                <td>
                  <a href="?directory=<?php echo "" . $dir_path . "" . $entry . "/" ?>">
                    <?php echo $entry; ?>
                  </a>
                </td> 
                <td></td>
              </tr>     
            <?php else: ?>
              <tr>
                <td><i class='bi bi-file-earmark-text'></i> File</td>
                <td><?php echo $entry; ?></td>
                <td>
                  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="hidden" name="delefile" value="<?php echo $dir_path . $entry; ?>">
                    <button type="submit" name="submit" class="btn btn-outline-danger btn-sm" value="<?php echo $dir_path . $entry; ?>">
                      <i class='bi bi-trash'></i>
                    </button>
                    <a href="?download=<?php echo $entry; ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-arrow-down"></i></a>
                  </form>
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
  </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-1">
            <button type="button" class="btn btn-outline-primary btn-md" onclick="window.history.back()">Back</button>
        </div>    
        <div class="col-3">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">    
            <input type="name" name="name" class="form-control" id="name" placeholder="New directory name">
        </div>
        <div class="col-2">
            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
        </div>
        </form>
        <div class="col-6">
          <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" class="row g-2">
            <div class="col-auto"><input class="form-control" type="file" id="formFile" name="image"></div>
            <div class="col-auto"><button type="submit" name="submit" class="btn btn-primary">Upload</button></div>
          </form>
        </div>
        <br>
        <br>
        <br>
    </div>
  </div>

<div class="container-fluid">
  <div class="row">
    <div class="col">Click here to <a href = "index.php?action=logout" class="btn btn-primary btn-sm">logout</a></div>
  </div>
</div>

<?php endif; ?>
    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
  </body>
</html>
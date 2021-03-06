<?php

/**
* Sync the files of a Git repository with the Web server
*
* @author Ignacio de Tomás <nacho@inacho.es>
* @copyright 2013 Ignacio de Tomás (http://inacho.es)
*/


/* CONFIG
--------------------------------------------- */

require_once('credentials.php');

define('LOGIN_ENABLED', true);

define('SCRIPT_PATH_DEPLOY', 'scripts/git-local-deploy.sh');
define('SCRIPT_PATH_STATUS', 'scripts/git-local-status.sh');
define('SCRIPT_PATH_CHECKOUT', 'scripts/git-local-checkout.sh');


/* SCRIPT
--------------------------------------------- */

header('Content-Type: text/html; charset=UTF-8');

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('SELF_URL', $_SERVER['SCRIPT_NAME']);

session_start();

function isValidUser()
{
  if (! LOGIN_ENABLED) {
    return true;
  }

  if (isset($_SESSION['validUser']) && $_SESSION['validUser'] == 1) {
    return true;
  }

  return false;
}

function setValidUser($valid)
{
  if ($valid) {
    $_SESSION['validUser'] = 1;
  } else {
    unset($_SESSION['validUser']);
  }
}

function setMsg($name, $message)
{
  $_SESSION[$name] = $message;
}

function getMsg($name)
{
  if (isset($_SESSION[$name])) {
    $res = $_SESSION[$name];
    unset($_SESSION[$name]);
    return $res;
  }

  return '';
}


$action = isset($_GET['action']) ? $_GET['action'] : '';

/* BRANCHES
--------------------------------------------- */
exec('echo "$(git --git-dir ../.git ls-remote --heads)" 2>&1', $branches);
exec('echo "$(git --git-dir ../.git rev-parse --abbrev-ref HEAD)" 2>&1', $local_branch);
$local_branch = $local_branch[0];
$branch_select = array();

foreach ($branches as $branch) {
  $branch = explode('refs/heads/', $branch);
  $branch_select[] = $branch[1];
}

/* ACTION
--------------------------------------------- */
switch ($action) {
  case 'login':
    if (! isValidUser() && ! empty($_POST)) {
      if ($_POST['user'] == USER && $_POST['pass'] == PASS) {
        setValidUser(true);
      } else {
        setMsg('loginFailed', 'Invalid username or password');
      }
      header('Location: ' . SELF_URL);
      exit;
    }
  break;

  case 'logout':
    if (isValidUser()) {
      setValidUser(false);
      header('Location: ' . SELF_URL);
      exit;
    }
  break;

  case 'deploy':
    if (isValidUser()) {
      exec(SCRIPT_PATH_DEPLOY . ' 2>&1', $execResult);
      if (! empty($execResult)) {
        $execResult = implode("\n", $execResult);
        setMsg('execResult', $execResult);
      }
    }
    header('Location: ' . SELF_URL);
    exit;
  break;

  case 'status':
    if (isValidUser()) {
      exec(SCRIPT_PATH_STATUS . ' 2>&1', $execResult);
      if (! empty($execResult)) {
        $execResult = implode("\n", $execResult);
        setMsg('execResult', $execResult);
      }
    }
    header('Location: ' . SELF_URL);
    exit;
  break;

  case 'log':
    if (isValidUser()) {
      exec('echo "$(git --git-dir ../.git log -3 --format=medium)" 2>&1', $execResult);
      if (! empty($execResult)) {
        $execResult = "Latest commit deployed in this server:\n\n" . implode("\n", $execResult);
        setMsg('execResult', $execResult);
      }
    }
    header('Location: ' . SELF_URL);
    exit;
  break;

  case 'checkout':
    if (isValidUser()) {
      $branch = $_POST['branch'];
      exec(SCRIPT_PATH_CHECKOUT . " {$branch} 2>&1", $execResult);
      if (! empty($execResult)) {
        $execResult = implode("\n", $execResult);
        setMsg('execResult', $execResult);
      }
    }
    header('Location: ' . SELF_URL);
    exit;
  break;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Deploy to <?php echo $_SERVER['SERVER_NAME'] ?></title>

  <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2lpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wUmlnaHRzPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvcmlnaHRzLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcFJpZ2h0czpNYXJrZWQ9IkZhbHNlIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjMyQzA0MEQ0RDU1RDExRTA5QjRBRjk5NzJEQzkzRjBGIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjMyQzA0MEQzRDU1RDExRTA5QjRBRjk5NzJEQzkzRjBGIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDUzMgV2luZG93cyI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ1dWlkOkFDMUYyRTgzMzI0QURGMTFBQUI4QzUzOTBEODVCNUIzIiBzdFJlZjpkb2N1bWVudElEPSJ1dWlkOkM5RDM0OTY2NEEzQ0REMTFCMDhBQkJCQ0ZGMTcyMTU2Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+ZLCPdwAAAphJREFUeNqkU81LG1EQn83uS/xqNtm1dcFKvLheSj/oofj9gRexi7WoEG8eS6EgufXQPyBQemmPUqGH3kSIKD2I4setFaRSyQo1xmq1Vks3MWs2m2xnVlOj9NaBH++9mdl5s7/fPM5xHPgfE3p6ngFjDARBgIqKiqJfqqu78UiS/O10OD42Fnd2fkzRtlAouAm2bbsQSqvl83ngeV5qbb39StOawoois5MTC5LJw5GlpdWOlZXPY1TkUgelh1wuB3jzQF9fUzgUUhj5fD4GluWwlpZ7Ybw9mE5njo+OjMVE4rvbkVDacnV1oL22tvqWJIns9NT+W5jnORBFPxsd7dOIst3dw5GZmZWO+flPYwL+h9TVdR9b7ggHAiJLpUygj/P5C3LJZ9t57NABjgOoqZHdjjY2vi4KihIc6O5+EA4Gg8yyLDBNE/b3c3hjFXg8PBYzIZ024UwtB8rLfZiTxa48rKqqrFmorPS1ZTIZtrWVvCSPYRjg9/vdtWiY565ULJVKZbe3v60Jk5MfXhvGb/mqvg0NIXVwUFOJ2Lm5ZX1zc1svxhynkN3b+0lEvhVQuo/T0wtaMUjakvX2tsaGh/tVmpH19bg+O7vs5tC8FA1/+0JGGhDioLGxPqKq9Z0IFWcCvF4v0B5TYrqeWIjHEy/J5/F4zhTy+W66t9LHZM3Nd59Hoy8eynJAJg44pF1RrstDQ/3q6uqahUXe08BRAZpcnhIs6xdRhOBA13e/lJVx3Z2dbTLFqGVRFGF8/J0+MTH1BPMOAPZQ5h1UZ+tfz6MSUX8nEonGdf3QIdCefGexy8aV7HnENeIGIQI2rmmPn1IgFpt8A7B/QDN1/hZIW/tqAWLFiyinJ3B+LjWapCzCPF/dZ/lHgAEAIrYgy57KIPYAAAAASUVORK5CYII=" />

  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />

  <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet" />
  <!--[if lt IE 9]>
  <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->

  <style>
  .form-signin {
    max-width: 330px;
    padding: 15px;
    margin: 0 auto;
  }
  .form-signin .form-signin-heading,
  .form-signin .checkbox {
    margin-bottom: 10px;
  }
  .form-signin .checkbox {
    font-weight: normal;
  }
  .form-signin .form-control {
    position: relative;
    font-size: 16px;
    height: auto;
    padding: 10px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
  }
  .form-signin .form-control:focus {
    z-index: 2;
  }
  .form-signin input[type="text"] {
    margin-bottom: -1px;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
  }
  .form-signin input[type="password"] {
    margin-bottom: 10px;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
  }
  </style>

</head>
<body>
  <?php if(isValidUser()): ?>

    <div class="container">
      <div class="page-header">
        <h1>
          <img src="assets/logo.png" alt="logo"/>
          Git-deploy script</h1>
      </div>
      <p class="lead">
        Script de sincronització del codi del servidor amb el repositori Git
      </p>

      <h3>Funcionalitats</h3>
      <ul>
        <li><b>Run deploy</b> - Desplega la darrera versió de la branca per defecte</li>
        <li><b>Git status</b> - Estat de la versió local del codi</li>
        <li><b>Git log</b> - Missatges de registre de la versió local del codi</li>
        <li><b>Checkout</b> - Missatges de registre de la branca seleccionada</li>
      </ul>
      <hr />
      <p>
        <a href="<?php echo SELF_URL ?>?action=deploy" class="btn btn-success btn-md"><span class="glyphicon glyphicon-cloud-download"></span> Run deploy</a>
        <a href="<?php echo SELF_URL ?>?action=status" class="btn btn-primary btn-md"><span class="glyphicon glyphicon-list"></span> Git status</a>
        <a href="<?php echo SELF_URL ?>?action=log" class="btn btn-primary btn-md"><span class="glyphicon glyphicon-question-sign"></span> Git log</a>
        <?php if (LOGIN_ENABLED): ?>
          <a href="<?php echo SELF_URL ?>?action=logout" class="btn btn-danger btn-md"><span class="glyphicon glyphicon-log-out"></span> Surt</a>
        <?php endif ?>
        <form action="<?php echo SELF_URL ?>?action=checkout" method="post">
          <select name="branch">
            <?php
              foreach ($branch_select as $branch) {
                $selected = ($branch == $local_branch) ? 'selected' : '';
                echo "<option value='{$branch}' {$selected}>{$branch}</option>";
              }
            ?>
          </select>
          <button type="submit" class="btn btn-warning btn-md"><span class="glyphicon glyphicon-cloud-download"></span> Checkout</button>
        </form>
      </p>

      <?php if ($result = getMsg('execResult')): ?>
        <h3>Missatge d'execució</h3>
        <pre><?php echo htmlentities($result, ENT_COMPAT, 'utf-8') ?></pre>
      <?php endif ?>
    </div>

  <?php else: ?>

    <div class="container">
      <form action="<?php echo SELF_URL ?>?action=login" method="post" class="form-signin">
        <?php if ($loginFailed = getMsg('loginFailed')): ?>
          <div class="alert alert-danger"><?php echo $loginFailed ?></div>
        <?php endif ?>

        <h2 class="form-signin-heading">Formulari d'entrada</h2>
        <input type="text" name="user" class="form-control" placeholder="Username" autofocus />
        <input type="password" name="pass" class="form-control" placeholder="Password" />
        <button class="btn btn-lg btn-primary btn-block" type="submit"><span class="glyphicon glyphicon-log-in"></span> Entra</button>
      </form>
    </div> <!-- /container -->

  <?php endif ?>
</body>
</html>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
    ?>
    <div class="message error">
        <?= $errors ?>
    </div> <?php
}

// Handling success messages.
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
    ?>
    <div class="message success">
        <?= $success ?>
    </div> <?php
}

var_dump($_SESSION['user']);
?>
<nav>
    <ul>
        <li><a href="/index.php?c=home&a=index">Home</a></li>
        <li><a href="/index.php?c=user&a=registerPage">register</a></li>
        <li><a href="/index.php?c=article&a=articleForm">ajout article</a></li>
        <li><a href="/index.php?c=user&a=loginPage">Connexion</a></li>
    </ul>

</nav>
    <main class="container">
        <?= $html ?>
    </main>
    <script src="/assets/js/app.js"></script>
</body>
</html>
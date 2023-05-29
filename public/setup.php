<?php

/**
 * Usually the user lands on this page only if Composer autoload failed
 * Double check if this is really the case, if not redirect back to home
 * If this is the case display a static page telling the user to run composer
 */

//Try to include autoload without displaying error messages
error_reporting(E_ERROR);
try {
    require_once 'vendor/qrest/autoload.php';
    header('Location: home');
} catch (Error $e) {
    $check_composer = false;
    $composer_message = $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Qrest</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/halfmoon/css/halfmoon.min.css" />
    <link rel="stylesheet" href="css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/halfmoon@1.1.1/js/halfmoon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="js/main.js"></script>
</head>

<body class="dark-mode">
    <div class="page-wrapper">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-8 offset-md-2 pt-20">


                    <h1 class="mt-20 pt-20 mb-20 pb-20">Installation incomplete</h1>
                    <p>Dependencies have not been installed. To install the dependencies manually, execute <strong class="badge">composer install</strong> in the root folder.</p>

                    <table class="table table-bordered">
                        <tr>
                            <th>Details</th>
                        </tr>
                        <tr class="table-danger">
                            <td><?php echo ($composer_message); ?></td>
                        </tr>
                    </table>

                    <p>Refer to the installation manual in the <a href="https://github.com/nick-van-h/qrest">GitHub repository</a>.</p>

                </div>
            </div>
        </div>
</body>

</html>
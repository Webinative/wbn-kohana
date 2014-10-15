<?php
defined('SYSPATH') or die('No direct access');

// meta description
$escaped_meta_desc = 'WBN Kohana Framework!';
if (isset($meta_desc)):
    $escaped_meta_desc = htmlspecialchars($meta_desc);
endif;

// title
$escaped_title = 'Webinative Technologies - Kohana framework';
if (isset($title)):
    $escaped_title = htmlspecialchars($title);
endif;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="description" content="<?php echo $escaped_meta_desc; ?>"/>

        <!-- Cache control
        <meta http-equiv="Cache-Control" content="no-cache,no-store,must-revalidate"/>
        <meta http-equiv="Pragma" content="no-cache"/>
        <meta http-equiv="Expires" content="0"/>
        -->

        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

        <title><?php echo $escaped_title; ?></title>

        <!-- jQuery -->
        <script type="text/javascript" src="/js/jquery/jquery-1.11.0.min.js" ></script>
        <script type="text/javascript" src="/js/jquery/jquery-ui-1.10.4.min.js"></script>

        <!-- Bootstrap -->
        <link rel="stylesheet" href="/js/plugins/bootstrap-3.2.0/css/bootstrap.min.css">
        <!-- Optional theme -->
        <link rel="stylesheet" href="/js/plugins/bootstrap-3.2.0/css/bootstrap-theme.min.css">
        <script src="/js/plugins/bootstrap-3.2.0/js/bootstrap.min.js"></script>

        <link rel="stylesheet" type="text/css" media="screen" href="/css/template/default.css"/>

        <?php
        if (isset($stylesheets) AND is_array($stylesheets)):
            Helper_Media::load_css($stylesheets);
        endif;
        ?>
    </head>
    <body>
        <div class="container">
            <header></header>
        </div>
        <div class="clearfix"></div>

        <div id="view-content">
            <?php if (isset($content)) echo $content; ?>
        </div>

        <footer class="container">
            Copyrights &copy; 2011-<?php echo date('Y'); ?> Magesh Ravi
        </footer>
    </body>
</html>

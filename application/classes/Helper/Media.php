<?php defined('SYSPATH') or die('No direct access');

/**
 * Description of Stylesheet
 *
 * @author mageshravi
 */
class Helper_Media {
    
    public static function load_css(array $stylesheets) {
        foreach($stylesheets as $stylesheet) {
            $css_dir = DIRECTORY_SEPARATOR. 'css' . DIRECTORY_SEPARATOR;
            $file_path = $css_dir . $stylesheet .'.css';
            ?>

    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $file_path; ?>">

            <?php
        }
    }
}

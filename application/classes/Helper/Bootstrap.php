<?php

/**
 * Description of Bootstrap
 *
 * @author mageshravi
 */
class Helper_Bootstrap {

    public static function text_input_tag($_attrs) {
        $label = (isset($_attrs['label'])) ? $_attrs['label'] : null;
        $id = (isset($_attrs['id'])) ? $_attrs['id'] : null;
        $class = (isset($_attrs['class'])) ? $_attrs['class'] : null;
        $has_error = false;
        if (isset($_attrs['error'])) {
            $has_error = true;
            $label = $_attrs['error'];
        }

        $arr_processed_attrs = array('label', 'id', 'class', 'error');
        ?>

        <div class="form-group <?php if($has_error) echo "has-error"; ?>">
            <label for="<?php echo $id; ?>" class="control-label"><?php echo $label; ?></label>
            <input 
            <?php
            foreach ($_attrs as $attr => $value):
                if (!in_array($attr, $arr_processed_attrs)):
                    echo " $attr='$value' ";
                endif;
            endforeach;
            ?>
                class="form-control <?php echo $class; ?>">
        </div>

        <?php
    }
}

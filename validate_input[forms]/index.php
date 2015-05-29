<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 5/28/15
 * Time: 8:29 AM
 */

echo '<h1>Testing Class Pos_Validator </h1>' ;

require_once 'class/autoload.php';

$missing = null;
$errors = null;
$required = array('name','email','comments');
if (filter_has_var(INPUT_POST, 'send')) {
try {
    $val = new Pos_Validator($required);
    $val->checkTextLength('name', 3);
    $val->removeTags('name');
    $val->isEmail('email');
    $val->checkTextLength('comments', 10, 500);
    $val->useEntities('comments');
    $filtered = $val->getFiltered();
    $missing = $val->getMissing();
    $errors = $val->getErrors();
} catch (Exception $e){
    echo $e->getMessage();
}
}
?>
<?php
if ($missing) {
    echo '<div> The following required fields have not been filld in:';
    echo '<ul>';
    foreach ($missing as $field) {
        echo "<li>$field</li>";
    }
    echo '</ul></div>';
}

?>
<form id="form1" name="form1" method="post" action="">
<p>
    <label for="name"><strong>Name:</strong>
        <?php
        if (isset($errors['name'])) {
        echo $errors['name'] .' <br>';
        }
        ?>
    </label>
    <input name="name" type="text" class="textfield" id="name" />
</p>
<p>
    <label for="email"><strong>Email:</strong>
        <?php
        if (isset($errors['email'])) {
            echo $errors['email'] .' <br>';
        }
        ?>
    </label>
    <input name="email" type="text" class="textfield" id="email" /></p>
<p>
    <label for="comments"><strong> Comments:</strong>
        <?php
        if (isset($errors['comments'])) {
            echo $errors['comments'] .' <br>';
        }
        ?>
    </label>
    <textarea name="comments" id="comments" cols="45" rows="5"></textarea>
</p>
<p><input type="submit" name="send" id="send" value="Send comments" /></p>
</form>

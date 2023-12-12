<?php

$number_product = 0;
$is_block = 'none';

if(isset($_POST['SubmitButton'])){

    $number_product = sanitize_title( (int)$_POST["number_product"] );
    $product_type = sanitize_title($_POST["product-type"]);

    if( $number_product > 0 ){
        $number_product = insert_fake_post( $number_product, $product_type );
        $is_block = 'block';
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Make Dummy Products</title>
    <style>
    </style>
</head>
<body>

<div class="formHolder">
    <form action="#" method="post" class="form-container">
        <div class=" textfieldContainer">
            <span class="productattrText"> How Many Product Do You Want To Create? </span> <br>
            <input type="number" name="number_product" value="2">
        </div>
        <br>
        <div class="textfieldContainer">
            <span class="productattrText">Select product Type</span><br>
            <select id="product-type" name="product-type">
                <optgroup label="Product Type">
                    <option value="simple" selected="selected">Simple product</option>
                    <option value="grouped">Grouped product</option>
                    <option value="external">External/Affiliate product</option>
                    <option value="variable">Variable product</option>
                </optgroup>
            </select>
        </div><br>
        <div class="btnHolder">
            <input class="generateProducts" type="submit" value="Create Products" name="SubmitButton">
        </div>
    </form>
    <div class="successfulmessage" style="display: <?php echo $is_block?>">
        <span class="successfulmessagetext"> <?php echo $number_product.' Products Successfully Created '; ?> </span>
    </div>

</div>

</body>
</html>

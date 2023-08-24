<?php
require "functions/functions.php";

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
        .formHolder{
            display: block;
            /*float: left;*/
            position: relative;
            padding: 20px 20px;
            width: 300px;
            margin: auto;
        }
        .textfieldContainer{
            display: block;
            float: left;
            width: 100%;
            margin: 10px;
        }
        .productattrText{
            display: block;
            float: left;
            font-size: 15px;
            color: #aa0000;
            padding-bottom: 8px;
        }
        .successfulmessage{
            display: block;
            float: left;
            width: calc( 100% - 20px );
            padding: 10px 10px;
        }
        .successfulmessagetext{
            font-size: 18px;
            color: #1daf1d;
        }
        .successfulerrormessagetext{
            font-size: 18px;
            color: #aa0000;
        }
        .btnHolder{
            display: block;
            float: left;
            width: 100%;
        }
        .generateProducts{
            display: block;
            /*float: left;*/
            position: relative;
            padding: 8px 10px;
            border: 1px solid #d4c7c7;
            border-radius: 3px;
            cursor: pointer;
            margin: auto;
        }
        .generateProducts:hover{
            background-color: #0c71d0;
            color: #FFFFFF;
        }
    </style>
</head>
<body>

<div class="formHolder">
    <form action="#" method="post" >

        <div class=" textfieldContainer">
            <span class="productattrText"> How Many Product Do You Want To Create? </span> <br>
            <input type="number" name="number_product" value="2">
        </div>
        <br>
        <div class="textfieldContainer">
            <span class="productattrText"> Product Type mention</span><br>
            <select id="product-type" name="product-type">
                <optgroup label="Product Type">
                    <option value="simple" selected="selected">Simple product</option>
                    <option value="grouped">Grouped product</option>
                    <option value="external">External/Affiliate product</option>
                    <option value="variable">Variable product</option>
                </optgroup>
            </select>
        </div><br>

        <!--    <input type="number" name="qty"><br>-->
        <div class="btnHolder">
            <input class="generateProducts" type="submit" value="Created Products" name="SubmitButton">
        </div>

    </form>

<!--    --><?php //if( $number_product > 0 ){?>
    <div class="successfulmessage " style="display: <?php echo $is_block?>">
        <span class="successfulmessagetext"> <?php echo $number_product.' Product Is Successfully Created '; ?> </span>
    </div><!--
    <?php /*} else {*/?>
        <div class="successfulmessage ">
            <span class="successfulerrormessagetext"> Please Provide Number of Product Do You Create  </span>
        </div>
    --><?php /*}*/?>
</div>




</body>
</html>

<?php

include "../../boot.php";

$engine = new Alchemy\Component\UI\Engine(new Alchemy\Component\UI\ReaderFactory(), new Alchemy\Component\UI\Parser);
$data = empty($_POST) ? array() : $_POST;

$engine->setMetaFile("form1.yaml");
$engine->setTargetBundle("bootstrap");
$form = $engine->build($data);

?>

<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

<div class="container">
<h1><?php echo $form->title?></h1>

<form role="form"
      class="col-sm-8 blog-main"
      id="<?php echo $form->getId()?>"<?php foreach($form->getAttributes() as $k=>$v) echo " ",$k,'="',$v,'"'?>>

    <?php foreach ($form->getItems() as $item) { ?>
    <div class="form-group">
        <label for="<?php echo $item->getId()?>'"><?php echo $item->getFieldLabel()?></label> :
        <?php echo $item->getGenerated('html')?>
    </div>
    <?php } ?>
    <?php foreach ($form->getButtons() as $button) { ?>
    <?php if ($button->getType() == "link") { ?>
    <a href="<?php echo $button->getUrl()?>" target="<?php echo $button->getTarget()?>"><?php echo $button->getLabel()?></a>
    <?php } else { ?>
    <button type="<?php echo $button->getType()?>"
        class="btn <?php if($button->getType()=="submit") echo "btn-primary"; else echo "btn-default"?>">
        <?php echo $button->getLabel()?>
    </button>
    <?php }} ?>
</form>
</div>

<?php
if (! empty($_POST)) {
    echo "<pre>";
    echo "POST DATA" . PHP_EOL;
    print_r($_POST);
}

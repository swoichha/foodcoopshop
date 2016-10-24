<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta name="theme-color" content="#719f41">

<title><?php echo $title_for_layout; ?> - <?php echo Configure::read('app.titleSuffix'); ?></title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    
    <?php echo $this->element('jsNamespace'); ?>
    <link
	href='http://fonts.googleapis.com/css?family=Open+Sans:400,700'
	rel='stylesheet' type='text/css'>
    
    <?php
    echo $this->element('renderCss', array(
        'config' => 'admin'
    ));
    ?>
    
</head>
<body
	class="<?php echo Inflector::tableize($this->name); ?> <?php echo Inflector::singularize(Inflector::tableize($this->action)); ?>">

	<div id="container">
        
        <?php echo $this->element('menu'); ?>
        
        <div id="content">
    	    <?php echo $this->Session->flash(); ?>
            <?php echo $this->fetch('content'); ?>
        </div>
	</div>
    
    <?php echo $this->element('scrollToTopButton'); ?>
    
    <div class="sc"></div>
	<div class="is-mobile-detector"></div>
    <?php echo $this->element('sql_dump'); ?>
    
<?php

echo $this->element('renderJs', array(
    'config' => 'admin'
));

echo $this->Html->script('vendor/ckeditor/ckeditor');
echo $this->Html->script('vendor/ckeditor/adapters/jquery');

echo $this->fetch('script'); // all scripts from layouts
?>


</body>
</html>
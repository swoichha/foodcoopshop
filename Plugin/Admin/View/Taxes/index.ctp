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
<div id="taxes">

     <?php
    $this->element('addScript', array(
        'script' => Configure::read('app.jsNamespace') . ".Admin.init();"
    ));
    $this->element('highlightRowAfterEdit', array(
        'rowIdPrefix' => '#tax-'
    ));
    ?>
   
    <div class="filter-container">
		<h1><?php echo $title_for_layout; ?></h1>
		<div class="right">
            <?php
            echo '<div id="add-tax-button-wrapper" class="add-button-wrapper">';
            echo $this->Html->link('<i class="fa fa-plus-square fa-lg"></i> Neue Steuersatz erstellen', $this->Slug->getTaxAdd(), array(
                'class' => 'btn btn-default',
                'escape' => false
            ));
            echo '</div>';
            ?>
        </div>

	</div>

	<div id="help-container">
		<ul>
			<li>Auf dieser Seite kannst du Steuersätze verwalten.</li>
		</ul>
	</div>    
    
<?php

echo '<table class="list">';
echo '<tr class="sort">';
echo '<th class="hide">' . $this->Paginator->sort('Tax.id_tax', 'ID') . '</th>';
echo '<th></th>';
echo '<th>' . $this->Paginator->sort('Tax.rate', 'Steuersatz') . '</th>';
echo '<th>' . $this->Paginator->sort('Tax.active', 'Aktiv') . '</th>';
echo '</tr>';

$i = 0;

foreach ($taxes as $tax) {
    $i ++;
    $rowClass = array(
        'data'
    );
    if (! $tax['Tax']['active']) {
        $rowClass[] = 'deactivated';
    }
    echo '<tr id="tax-' . $tax['Tax']['id_tax'] . '" class="' . implode(' ', $rowClass) . '">';
    
    echo '<td class="hide">';
    echo $tax['Tax']['id_tax'];
    echo '</td>';
    
    echo '<td>';
    echo $this->Html->getJqueryUiIcon($this->Html->image('/js/vendor/famfamfam-silk/dist/png/page_edit.png'), array(
        'title' => 'Bearbeiten'
    ), $this->Slug->getTaxEdit($tax['Tax']['id_tax']));
    echo '</td>';
    
    echo '<td>';
    echo $this->Html->formatAsPercent($tax['Tax']['rate']);
    echo '</td>';
    
    echo '<td align="center">';
    if ($tax['Tax']['active'] == 1) {
        echo $this->Html->image('/js/vendor/famfamfam-silk/dist/png/accept.png');
    } else {
        echo $this->Html->image('/js/vendor/famfamfam-silk/dist/png/delete.png');
    }
    echo '</td>';
    
    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="4"><b>' . $i . '</b> Datensätze</td>';
echo '</tr>';

echo '</table>';

?>    
</div>
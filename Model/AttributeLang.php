<?php
/**
 * AttributeLang
 *
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
class AttributeLang extends AppModel
{

    public $useTable = 'attribute_lang';
    public $primaryKey = 'id_attribute';

    public $hasOne = array(
        'Attribute' => array(
            'foreignKey' => 'id_attribute'
        )
    );

    public $validate = array(
        'name' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib einen Namen an.'
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'Eine Variante mit dem Namen existiert bereits.'
            )
        )
    );

    public function getForDropdown()
    {
        $this->recursive = 2;
        $attributes = $this->find('all', array(
            'order' => array(
                'AttributeLang.name' => 'ASC'
            )
        ));
        
        $attributesForDropdown = array();
        foreach ($attributes as $attribute) {
            $attributesForDropdown[$attribute['Attribute']['id_attribute']] = $attribute['AttributeLang']['name'];
        }
        
        return $attributesForDropdown;
    }
}

?>
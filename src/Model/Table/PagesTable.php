<?php

namespace App\Model\Table;
use Cake\Core\Configure;
use Cake\Validation\Validator;

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
class PagesTable extends AppTable
{
    
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_page');
        $this->addBehavior('Tree', [
            'parent' => 'id_parent'
        ]);
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('title', 'Bitte gib einen Titel an.');
        $validator->minLength('title', 2, 'Bitte gib mindestens 3 Zeichen ein.');
        $validator->range('position', [-1, 1001], 'Bitte gibt eine Zahl von 0 bis 1000 an.');
        $validator->urlWithProtocol('extern_url', 'Bitte gibt eine gültige Internet-Adresse an.');
        $validator->allowEmpty('extern_url');
        return $validator;
    }

    public function findAllGroupedByMenu($conditions)
    {
        $pages = $this->find('threaded', [
            'parentField' => 'id_parent',
            'conditions' => $conditions,
            'order' => [
                'Pages.menu_type' => 'DESC',
                'Pages.position' => 'ASC',
                'Pages.title' => 'ASC'
            ],
            'contain' => [
                'Customers'
            ]
        ]);
        return $pages;
    }

    public function getMainPagesForDropdown($pageIdToExcluce = null)
    {
        $conditions = [
            'Pages.id_parent IS NULL',
            'Pages.active > ' . APP_DEL
        ];
        if ($pageIdToExcluce > 0) {
            $conditions[] = 'Pages.id_page != ' . $pageIdToExcluce;
        }
        $pages = $this->find('all', [
            'conditions' => $conditions,
            'order' => [
                'Pages.menu_type' => 'DESC',
                'Pages.position' => 'ASC',
                'Pages.title' => 'ASC'
            ]
        ]);

        $preparedPages = [];
        foreach ($pages as $page) {
            $preparedPages[$page->id_page] = $page->title . ' - ' . Configure::read('app.htmlHelper')->getMenuType($page->menu_type);
        }
        return $preparedPages;
    }
}

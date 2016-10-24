<?php

App::uses('FrontendController', 'Controller');

/**
 * PagesController
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
class PagesController extends FrontendController
{

    public function home()
    {
        
        /**
         * START: security keys check
         */
        $showKeyGeneratorWebsite = 0;
        $securityErrors = 0;
        if (Configure::read('app.cookieKey') == '') {
            echo '<p>Please copy this <b>app.cookieKey</b> to your config.custom.php: '.StringComponent::createRandomString(58).'</p>';
            $securityErrors++;
        }
        if (Configure::read('Security.salt') == '') {
            echo '<p>Please generate the <b>Security.salt</b> and copy it to your config.custom.php (not to your core.php)</p>';
            $securityErrors++;
            $showKeyGeneratorWebsite = 1;
        }
        if (Configure::read('Security.cipherSeed') == '') {
            echo '<p>Please generate the <b>Security.cipherSeed</b> and copy it to your config.custom.php (not to your core.php)</p>';
            $securityErrors++;
            $showKeyGeneratorWebsite = 1;
        }
        if ($showKeyGeneratorWebsite) {
            echo '<p>Security.salt and Security.sipherSeed can be generated on this website: <a target="_blank" href="http://cakephp.thomasv.nl/">http://cakephp.thomasv.nl</a></p>';
        }
        if ($securityErrors > 0) {
            die('<p><b>Security errors: '.$securityErrors.'</b></p>');
        }
        /**
         * END: security keys check
         */
        
        $this->loadModel('BlogPost');
        $blogPosts = $this->BlogPost->findFeatured($this->AppAuth);
        $this->set('blogPosts', $blogPosts);
        
        $this->set('title_for_layout', 'Home');
        
        $this->loadModel('Slider');
        $sliders = $this->Slider->getForHome();
        $this->set('sliders', $sliders);
    }

    public function detail()
    {
        $pageId = (int) $this->params['pass'][0];
        
        $page = $this->Page->getPageForFrontend($pageId);
        if (empty($page)) {
            throw new MissingActionException('page not found');
        }
        
        $children = $this->Page->children($pageId, false, null, array(
            'Page.position' => 'ASC'
        ));
        $page['children'] = array();
        foreach ($children as $child) {
            $page['children'][] = $this->Page->getPageForFrontend($child['Page']['id_cms']);
        }
        
        $correctSlug = Configure::read('slugHelper')->getPageDetail($page['Page']['id_cms'], $page['PageLang']['meta_title']);
        if ($correctSlug != Configure::read('slugHelper')->getPageDetail($pageId, StringComponent::removeIdFromSlug($this->params['pass'][0]))) {
            $this->redirect($correctSlug);
        }
        
        $this->set('page', $page);
        $this->set('title_for_layout', $page['PageLang']['meta_title']);
    }
}
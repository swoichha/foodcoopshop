<?php

App::uses('CakeEmail', 'Network/Email');
App::uses('EmailLog', 'Model');

/**
 * AppEmail
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
class AppEmail extends CakeEmail
{

    public function __construct($config = null)
    {
        parent::__construct('default');

        if (Configure::read('app.db_config_FCS_BACKUP_EMAIL_ADDRESS_BCC') != '') {
            $this->addBcc(Configure::read('app.db_config_FCS_BACKUP_EMAIL_ADDRESS_BCC'));
        }
    }

    /**
     * declaring this method public enables rendering an email (for preview)
     * {@inheritDoc}
     * @see CakeEmail::_renderTemplates()
     */
    public function _renderTemplates($content)
    {
        return parent::_renderTemplates($content);
    }

    public function logEmailInDatabase($success)
    {
        $emailLogModel = new EmailLog();
        $email2save = array(
            'from_address' => json_encode($this->from()),
            'to_address' => json_encode($this->to()),
            'cc_address' => json_encode($this->cc()),
            'bcc_address' => json_encode($this->bcc()),
            'subject' => $this->subject(),
            'headers' => $success['headers'],
            'message' => $success['message']
        );
        $emailLogModel->id = null;
        return $emailLogModel->save($email2save);
    }

    /**
     * fallback if email config is wrong (e.g.
     * password changed from third party)
     *
     * @see CakeEmail::send()
     */
    public function send($content = null)
    {
        try {
            $success = parent::send($content);
            if (Configure::read('app.db_config_FCS_EMAIL_LOG_ENABLED')) {
                $this->logEmailInDatabase($success);
            }
            return $success;
        } catch (Exception $e) {
            if (Configure::read('app.emailErrorLoggingEnabled')) {
                CakePlugin::load('EmailLog', array(
                    'bootstrap' => true
                ));
            }
            CakeLog::write('error', $e->getMessage());

            if (Configure::check('fallbackEmailConfig')) {
                $fallbackEmailConfig = Configure::read('fallbackEmailConfig');
                $originalFrom = $this->from();

                // resend the email with the fallbackEmailConfig
                // avoid endless loops if this email also not works
                if ($this->from() != $fallbackEmailConfig['from']) {
                    $this->config($fallbackEmailConfig);
                    $this->from(array(
                        key($this->from()) => Configure::read('app.db_config_FCS_APP_NAME')
                    ));
                    CakeLog::write('info', 'email was sent with fallback config');
                    return $this->send($content);
                }
            } else {
                throw $e;
            }
        }
    }
}

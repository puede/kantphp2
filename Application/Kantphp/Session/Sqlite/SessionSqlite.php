<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 - 2013 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Session\Sqlite;

use Kant\Session\Sqlite\SessionSqliteModel;
use Kant\Secure\Crypt\Crypt_AES;

class SessionSqlite {

    protected $sidpre = 'sess_';
    //Session setting: gc_maxlifetime, auth_key;
    private $_setting;
    //Session Model
    protected $model;

    public function __construct($setting) {
        $this->_setting = $setting;
        require_once KANT_PATH . 'Session/Sqlite/SessionSqliteModel.php';
        $this->model = new SessionSqliteModel();
        self::_setSessionModule();
    }

    /**
     * Set Session Module
     */
    private function _setSessionModule() {
        session_module_name('user');
        session_set_save_handler(
                array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc')
        );
        register_shutdown_function('session_write_close');
        session_start();
    }

    public function open() {
        return true;
    }

    public function close() {
        $maxlifetime = $this->_setting['maxlifetime'] ? $this->_setting['maxlifetime'] : ini_get('session.gc_maxlifetime');
        return self::gc($maxlifetime);
    }

    /**
     * READ SESSION
     * 
     * @param string $sid
     * @return string
     */
    public function read($sid) {
        $sessionid = $this->sidpre . $sid;
        $row = $this->model->readSession($sessionid);
        if ($row) {
            $row = $row[0];
            $crypt = new Crypt_AES();
            $crypt->setKey($this->_setting['auth_key']);
            $secure_data = $row['data'];
            //BASE64 decode, AES decrypt
            $data = $crypt->decrypt(base64_decode($secure_data));
            return $data;
        }
    }

    /**
     * Write Session
     * 
     * @param string $sid
     * @param string $data
     * @return boolean
     */
    public function write($sid, $data) {
        $sessionid = $this->sidpre . $sid;
        $crypt = new Crypt_AES();
        $crypt->setKey($this->_setting['auth_key']);
        //AES encrypt, BASE64 encode
        $secure_data = base64_encode($crypt->encrypt($data));
        $exist = $this->model->readSession($sessionid);
        if (!$exist) {
            $row = $this->model->saveSession(array(
                'sessionid' => $sessionid,
                'data' => $secure_data,
                'lastvisit' => time(),
                'ip' => get_client_ip()
            ));
        } else {
            $row = $this->model->saveSession(array(
                'data' => $secure_data,
                'lastvisit' => time(),
                'ip' => get_client_ip()
                    ), $sessionid);
        }

        return $row;
    }

    public function destroy($sid) {
        $sessionid = $this->sidpre . $sid;
        $this->model->readSession($sessionid);
        return true;
    }

    public function gc($maxlifetime) {
        $expiretime = time() - $maxlifetime;
        $this->model->deleteExpire($expiretime);
        return true;
    }

}

?>

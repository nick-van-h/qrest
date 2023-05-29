<?php

namespace Qrest\Controllers;

// use Qrest\Controllers\Twig;
use Qrest\Models\Db;
use \Qrest\Models\Session;
use \Qrest\Util\Crypt;

class Collections
{
    private $db;
    // private $twig;

    public function __construct()
    {
        $this->db = new Db();
        // $this->twig = new Twig();
    }

    public function add($collectionName)
    {
        $uid = bin2hex(random_bytes(8));
        $iv = Crypt::generateRandomIV($collectionName);
        $collectionName_enc = Crypt::encryptWithKey($collectionName, $iv);
        $collectionType_enc = Crypt::encryptWithKey("FooBar", $iv);
        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $sortOrder = $this->db->getValue('collections', 'max(sortOrder)') + 1;

        $data = array('userId' => $userId, 'uid' => $uid, 'sortOrder' => $sortOrder, 'name_enc' => $collectionName_enc, 'type_enc' => $collectionType_enc, 'iv' => $iv);
        $id = $this->db->insert('collections', $data);

        return $uid;
    }

    public function update($collectionUid, $collectionName)
    {

        $userId = Session::getUserId();

        //Get IV & encrypt name
        $this->db->where('userId', $userId);
        $this->db->where('uid', $collectionUid);
        $iv = $this->db->getValue('collections', 'iv');
        $collectionName_enc = Crypt::encryptWithKey($collectionName, $iv);

        //Store encrypted name
        $this->db->where('userId', $userId);
        $this->db->where('uid', $collectionUid);
        $data = array(
            'name_enc' => $collectionName_enc
        );
        $this->db->update('collections', $data);
        return true;
    }

    public function updateOrder($collectionUid, $newSortOrder)
    {

        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $collectionUid);
        $data = array(
            'sortOrder' => $newSortOrder
        );
        $this->db->update('collections', $data);
        return true;
    }

    public function getName($collectionUid)
    {
        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $collectionUid);
        $res = $this->db->getValue('collections', 'name_enc,iv');
        $name = Crypt::decryptWithKey($res['name_enc'], $res['iv']);
        return $name;
    }

    public function getSortOrder($collectionUid)
    {
        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $collectionUid);
        $res = $this->db->getValue('collections', 'sortOrder');
        return $res;
    }

    public function getNrItems($collectionUid)
    {

        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('collectionUid', $collectionUid);
        $res = $this->db->getValue('items', 'count(*)');
        // echo ('_' . $res . '_');
        return $res;
    }

    public function delete($collectionUid)
    {


        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $collectionUid);
        $res = $this->db->delete('collections');
    }

    public function getAll($ascDesc = 'ASC')
    {
        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->orderBy("sortOrder", $ascDesc);
        $res = $this->db->get('collections');
        $args = [];
        $li = [];
        foreach ($res as $item) {
            $args[] = array(
                'id' => $item['id'],
                'uiserid' => $item['userId'],
                'uid' => $item['uid'],
                'name' => Crypt::decryptWithKey($item['name_enc'], $item['iv']),
                'type' => Crypt::decryptWithKey($item['type_enc'], $item['iv']),
                'sortOrder' => $item['sortOrder'],
            );
        }

        return $args;
    }

    public function uidExists($uid)
    {

        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $uid);
        $res = $this->db->getValue('collections', 'uid');

        return ($res == $uid);
    }
}

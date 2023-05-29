<?php

namespace Qrest\Controllers;

use Qrest\Models\Db;
use \Qrest\Models\Session;
use \Qrest\Util\Crypt;

class Items
{
    private $db;

    public function __construct()
    {
        $this->db = new Db();
    }

    public function add($itemName, $collectionUid)
    {
        $uid = bin2hex(random_bytes(8));
        $iv = Crypt::generateRandomIV($itemName);
        $name_enc = Crypt::encryptWithKey($itemName, $iv);
        $checked_enc = Crypt::encryptWithKey(0, $iv);
        $userId = Session::getUserId();

        $this->db->where('userId', $userId);
        $sortOrder = $this->db->getValue('items', 'max(sortOrder)') + 1;

        $data = array('userId' => $userId, 'collectionUid' => $collectionUid, 'uid' => $uid, 'sortOrder' => $sortOrder, 'name_enc' => $name_enc, 'checked_enc' => $checked_enc, 'iv' => $iv);
        $id = $this->db->insert('items', $data);

        return $uid;
    }

    public function update($itemName, $itemChecked, $itemUid)
    {
        $userId = Session::getUserId();

        //Get IV and re-encrypt item name
        $this->db->where('userId', $userId);
        $this->db->where('uid', $itemUid);
        $iv = $this->db->getValue('items', 'iv');
        $name_enc = Crypt::encryptWithKey($itemName, $iv);
        $checked_enc = Crypt::encryptWithKey($itemChecked, $iv);

        //Store new item in database
        $this->db->where('userId', $userId);
        $this->db->where('uid', $itemUid);
        $data = array(
            'name_enc' => $name_enc,
            'checked_enc' => $checked_enc
        );
        $this->db->update('items', $data);
        // throw new \Error("updated item " . $itemUid . ' to ' . $itemName);
        return true;
    }

    public function updateOrder($itemUid, $newSortOrder)
    {

        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $itemUid);
        $data = array(
            'sortOrder' => $newSortOrder
        );
        $this->db->update('items', $data);
        return true;
    }

    public function updateCollection($itemUid, $newCollectionUid)
    {
        $userId = Session::getUserId();
        //Get new sort order first (move to top of the list)
        $this->db->where('userId', $userId);
        $sortOrder = $this->db->getValue('items', 'max(sortOrder)') + 1;

        //Update the record
        $this->db->where('userId', $userId);
        $this->db->where('uid', $itemUid);
        $data = array(
            'collectionUid' => $newCollectionUid,
            'sortOrder' => $sortOrder
        );
        $this->db->update('items', $data);
        return true;
    }

    public function getAll($collectionUid = '', $ascDesc = 'DESC')
    {
        $userId = Session::getUserId();
        //Get list name

        if (isset($collectionUid) && !empty($collectionUid)) {
            $this->db->where('userId', $userId);
            $this->db->where('uid', $collectionUid);
            $res = $this->db->getOne('collections', 'name_enc,iv');
            if ($res) {
                $listName = Crypt::decryptWithKey($res['name_enc'], $res['iv']);
            } else {
                $listName = "No list exists with ID " . $collectionUid;
            }
        } else {
            $listName = "All items";
        }

        //Get items
        $this->db->where('userId', $userId);
        if (isset($collectionUid) && !empty($collectionUid))
            $this->db->where('collectionUid', $collectionUid);

        $this->db->orderBy("sortOrder", $ascDesc);
        $res = $this->db->get('items');
        $res_dec = [];
        foreach ($res as $item) {
            $res_dec[] = array(
                'id' => $item['id'],
                'userId' => $item['userId'],
                'collectionUid' => $item['collectionUid'],
                'uid' => $item['uid'],
                'name' => Crypt::decryptWithKey($item['name_enc'], $item['iv']),
                'checked' => Crypt::decryptWithKey($item['checked_enc'], $item['iv']),
                'sortOrder' => $item['sortOrder']
            );
        }
        return array('listName' => $listName, 'items' => $res_dec);
    }


    public function uidExists($uid, $collectionUid = '')
    {

        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $uid);
        if ($collectionUid != '')
            $this->db->where('collectionUid', $uid);
        $res = $this->db->getValue('items', 'uid');

        return ($res == $uid);
    }

    public function delete($itemUid)
    {
        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $itemUid);
        $res = $this->db->delete('items');

        return $res;
    }

    public function deleteAllOnCollection($collectionUid)
    {

        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('collectionUid', $collectionUid);
        $res = $this->db->delete('items');

        return $res;
    }

    public function getName($uid)
    {
        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $uid);
        $res = $this->db->getOne('items', 'name_enc,iv');

        if ($res) {
            $name = Crypt::decryptWithKey($res['name_enc'], $res['iv']);
        } else {
            $name = '';
        }
        return $name;
    }

    public function getDetails($uid)
    {

        $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $uid);
        $res = $this->db->getOne('items', 'note_enc,iv');

        if (!$res)
            throw new \Exception("No items exists with ID " . $uid);

        $note = Crypt::decryptWithKey($res['note_enc'], $res['iv']);
        if (!$note) $note = '';
        $details = ['note' => $note, "iv" => $res['iv'], "userid" =>  $userId, "uid" =>  $uid, "note_enc" => $res['note_enc']];
        return $details;
    }

    public function updateNote($uid, $note, $iv = '')
    {

        $userId = Session::getUserId();

        //Get IV if not passed
        if ($iv == '') {
            $this->db->where('userId', $userId);
            $this->db->where('uid', $uid);
            // $iv = $this->db->get('items');
            $iv = $this->db->getValue('items', 'iv');
        }

        if ($iv == '') throw new \Error('No IV returned by DB for item UID ' . $uid);

        //Encrypt the note
        $note_enc = Crypt::encryptWithKey($note, $iv);
        $note_dec = Crypt::decryptWithKey($note, $iv);

        //Store the encrypted note
        // $userId = Session::getUserId();
        $this->db->where('userId', $userId);
        $this->db->where('uid', $uid);
        $data = array(
            'note_enc' => $note_enc,
        );
        $this->db->update('items', $data);

        return ["iv" => $iv, "userid" =>  $userId, "uid" =>  $uid, "note_enc" => $note_enc, "note_dec" => $note_dec];
    }
}

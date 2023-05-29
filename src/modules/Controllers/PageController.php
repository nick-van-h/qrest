<?php

namespace Qrest\Controllers;

use Qrest\Controllers\User;
use Qrest\Controllers\Collections;
use Qrest\Controllers\Items;
use Qrest\Controllers\Twig;

use App\Models\Product;
use Qrest\Models\Setup;
use Symfony\Component\Routing\RouteCollection;

class PageController
{
    private $loader;
    private $twig;
    private $user;
    private $collections;
    private $items;
    private $setup;


    public function __construct()
    {
        $this->twig = new Twig();
        $this->user = new User();
        $this->collections = new Collections();
        $this->items = new Items();
        $this->setup = new Setup();
    }

    /**
     * Frontpages
     */
    // Homepage action
    public function showHome($routes = '')
    {
        $args = $this->getDefaultArray();
        echo $this->twig->render('home.twig', $args);
    }

    public function showAccountRecovery($routes = '')
    {
        $args = array_merge($this->getDefaultArray(), ['ignoreSignupButton' => true]);
        echo $this->twig->render('recovery.twig', $args);
    }

    /**
     * App
     */
    public function showApp($routes = '', $page = '', $list = '', $item = '')
    {
        /**
         * User/URL validation
         */
        //Redirect the user to home if not logged in
        if (!$this->user->isLoggedIn())
            header('location: ' . BASE_URL . '/home');

        //If no valid list ID is passed redirect to app
        if (($list != '' && !$this->collections->uidExists($list)) || ($page == 'list' && $list == '')) {
            header('location: ' . BASE_URL . '/app');
            exit();
        }

        //If no valid item ID is passed redirect to list (which should be valid because of prior check)
        if ($item != '' && !$this->items->uidExists($item, $list)) {
            header('location: ' . BASE_URL . '/app/list/' . $list);
            exit();
        }

        /**
         * Generate variables to be passed to the page
         */
        //Generate a recovery key if not yet set and pass it to the page
        if (!$this->user->getRecoveryKeyIsSet()) {
            $recoveryKey = $this->user->getNewRecoveryKey();
        } else {
            $recoveryKey = '';
        }

        //Dummy variables
        // $collections = array('Foo-1', 'Bar-2', 'Baz-3', 'Dummy-list-from-PageController');
        // $items = array('Do something', 'Read something', 'Go sporting', 'Dummy item from PageController');
        // $listName = 'Foo-list from PageController';
        // $itemName = 'Foo-item from PageController';
        //End dummy variables

        //Lists & items
        $collections = $this->collections->getAll();
        $items = $this->items->getAll($list);
        $listId = $list;
        $itemId = $item;
        $itemName = $this->items->getName($item);


        $args = array_merge(
            $this->getDefaultArray(),
            [
                'page' => $page,
                'listId' => $listId,
                'listName' => $items['listName'],
                'recoverykey' => $recoveryKey,
                'collections' => $collections,
                'items' => $items['items'],
                'itemId' => $itemId,
                'itemName' => $itemName
            ]
        );
        echo $this->twig->render('app.twig', $args);
    }

    /**
     * Helpers
     */
    public function showAdmin($routes = '')
    {
        $args = array_merge(
            $this->getDefaultArray(),
            [
                'dbConnectionOk' => $this->setup->checkDatabaseConnection(),
                'dbConnectionMessage' => $this->setup->getError(true),
                'dbVersionOk' => $this->setup->checkDbVersion(),
                'dbVersionMessage' => $this->setup->getError(false) ? $this->setup->getError(true) : 'Current version: ' . $this->setup->getCurrentVersion() . ', target version: ' . $this->setup->getTargetVersion(),
            ]
        );
        echo $this->twig->render('admin.twig', $args);
    }

    public function getAdminContent()
    {
        $args = array_merge(
            $this->getDefaultArray(),
            [
                'dbConnectionOk' => $this->setup->checkDatabaseConnection(),
                'dbConnectionMessage' => $this->setup->getError(true),
                'dbVersionOk' => $this->setup->checkDbVersion(),
                'dbVersionMessage' => $this->setup->getError(false) ? $this->setup->getError(true) : 'Current version: ' . $this->setup->getCurrentVersion() . ', target version: ' . $this->setup->getTargetVersion(),
            ]
        );
        return $this->twig->render('/templates/content/admin.twig', $args);
    }

    public function logout($routes = '')
    {
        header('Location: ' . BASE_URL . '/interfaces/logout.php');
    }

    /**
     * Templates
     */
    public function getTemplate_List($collectionUid)
    {
        $args = $this->items->getAll($collectionUid);
        return $this->twig->render('templates/app/list.twig', ['listName' => $args['listName'], 'items' => $args['items']]);
        // $this->items->getSubPage($collectionUid);
    }
    public function getTemplate_Items($collectionUid)
    {
        $args = $this->items->getAll($collectionUid);
        return $this->twig->render('templates/app/list/items.twig', ['listName' => $args['listName'], 'items' => $args['items']]);
    }

    /**
     * Parts
     */
    public function getPart_Collection($collectionUid, $collectionName = '', $collectionSortOrder = '')
    {
        if ($collectionName == '') {
            $collectionName = $this->collections->getName($collectionUid);
        }
        $args = array(
            'uid' => $collectionUid,
            'name' => $collectionName,
            'sortOrder' => $collectionSortOrder,
        );

        return $this->twig->render('parts/app/li/collection.twig', ['collection' => $args]);
    }
    public function getPart_Item($itemUid)
    {
        $itemName = $this->items->getName($itemUid);
        $args = array(
            'uid' => $itemUid,
            'name' => $itemName,
        );
        return $this->twig->render('parts/app/li/item.twig', ['item' => $args]);
    }

    public function getPart_PopupContent($itemType, $itemUid)
    {
        switch ($itemType) {
            case 'collections':
                $content = $this->twig->render('parts/menu/popup/collection.twig', ['uid' => $itemUid]);
                break;

            case 'list':
            case 'list-completed':
                $content = $this->twig->render('parts/menu/popup/item.twig', ['uid' => $itemUid]);
                break;

            default:
                $content = "Unknown menu";
                break;
        }
        return $content;
    }

    /**
     * Error pages
     */
    public function showErrorPage($errorCode, $message)
    {
        if (SHOW_ERROR_MESSAGE) {
            $msg = $message;
        } else {
            $msg = "";
        }
        // $args = [];
        $args = array_merge($this->getDefaultArray(), ['errormessage' => $msg]);
        echo $this->twig->render($errorCode . '.twig', $args);
    }

    /**
     * Private functions
     */
    private function getDefaultArray()
    {
        return ['baseUrl' => BASE_URL, 'userIsLoggedIn' => $this->user->isLoggedIn()];
        // return ['userIsLoggedIn' => true];
    }
}

<?php

use que\common\manager\Manager;
use que\common\structure\Page;
use que\template\Composer;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/16/2020
 * Time: 1:16 AM
 */

class View extends Manager implements Page
{

    /**
     * This method will run when the module is accessed via GET request
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @note Que will run this method for you automatically
     */
    public function onLoad(array $uri_args): void
    {
        // TODO: Implement onLoad() method.
        current_route()->setTitle('Welcome Que');
//        \que\support\Config::set('auth.jwt.algo',);
//        $users = $this->db()->select('user as users', '*, name AS names, id AS add', [
//            'AND' => [
//                'name' => ['Ngozi', 'Emenike', 'Samuel'],
//                'id[BETWEEN]' => [1, 400],
////                'name[like]' => '%new%'
//            ]
//        ]);
//
//        debug_print([
//            $users->getQueryResponseWithModel('custom'),
//            $users->isSuccessful() ? 'true' : 'false',
//            $users->getQueryString(),
//            $users->getQueryError(),
//            $users->getQueryErrorCode()
//        ]);

//        $count = db()->avg('user', 'id', [
//            'AND' => [
//                'user.name[like]' => '%new%'
//            ]
//        ]);

//        debug_print([
//            $count->getQueryResponse(),
//            $count->getQueryString(),
//            $count->getQueryError(),
//            $count->getQueryErrorCode()
//        ]);

//        $delete = db()->delete('user', [
//            'AND' => [
//                'id' => 113
//            ]
//        ]);
//
//        debug_print([
//            $delete->isSuccessful() ? 'true' : 'false',
//            $delete->getQueryResponse(),
//            $delete->getLastInsertID(),
//            $delete->getAffectedRows(),
//            $delete->getQueryString(),
//            $delete->getQueryError(),
//            $delete->getQueryErrorCode()
//        ]);

        $update = db()->update('user', [
            'name' => 'Newton Job'
        ], [
            'AND' => [
                'id' => [30, 111]
            ]
        ]);

        debug_print([
            $update->isSuccessful() ? 'true' : 'false',
            $update->getQueryResponse(),
            $update->getLastInsertID(),
            $update->getAffectedRows(),
            $update->getQueryString(),
            $update->getQueryError(),
            $update->getQueryErrorCode()
        ]);

//        $insert = db()->insert('user', [
//            'name' => 'Wisdom Samuel Emenike'
//        ]);
//
//        debug_print([
//            $insert->isSuccessful() ? 'true' : 'false',
//            $insert->getQueryResponse(),
//            $insert->getLastInsertID(),
//            $insert->getAffectedRows(),
//            $insert->getQueryString(),
//            $insert->getQueryError(),
//            $insert->getQueryErrorCode()
//        ]);

        $this->composer()->data([
            'hello' => 'Hello world'
        ]);

    }

    /**
     * This method will run last, to finalize your Composer and render your template
     * @param Composer $composer
     * @note Que will run this method for you automatically
     */
    public function setTemplate(Composer $composer): void
    {
        // TODO: Implement setTemplate() method.
        $composer->setTmpFileName('module/view.html');
        $composer->prepare()->renderWithSmarty();
    }
}
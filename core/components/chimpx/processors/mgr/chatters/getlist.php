<?php
/**
 * chimpx
 *
 * Copyright 2011 by Romain Tripault <romain@meltingmedia.net>
 *
 * chimpx is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * chimpx is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * chimpx; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package chimpx
 */
/**
 * Get a list of Items
 *
 * @package chimpx
 * @subpackage processors
 */

$isLimit = !empty($_REQUEST['limit']);
$start = $modx->getOption('start',$_REQUEST,0);
$limit = $modx->getOption('limit',$_REQUEST,20);


$api = new MCAPI($modx->getOption('chimpx_apikey'));

$chatters = $api->chimpChatter();

if ($api->errorCode){
    //echo "Unable to Pull list of Campaign!";
    //echo "\n\tCode=".$api->errorCode;
    //echo "\n\tMsg=".$api->errorMessage."\n";
    return 'error';
} else {

    $count = $chatters['total'];

    $list = array();
    foreach ($chatters['data'] as $chatter) {

        $list[] = $chatter;
    }
    return $this->outputArray($list,$count);
}